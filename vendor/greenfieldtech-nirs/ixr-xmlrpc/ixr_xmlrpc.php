<?php

/* 
   IXR - The Inutio XML-RPC Library - (c) Incutio Ltd 2002-2005

   Version 1.7.1.1 (beta) - Sebastian Berm, 28th Januari 2007
   
   Version 1.7.1 (beta) - Jason Stirk, 26th May 2005
   Site:    http://blog.griffin.homelinux.org/projects/xmlrpc/
   Manual:  http://blog.griffin.homelinux.org/projects/xmlrpc/

   From:   Version 1.7 (beta) - Simon Willison, 23rd May 2005
   Site:   http://scripts.incutio.com/xmlrpc/
   Manual: http://scripts.incutio.com/xmlrpc/manual.php
   Made available under the Artistic License: http://www.opensource.org/licenses/artistic-license.php
   
   Changed in 1.7.1.1 (Sebastian Berm):
   * Fixed CURL SSL verify host problem.
   
   Changed in 1.7.1 (Jason Stirk):
   * Merged IXR_ class into this file
   * Merged IXR_ClassServer class into this file
   * Added $wait parameter to IXR_Server class constructor

   Changed in 1.7:
   * Fixed bug where whitespace between elements accumulated in _currentTagContents
   * Fixed bug in IXR_Date where Unix timestamps were parsed incorrectly
   * Fixed bug with request longer than 4096 bytes (thanks Ryuji Tamagawa)
   * Struct keys now have XML entities escaped (thanks Andrew Collington)
   Merged changes from WordPress (thanks, guys):
   * Trim before base64_decode: http://trac.wordpress.org/ticket/654
   * Added optional timeout parameter to IXR_Client: http://trac.wordpress.org/changeset/1673
   * Added support for class object callbacks: http://trac.wordpress.org/ticket/708
     (thanks Owen Winkler)
   
   Previous version was 1.61, released 11th July 2003

*/


class IXR_Value {
    var $data;
    var $type;
    function __construct ($data, $type = false) {
        $this->data = $data;
        if (!$type) {
            $type = $this->calculateType();
        }
        $this->type = $type;
        if ($type == 'struct') {
            /* Turn all the values in the array in to new IXR_Value objects */
            foreach ($this->data as $key => $value) {
                $this->data[$key] = new IXR_Value($value);
            }
        }
        if ($type == 'array') {
            for ($i = 0, $j = count($this->data); $i < $j; $i++) {
                $this->data[$i] = new IXR_Value($this->data[$i]);
            }
        }
    }
    function calculateType() {
        if ($this->data === true || $this->data === false) {
            return 'boolean';
        }
        if (is_integer($this->data)) {
            return 'int';
        }
        if (is_double($this->data)) {
            return 'double';
        }
        // Deal with IXR object types base64 and date
        if (is_object($this->data) && is_a($this->data, 'IXR_Date')) {
            return 'date';
        }
        if (is_object($this->data) && is_a($this->data, 'IXR_Base64')) {
            return 'base64';
        }
		// ChT: FozenDate, FrozenTime, FrozenDateTime
		if (is_object($this->data) && is_a($this->data, '\Cake\I18n\FrozenDate')) {
			$this->data = [
				'year' => $this->data->year,
				'month' => $this->data->month,
				'day' => $this->data->day,
			];
			return 'struct';
		}
		if (is_object($this->data) && is_a($this->data, '\Cake\I18n\FrozenTime')) {
			$this->data = [
				'hour' => $this->data->hour,
				'minute' => $this->data->minute,
				'second' => $this->data->second,
			];
			return 'struct';
		}
		if (is_object($this->data) && is_a($this->data, '\App\I18n\FrozenDateTime')) {
			$this->data = [
				'year' => $this->data->year,
				'month' => $this->data->month,
				'day' => $this->data->day,
				'hour' => $this->data->hour,
				'minute' => $this->data->minute,
				'second' => $this->data->second,
			];
			return 'struct';
		}
		
        // If it is a normal PHP object convert it in to a struct
        if (is_object($this->data)) {
            $this->data = get_object_vars($this->data);
            return 'struct';
        }
        if (!is_array($this->data)) {
            return 'string';
        }
        /* We have an array - is it an array or a struct ? */
        if ($this->isStruct($this->data)) {
            return 'struct';
        } else {
            return 'array';
        }
    }
    function getXml() {
        /* Return XML for this value */
        switch ($this->type) {
            case 'boolean':
                return '<boolean>'.(($this->data) ? '1' : '0').'</boolean>';

			case 'int':
                return '<int>'.$this->data.'</int>';
				
            case 'double':
                return '<double>'.$this->data.'</double>';
				
            case 'string':
                return '<string>'.htmlspecialchars($this->data).'</string>';
				
            case 'array':
                $return = '<array><data>'."\n";
                foreach ($this->data as $item) {
                    $return .= '  <value>'.$item->getXml()."</value>\n";
                }
                $return .= '</data></array>';
                return $return;
				
            case 'struct':
                $return = '<struct>'."\n";
                foreach ($this->data as $name => $value) {
                    $name = htmlspecialchars($name);
                    $return .= "  <member><name>$name</name><value>";
                    $return .= $value->getXml()."</value></member>\n";
                }
                $return .= '</struct>';
                return $return;
				
            case 'date':
            case 'base64':
                return $this->data->getXml();				
        }
        return false;
    }
    function isStruct($array) {
        /* Nasty function to check if an array is a struct or not */
        $expected = 0;
        foreach ($array as $key => $value) {
            if ((string)$key != (string)$expected) {
                return true;
            }
            $expected++;
        }
        return false;
    }
}


class IXR_Message {
    var $message;
    var $messageType;  // methodCall / methodResponse / fault
    var $faultCode;
    var $faultString;
    var $methodName;
    var $params;
    // Current variable stacks
    var $_arraystructs = array();   // The stack used to keep track of the current array/struct
    var $_arraystructstypes = array(); // Stack keeping track of if things are structs or array
    var $_currentStructName = array();  // A stack as well
    var $_param;
    var $_value;
    var $_currentTag;
    var $_currentTagContents;
    // The XML parser
    var $_parser;
    function __construct ($message) {
        $this->message = $message;
    }
    function parse() {
        // first remove the XML declaration
        $this->message = preg_replace('/<\?xml(.*)?\?'.'>/', '', $this->message);
        if (trim($this->message) == '') {
            return false;
        }
        $this->_parser = xml_parser_create();
        // Set XML parser to take the case of tags in to account
        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, false);
        // Set XML parser callback functions
        xml_set_object($this->_parser, $this);
        xml_set_element_handler($this->_parser, 'tag_open', 'tag_close');
        xml_set_character_data_handler($this->_parser, 'cdata');
        if (!xml_parse($this->_parser, $this->message)) {
            /* die(sprintf('XML error: %s at line %d',
                xml_error_string(xml_get_error_code($this->_parser)),
                xml_get_current_line_number($this->_parser))); */
            return false;
        }
        xml_parser_free($this->_parser);
        // Grab the error messages, if any
        if ($this->messageType == 'fault') {
            $this->faultCode = $this->params[0]['faultCode'];
            $this->faultString = $this->params[0]['faultString'];
        }
        return true;
    }
    function tag_open($parser, $tag, $attr) {
        $this->_currentTagContents = '';
        $this->currentTag = $tag;
        switch($tag) {
            case 'methodCall':
            case 'methodResponse':
            case 'fault':
                $this->messageType = $tag;
                break;
            /* Deal with stacks of arrays and structs */
            case 'data':    // data is to all intents and puposes more interesting than array
                $this->_arraystructstypes[] = 'array';
                $this->_arraystructs[] = array();
                break;
            case 'struct':
                $this->_arraystructstypes[] = 'struct';
                $this->_arraystructs[] = array();
                break;
        }
    }
    function cdata($parser, $cdata) {
        $this->_currentTagContents .= $cdata;
    }
    function tag_close($parser, $tag) {
        $valueFlag = false;
        switch($tag) {
            case 'int':
            case 'i4':
                $value = (int)trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'double':
                $value = (double)trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'string':
                $value = $this->_currentTagContents;
                $valueFlag = true;
                break;
            case 'dateTime.iso8601':
                $value = new IXR_Date(trim($this->_currentTagContents));
                // $value = $iso->getTimestamp();
                $valueFlag = true;
                break;
            case 'value':
                // "If no type is indicated, the type is string."
                if (trim($this->_currentTagContents) != '') {
                    $value = (string)$this->_currentTagContents;
                    $valueFlag = true;
                }
                break;
            case 'boolean':
                $value = (boolean)trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'base64':
                $value = base64_decode(trim($this->_currentTagContents));
                $valueFlag = true;
                break;
            /* Deal with stacks of arrays and structs */
            case 'data':
            case 'struct':
                $value = array_pop($this->_arraystructs);
                array_pop($this->_arraystructstypes);
                $valueFlag = true;
                break;
            case 'member':
                array_pop($this->_currentStructName);
                break;
            case 'name':
                $this->_currentStructName[] = trim($this->_currentTagContents);
                break;
            case 'methodName':
                $this->methodName = trim($this->_currentTagContents);
                break;
        }
        if ($valueFlag) {
            if (count($this->_arraystructs) > 0) {
                // Add value to struct or array
                if ($this->_arraystructstypes[count($this->_arraystructstypes)-1] == 'struct') {
                    // Add to struct
                    $this->_arraystructs[count($this->_arraystructs)-1][$this->_currentStructName[count($this->_currentStructName)-1]] = $value;
                } else {
                    // Add to array
                    $this->_arraystructs[count($this->_arraystructs)-1][] = $value;
                }
            } else {
                // Just add as a paramater
                $this->params[] = $value;
            }
        }
        $this->_currentTagContents = '';
    }       
}


class IXR_Server {
    var $data;
    var $callbacks = array();
    var $message;
    var $capabilities;
    function __construct($callbacks = false, $data = false, $wait = false) {
        $this->setCapabilities();
        if ($callbacks) {
            $this->callbacks = $callbacks;
        }
        $this->setCallbacks();
		if (!$wait) {
			$xml = $this->serve($data);
			$this->output($xml);
		}
    }
    function serve($data = false) {
        if (!$data) {
			$data = file_get_contents("php://input");
            if (!$data) {
               die('XML-RPC server accepts POST requests only.');
            }
        }
        $this->message = new IXR_Message($data);
        if (!$this->message->parse()) {
            $this->error(-32700, 'parse error. not well formed');
        }
        if ($this->message->messageType != 'methodCall') {
            $this->error(-32600, 'server error. invalid xml-rpc. not conforming to spec. Request must be a methodCall');
        }
        $result = $this->call($this->message->methodName, $this->message->params);
        // Is the result an error?
        if (is_a($result, 'IXR_Error')) {
            $this->error($result);
        }
        // Encode the result
        $r = new IXR_Value($result);
        $resultxml = $r->getXml();
        // Create the XML
        $xml = <<<EOD
<methodResponse>
  <params>
    <param>
      <value>
        $resultxml
      </value>
    </param>
  </params>
</methodResponse>

EOD;
        // Send it
        // $this->output($xml);
		// Instead of echoing the result we return it and leave it to the caller
		// what to do with it. This way we can use it in tests
		return '<?xml version="1.0"?>' . "\n" . $xml;
    }
    function call($methodname, $args) {
        if (!$this->hasMethod($methodname)) {
            return new IXR_Error(-32601, 'server error. requested method '.
                $methodname.' does not exist.');
        }
        $method = $this->callbacks[$methodname];
        // Perform the callback and send the response
        if (is_array($args) && count($args) == 1) {
            // If only one paramater just send that instead of the whole array
            $args = $args[0];
        }
        // Are we dealing with a function or a method?
        if (is_string($method) && substr($method, 0, 5) == 'this:') {
            // It's a class method - check it exists
            $method = substr($method, 5);
            if (!method_exists($this, $method)) {
                return new IXR_Error(-32601, 'server error. requested class method "'.
                    $method.'" does not exist.');
            }
            // Call the method
            $result = $this->$method($args);
        } else {
            // It's a function - does it exist?
            if (is_array($method)) {
                if (!method_exists($method[0], $method[1])) {
                    return new IXR_Error(-32601, 'server error. requested object method "'.
                        $method[1].'" does not exist.');
                }
            } else if (!function_exists($method)) {
                return new IXR_Error(-32601, 'server error. requested function "'.
                    $method.'" does not exist.');
            }
            // Call the function
            $result = call_user_func($method, $args);
        }
        return $result;
    }

    function error($error, $message = false) {
        // Accepts either an error object or an error code and message
        if ($message && !is_object($error)) {
            $error = new IXR_Error($error, $message);
        }
        $this->output($error->getXml());
    }
    function output($xml) {
        $xml = '<?xml version="1.0"?>'."\n".$xml;
        $length = strlen($xml);
        header('Connection: close');
        header('Content-Length: '.$length);
        header('Content-Type: text/xml');
        header('Date: '.date('r'));
        echo $xml;
        exit;
    }
    function hasMethod($method) {
        return in_array($method, array_keys($this->callbacks));
    }
    function setCapabilities() {
        // Initialises capabilities array
        $this->capabilities = array(
            'xmlrpc' => array(
                'specUrl' => 'http://www.xmlrpc.com/spec',
                'specVersion' => 1
            ),
            'faults_interop' => array(
                'specUrl' => 'http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php',
                'specVersion' => 20010516
            ),
            'system.multicall' => array(
                'specUrl' => 'http://www.xmlrpc.com/discuss/msgReader$1208',
                'specVersion' => 1
            ),
        );   
    }
    function getCapabilities($args) {
        return $this->capabilities;
    }
    function setCallbacks() {
        $this->callbacks['system.getCapabilities'] = 'this:getCapabilities';
        $this->callbacks['system.listMethods'] = 'this:listMethods';
        $this->callbacks['system.multicall'] = 'this:multiCall';
    }
    function listMethods($args) {
        // Returns a list of methods - uses array_reverse to ensure user defined
        // methods are listed before server defined methods
        return array_reverse(array_keys($this->callbacks));
    }
    function multiCall($methodcalls) {
        // See http://www.xmlrpc.com/discuss/msgReader$1208
        $return = array();
        foreach ($methodcalls as $call) {
            $method = $call['methodName'];
            $params = $call['params'];
            if ($method == 'system.multicall') {
                $result = new IXR_Error(-32600, 'Recursive calls to system.multicall are forbidden');
            } else {
                $result = $this->call($method, $params);
            }
            if (is_a($result, 'IXR_Error')) {
                $return[] = array(
                    'faultCode' => $result->code,
                    'faultString' => $result->message
                );
            } else {
                $return[] = array($result);
            }
        }
        return $return;
    }
}

class IXR_Request {
    var $method;
    var $args;
    var $xml;
    function __construct($method, $args) {
        $this->method = $method;
        $this->args = $args;
        $this->xml = <<<EOD
<?xml version="1.0"?>
<methodCall>
<methodName>{$this->method}</methodName>
<params>

EOD;
        foreach ($this->args as $arg) {
            $this->xml .= '<param><value>';
            $v = new IXR_Value($arg);
            $this->xml .= $v->getXml();
            $this->xml .= "</value></param>\n";
        }
        $this->xml .= '</params></methodCall>';
    }
    function getLength() {
        return strlen($this->xml);
    }
    function getXml() {
        return $this->xml;
    }
}


class IXR_Client {
    var $server;
    var $port;
    var $path;
    var $useragent;
    var $response;
    var $message = false;
    var $debug = false;
    var $timeout;
    // Storage place for an error message
    var $error = false;
    function __construct($server, $path = false, $port = 80, $timeout = false) {
        if (!$path) {
            // Assume we have been given a URL instead
            $bits = parse_url($server);
            $this->server = $bits['host'];
            $this->port = isset($bits['port']) ? $bits['port'] : 80;
            $this->path = isset($bits['path']) ? $bits['path'] : '/';
            // Make absolutely sure we have a path
            if (!$this->path) {
                $this->path = '/';
            }
        } else {
            $this->server = $server;
            $this->path = $path;
            $this->port = $port;
        }
        $this->useragent = 'The Incutio XML-RPC PHP Library';
        $this->timeout = $timeout;
    }
    function query() {
        $args = func_get_args();
        $method = array_shift($args);
        $request = new IXR_Request($method, $args);
        $length = $request->getLength();
        $xml = $request->getXml();
        $r = "\r\n";
        $request  = "POST {$this->path} HTTP/1.0$r";
        $request .= "Host: {$this->server}$r";
        $request .= "Content-Type: text/xml$r";
        $request .= "User-Agent: {$this->useragent}$r";
        $request .= "Content-length: {$length}$r$r";
        $request .= $xml;
        // Now send the request
        if ($this->debug) {
            echo '<pre>'.htmlspecialchars($request)."\n</pre>\n\n";
        }
        if ($this->timeout) {
            $fp = @fsockopen($this->server, $this->port, $errno, $errstr, $this->timeout);
        } else {
            $fp = @fsockopen($this->server, $this->port, $errno, $errstr);
        }
        if (!$fp) {
            $this->error = new IXR_Error(-32300, "transport error - could not open socket: $errno $errstr");
            return false;
        }
        fputs($fp, $request);
        $contents = '';
        $gotFirstLine = false;
        $gettingHeaders = true;
        while (!feof($fp)) {
            $line = fgets($fp, 4096);
            if (!$gotFirstLine) {
                // Check line for '200'
                if (strstr($line, '200') === false) {
                    $this->error = new IXR_Error(-32300, 'transport error - HTTP status code was not 200');
                    return false;
                }
                $gotFirstLine = true;
            }
            if (trim($line) == '') {
                $gettingHeaders = false;
            }
            if (!$gettingHeaders) {
                $contents .= trim($line);
            }
        }
        if ($this->debug) {
            echo '<pre>'.htmlspecialchars($contents)."\n</pre>\n\n";
        }
        // Now parse what we've got back
        $this->message = new IXR_Message($contents);
        if (!$this->message->parse()) {
            // XML error
            $this->error = new IXR_Error(-32700, 'parse error. not well formed');
            return false;
        }
        // Is the message a fault?
        if ($this->message->messageType == 'fault') {
            $this->error = new IXR_Error($this->message->faultCode, $this->message->faultString);
            return false;
        }
        // Message must be OK
        return true;
    }
    function getResponse() {
        // methodResponses can only have one param - return that
        return $this->message->params[0];
    }
    function isError() {
        return (is_object($this->error));
    }
    function getErrorCode() {
        return $this->error->code;
    }
    function getErrorMessage() {
        return $this->error->message;
    }
}


class IXR_Error {
    var $code;
    var $message;
    function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
    function getXml() {
        $xml = <<<EOD
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>{$this->code}</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>{$this->message}</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse> 

EOD;
        return $xml;
    }
}


class IXR_Date {
    var $year;
    var $month;
    var $day;
    var $hour;
    var $minute;
    var $second;
    function __construct($time) {
        // $time can be a PHP timestamp or an ISO one
        if (is_numeric($time)) {
            $this->parseTimestamp($time);
        } else {
            $this->parseIso($time);
        }
    }
    function parseTimestamp($timestamp) {
        $this->year = date('Y', $timestamp);
        $this->month = date('m', $timestamp);
        $this->day = date('d', $timestamp);
        $this->hour = date('H', $timestamp);
        $this->minute = date('i', $timestamp);
        $this->second = date('s', $timestamp);
    }
    function parseIso($iso) {
        $this->year = substr($iso, 0, 4);
        $this->month = substr($iso, 4, 2); 
        $this->day = substr($iso, 6, 2);
        $this->hour = substr($iso, 9, 2);
        $this->minute = substr($iso, 12, 2);
        $this->second = substr($iso, 15, 2);
    }
    function getIso() {
        return $this->year.$this->month.$this->day.'T'.$this->hour.':'.$this->minute.':'.$this->second;
    }
    function getXml() {
        return '<dateTime.iso8601>'.$this->getIso().'</dateTime.iso8601>';
    }
    function getTimestamp() {
        return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
    }
}


class IXR_Base64 {
    var $data;
    function __construct($data) {
        $this->data = $data;
    }
    function getXml() {
        return '<base64>'.base64_encode($this->data).'</base64>';
    }
}


class IXR_IntrospectionServer extends IXR_Server {
    var $signatures;
    var $help;
    function __construct() {
		parent::__construct();
		
        $this->setCallbacks();
        $this->setCapabilities();
        $this->capabilities['introspection'] = array(
            'specUrl' => 'http://xmlrpc.usefulinc.com/doc/reserved.html',
            'specVersion' => 1
        );
        $this->addCallback(
            'system.methodSignature', 
            'this:methodSignature', 
            array('array', 'string'), 
            'Returns an array describing the return type and required parameters of a method'
        );
        $this->addCallback(
            'system.getCapabilities', 
            'this:getCapabilities', 
            array('struct'), 
            'Returns a struct describing the XML-RPC specifications supported by this server'
        );
        $this->addCallback(
            'system.listMethods', 
            'this:listMethods', 
            array('array'), 
            'Returns an array of available methods on this server'
        );
        $this->addCallback(
            'system.methodHelp', 
            'this:methodHelp', 
            array('string', 'string'), 
            'Returns a documentation string for the specified method'
        );
    }
    function addCallback($method, $callback, $args, $help) {
        $this->callbacks[$method] = $callback;
        $this->signatures[$method] = $args;
        $this->help[$method] = $help;
    }
    function call($methodname, $args) {
        // Make sure it's in an array
        if ($args && !is_array($args)) {
            $args = array($args);
        }
        // Over-rides default call method, adds signature check
        if (!$this->hasMethod($methodname)) {
            return new IXR_Error(-32601, 'server error. requested method "'.$this->message->methodName.'" not specified.');
        }
        $method = $this->callbacks[$methodname];
        $signature = $this->signatures[$methodname];
        $returnType = array_shift($signature);
        // Check the number of arguments
        if (count($args) != count($signature)) {
            return new IXR_Error(-32602, 'server error. wrong number of method parameters');
        }
        // Check the argument types
        $ok = true;
        $argsbackup = $args;
        for ($i = 0, $j = count($args); $i < $j; $i++) {
            $arg = array_shift($args);
            $type = array_shift($signature);
            switch ($type) {
                case 'int':
                case 'i4':
                    if (is_array($arg) || !is_int($arg)) {
                        $ok = false;
                    }
                    break;
                case 'base64':
                case 'string':
                    if (!is_string($arg)) {
                        $ok = false;
                    }
                    break;
                case 'boolean':
                    if ($arg !== false && $arg !== true) {
                        $ok = false;
                    }
                    break;
                case 'float':
                case 'double':
                    if (!is_float($arg)) {
                        $ok = false;
                    }
                    break;
                case 'date':
                case 'dateTime.iso8601':
                    if (!is_a($arg, 'IXR_Date')) {
                        $ok = false;
                    }
                    break;
            }
            if (!$ok) {
                return new IXR_Error(-32602, 'server error. invalid method parameters');
            }
        }
        // It passed the test - run the "real" method call
        return parent::call($methodname, $argsbackup);
    }
    function methodSignature($method) {
        if (!$this->hasMethod($method)) {
            return new IXR_Error(-32601, 'server error. requested method "'.$method.'" not specified.');
        }
        // We should be returning an array of types
        $types = $this->signatures[$method];
        $return = array();
        foreach ($types as $type) {
            switch ($type) {
                case 'string':
                    $return[] = 'string';
                    break;
                case 'int':
                case 'i4':
                    $return[] = 42;
                    break;
                case 'double':
                    $return[] = 3.1415;
                    break;
                case 'dateTime.iso8601':
                    $return[] = new IXR_Date(time());
                    break;
                case 'boolean':
                    $return[] = true;
                    break;
                case 'base64':
                    $return[] = new IXR_Base64('base64');
                    break;
                case 'array':
                    $return[] = array('array');
                    break;
                case 'struct':
                    $return[] = array('struct' => 'struct');
                    break;
            }
        }
        return $return;
    }
    function methodHelp($method) {
        return $this->help[$method];
    }
}


class IXR_ClientMulticall extends IXR_Client {
    var $calls = array();
    function __construct($server, $path = false, $port = 80) {
        parent::__construct($server, $path, $port);
        $this->useragent = 'The Incutio XML-RPC PHP Library (multicall client)';
    }
    function addCall() {
        $args = func_get_args();
        $methodName = array_shift($args);
        $struct = array(
            'methodName' => $methodName,
            'params' => $args
        );
        $this->calls[] = $struct;
    }
    function query() {
        // Prepare multicall, then call the parent::query() method
        return parent::query('system.multicall', $this->calls);
    }
}

/**
 * Client for communicating with a XML-RPC Server over HTTPS.
 * @author Jason Stirk <jstirk@gmm.com.au> (@link http://blog.griffin.homelinux.org/projects/xmlrpc/)
 * @version 0.2.1 28th January 2007
 * @copyright (c) 2004-2005 Jason Stirk
 *
 * Patched by Sebastian Berm
 */
class IXR_ClientSSL extends IXR_Client
{

/**
 * Filename of the SSL Client Certificate
 * @access private
 * @since 0.1.0
 * @var string
 */
var $_certFile;

/**
 * Filename of the SSL CA Certificate
 * @access private
 * @since 0.1.0
 * @var string
 */
var $_caFile;

/**
 * Filename of the SSL Client Private Key
 * @access private
 * @since 0.1.0
 * @var string
 */
var $_keyFile;

/**
 * Passphrase to unlock the private key
 * @access private
 * @since 0.1.0
 * @var string
 */
var $_passphrase;

/**
 * Constructor
 * @param string $server URL of the Server to connect to
 * @since 0.1.0
 */
function __construct($server, $path = false, $port = 443, $timeout = false) {
	parent::__construct($server, $path, $port);
	$this->useragent = 'The Incutio XML-RPC PHP Library for SSL';

	//Set class fields
	$this->_certFile=false;
	$this->_caFile=false;
	$this->_keyFile=false;
	$this->_passphrase='';

	//Since 23Jun2004 (0.1.2) - Made timeout a class field
	//and changed default from 5s to 15s
	if (!$timeout)
		{
		$this->timeout=15;
		}
		else
		{
		$this->timeout=$timeout;
		}
}

/**
 * Set the client side certificates to communicate with the server.
 * @since 0.1.0
 * @param string $certificateFile Filename of the client side certificate to use
 * @param string $keyFile Filename of the client side certificate's private key
 * @param string $keyPhrase Passphrase to unlock the private key
 */
function setCertificate($certificateFile, $keyFile, $keyPhrase='') {
	//Check the files all exist
	if (is_file($certificateFile))
		{
		$this->_certFile=$certificateFile;
		}
		else
		{
		die('Could not open certificate: ' . $certificateFile);
		}
	
	if (is_file($keyFile))
		{
		$this->_keyFile=$keyFile;
		}
		else
		{
		die('Could not open private key: ' . $keyFile);
		}

	$this->_passphrase=(string)$keyPhrase;
}

function setCACertificate($caFile)
	{
	if (is_file($caFile))
		{
		$this->_caFile=$caFile;
		}
		else
		{
		die('Could not open CA certificate: ' . $caFile);
		}
	}

/**
 * Sets the connection timeout (in seconds)
 * @param int $newTimeOut Timeout in seconds
 * @returns void
 * @since 0.1.2
 */
function setTimeOut($newTimeOut)
	{
	$this->timeout=(int)$newTimeOut;
	}

/**
 * Returns the connection timeout (in seconds)
 * @returns int
 * @since 0.1.2
 */
function getTimeOut()
	{
	return $this->timeout;
	}

/**
 * Set the query to send to the XML-RPC Server
 * @since 0.1.0
 */
function query()
	{
        $args = func_get_args();
        $method = array_shift($args);
        $request = new IXR_Request($method, $args);
        $length = $request->getLength();
        $xml = $request->getXml();

        if ($this->debug) {
            echo '<pre>'.htmlspecialchars($xml)."\n</pre>\n\n";
        }

		//This is where we deviate from the normal query()
		//Rather than open a normal sock, we will actually use the cURL
		//extensions to make the calls, and handle the SSL stuff.

		//Since 04Aug2004 (0.1.3) - Need to include the port (duh...)
		//Since 06Oct2004 (0.1.4) - Need to include the colon!!!
		//		(I swear I've fixed this before... ESP in live... But anyhu...)
		$curl=curl_init('https://' . $this->server . ':' . $this->port . $this->path);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		//Since 23Jun2004 (0.1.2) - Made timeout a class field
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);

		if ($this->debug)
			{
			curl_setopt($curl, CURLOPT_VERBOSE, 1);
			}

		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($curl, CURLOPT_PORT, $this->port);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
									"Content-Type: text/xml",
									"Content-length: {$length}"));

		//Process the SSL certificates, etc. to use
		if (!($this->_certFile === false))
		{
            //We have a certificate file set, so add these to the cURL handler
            curl_setopt($curl, CURLOPT_SSLCERT, $this->_certFile);
            curl_setopt($curl, CURLOPT_SSLKEY, $this->_keyFile);

            if ($this->debug)
                {
                echo "SSL Cert at : " . $this->_certFile . "\n";
                echo "SSL Key at : " . $this->_keyFile . "\n";
                }

            //See if we need to give a passphrase
            if (!($this->_passphrase === ''))
                {
                curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $this->_passphrase);
                }
        }
        if ($this->_caFile === false)
        {
            //Don't verify their certificate, as we don't have a CA to verify against
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        else
        {
            //Verify against a CA
            curl_setopt($curl, CURLOPT_CAINFO, $this->_caFile);
        }
			


		//Call cURL to do it's stuff and return us the content
		$contents=curl_exec($curl);
		curl_close($curl);		

		//Check for 200 Code in $contents
		if (!strstr($contents, '200 OK'))
			{
			//There was no "200 OK" returned - we failed
            $this->error = new IXR_Error(-32300, 'transport error - HTTP status code was not 200');
            return false;
			}

        if ($this->debug) {
            echo '<pre>'.htmlspecialchars($contents)."\n</pre>\n\n";
        }
        // Now parse what we've got back
		//Since 20Jun2004 (0.1.1) - We need to remove the headers first
		//Why I have only just found this, I will never know...
		//So, remove everything before the first <
		$contents=substr($contents,strpos($contents, '<'));

        $this->message = new IXR_Message($contents);
        if (!$this->message->parse()) {
            // XML error
            $this->error = new IXR_Error(-32700, 'parse error. not well formed');
            return false;
        }
        // Is the message a fault?
        if ($this->message->messageType == 'fault') {
            $this->error = new IXR_Error($this->message->faultCode, $this->message->faultString);
            return false;
        }

        // Message must be OK
        return true;
	}
}

/**
 * Extension of the {@link IXR_Server} class to easily wrap objects.
 * Class is designed to extend the existing XML-RPC server to allow the
 * presentation of methods from a variety of different objects via an
 * XML-RPC server.
 * It is intended to assist in organization of your XML-RPC methods by allowing
 * you to "write once" in your existing model classes and present them.
 * @author Jason Stirk <jstirk@gmm.com.au>
 * @version 1.0.1 19Apr2005 17:40 +0800
 * @copyright Copyright (c) 2005 Jason Stirk
 * @package IXR_Library
 */
class IXR_ClassServer extends IXR_Server
{
var $_objects;
var $_delim;

function __construct($delim=".", $wait=false)
	{
	parent::__construct(array(), false, $wait);
	$this->_delimiter=$delim;
	$this->_objects=array();
	}

function addMethod($rpcName, $functionName)
	{
	$this->callbacks[$rpcName]=$functionName;
	}

function registerObject($object, $methods, $prefix=null)
	{
	if (is_null($prefix))
		{
		$prefix=get_class($object);
		}
	$this->_objects[$prefix]=$object;
	
	//Add to our callbacks array
	foreach($methods as $method)
		{
		if (is_array($method))
			{
			$targetMethod=$method[0];
			$method=$method[1];
			}
			else
			{
			$targetMethod=$method;
			}
		$this->callbacks[$prefix . $this->_delimiter . $method]=array($prefix, $targetMethod);
		}
	}

    function call($methodname, $args) {
        if (!$this->hasMethod($methodname)) {
            return new IXR_Error(-32601, 'server error. requested method '.$methodname.' does not exist.');
        }
        $method = $this->callbacks[$methodname];
        // Perform the callback and send the response
        if (count($args) == 1) {
            // If only one paramater just send that instead of the whole array
            $args = $args[0];
        }
		// See if this method comes from one of our objects or maybe self
        if (is_array($method) || (substr($method, 0, 5) == 'this:')) {
			if (is_array($method))
				{
				$object=$this->_objects[$method[0]];
				$method=$method[1];
				}
				else
				{
				$object=$this;
            	$method = substr($method, 5);
				}

            // It's a class method - check it exists
            if (!method_exists($object, $method)) {
                return new IXR_Error(-32601, 'server error. requested class method "'.$method.'" does not exist.');
            }
            // Call the method
            $result = $object->$method($args);
        } else {
            // It's a function - does it exist?
            if (!function_exists($method)) {
                return new IXR_Error(-32601, 'server error. requested function "'.$method.'" does not exist.');
            }
            // Call the function
            $result = $method($args);
        }
        return $result;
    }
}

?>