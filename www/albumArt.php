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

// grabs albumn art given an ID

require_once __DIR__.'/../VSE/keyStore.class.php';
require_once __DIR__.'/../httpResponse.class.php';

$id = &$_REQUEST['id'];

$k = new keyStore('albumArt');
$albumArt = $k->get($id);

$response = new httpResponse();
$response->load_string($albumArt);
$response->mimetype = httpResponse::mimetype('jpg');

if (!$albumArt)
	$response->status = 404;

$response->inline = true;

// the client can cache this file forever!
$response->persistent = true;
$response->serve();
