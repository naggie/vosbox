<?php
/*
    This file is part of Vosplayer.

    Vosplayer is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Vosplayer is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.

    Vosplayer copyright Callan Bryant 2011 <callan.bryant@gmail.com>
*/

require_once __DIR__.'/../indexer.class.php';
require_once __DIR__.'/../audioFile.class.php';
require_once __DIR__.'/../keyStore.class.php';

try
{
	if (!extension_loaded('json'))
		throw new Exception('json extension not loaded');

	$store = new keyStore('playlists');
	$index = indexer::getInstance();


	// save a playlist from serialised IDs
	if (@$idsCsv = $_REQUEST['save'])
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
	elseif (@$id = $_REQUEST['load'])
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
