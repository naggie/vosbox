<?php
// override constants in var/config.php
// use @define() like define() here to set or override constants
// values here can be overridden in the client config
// values here can overide particular constants in the kernel. For example:

// Using an error supressed define, allow multiple defines
// loyal to the precidence of order

// paths - all absolute!
// Main directories, hard set
define ('ROOT_DIR', realpath( __DIR__ ).'/');

define ('VAR_DIR',ROOT_DIR.'var/');
define ('WWW_DIR',ROOT_DIR.'www/');
define ('CLI_DIR',ROOT_DIR.'cli/');

define ('GETID3_INCLUDEPATH', ROOT_DIR.'/getid3/');

// all config files, in order of precidence
define ('CONFIG_FILE',VAR_DIR.'config.php');
define ('DEFAULTS_FILE',__FILE__);

// useful constants
define ('START_TIME',microtime(true));

// client config - can override anything below
// the user config file may override
if (file_exists(CONFIG_FILE) )
	require CONFIG_FILE;

// specific stuff
@define('KEYSTORE_DIR',VAR_DIR.'keyStore/');
@define('LOG_FILE',VAR_DIR.'events.log');

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
