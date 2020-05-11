<?php
/**
 * Created by PhpStorm.
 * User: Nir Simionovich
 * Date: 3/13/14
 * Time: 2:36 PM
 */

require 'vendor/autoload.php';

/* First, we need to initiate our XML-RPC endpoint */
$client = new IXR_Client('http://localhost/ixr-xmlrpc/examples/xmlrpc-server.php');

/*
 * A Simple XML-RPC test without any arguments
 * Prints the current time, according to our web server
 */
$client->query('test.getTime');
print $client->getResponse();

/*
 * Now, let's include some error checking in here
 */
if (!$client->query('test.getTime')) {
    die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
}
print $client->getResponse();

/*
 * Now, let's send some arguments as well
 */
if (!$client->query('test.add', 4, 5)) {
    die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
}
print $client->getResponse();

/*
 * Now, let's make it better
 */
if (!$client->query('test.addArray', array(3, 5, 7))) {
    die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
}
print $client->getResponse();

?>