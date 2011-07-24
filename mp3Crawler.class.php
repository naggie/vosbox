<?php
/*
hydratag search engine ID3 tag crawler
TODO: derive a title from non ID3 files, in filename etc

TODO: audio file info struct class (hidden filepath etc)
TODO: OR blacklist system? Auto blacklist system (look for globally common tags)?
TODO: support extended tag? (TAG+) see wikipedia
TODO: return orecho a summary

TODO: fileCrawler to replace this
*/

class mp3Crawler //implements crawler
{
	static protected $genres =
array ('Blues','Classic Rock','Country','Dance','Disco','Funk','Grunge','Hip-Hop',
'Jazz','Metal','New Age','Oldies','Other','Pop','R&B','Rap','Reggae','Rock','Techno',
'Industrial','Alternative','Ska','Death Metal','Pranks','Soundtrack','Euro-Techno',
'Ambient','Trip-Hop','Vocal','Jazz+Funk','Fusion','Trance','Classical','Instrumental',
'Acid','House','Game','Sound Clip','Gospel','Noise','Alternative Rock','Bass','Soul',
'Punk','Space','Meditative','Instrumental Pop','Instrumental Rock','Ethnic','Gothic',
'Darkwave','Techno-Industrial','Electronic','Pop-Folk','Eurodance','Dream',
'Southern Rock','Comedy','Cult','Gangsta','Top 40','Christian Rap','Pop/Funk',
'Jungle','Native US','Cabaret','New Wave','Psychadelic','Rave','Showtunes',
'Trailer','Lo-Fi','Tribal','Acid Punk','Acid Jazz','Polka','Retro','Musical',
'Rock & Roll','Hard Rock','Folk','Folk-Rock','National Folk','Swing','Fast Fusion',
'Bebob','Latin','Revival','Celtic','Bluegrass','Avantgarde','Gothic Rock',
'Progressive Rock','Psychedelic Rock','Symphonic Rock','Slow Rock','Big Band',
'Chorus','Easy Listening','Acoustic','Humour','Speech','Chanson','Opera',
'Chamber Music','Sonata','Symphony','Booty Bass','Primus','Porn Groove','Satire',
'Slow Jam','Club','Tango','Samba','Folklore','Ballad','Power Ballad','Rhythmic Soul',
'Freestyle','Duet','Punk Rock','Drum Solo','Acapella','Euro-House','Dance Hall',
'Goa','Drum & Bass','Club - House','Hardcore','Terror','Indie','BritPop','Negerpunk',
'Polsk Punk','Beat','Christian Gangsta Rap','Heavy Metal','Black Metal','Crossover',
'Contemporary Christian','Christian Rock','Merengue','Salsa','Thrash Metal','Anime',
'JPop','Synthpop');

	protected $indexer;

	// instantiate the class given an instance of a hydratag indexer
	public function __construct(indexer $indexer)
	{
		$this->indexer = $indexer;
		// empty the index
		//$this->indexer->flush();
	}

	// crawl an arbitrary resource (folder, URL, etc)
	// generally, list all relevant items and then index() them separately
	public function crawl($resource)
	{
		$iterator = new RecursiveDirectoryIterator($resource);
		$files = new RecursiveIteratorIterator($iterator);

		foreach ($files as $file)
		{
			// must be a file capable of id3
			if (!preg_match('/\.mp3$/i',$file))
				continue;

			$info = self::getID3($file);
			if (!$info)
			{
				// guess the title and album from the
				// filepath
				$info = array();
				$bits = explode('/',$file);
				$info['title'] = array_pop($bits);
				$info['album'] = array_pop($bits);
				$info['artist'] = array_pop($bits);
				echo "Adding $file (no ID3 tag, metadata guessed)\n";
			}
			else
				echo "Adding $file\n";

			// sanitise the metadata
			foreach ($info as &$attribute)
				$attribute = strip_tags($attribute);

			$info['path'] = (string)$file;

			// type is audio -- make vosplayer able to play it
			// and list it correctly
			$info['type'] = 'audio';

			$this->indexer->indexObject((object)$info);
		}
	}

	// Returns an associative array of information about an mp3 file
	// TODO: replace with getID3.org implementation
	// idea taken from readme.txt in getID3() from getid3.org
	protected static function getID3($file)
	{
		// use file pointers instead of file_get_contents
		// to fix a memory leak due to failed re-use of allocated memory
		// when loading successivle bigger files
		$h = fopen($file, 'rb');
		if ($h === false)
			throw new Exception("Could not open $file");
		fseek($h,-128,SEEK_END);
		$data = fread($h,128);	
		fclose($h);

		$info = unpack('a3TAG/a30title/a30artist/a30album/a4year/a28comment/c1track/c1genreid',$data);

		// check for valid tag
		if (@$info['TAG'] == 'TAG')
			unset ($info['TAG']);
		else
			return array();

		// replace genreid with real text genre
		if (isset( self::$genres[$info['genreid']] ))
			$info['genreid'] = self::$genres[$info['genreid']];
		else
			$info['genreid'] = 'Unknown';

		return $info;
	}
}
