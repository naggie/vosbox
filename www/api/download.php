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

require_once __DIR__.'/../../VSE/indexer.class.php';
require_once __DIR__.'/../../audioFile.class.php';
require_once __DIR__.'/../../httpResponse.class.php';

$id = &$_REQUEST['id'];

$i = indexer::getInstance();
$response = new httpResponse();
$file = $i->getObject($id);

// remove the object from teh index.
if (!$file)// or !file_exists( $file->getPath() ) )
{
//	$response->status = 404;
	$response->status = 500;
//	$i->depreciateObject($id);
}
else
{
	$response->load_local_file( $file->getPath() );
	// the client can cache this file forever!
	$response->persistent = true;
}

$response->serve();
