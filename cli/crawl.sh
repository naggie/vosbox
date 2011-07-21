#!/usr/bin/php
<?php
// initialise the voswork environment
require_once __DIR__.'/../kernel.class.php';
kernel::bootstrap();

if (!isset($argv[1]))
	die("Usage $argv[0] <directory>\n");


$i = new sqliteIndexer();
$c = new mp3Crawler($i);

$i->flush();
$c->crawl($argv[1]);

?>
