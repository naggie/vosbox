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
    along with Vosbox.  If not, see <http://www.gnu.org/licenses/>.

    Vosbox copyright Callan Bryant 2011 <callan.bryant@gmail.com>
*/

require_once __DIR__.'/cache.class.php';

/**
 * RAM cache class
 *
 * uses memory, memcached
 *
 * Provides a means to store/retrieve temporary variables, arrays
 * or objects; all are refered to as objects in this context.
 *
 * @package core
 * @author Callan Bryant
 */
class memcachedCache extends cache
{
	// memcache instance to abstract
	protected $memcache;

	/**
	 * Core constructor
	 *
	 * @author Callan Bryant
	 * @param $cache_dir dir for cache
	 */
	public function __construct($ip)
	{
		// check to see if php5-memcache is installed (extension)
		if (!extension_loaded('memcache'))
			throw new Exception
			('php APC extension not loaded. On ubuntu/debian,'.
			'install the package php5-memcache');

		$this->memcache = new Memcache();
		$this->memcache->connect($ip);
	}

	/**
	 * calls parent set with correct param order
	 * 
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function set($tag,$object,$expiry = 0)
	{
		$this->memcache->set($tag,$object,false,$expiry);
	}

	/**
	 * calls parent get with correct param order
	 *
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function get($tag)
	{
		return $this->memcache->get($tag,false);
	}

	/**
	 * calls parent delete with correct param order
	 *
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function delete($tag)
	{
		$this->memcache->delete($tag,0);
	}

	/**
	 * cache flusher
	 * Flushes _all_ cache objects
	 */
	public function flush()
	{
		$this->memcache->flush();
	}
}
