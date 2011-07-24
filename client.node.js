/*
TODO: full keyboard interface support (arrows to nav results etc)
*/

search = new Object();
search.placeholder = 'Search for music...';

$(document).ready(function()
{
// on demand UI:
// Make the left (search) pane fill the screen and fade in, 
// leaving the right pane visible, but behind
$('#left,#searchIcon').css('right',0).fadeIn(function(){
	$('#right').css('display','inherit');
});

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

// clear on focus TODO -- focus, not click
$('#search').click(function (){
	$(this).val('');
});

// override form submit
$('#left form').submit(function(){
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
	$('#searchResults').empty().scrollTop(0);

	if (results.length)
	{
		// results found
		for (var i in results)
			search.addResult(results[i]);

		// show playlist if required
//		if ($('#left').css('right') == '100%')
			$('#left').animate({'right':'50%'});

		// attach a click event to each to add to playlist
		$('#searchResults .item').click(function(){
			// move the div to playlist, keeping metadata
			// to copy, add .clone() to the chain and copy metadata
			// manually 
			$(this).hide().fadeIn().appendTo('#playlist');

			// remove the message if any
			if ($('#playlist .message').length)
				$('#playlist .message').empty();

			// scroll to the end of the list
			$("#playlist").scrollTop($("#playlist").attr("scrollHeight"));
		});
	}

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
