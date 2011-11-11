@hotkey V
@url http://github.com/naggie/vosplayer/


**Vosplayer is a HTML5 PHP/jQuery based jukebox.**

It supports

  * Making and sharing playlists (via a link)
  * Album art
  * Watching a directory for new music
  * A tag-based index
  * Keyboard shortcuts
  * A simple interface and API

Given an arbitrary mess of MP3s in any directory structure, vosplayer will
find all MP3s, prefering high-quality files.

Album art will be extracted and resized using the <imagemagick.org> library
from the MP3 file with the fantastic <GetID3.org> library, or loaded from
the containing folder.

Vosplayer currently supports sqlite or file based backends, with support
for any other database easy to implement.

A native android app that uses the API is planned.


----

# Dependencies

  * php-imagick
  * The php JSON extension
  * inotify-tools (if you want to watch a directory)

# Usage

  1. Serve `www/` with Apache and PHP
  2. Crawl any directory (read permissions required) with
     `cli/crawl.sh <directory>`
  3. Watch that directory, if required, for new songs with
     `cli/watch.sh <directory>`

# Keyboard shortcuts

	f       : search
	up/down : prev/next
	d       : download active song
	space   : pause/play

# JSON API

Songs are returned as the following JSON-formatted objects:

	{
		title      : "The song name",
		artist     : "The artist name",
		album      : "The album name",
		year       : "2009",
		genre      : "Indie",
		id         : "74a12d502d7393e139e2fcca51200d67",
		albumArtId : "b092c997d98e13a21431c9ce58b80fbf"
	}

  * To search for anything, POST or GET it as the parameter **keywords** to

	search.php?keywords=

  * To save a playlist, POST a CSV list of IDs as the parameter **save**

	playlist.php?save=

  * To load a playlist, POST or GET the playlist ID as the parameter **load**

	playlist.php?load=

  * To download album art, POST or GET the albumArt ID, given in the song object,
    using the parameter **id** (the result is a 128x128 JPG):

	albumArt.php?id=

  * To download the song itself (currently MP3 only) POST or GET the song ID, as
    the parameter **id** to:

	download.php?id=
