<?php
$id = &$_REQUEST['id'];

$i = indexer::getInstance();

$albumArt = $i->getObject($id)->getAlbumArt();

$response = new httpResponse();
$response->load_string($albumArt);
$response->mimetype = httpResponse::mimetype('jpg');

// the client can cache this file forever!
$response->inline = true;
$response->persistent = true;
$response->serve();
