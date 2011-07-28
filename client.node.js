/*
TODO: full keyboard interface support (arrows to nav results etc)
TODO: oiplayer?
*/

searcher = new Object();
player = new Object();

$(document).ready(function()
{
	if(!$.browser.webkit)
	{
		$('body').html('For now only webkit browsers are supported. <a href="http://www.google.com/chrome/">Chrome</a> is good.');
		return;
	}

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
	//$('#search').val(searcher.placeholder);
	$('#search').focus();

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

	$('#searcher .message').show().text('To begin, search for music in the box above');
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

	// reset results area
	$('#searchResults').empty().scrollTop(0);
	$('#searcher .message').show().text('Searching...');

	// when used as a callback, replace the default action
	return false;
}

// given an array of nodes, display them
searcher.showResults = function (results)
{
	if (results.error)
	{
		$('#searcher .message').show().text(results.error);
		return;
	}

	// playlist is empty, successful search: tell user what to do next
	if (results.length && !$('#playlist *').length)
		$('#player .message').hide().fadeIn().text('Click a result to add it to this playlist');

	// remove the message
	$('#searcher .message').hide();

	if (results.length)
	{
		// results found
		for (var i in results)
			searcher.addResult(results[i]);

		// attach a click event to each to add to playlist
		$('#searchResults .item').click(player.enqueue);
	}

	else
		$('#searcher .message').show().text('No results found');
}

searcher.addResult = function (result)
{
	// add the HTML
	$('#searchResults').append('<div class="item">'+
	'<div class="artist">'+result.artist+'</div><div class="title">'+result.title+'</div>'+
	'<div class="context">'+result.album+'</div></div>');
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

	// add a watcher to set the progress bar
	window.setInterval(function(){
		// calculate percentage of time passed on current song
		var percent = 100*player.audio.currentTime/player.audio.duration;

		// set loader if appropiate
		if (player.state == 'playing' && player.audio.currentTime == 0)
		{
			// must be loading
			$('#controls .progress .bar').hide();
			$('#controls .progress').css('background','url("?load")')
		}
		else
		{
			// not loading, playing properly	
			$('#controls .progress .bar').show();
			$('#controls .progress').css('background','white')
		}

		// set progress
		$('#controls .progress .bar').css('width',percent+'%');
	},100);

	// add event to advance the playlist on song completion
	player.audio.addEventListener('ended',player.next);

	// controls: events
	$('#next').click(player.next);
	$('#prev').click(player.prev);
	$('#pause,#play').click(player.playPause);

	// seek
	$('#controls .progress').click(function(e){
		// translate X click pos to time in song
		var offset = e.pageX - $(this).offset().left;
		var proportion = offset/$(this).width();
		var newTime = proportion*player.audio.duration;

		// set the new time. Note that, for some unknown reason,
		// this does not always work...
		player.audio.currentTime = newTime;
	});
	

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

// enqueue an item (as an element in 'this' context)
player.enqueue = function ()
{
	// create a clone of the item, replacing click event, fading into playlist
	// preserving data
	item = $(this).clone(1).unbind('click').click(player.select).hide().fadeIn().appendTo('#playlist');

	$('#player .message').hide();

	// play the item on first add (to empty playlist) or add to idle playlist
	if (player.state == 'stopped' || !$('#playlist').length)
	{
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


	// update the meta area with album art etc. Forcing not-null
	// so fields are always updated
	var meta = $('#playlist .playing').data('meta');
	$('#nowPlaying .title').text(String(meta.title));
	$('#nowPlaying .album').text(String(meta.album));
	$('#nowPlaying .artist').text(String(meta.artist));

	// play the file
	player.audio.setAttribute('src', '?node=download&id='+meta.id);

	//$('#playlist').scrollTop(offset);
	// animate to offset, clearing any other previous, possibly conflicting
	// animations
	$('#playlist').stop().animate({scrollTop:offset});

	// play it if appropiate
	if (player.state == 'playing')
		player.play();
}

// play the item currently selected on the playlist, from start
player.play = function ()
{
	player.state = 'playing';

	// make sure the controls are set right
	$('#play').hide();
	$('#pause').show();

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

