#!/usr/bin/php
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

// flushes index (including cache) and playlists -- not album art

require_once __DIR__.'/../indexer.class.php';
require_once __DIR__.'/../keyStore.class.php';

indexer::getInstance()->flush();
$k = new keyStore('playlists');
$k->flush();

?>
