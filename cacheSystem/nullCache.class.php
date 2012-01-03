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
require_once __DIR__.'/cache.class.php';

/**
 * null cache - does not cache, always returns a cache miss
 * Useful for debugging, or when it is not possible/desirable to write to the
 * filesystem. WARNING: IT'S SLOW!
 *
 * Provides a means to store/retrieve temporary variables, arrays
 * or objects; all are refered to as objects in this context.
 *
 * @package core
 * @author Callan Bryant
 */
class nullCache extends cache
{
	public function get($tag){return null;}
	public function set($tag,$object,$expiry = 0){}
	public function delete($tag){return true;}
	public function flush(){}
}

