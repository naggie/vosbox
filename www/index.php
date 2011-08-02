<?php
/**
 * standard example interface
 *
 * This file defines what is loaded in what situation, given the arguments from
 * the URL. The default is to load the leaf as specified below. 
 * 
 * Other files can also reside in www/ 
 */

// initialise the voswork environment
require_once __DIR__.'/../kernel.class.php';
kernel::bootstrap();

// now, decide what to do based on arguments....
if (@$_REQUEST['node'] != null)
	// node was specified by http://localhost/?node=blah
	loadNode($_REQUEST['node']);
elseif (@$_SERVER['QUERY_STRING'])
	// node may have been specified by was specified by http://localhost/?blah
	loadNode(urldecode(@$_SERVER['QUERY_STRING']) );
else
	// fall back to default node
	loadNode('default');



function loadNode($node)
{
	// instantiate a new manifest matching nodes in the system dir
	$nodes = new manifest(NODE_MANIFEST_REGEX,ROOT_DIR);

	// find corresponding path
	$path = $nodes->$node;

	if ($path == null)
		throw new Exception('Node "'.strip_tags($node).'" not found');

	$ext = end(explode('.',$path));

	// file is a dynamic php script, execute it (in the correct directory)
	chdir( dirname($path) );
	unset ($node);
	require_once $path;
}

?>
