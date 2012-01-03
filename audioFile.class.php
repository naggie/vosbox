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

/*
produces an abject containing metadata of an mp3 file
define ('GETID3_INCLUDEPATH', ROOT_DIR.'/getid3/');
*/
require_once __DIR__.'/constants.php';
require_once __DIR__.'/VSE/keyStore.class.php';
require_once GETID3_INCLUDEPATH.'/getid3.php';


class audioFile
{
	public $title,$artist,$album,$year,$genre,$id,$time;
	protected $path;

	// id of blob in keystore in namespace 'albumArt'
	// for album cover art, if any
	public $albumArtId = null;

	public $count = 0;

	// output from getid3 (removed after use)
	private $analysis;
	private $dir;

	// ... for compariston purposes when multiple songs exist
	// integer
	protected $quality = 0;

	public function __construct($filepath)
	{
		// force string on filename (when using recursive file iterator,
		// objects are returned)
		$filepath = (string)$filepath;

		if (!extension_loaded('Imagick'))
			throw new Exception("Imagic extension required. Not loaded");

		if (!file_exists($filepath))
			throw new Exception("$filepath not found");

		if (!is_readable($filepath))
			throw new Exception("permission denied reading $filepath");

		$this->path = $filepath;
		$this->dir = dirname($filepath).'/';

		$getID3 = new getID3();
		$this->analysis = $getID3->analyze($filepath);

		if (@isset($this->analysis['error']) )
			throw new Exception( $this->analysis['error'][0] );

		if (!isset($this->analysis['id3v1']) and !isset($this->analysis['id3v2']) )
			throw new Exception("no ID3v1 or ID3v2 tags in $filepath");

		// aggregate both tag formats (clobbering other metadata)
		getid3_lib::CopyTagsToComments($this->analysis);

		@$this->title = $this->analysis['comments']['title'][0];
		@$this->artist = $this->analysis['comments']['artist'][0];
		@$this->year = $this->analysis['comments']['year'][0];
		@$this->genre = $this->analysis['comments']['genre'][0];
		@$this->album = $this->analysis['comments']['album'][0];

		@$seconds = ceil($this->analysis['playtime_seconds']);
		@$this->time = floor($seconds/60).':'.str_pad($seconds%60, 2, "0", STR_PAD_LEFT);

		if (!$this->title)
			throw new Exception("No title found in $filepath");

		if (!$this->album)
			$this->album = 'Various artists';

		$this->assignAlbumArt();

		// set an ID relative to metadata
		$this->id = md5($this->artist.$this->album.$this->title);

		// let's guess quality is proportional to bitrate
		@$this->quality = floor($this->analysis['audio']['bitrate']/1000);

		// remove the getID3 analysis -- it's massive. It should not be indexed!
		unset ($this->analysis);
	}

	// get and save album art from the best source possible
	// then resize it to 128x128 JPG format
	private function assignAlbumArt()
	{
		$k = new keyStore('albumArt');

		// generate a potential ID corresponding to this album/artist combination
		$id = md5($this->album.$this->artist);

		// check for existing art from the same album
		// if there, then assign this song that albumn ID
		if ($k->get($id))
			return $this->albumArtId = $id;

		// get an instance of the ImageMagick class to manipulate
		// the album art image
		$im = new Imagick();
		$blob = null;

		// look in the ID3v2 tag
		if (isset($this->analysis['id3v2']['APIC'][0]['data']))
			$blob = &$this->analysis['id3v2']['APIC'][0]['data'];
		elseif (isset($this->analysis['id3v2']['PIC'][0]['data']))
			$blob = &$this->analysis['id3v2']['PIC'][0]['data'];

		// look in containing folder for images
		elseif($images = glob($this->dir.'*.{jpg,png}',GLOB_BRACE) )
		{
			// use file pointers instead of file_get_contents
			// to fix a memory leak due to failed re-use of allocated memory
			// when loading successivle bigger files
			@$h = fopen($images[0], 'rb');
			$size = filesize($images[0]);

			if ($h === false)
				throw new Exception("Could not open cover art: $images[0]");

			if (!$size)
				// invalid or no image
				//throw new Exception("Could not open cover art: $images[0]");
				// assign no art
				return;

			$blob = fread($h,$size);
			fclose($h);
		}
		else
			// no albumn art available, assign none
			return;

		// TODO, if necessary: try amazon web services

		// standardise the album art to 128x128 jpg
		$im->readImageBlob($blob);
		$im->thumbnailImage(128,128);
		$im->setImageFormat('jpeg');
        	$im->setImageCompressionQuality(90);
		$blob = $im->getImageBlob();

		// save the album art under the generated ID
		$k->set($id,$blob);
		// assign this song that albumn art ID
		$this->albumArtId = $id;

	}

	public function getPath()
	{
		return $this->path;
	}

	public function getQuality()
	{
		return $this->quality;
	}
}
