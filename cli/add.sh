#!/usr/bin/php
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

// adds (updates) an individual file to the index
require_once __DIR__.'/../VSE/indexer.class.php';
require_once __DIR__.'/../audioFile.class.php';

if (!isset($argv[1]))
	throw new Exception("Usage $argv[0] <mp3 file>\n");

$indexer = indexer::getInstance();
$file = $argv[1];

try
{
	// must be a file capable of id3
	if (!preg_match('/\.mp3$/i',$file))
		throw new Exception("Only MP3 files are supported at this time, skipping $file");

	$meta = new audioFile($file);

	$indexer->indexObject($meta);
	echo "+ $meta->artist -- $meta->title [$meta->album]\n";
}
catch (Exception $e)
{
	// individual file errors are not critical
	// error -> STDERR. Continue loop.
	file_put_contents('php://stderr', "> ".$e->getMessage()."\n");
}

?>
