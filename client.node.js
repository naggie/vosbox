/*
TODO: full keyboard interface support (arrows to nav results etc)
TODO: split up more into methods, using classes perhaps
TODO: set an attribute of player to show if visible
*/

search = new Object();
player = new Object();

search.placeholder = 'Search for music...';

$(document).ready(function()
{
// make the player vanish instantly, before fading everything in
player.init();

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

		// make sure the player is there.
		if(!player.visible)
			player.reveal();

		// attach a click event to each to add to playlist
		$('#searchResults .item').click(player.enqueue);
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
	$('#searchResults .item:last-child').data('meta',result);
}

// modify CSS to make search pane obscure player, fading everything in
// without player visible. This allows CSS to define the full view,
// using pure JS to handle the dynamic UI.
player.init = function()
{
	// on demand UI:
	// Make the left (search) pane fill the screen and fade in, 
	// leaving the right pane visible, but behind
	$('#left,#searchIcon').css('right',0).fadeIn(function(){
		$('#right').css('display','inherit');
	});
	player.visible = false;
}

// animate the search panel (left) to reveal the player
player.reveal = function ()
{
	$('#left').animate({'right':'50%'});
	player.visible = true;
}

// enqueue an item (as an element in 'this' context)
player.enqueue = function ()
{
	// copy the div to playlist, keeping metadata,
	// making onclick event play it, rather than
	// adding it to the playlist again
	$(this).clone(1).unbind('click').click(player.play).hide().fadeIn().appendTo('#playlist');

	// remove the message in playlist, if playlist is empty
	if ($('#playlist .message').length)
	{
		// remove message
		$('#playlist .message').empty();

		// play the item on first add
		//player.play();
	}

	// scroll to the end of the list
	$("#playlist").scrollTop($("#playlist").attr("scrollHeight"));
}

// play an item on the playlist (as an element in 'this' context)
player.play = function ()
{
	// update the meta area with album art etc
	var meta = $(this).data('meta');
	$('#meta .title').text(meta.title);
	$('#meta .album').text(meta.album);
	$('#meta .artist').text(meta.artist);

	// highlight the item as currently playing, clearing others
	$('#playlist .item').removeClass('playing');
	$(this).addClass('playing');
}
