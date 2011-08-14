#!/usr/bin/php
<?php
// adds (updates) an individual file to the index
require_once __DIR__.'/../indexer.class.php';
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
	echo "Added $file\n";
}
catch (Exception $e)
{
	// individual file errors are not critical
	// error -> STDERR. Continue loop.
	file_put_contents('php://stderr', "> ".$e->getMessage()."\n");
}

?>
