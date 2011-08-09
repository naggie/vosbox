<?php
// TODO: re-enable persistent option 
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
