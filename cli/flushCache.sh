#!/usr/bin/php
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
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.

    Vosbox copyright Callan Bryant 2011 <callan.bryant@gmail.com>
*/

// flushes cache
require_once __DIR__.'/../cache.class.php';

cache::getInstance()->flush();
?>
