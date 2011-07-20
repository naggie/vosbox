<?php
/**
 * main function library class container page
 *
 *     Voswork - the PHP app template
 *     Voswork Copyright (C) 2009-2011  Callan Bryant <callan.bryant@gmail.com>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package core
 * @author Callan Bryant <callan.bryant@gmail.com>
 */

/**
 * main static (general) function library
 * the interface to core
 * agnostic of autoloader!
 *
 * low level, static on purpose. This is so there only can be one 'instance'
 * and the kernel can be called in different scripts without passing round
 * an object.
 *
 * @static
 * @package core
 * @author Callan Bryant
 */
class kernel
{
	// an instance of the best cache available - use kernel::initCache()
	// to instantiate it.
	public static $cache;

	// manifests for class loading
	public static $classes;


	/**
	 * BOOTSTRAPS THE KERNEL (replaces bootstrap.php)
	 * Sets up the voswork environment.
	 * Run this in app/public/index.php
	 * methods will not work without this
	 */
	public static function bootstrap()
	{
		// load the appropiate from here, app config and user config
		// in respective precidence
		kernel::configure();

		// load the classes used by the kernel
		require ROOT_DIR.'manifest.class.php';

		kernel::defineExceptionHandler();
		kernel::initCache();

		//load a new manifest matching class files in correct dirs
		self::$classes = new manifest(CLASS_MANIFEST_REGEX,ROOT_DIR);

		kernel::defineAutoloader();
	}


	/**
	 * logs an event to LOG_FILE (default: var/events.log)
	 * As the file is only appended, it does not use a significant amount of
	 * memory.
	 *
	 * Use sparingly.
	 *
	 * @param $event significant event
	 */
	public static function log($event)
	{
		// no new lines in event - they are used for delimitation
		$event = str_replace("\n",' ',$event);

		if (LOG_TIMESTAMP)
			$event = time().' '.$event;

		file_put_contents(LOG_FILE,"$event\n",FILE_APPEND|LOCK_EX);
	}



	/**
	 * Loads all config by order of decreasing precidence
	 */
	public static function configure()
	{
		// Using an error supressed define, allow multiple defines
		// loyal to the precidence of order

		// paths - all absolute!
		// Main directories, hard set
		define ('ROOT_DIR', realpath( __DIR__ ).'/');

		define ('VAR_DIR',ROOT_DIR.'var/');
		define ('WWW_DIR',ROOT_DIR.'www/');
		define ('CLI_DIR',ROOT_DIR.'cli/');

		// all config files, in order of precidence
		define ('CONFIG_FILE',VAR_DIR.'config.php');
		define ('DEFAULTS_FILE',ROOT_DIR.'defaults.php');

		// for the manifest of 'magic' files
		define ('NODE_MANIFEST_REGEX','/([\-a-z0-9._ ]+)\.node\.[a-z0-9]+$/i');
		define ('CLASS_MANIFEST_REGEX','/([a-z0-9_]+)\.(class|interface)\.php$/i');

		define ('START_TIME',microtime(true));
		define ('CLI_MODE',@defined('STDIN'));

		// client config - can override anything below
		// the user config file may override
		if (file_exists(CONFIG_FILE) )
			require CONFIG_FILE;

		// app config - can override anything below
		// the default, app specific config file may override any value
		if (file_exists(DEFAULTS_FILE) )
			require DEFAULTS_FILE;

		// specific stuff
		@define('LOG_TIMESTAMP',true);
		@define('KEYSTORE_DIR',VAR_DIR.'keystore/');
		@define('LOG_FILE',VAR_DIR.'events.log');
		@define('CACHE_ACTOR','null');
		@define('DISK_CACHE_DIR',VAR_DIR.'cache/');
		@define('PERSISTENT_STATIC_NODES',false);
		@define('MEMCACHED_IP','127.0.0.1');
	}

	/**
	 * sets the best cache avaliable conforming to 'cache' interface
	 *
	 * sets tp kernel::$cache; - all voswork apps must use this
	 * to avoid opening more than one socket and to automatically have the
	 * best one
	 *
	 * @return $cache object
	 */
	public static function initCache()
	{
		require_once ROOT_DIR.'cache.interface.php';

		switch (CACHE_ACTOR)
		{
			case 'memcached':
				require_once ROOT_DIR.'memcachedCache.class.php';
				self::$cache = new memcachedCache(MEMCACHED_IP);
			break;

			case 'disk':
				require_once ROOT_DIR.'diskCache.class.php';
				self::$cache = new diskCache(DISK_CACHE_DIR);
			break;

			case 'APC':
			case 'apc':
				require_once ROOT_DIR.'apcCache.class.php';
				self::$cache = new apcCache();
			break;

			case 'null':
			// sort out problem of config ambiguity
			case null:
				require_once ROOT_DIR.'nullCache.class.php';
				self::$cache = new nullCache();
			break;

			default:
				throw new Exception('Invalid cache type');
		}

	}

	/**
	 * registers the autoloader that uses the manifest (hence cache)
	 * for bootstrap
	 */
	public static function defineAutoloader()
	{
		/**
		 * magic autoloader
		 *
		 * not to be called explicitly
		 *
		 * Note: Exceptions thrown in __autoload function cannot be caught
		 * in the catch block and results in a fatal error.
		 *
		 * @package core
		 * @author Callan Bryant
		 * @param string $class the class that needs to be loaded
		 */
		function __autoload($class)
		{	// find corresponding path
			$path = kernel::$classes->$class;

			if ($path == null)
				throw new Exception("Class '$class' not found");

			require $path;
		}
	}

	/**
	 * sets the default exception handler for uncaught exceptions
	 * automatically catches them.
	 *
	 * Looks nicer in CLI mode, also clears buffer
	 */
	public static function defineExceptionHandler()
	{
		$callback = function($e)
		{
			// cancel any previous output
			while (@ob_end_clean() );

			//make the browser use a monospace font
			@header('Content-type:text/plain');

			echo "# Exception caught\n\n";
			echo "> ".$e->getMessage()."\n\n";
			echo get_class($e)." raised in \n".$e->GetFile().":".$e->GetLine()."\n\n";
			echo "## Backtrace\n\n".$e->GetTraceAsString()."\n";

			die();
		};


		set_exception_handler($callback);
	}

	/**
	 * gets the (rough) execution time up to the point called in
	 * milliseconds as an integer 
	 *
	 * @return float time in milliseconds
	 */
	public static function elapsed()
	{
		return ceil((microtime(true) - START_TIME)*1000);
	}
}
?>
