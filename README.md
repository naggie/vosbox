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

 ...

----

Vosplayer is ready to be released shortly on github following GPLv3
licensing and mercurial-to-git conversion.
