<?php
try
{
	if (!extension_loaded('json'))
		throw new Exception('json extension not loaded');

	$store = new keyStore('playlists');
	$index = indexer::getInstance();


	// save a playlist from serialised IDs
	if ($idsCsv = $_REQUEST['save'])
	{
		// convert to array of IDs
		$ids = explode(',',$idsCsv);

		// check each object exists and record it
		$objects = array();
		foreach ($ids as $id)
			if ($object = $index->getObject($id))
				$objects[] = $object;
			else
				throw new Exception('Invalid playlist items');

		// save the playlist (list of ids), generating an ID 10 chars
		$playlistId = substr( md5($idsCsv) ,0,10);
		$store->set($playlistId,$objects);

		header('Content-Type:application/json');
		echo json_encode(array('id' => $playlistId));
	}
	// load a playlist from a playlist ID
	elseif ($id = $_REQUEST['load'])
	{
		$objects = (array)$store->get($id);

		if (!$objects)
			throw new Exception('Playlist not found');

		header('Content-Type:application/json');
		echo json_encode($objects);
	}
	else
		throw new Exception("specify a playlist ID to load=, or a list of song IDs to save=");
}
catch (Exception $e)
{
	// manually throw the error, as the json ext may not be loaded
	header('Content-Type:application/json');
	echo '{"error":"'.$e->getMessage().'"}';
}
