<?php
/*
    This file is part of Vosbox.

    Vosbox is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Vosbox is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.

    Vosbox copyright Callan Bryant 2011 <callan.bryant@gmail.com>
*/

require_once __DIR__.'/constants.php';

/**
 * standard voswork cache interface - for builing different types of
 * cache. Deliberately simple!
 * @package core
 * @author Callan Bryant
 */
abstract class cache
{
	/**
	 * loads a previously saved object by $tag
	 * @param $tag string identifier for object
	 * @return mixed said object
	 */
	public function get($tag);

	/**
	 * saved a previously loaded object by $tag
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function set($tag,$object,$expiry = 0);

	/**
	 * cache object deleter
	 *
	 * @param $tag
	 */
	public function delete($tag);

	/**
	 * cache flusher
	 * Flushes _all_ cache objects
	 */
	public function flush();

	// grab an instance of a cache actor
	public static final function getInstance()
	{
		if (! defined('CACHE')
			throw new Exception ('Define CACHE actor first');

		switch (CACHE)
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
				throw new Exception('Invalid CACHE actor');
		}
	}
}
