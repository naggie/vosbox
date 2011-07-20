<?php
/**
 * APC cache class page
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
 * APC cache class
 * 
 * Provides a means to store/retrieve temporary variables, arrays
 * or objects; all are refered to as objects in this context.
 * 
 * @package core
 * @author Callan Bryant
 */
class apcCache implements cache
{
	public function __construct()
	{
		// this cannot be in the constructor as the class requires the extension
		// check to see if php5-memcache is installed (extension)
		if (!extension_loaded('apc'))
			throw new Exception('php APC extension not loaded.'.
			'On ubuntu/debian, install the package php-apc');
	}
	  
	/**
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function set($tag,$object,$expiry = 0)
	{
		$object = serialize($object);
		return apc_store($tag,$object,$expiry);
	}
	
	/**
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function get($tag)
	{
		return unserialize(apc_fetch($tag));
	}
	
	/**
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function delete($tag)
	{
		return apc_delete($tag);
	}

	/**
	 * cache flusher
	 * Flushes _all_ cache objects
	 */
	public function flush()
	{
		return apc_clear_cache();
	}
}
