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

/*
search engine indexer

Extend this class to build an indexer backend.

An indexer, in this context, maps relationships between object IDs and tags,
allowing a search for intersecting tags to return an array of objects.

TODO:
documentation for concepts and usage. EG: crawler gathers metadata into objects
*/

require_once __DIR__.'/constants.php';

abstract class indexer
{
	// the maximum number of results to return in one query. Remember
	// that total relevance is probably inversely proportional to number
	// of results.
	public static $maxResults = 200;

	// initialise the index under a namespace
	abstract public function __construct($namespace = null);
	// associate an id with a selection of tags
	abstract protected function map($id, array $tags);
	// Return an array of IDs that have $tags in common
	abstract protected function reduce(array $tags);
	// Empty the index, objects and caches. This is typically used before a
	// new absolute crawl.
	abstract public function flush();
	// remove an object (by ID) from the index, not necessarily removing
	// its tag relationships within the index; only a flush() followed
	// by an absolute crawl will acheive this. This should be used
	// almost never used.
	abstract public function depreciateObject($id);
	// save an object under an id
	abstract protected function saveObject($id,$object);
	// load an object by ID
	abstract public function getObject($id);

	// add an object to the index
	// if the attribute $id is set, it will be used as such. Failing this,
	// an id will be generated and returned.
	// Metadata will be tokeniseStringd from all public attributes, forming
	// tags.
	public function indexObject($object)
	{
		if (!is_object($object))
			throw new Exception('You can only index a PHP object');

		// convert metadata into canonical tags
		$tags = self::deriveTags($object);

		// store the object, getting an ID
		if (!isset($object->id))
			$object->id = md5(serialize($object));
		elseif (!is_string($object->id))
			throw new Exception ('Object $id must be a string');

		$this->saveObject($object->id,$object);

		// map the object ID to the tags
		$this->map($object->id,$tags);

		return $object->id;
	}

	// search with an arbitrary string, returning an array of objects
	// that are related to the query.
	// The string is run through the tokeniseStringr, akin to how objects
	// are added in the first place. This ensures that tags are
	// consistent enough not to do a fulltext search
	public function search($string)
	{
		$keywords = self::tokeniseString($string);
		$ids =  $this->reduce($keywords);

		// force limit on number of results. This should have been done
		// already; this is precautionary.
		$ids = array_slice($ids,0,self::$maxResults);

		$objects = array();

		foreach ($ids as &$id)
			if ($object = $this->getObject($id))
				$objects[] = $object;

		return $objects;
	}

	// derive tags from an object (via the tokeniseStringr)
	// 2 layers deep only
	private static function deriveTags($object)
	{
		$tags = array();
		// TODO: fix to be recursive or something
		foreach ($object as $attribute)
			if (is_scalar($attribute))
				$tags = array_merge($tags,self::tokeniseString($attribute));
			elseif (is_array($attribute))
				foreach ($attibute as $value)
					if (is_scalar($value))
						$tags = array_merge($tags,self::tokeniseString($value));

		return array_unique($tags);
	}

	// convert a string into a series of canonical tags
	private static function tokeniseString($string = null)
	{
		// split up the string
		$tags = preg_split('/[^a-z0-9]+/i',$string);

		// normalise each tag
		foreach ($tags as $key => &$tag)
		{
			$tag = strtolower($tag);
			if (!$tag)
				unset($tags[$key]);
		}

		// filter duplicates
		$tags = array_unique($tags);

		// TODO: sort by length desc? --  speeds up search

		// convert to numeric array (sequential)
		return array_values($tags);
	}

	// returns the chosen actor indexer
	final public static function getInstance($namespace = null)
	{
		switch (constant('INDEXER'))
		{
			case 'sqlite':
			case 'sqlite3':
				require_once __DIR__.'/sqliteIndexer.class.php';
				return new sqliteIndexer($namespace);

			case 'keyStore':
			case 'keystore':
			default:
				require_once __DIR__.'/keyStoreIndexer.class.php';
				return new keyStoreIndexer($namespace);
		}
	}
}
