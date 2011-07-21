<?php
/*
TODO: exception based JSON error system
*/

$keywords = &$_REQUEST['keywords'];

$i = new sqliteIndexer();
$results = $i->search($keywords);

header('Content-Type:application/json');
echo json_encode($results);
