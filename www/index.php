<?php
/**
 * standard example interface
 *
 * This file defines what is loaded in what situation, given the arguments from
 * the URL. The default is to load the leaf as specified below. 
 * 
 * Other files can also reside in public/ and use the kernel via include. Only 
 * Ever do this if you require some specific behaviour that requires separate
 * files; for example, a hard linked JS library such as ExtJS, or /announce.php
 * for the bittorrent protocol.
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

	if ($ext === 'php')
	{
		// file is a dynamic php script, execute it (in the correct directory)
		chdir( dirname($path) );
		unset ($node);
		require_once $path;
	}
	else
	{
		// file is static, let http class handle it
		$s = new httpResponse();
		$s->load_local_file($path);

		if (PERSISTENT_STATIC_NODES)
			$s->persistent = true;

		// override name (to remove .node)
		$s->name = $node.'.'.$ext;
		$s->serve();
	}
	// no more output is allowed
	die();
}

?>
