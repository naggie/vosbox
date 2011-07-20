<?php
/**
 * cache interface page
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
 * 
 * @package core
 * @author Callan Bryant <callan.bryant@gmail.com>
 */

/**
 * standard voswork cache interface - for builing different types of 
 * cache. Deliberately simple!
 * @package core
 * @author Callan Bryant
 */
interface cache
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
}
