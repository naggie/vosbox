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

		// check each object exists
		foreach ($ids as $id)
			if (!$index->getObject($id))
				throw new Exception('Invalid playlist');

		// save the playlist (list of ids), generating an ID 10 chars
		$playlistId = substr( md5($idsCsv) ,0,10);
		$store->set($playlistId,$ids);

		header('Content-Type:application/json');
		echo json_encode(array('id' => $playlistId));
	}
	// load a playlist from a playlist ID
	elseif ($id = $_REQUEST['load'])
	{
		$ids = (array)$store->get($id);

		if (!$ids)
			throw new Exception('Playlist not found');

		$objects = array();
		// load each playlist item, fresh from the index.
		// it is done every time because an object may have changed or moved
		// the overhead is acceptable as this is much less work than a search!
		foreach ($ids as $id)
			if ($object = $index->getObject($id))
				$objects[] = $object;

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
