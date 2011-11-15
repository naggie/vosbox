<?php
/*
    This file is part of Vosbox.

    Vosbox is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Vosbox is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Vosbox.  If not, see <http://www.gnu.org/licenses/>.

    Vosbox copyright Callan Bryant 2011 <callan.bryant@gmail.com>
*/

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
