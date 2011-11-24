<?php
/*
    This file is part of Vosbox.

    Vosbox is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Vosbox is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Vosbox.  If not, see <http://www.gnu.org/licenses/>.

    Vosbox copyright Callan Bryant 2011 <callan.bryant@gmail.com>
*/

$keywords = &$_REQUEST['keywords'];
require_once __DIR__.'/../VSE/indexer.class.php';
// include original class to reconstruct each item
require_once __DIR__.'/../audioFile.class.php';

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
