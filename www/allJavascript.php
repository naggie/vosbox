<?php
// stiches all JS

// some js files must be included in an order.
// Jquery itself first, then library files, then
// the client itself.

$files = array();

// jquery
$files[] = __DIR__.'/jquery-1.6.2.min.js';

// jqueryui effects core
//$files[] = __DIR__.'/jquery-ui-1.8.16.custom.min.js';

// jquery plugins
$plugins = glob( __DIR__.'/jquery.*.js' );
$files = array_merge($files, $plugins);

// client
$files[] = __DIR__.'/client.js';

// gz compression
ob_start("ob_gzhandler");

// headers for mimetype and persistence
header('Content-Type:text/javascript');

// cache it client side for about 3 years, effectively ~infinite!
header('Cache-Control: public, maxage=99999999');

// depreciated old way for HTTP/1.0 (absolute, therefore flawed)
header('Expires: '.date('D, d M Y H:i:s', (time()+99999999)).' GMT');

foreach ($files as $file)
	readfile($file);
