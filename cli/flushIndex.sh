#!/usr/bin/php
<?php
// initialise the voswork environment
require_once __DIR__.'/../kernel.class.php';
kernel::bootstrap();

// flushes index (including cache) and playlists

$i = indexer::getInstance();
$k = new keyStore('playlists');

$i->flush();
$k->flush();

?>
