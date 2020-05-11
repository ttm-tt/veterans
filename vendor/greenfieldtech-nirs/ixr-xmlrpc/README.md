ixr-xmlrpc
==========
The Incutio XML-RPC library (IXR) is designed primarily for ease of use. It incorporates both client and server classes, and is designed to hide as much of the workings of XML-RPC from the user as possible. A key feature of the library is automatic type conversion from PHP types to XML-RPC types and vice versa. This should enable developers to write web services with very little knowledge of the underlying XML-RPC standard.

Don't however be fooled by it's simple surface. The library includes a wide variety of additional XML-RPC specifications and has all of the features required for serious web service implementations.

Features
========
1. A complete implementation of the XML-RPC specification

2. Written for PHP 4 in strict error reporting mode - no warnings or notices

3. Basic classes are designed to be usable in as little code as possible

4. Advanced classes extend the basic classes and provide additional features

5. Can be used with both Object Orientated and functional programming styles

6. Type conversions (PHP to XML-RPC and back again) are handled transparently

7. Built in support for system.getCapabilities

8. Built in support for system.listMethods

9. system.methodSignature and system.methodHelp are supported in an extension class

10. system.multicall is implemented in both the server and extended client classes

11. Follows the Specification for Fault Code Interoperability

Composer Install
================
1. Download the [`composer.phar`](https://getcomposer.org/composer.phar) executable or use the installer.

    ``` sh
    $ curl -sS https://getcomposer.org/installer | php
    $ cp composer.phar /usr/local/bin/composer
    $ chmod +x /usr/local/bin/composer
    ```

2. Add the following to your composer.json file:

```
        "greenfieldtech-nirs/ixr-xmlrpc": "dev-master"
```

So now, your composer.json should resemble the following:

```
{
    .
    .
    .
    "require":{
        "php":">=5.1.0",
        .
        .
        .
        "greenfieldtech-nirs/ixr-xmlrpc": "dev-master"
    },
    .
    .
}
```

3. Now, add the autoload to your PHP scripts and you are ready to go

```
require 'vendor/autoload.php';
```

Credits
=======
The following people had contributed to the creation of this Composer package:

   * Nir Simionovich, http://www.simionovich.com

The original creators of the IXR XML-RPC Library are:

   * Sebastian Berm

   * Jason Stirk

   * Simon Willison




