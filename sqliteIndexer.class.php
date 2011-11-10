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
search engine indexer: sqlite
*/

require_once __DIR__.'/indexer.class.php';
require_once __DIR__.'/constants.php';

class sqliteIndexer extends indexer
{
	// instance of sqlite3 DB
	protected $db;
	protected $file;
	protected $namespace;

	// check for/create DB
	public function __construct($namespace = null)
	{
		if (!extension_loaded('sqlite3'))
			throw new Exception('sqlite3 extension not loaded');

                // force an alphanumeric namespace
                if ($namespace and !preg_match('/^[a-z0-9]+$/i',$namespace) )
                        throw new Exception('$namespace must be alphanumeric');

		// get a nice filepath for the database
		$file = VAR_DIR.'/index'.$namespace.'.sqlite';

		// create the database if needed
		if ( !file_exists($file) )
		{
			$db = new SQLite3($file);

			// make the map of tags to ids -- forcing the relationship to be unique
			$db->exec("CREATE TABLE map (id TEXT,tag TEXT, PRIMARY KEY (id,tag))");

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

			// insert into db if that relationship doesn't already exist
			$this->db->exec("INSERT OR IGNORE INTO map VALUES ($id,$tag)");
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
