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

	// controls: events
	$('#next').click(function(){
		$('#playlist .playing').next().each(player.play);
	});

	$('#prev').click(function(){
		$('#playlist .playing').prev().each(player.play);
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
	$('#searchResults').append('<div class="item"><div class="state"></div>'+
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
	// create a clone of the item, replacing click event, fading into playlist
	// preserving data
	item = $(this).clone(1).unbind('click').click(player.play).hide().fadeIn().appendTo('#playlist');

	// remove the message in playlist, if playlist is empty
	if ($('#playlist .message').length)
	{
		// remove message
		$('#playlist .message').remove();

		// play the item on first add
		item.each(player.play);
	}

	// scroll to the end of the list, clearing any conflicting animation
	// currently running
	var length = $("#playlist").attr("scrollHeight");
	$("#playlist").stop().animate({scrollTop:length});
}

// play an item on the playlist (as an element in 'this' context)
player.play = function ()
{
	// update the meta area with album art etc. Forcing not-null
	// so fields are always updated
	var meta = $(this).data('meta');
	$('#nowPlaying .title').text(String(meta.title));
	$('#nowPlaying .album').text(String(meta.album));
	$('#nowPlaying .artist').text(String(meta.artist));

	// highlight the item as currently playing, clearing others
	$('#playlist .item').removeClass('playing').children().filter('.state').empty();
	$(this).addClass('playing').children().filter('.state').text('Now playing');

	// scroll the item on the playlist into view (around half way down list)
	// find position relative to the top of the list, remove half the
	// height of the list.
	var offset = $('#playlist .playing').offset().top 
		+ $('#playlist').scrollTop() 
		- $('#playlist').offset().top
		- $('#playlist').height()/4;

	//$('#playlist').scrollTop(offset);
	$('#playlist').animate({scrollTop:offset});
}
