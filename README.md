**vosbox is a HTML5 PHP/jQuery based jukebox.**

![Screenshot of vosbox](http://callanbryant.co.uk/images/vosbox.png)

It supports

  * A search-oriented interface
  * Making and sharing playlists (via a link)
  * Album art
  * Watching a directory for new music
  * A tag-based index
  * Keyboard shortcuts
  * A simple interface and API
  * Flat file and sqlite backends (documentation for that coming soon)
  * Flash fallback via SoundManager2 library

Given an arbitrary mess of MP3s in any directory structure, vosbox will
find all MP3s, prefering high-quality files.

Album art will be extracted and resized using the <imagemagick.org> library
from the MP3 file with the fantastic <http://GetID3.org/> library, or loaded from
the containing folder.

vosbox currently supports sqlite or file based backends, with support
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

# Example Apache configuration (000-default.conf)

The use of a `.htaccess` file is recommended to enable password protection.

```
<VirtualHost *:80>
	DocumentRoot /path/to/vosbox/www
	ServerName pibox.local # This should be the FQDN of the Pi
	ServerAlias pibox vosbox.local vosbox # Any aliases should go here
	<Directory /path/to/vosbox/www>
		AllowOverride All # Allows setting of basic auth etc. via .htaccess files
		Order allow,deny  # Replace these two lines with 'Require all granted' for apache >2.4
		Allow from all
	</Directory>
</VirtualHost>
```

To enable SSL, using a self-signed certificate:

```
<IfModule mod_ssl.c>
	<VirtualHost *:443>
		DocumentRoot /path/to/vosbox/www
		ServerName pibox.local # This should be the FQDN of the Pi
		ServerAlias pibox vosbox.local vosbox # Any aliases should go here
		SSLEngine On
		SSLCertificateFile /path/to/ssl/certificate
		SSLCertificateKeyFile /path/to/ssl/keyfile
		#SSLCACertificateFile /path/to/ssl/CAcertificate  # You probably won't need these two options unless you know what they are
		#SSLCertificateChainFile /path/to/ssl/CAchaincertificate
		<Directory /path/to/vosbox/www>
			AllowOverride All # Allows setting of basic auth etc. via .htaccess files
			Order allow,deny  # Replace these two lines with 'Require all granted' for apache >2.4
			Allow from all
		</Directory>
	</VirtualHost>
</IfModule>
```

Thanks to @frillip for this.

# Keyboard shortcuts

	f       : search
	up/down : prev/next
	d       : download active song
	space   : pause/play
	s       : shuffle

# Mouse functions

Search results: Left click to play, right click to enqueue.

Playlist: Left click to play, right click to delete

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

	api/search.php?keywords=

  * To save a playlist, POST a CSV list of IDs as the parameter **save**

	api/playlist.php?save=

  * To load a playlist, POST or GET the playlist ID as the parameter **load**

	api/playlist.php?load=

  * To download album art, POST or GET the albumArt ID, given in the song object,
    using the parameter **id** (the result is a 128x128 JPG):

	api/albumArt.php?id=

  * To download the song itself (currently MP3 only) POST or GET the song ID, as
    the parameter **id** to:

	api/download.php?id=


----

# The vosbox search engine (VSE)

The search engine that powers vosbox is very different to a standard fulltext search engine.

How it indexes:

  1. A PHP object per item, with descriptive attributes is added
  2. This object is scraped for unique lower-case alphanumeric keywords (using the tokeniser)
  3. The object is stored under an ID (specified or created automatically)
  4. Per keyword, the ID is associated in a reverse-map list

How it searches:

  1. The search string is tokenised in the same manner
  2. For each keyword, the corresponding list of IDs is intersected with the last (by the backend)
  3. The resulting intersection of IDs is used to return a set number of corresponding objects

This can be acheived with, currently, a file based or sqlite based backend.

The idea is that avoiding fulltext search queries with multiple wildcards results in a speed
increase.

# Configuration

In order to choose a different indexer, simply create `var/config.php` and define the constant
`INDEXER` to be either `sqlite` or `keystore`. Redis is also planned, and will involve defining
some more constants.


----

Vosbox uses the following excellent libraries:

  * [jQuery](http://jquery.com)
  * [js-hotkeys plugin](http://code.google.com/p/js-hotkeys/)
  * [jQuery rightClick plugin](http://abeautifulsite.net/blog/2008/05/jquery-right-click-plugin/)
  * [jQuery shuffle plugin](http://www.yelotofu.com/2008/08/jquery-shuffle-plugin/)
  * [css3buttons by Micheal Henriksen](https://github.com/michenriksen/css3buttons)
  * [getID3](http://getid3.org/)
  * [SoundManager2](http://www.schillmania.com/projects/soundmanager2/)
  * [Gritter](http://boedesign.com/blog/2009/07/11/growl-for-jquery-gritter/)


