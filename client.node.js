/*
TODO: full keyboard interface support (arrows to nav results etc)
TODO: oiplayer?
*/

searcher = new Object();
player = new Object();

$(document).ready(function()
{
	if($.browser.msie && (parseInt($.browser.version) < 9) )
	{
		alert('Update your browser, please');
		return;
	}

	// make the player vanish instantly, before fading everything in
	player.init();
	searcher.init();
});

searcher.init = function()
{
	searcher.placeholder = 'Search for music...';

	$('#search').val(searcher.placeholder);

	// ctrl+f to search
	$(document).bind('keydown', 'ctrl+f', function(){
		$('#search').focus().val('');
	});

	// clear on focus TODO -- focus, not click
	$('#search').click(function (){
		$(this).val('');
	});

	// override form submit
	$('#searcher form').submit(searcher.search);

}


searcher.search = function()
{
	$.ajax(
	{
		data:{keywords:$('#search').val()},
		url: "?node=search",
		dataType: 'json',
		cache: false,
		success: searcher.showResults
	}); 

	// when used as a callback, replace the default action
	return false;
}

// given an array of nodes, display them
searcher.showResults = function (results)
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
			searcher.addResult(results[i]);

		// make sure the player is there.
		if(!player.visible)
			player.reveal();

		// attach a click event to each to add to playlist
		$('#searchResults .item').click(player.enqueue);
	}

	else
		$('#searchResults').html('<div class="message">No results found</div>');
}

searcher.addResult = function (result)
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
	player.audio = document.createElement('audio');
	player.state = 'stopped';

	// remove the pause button
	//$('#pause').hide();

	// on demand UI:
	// Make the left (search) pane fill the screen and fade in, 
	// leaving the right pane visible, but behind
	// TODO: make search icon part of #searcher
	$('#searcher,#searchIcon').css('right',0).fadeIn(function(){
		$('#player').css('display','inherit');
	});

	// add a watcher to set the progress bar
	window.setInterval(function(){
		// calculate percentage of time passed on current song
		var percent = 100*player.audio.currentTime/player.audio.duration;

		// set progress
		$('#controls .progress .bar').css('width',percent+'%');
	},100);

	// add event to advance the playlist on song completion
	player.audio.addEventListener('ended',player.next);

	player.visible = false;

	// controls: events
	$('#next').click(player.next);
	$('#prev').click(player.prev);
	$('#pause,#play').click(player.playPause);

	// if not searching, up and down are prev and next
	$('*').not('#search').bind('keydown','right',player.next);
	$('*').not('#search').bind('keydown','left',player.prev);
	$('*').not('#search').bind('keydown','space',player.playPause);
//	$('*').not('#search').bind('keydown','up',player.hoverNext);
//	$('*').not('#search').bind('keydown','down',player.hoverPrev);
//	$('*').not('#search').bind('keydown','return',player.select);


	$('#stop').click(player.stop);
	$(document).bind('keydown','esc',player.stop);
}

// animate the search panel (left) to reveal the player
player.reveal = function ()
{
	$('#searcher').animate({'right':'50%'});
	player.visible = true;
}

// enqueue an item (as an element in 'this' context)
player.enqueue = function ()
{
	// create a clone of the item, replacing click event, fading into playlist
	// preserving data
	item = $(this).clone(1).unbind('click').click(player.select).hide().fadeIn().appendTo('#playlist');

	// remove the message in playlist, if playlist is empty
	if ($('#playlist .message').length)
	{
		// remove message
		$('#playlist .message').remove();

		// play the item on first add
		item.each(player.select);
		player.play();
	}

	// scroll to the end of the list, clearing any conflicting animation
	// currently running
	var length = $("#playlist").attr("scrollHeight");
	$("#playlist").stop().animate({scrollTop:length});
}

// select item on the playlist, playing if appropiate
//  (as an element in 'this' context)
player.select = function ()
{
	// highlight the item as currently playing, clearing others
	$('#playlist .item').removeClass('playing');//.children().filter('.state').empty();
	$(this).addClass('playing');//.children().filter('.state').text('Now playing');

	// scroll the item on the playlist into view (around half way down list)
	// find position relative to the top of the list, remove half the
	// height of the list.
	var offset = $('#playlist .playing').offset().top 
		+ $('#playlist').scrollTop() 
		- $('#playlist').offset().top
		- $('#playlist').height()/4;

	//$('#playlist').scrollTop(offset);
	// animate to offset, clearing any other previous, possibly conflicting
	// animations
	$('#playlist').stop().animate({scrollTop:offset});

	// play it if appropiate
	if (player.state == 'playing')
		player.play();
}

// play the item currently selected on the playlist
player.play = function ()
{
	player.state = 'playing';

	// make sure the controls are set right
	$('#play').hide();
	$('#pause').show();

	// update the meta area with album art etc. Forcing not-null
	// so fields are always updated
	var meta = $('#playlist .playing').data('meta');
	$('#nowPlaying .title').text(String(meta.title));
	$('#nowPlaying .album').text(String(meta.album));
	$('#nowPlaying .artist').text(String(meta.artist));

	// play the file
	player.audio.setAttribute('src', '?node=download&id='+meta.id);
	player.audio.play();
}

// select the next song, play if playing already, returns false
// so can be used to override normal events
player.next = function()
{
	$('#playlist .playing').next().each(player.select);
	return false;
}

// select the previous song, play if playing already, returns false
// so can be used to override normal events
player.prev = function()
{
	$('#playlist .playing').prev().each(player.select);
	return false;
}

player.playPause = function()
{
	switch (player.state)
	{
		case 'paused':
		case 'stopped':
			player.audio.play();
			// update icon
			$('#play').hide();
			$('#pause').show();
			// update state
			player.state = 'playing';
		break;
		case 'playing':
			player.audio.pause();
			// update icon
			$('#pause').hide();
			$('#play').show();
			// update state
			player.state = 'paused';
		break;
	}
	return false;
}

player.stop = function()
{
		// pause it, resetting counter
		player.audio.pause();
		player.audio.currentTime = 0;

		// update icon
		$('#pause').hide();
		$('#play').show();

		// update state
		player.state = 'stopped';
}

