<?php
require_once __DIR__.'/../indexer.class.php';
require_once __DIR__.'/../audioFile.class.php';
require_once __DIR__.'/../httpResponse.class.php';

$id = &$_REQUEST['id'];

$i = indexer::getInstance();

@$filepath = $i->getObject($id)->getPath();

// remove the object from teh index.
// the http class will handle the 4oh4
if (!file_exists($filepath) )
	$i->depreciateObject($id);

$response = new httpResponse();
$response->load_local_file($filepath);

// the client can cache this file forever!
$response->persistent = true;
$response->serve();
