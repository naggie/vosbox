<?php
/*
hydratag search engine indexer: Keystore
Uses the voswork keystore (cache and files)
*/

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
