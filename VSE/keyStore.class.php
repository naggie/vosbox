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

    Vosbox copyright Callan Bryant 2011-2012 <callan.bryant@gmail.com> http://callanbryant.co.uk/
*/

require_once __DIR__.'/../constants.php';

/**
 * key based database class page
 *
 * Provides a means to store/retrieve small amount of data, arrays
 * or objects; all are refered to as objects in this context.
 *
 * As this is a key based store, there is no way to flush by namespace.
 * The solution is to store in folders per category.
 *
 * This can be used as an alternative to a full database
 *
 * @package core
 * @author Callan Bryant
 */
class keyStore
{

	/**
	 * path to store folder (rel. to root)
	 */
	protected $dir;

	/**
	 * Core constructor
	 *
	 * @author Callan Bryant
	 * @param $dir dir for cache
	 */
	public function __construct($namespace = 'default')
	{
		// force an alphanumeric namespace
		if (!preg_match('/^[a-z0-9]+$/i',$namespace) )
			throw new Exception('$namespace must be alphanumeric');

		// come up with a sensible dir for the keys in namespace
		$dir = KEYSTORE_DIR."/$namespace/";

		// make sure cache dir is valid
		if ( !file_exists($dir) )
			if (! @mkdir($dir,0700,true) )
				throw new Exception("Could not create dir: $dir");

		// canonicalise it
		$this->dir = realpath($dir).'/';
	}

	/**
	 * returns file path for $tag
	 * @param $tag string tag of object
	 * @return string corresponding path of object
	 */
	protected function get_path($tag)
	{
		//sanitise $tag, give it an id - with salt so that if voswork
		//is put into a different dir, the manifest won't screw up
		$id = sha1(__FILE__.$tag);

		//corresponding path:
		return $this->dir.$id.'.bin';
	}

	/**
	 * loads a previously saved object by $tag
	 * @param $tag string identifier for object
	 * @return mixed said object
	 */
	public function get($tag)
	{
		if (!is_scalar($tag))
			throw new Exception('$tag must be scalar');

		// see if it was cached, salted with the store dir
		//if ($object = kernel::$cache->get($this->dir.$tag))
		//	return $object;

		// file path to save to
		$path	= $this->get_path($tag);

		// check if cache entry is there
		if (!file_exists($path) )
			//failure, cache miss
			return null;


		// returns false if failed for any other reason
		$result	= @file_get_contents($path);

		if ($result ===false )//failed
			throw new Exception("failed to read $path");

		// convert back
		$object = unserialize($result);

		// hit
		return $object;
	}

	/**
	 * saved a previously loaded object by $tag
	 * @param $tag string identifier for object
	 */
	public function set($tag,$object)
	{
		if (!is_scalar($tag))
			throw new Exception('$tag must be scalar');

		// write it to cache, salted
		//kernel::$cache->set($this->dir.$tag,$object);

		// save it......
		$path = $this->get_path($tag);

		$serial = serialize($object);

		if (strlen($tag) > 250)
			throw new Exception('TAG must be under 250 chars!');

		//if (strlen($serial) > 1024*1024)
		//	throw new Exception('Object must be significantly less than 1MB');

		$result	= @file_put_contents($path,$serial, LOCK_EX);

		if ( $result ===false )//failed
			throw new Exception("failed to save $path");
	}

	/**
	 * cache object deleter
	 *
	 * @param $tag
	 */
	public function delete($tag)
	{
		$path = $this->get_path($tag);

		// delete it from cache
		//kernel::$cache->delete($this->dir.$tag);

		//check if entry is there
		if (!file_exists($path) )
			//failure
			return false;

		//returns false if failed for any other reason
		$result	= @unlink($path);

		if ( $result ===false )//failed
			throw new Exception("failed to delete $path");

		return true;
	}

	/**
	 * cache flusher
	 * Flushes _all_ bjects
	 */
	public function flush()
	{
		// this can take a while, flush the buffers to load the page if any,
		// before doing this.
		while (@ob_end_flush() );

		// the only way to guarentee the cache won't return any keys
		// (as they are unknown) is to flush it too
		//kernel::$cache->flush();

		foreach (new DirectoryIterator($this->dir) as $file)
		{
			if ($file->isDot())
				continue;

			$filepath = $this->dir.'/'.$file;

			if (! @unlink($filepath) )
				throw new Exception("Error deleting $filepath");
		}
	}

	/**
	 * overloading aliases - allows setting or loading tags by attribute
	 */
	public function __get($tag){return $this->get($tag);}
	public function __set($tag,$value){$this->set($tag,$value);}
	public function __unset($tag){$this->delete($tag);}
}
