<?php
// END USER: save to data/config.php after editing

// use @define() like define() here to set or override constants
// values here can be overridden in the client config
// values here can overide particular constants in the kernel. For example:

// Indexer actor
@define ('INDEXER','keyStore');

// Cache actor (ordered by speed)
// * APC : fastest!
// * disk : no setup required
// * memcached : can be shared -- good for a a server farm. Config below required.
// * null : disable caching (100% cache miss) SLOW. Does not need write access.
@define ('CACHE','disk');

@define ('DISK_CACHE_DIR',VAR_DIR.'cache/');
@define ('MEMCACHED_IP','127.0.0.1');
?>
