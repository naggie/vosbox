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

require_once __DIR__.'/../indexer.class.php';
require_once __DIR__.'/../audioFile.class.php';

if (!isset($argv[1]))
	throw new Exception("Usage $argv[0] <directory>\n");

$indexer = indexer::getInstance();

// number of files indexed
$count = 0;

//$iterator = new RecursiveDirectoryIterator($resource);
// iterator that ignores locked directories
$iterator = new IgnorantRecursiveDirectoryIterator($argv[1]);
$files = new RecursiveIteratorIterator($iterator);

foreach ($files as $path)
{
	// must be a file capable of id3
	if (!preg_match('/\.mp3$/i',$path))
		continue;
	try
	{
		$file = new audioFile($path);

		// if the same song with a higher bitrate exists, don't index it
		if ($existingFile = $indexer->getObject($file->id) )
			if ($existingFile->getQuality() > $file->getQuality() )
				throw new Exception("Skipping inferior $path");

		$indexer->indexObject($file);
		echo "+ $file->artist -- $file->title [$file->album]\n";
		$count++;
	}
	catch (Exception $e)
	{
		// individual file errors are not critical
		// error -> STDERR. Continue loop.
		file_put_contents('php://stderr', "> ".$e->getMessage()."\n");
	}
}

echo "\n$count files indexed\n";


// hack from http://php.net/manual/en/class.recursivedirectoryiterator.php
// so that directories with bad perms are ignored
class IgnorantRecursiveDirectoryIterator extends RecursiveDirectoryIterator
{
        function getChildren()
        {
                try
                {
                        return parent::getChildren();
                }
                catch(UnexpectedValueException $e)
                {
                        return new RecursiveArrayIterator(array());
                }
        }
}

?>
