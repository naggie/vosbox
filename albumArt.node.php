<?php
$id = &$_REQUEST['id'];

$i = indexer::getInstance();

$albumArt = $i->getObject($id);//->getAlbumArt();


var_dump($albumArt);
die();

$response = new httpResponse();
$response->load_string($albumArt);
$response->mimetype = httpResponse('jpg');

// the client can cache this file forever!
$response->persistent = true;
$response->serve();
