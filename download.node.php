<?php
$id = &$_REQUEST['id'];


$i = indexer::getInstance();

$filepath = $i->getObject($id)->path;

$response = new httpResponse();
$response->load_local_file($filepath);

// the client can cache this file forever!
$response->persistent = true;
$response->serve();
