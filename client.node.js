/*
TODO: full keyboard interface support (arrows to nav results etc)
IDEA: player only appears on first add?
clone() remove() appendTo() used to add items (with attached data) to playlists
*/

search = new Object();
search.placeholder = 'Search for music...';

$(document).ready(function()
{

if($.browser.msie && (parseInt($.browser.version) < 9) )
{
	alert('Update your browser, please');
	return;
}

$('#search').val(search.placeholder);

// search on space
/*$('#search').bind('keydown', 'space', function(){
	search.do();
	return true;
});
*/

// ctrl+f to search
$(document).bind('keydown', 'ctrl+f', function(){
	$('#search').focus().val('');
});

$('#search').click(function (){
	$(this).val('');
});

// override form submit
$('#right form').submit(function(){
	search.do();
	//$('#search').val('');
	// remove the default page submit
	return false;
});

});


search.do = function()
{
	$.ajax(
	{
		data:{keywords:$('#search').val()},
		url: "?node=search",
		dataType: 'json',
		cache: false,
		success: search.showResults
	}); 
}

// given an array of nodes, display them
search.showResults = function (results)
{
	if (results.error)
	{
		$('#searchResults').html('<div class="message">'+results.error+'</div>');
		return;
	}

	// reset results area
	$('#searchResults').html('').scrollTop(0);

	if (results.length)
		for (var i in results)
			search.addResult(results[i]);
	else
		$('#searchResults').html('<div class="message">No results found</div>');
}

search.addResult = function (result)
{
	// add the HTML
	$('#searchResults').append('<div class="item">'+
	'<div class="artist">'+result.artist+'</div><div class="title">'+result.title+'</div>'+
	'<div class="context">'+result.album+'</div></div>'
	);
	// ...attaching to it the object itself
	// by first selecting the element just created...
	$('#searchResults div:last-child').data('meta',result);
}
