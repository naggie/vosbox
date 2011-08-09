<?php
// TODO: re-enable persistent option 
$id = &$_REQUEST['id'];

$i = indexer::getInstance();

$albumArt = $i->getObject($id)->getAlbumArt();

$response = new httpResponse();

$response->load_string($albumArt);
$response->mimetype = httpResponse::mimetype('jpg');

if (!$albumArt)
	$response->status = 404;

$response->inline = true;

// the client can cache this file forever!
//$response->persistent = true;
$response->serve();
