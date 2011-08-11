#!/usr/bin/php
<?php
// flushes index (including cache) and playlists -- not album art

require_once __DIR__.'/../indexer.class.php';
require_once __DIR__.'/../keyStore.class.php';

indexer::getInstance()->flush();
$k = new keyStore('playlists');
$k->flush();

?>
