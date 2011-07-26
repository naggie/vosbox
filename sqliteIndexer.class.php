<?php
/*
hydratag search engine indexer: sqlite
TODO:
benchmark getmany() compared to get one on sqlite keystore (w/primary key)
*/


class sqliteIndexer extends indexer
{
	// instance of sqlite3 DB
	protected $db;
	protected $file;
	protected $namespace;

	// check for/create DB
	public function __construct($namespace = null)
	{
		$this->store = new keyStore('index'.$namespace);

		// get a nice filepath for the database
		$file = VAR_DIR.'/index'.$namespace.'.sqlite';

		// create the database if needed
		if ( !file_exists($file) )
		{
			$db = new SQLite3($file);

			// make the map of tags to ids
			$db->exec("CREATE TABLE map (id TEXT,tag TEXT)");

			// make the object store
			$db->exec("CREATE TABLE objects (id TEXT PRIMARY KEY,object BLOB)");

			// indicies are created on the destructor for performance reasonss
		}
		else
			$db = new SQLite3($file);

		$this->db = $db;
		$this->file = $file;
		$this->namespace = $namespace;
	}

	// Empty the index and objects. This is typically used before a new 
	// absolute crawl.
	public function flush()
	{
		// delete the database file
		unlink($this->file);

		// create a new database
		$this->__construct($this->namespace);
	}

	// associate an id with a selection of words
	protected function map($id, array $tags)
	{
		$id = "'".SQLite3::escapeString($id)."'";
		foreach ($tags as $tag)
		{
			// sanitise
			$tag = "'".SQLite3::escapeString($tag)."'";

			$this->db->exec("INSERT INTO map VALUES ($id,$tag)");
		}		
	}

	// Return an array of IDs that have $tags in common
	protected function reduce(array $tags)
	{
		// quote and escape each tag
		foreach ($tags as &$tag)
			$tag = "'".SQLite3::escapeString($tag)."'";

		// compile the query
		$select = 'SELECT id FROM map WHERE tag = ';
		$query = $select;
		$query .= implode(' INTERSECT '.$select,$tags);

		$query .= ' LIMIT '.indexer::$maxResults;

		$result = $this->db->query($query);

		$ids = array();
		while ($row = $result->fetchArray())
			$ids[] = $row['id'];

		return $ids;
	}

	public function getObject($id)
	{
		$id = "'".SQLite3::escapeString($id)."'";
		$result = $this->db->querySingle("SELECT object FROM objects WHERE id = $id");
		$result = base64_decode($result);
		$result = unserialize($result);
		return $result;
	}

	public function saveObject($id,$object)
	{
		$id = "'".SQLite3::escapeString($id)."'";
		// convert the object into something suitable for storing
		$object = serialize($object);
		$object = base64_encode($object);
		$object = "'".SQLite3::escapeString($object)."'";


		return $this->db->exec("INSERT OR REPLACE INTO objects VALUES ($id,$object)");
	}

	public function depreciateObject($id)
	{
		$id = "'".SQLite3::escapeString($id)."'";
		return $this->db->exec("DELETE FROM objects WHERE id = $id");
	}

	public function __destruct()
	{
		// create indexes after crawl for perf reasons
		$this->db->exec("CREATE INDEX IF NOT EXISTS tags ON map (tag)");
		$this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS ids ON objects (id)");
	}
}
