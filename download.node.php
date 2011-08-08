<?php
$id = &$_REQUEST['id'];


$i = indexer::getInstance();

$filepath = $i->getObject($id)->getPath();

if (!file_exists($filepath) )
	$i->depreciateObject($id);

$response = new httpResponse();
$response->load_local_file($filepath);

// the client can cache this file forever!
$response->persistent = true;
$response->serve();
