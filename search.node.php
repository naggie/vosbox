<?php
/*
TODO: exception based JSON error system
*/

$keywords = &$_REQUEST['keywords'];

try
{
	if (!$keywords)
		throw new Exception('Erm, please search for something!');

	$i = indexer::getInstance();
	$response = $i->search($keywords);
}
catch (Exception $e)
{
	$response = array('error' => $e->getMessage() );
}

header('Content-Type:application/json');
echo json_encode($response);
