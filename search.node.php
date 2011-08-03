<?php
$keywords = &$_REQUEST['keywords'];

try
{
	if (!extension_loaded('json'))
		throw new Exception('json extension not loaded');

	if (!$keywords)
		throw new Exception('Erm, please search for something!');

	$i = indexer::getInstance();
	$response = $i->search($keywords);

	header('Content-Type:application/json');
	echo json_encode($response);
}
catch (Exception $e)
{
	// manually throw the error, as the json ext may not be loaded
	header('Content-Type:application/json');
	echo '{"error":"'.$e->getMessage().'"}';
}
