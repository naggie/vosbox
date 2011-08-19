#!/usr/bin/php
<?php
// flushes cache
require_once __DIR__.'/../cache.class.php';

cache::getInstance()->flush();
?>
