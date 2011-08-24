<?php
require_once __DIR__.'/../indexer.class.php';
require_once __DIR__.'/../audioFile.class.php';
require_once __DIR__.'/../httpResponse.class.php';

$id = &$_REQUEST['id'];

$i = indexer::getInstance();
$response = new httpResponse();
$file = $i->getObject($id);

// remove the object from teh index.
if (!$file)// or !file_exists( $file->getPath() ) )
{
//	$response->status = 404;
	$response->status = 500;
//	$i->depreciateObject($id);
}
else
{
	$response->load_local_file( $file->getPath() );
	// the client can cache this file forever!
	$response->persistent = true;
}

$response->serve();
