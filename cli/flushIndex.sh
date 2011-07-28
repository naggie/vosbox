#!/usr/bin/php
<?php
// initialise the voswork environment
require_once __DIR__.'/../kernel.class.php';
kernel::bootstrap();

$i = indexer::getInstance();
$c = new mp3Crawler($i);

$i->flush();

?>
