<?php
/*
produces an abject containing metadata of an mp3 file
*/

define ('GETID3_INCLUDEPATH', ROOT_DIR.'/getid3/');

class audioFileMeta
{
	public $title,$artist,$album,$year,$genre,$id;
	protected $path;
	protected $albumArtId;
	public $count = 0;

	// output from getid3 (removed after use)
	private $analysis;

	public function __construct($filepath)
	{
		// force string on filename (when using recursive file iterator,
		// objects are returned)
		$filepath = (string)$filepath;

		if (!file_exists($filepath))
			throw new Exception ("$filepath not found");

		if (!is_readable($filepath))
			throw new Exception ("permission denied reading $filepath");

		$this->path = $filepath;

		require_once GETID3_INCLUDEPATH.'/getid3.php';

		$getID3 = new getID3();
		$this->analysis = $getID3->analyze($filepath);

		if (@isset($this->analysis['error']) )
			throw new Exception( $this->analysis['error'][0] );

		if (!isset($this->analysis['id3v1']) and !isset($this->analysis['id3v2']) )
			throw new Exception("no ID3v1 or ID3v2 tags in $filepath");

		// aggregate both tag formats
		getid3_lib::CopyTagsToComments($this->analysis);

$this->analysis['comments']['picture'] = null;
//print_r($this->analysis['comments']);

		@$this->title = $this->analysis['comments']['title'][0];
		@$this->artist = $this->analysis['comments']['artist'][0];
		@$this->year = $this->analysis['comments']['year'][0];
		@$this->genre = $this->analysis['comments']['genre'][0];
		@$this->album = $this->analysis['comments']['album'][0];

		if (!$this->album)
			$this->album = 'Various artists';

		$this->obtainAlbumArt();

		// set an ID relative to metadata
		$this->id = md5($this->artist.$this->album.$this->title.$this->year);

		// remove the getID3 analysis -- it's massive. It should not be indexed!
		unset ($this->analysis);
	}

	// get and save album art from the best source possible
	private function obtainAlbumArt()
	{
		
	}

	public function getPath()
	{
		return $this->path;
	}
}
