<?php
/*
    This file is part of Vosplayer.

    Vosplayer is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Vosplayer is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.

    Vosplayer copyright Callan Bryant 2011 <callan.bryant@gmail.com>
*/

/*
search engine indexer: Keystore
Uses the voswork keystore (cache and files)
*/

require_once __DIR__.'/indexer.class.php';
require_once __DIR__.'/keyStore.class.php';

class keyStoreIndexer extends indexer
{
	// keyStore instance
	protected $store;

	public function __construct($namespace = null)
	{
		$this->store = new keyStore('index'.$namespace);
	}

	protected function map($id, array $tags)
	{
		foreach ($tags as &$tag)
			$this->relate($id,$tag);
	}

	// get IDs that have $tags in common
	protected function reduce(array $tags)
	{
		// eliminate copies
		$tags = array_unique($tags);

		// fill the array with initial match, to iteratively intersect
		// over
		$ids = $this->getIds( array_pop($tags) );

		foreach ($tags as &$tag)
		{
			if (count($ids) == 0)
				// no results, no need to refine further
				break;
			// look for mutual object keys for next tab
			$ids = array_intersect($ids,$this->getIds($tag));
		}

		return array_slice($ids,0,self::$maxResults);
	}

	public function flush()
	{
		$this->store->flush();
	}

	// relate an object key to a tag
	protected function relate($id,$tag)
	{
		// load the list of objects associated with this tag
		$ids = (array)$this->store->get($tag);

		// check to see if relationship already exists
		if (in_array($id,$ids))
			return;
		else
		{
			// add the relationship
			$ids[] = $id;
			// save the updated map
			$this->store->set($tag,$ids);
		}
	}

	// get an array of all ids relating to a tag
	// empty array on no match
	protected function getIds($tag)
	{
		return (array) $this->store->get($tag);
	}

	public function getObject($id){return $this->store->get($id);}
	public function saveObject($id,$object){return $this->store->set($id,$object);}
	public function depreciateObject($id){return $this->store->delete($id);}
}
