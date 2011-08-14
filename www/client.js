/*
TODO: full keyboard interface support (arrows to nav results etc)
TODO: oiplayer?
TODO: compress when dev is almost done
*/

searcher = new Object();
player = new Object();

// jQuery will fire this callback when the DOM is ready
$(function ()
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

searcher.init = function ()
{
	//$('#search').val(searcher.placeholder);
	$('#search').focus();

	// ctrl+f to focus search
	$(document).bind('keydown', 'ctrl+f', function (){
		$('#search').focus().val('');
	});

	// CTRL+A to add all results to playlist
	$(document).bind('keydown','ctrl+a',searcher.enqueueAll);
	$('#enqueueAll').click(searcher.enqueueAll);

	// clear on focus TODO -- focus, not click
	$('#search').click(function (){
		$(this).val('');
	});

/*	// look for 'tags' which onClick, will search
	$('.tag').live('click',function()
	{
		var keywords = $(this).text();
		$('#search').val(keywords);
		searcher.search();
	});*/

	// override form submit
	$('#searcher form').submit(searcher.search);

	$('#searcher .message').show().text('To begin, search for music in the box above');

	// add dynamic (live) click event to every search result to enqueue
	$('#searchResults .item').live('click',function (){
		meta = $(this).data('meta');
		player.enqueue(meta);
	});

}


searcher.search = function ()
{
	$.ajax(
	{
		data:{keywords:$('#search').val()},
		url: "search.php",
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
		// results found
		for (var i in results)
			searcher.addResult(results[i]);
	else
		$('#searcher .message').show().text('No results found');
}

searcher.addResult = function (result)
{
	// add the HTML
	item = $('<div class="item">'+
	'<div class="artist tag">'+result.artist+'</div><div class="title">'+result.title+'</div>'+
	'<div class="album tag">'+result.album+'</div></div>');

	// attach metadata
	item.data('meta',result);

	// add to search results area
	item.appendTo('#searchResults');
}

// enqueue everything currently in the search result area
searcher.enqueueAll = function ()
{
	$('#searchResults .item').each(function (){
		player.enqueue($(this).data('meta'));
	});

	// stop the playlist from scolling to the bottom
	$('#playlist').stop();

	// allow override if being used as callback
	return false;
}

// modify CSS to make search pane obscure player, fading everything in
// without player visible. This allows CSS to define the full view,
// using pure JS to handle the dynamic UI.
player.init = function ()
{
	// HTML5 audio player, not part of the DOM
	player.audio = document.createElement('audio');

	player.state = 'stopped';

	// remove the pause button
	//$('#pause').hide();

	// add a watcher to set the progress bar
	window.setInterval(function (){
		// calculate percentage of time passed on current song
		var percent = 100*player.audio.currentTime/player.audio.duration;

		// set loader if appropiate
		if (player.state == 'playing' && player.audio.currentTime == 0)
		{
			// must be loading
			$('#controls .progress .bar').hide();
			$('#controls .progress').css('background','url("load.gif")')
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

	$('#empty').click(player.empty);
	$('#share').click(player.sharePlaylist);
	$(document).bind('keydown','ctrl+s',player.sharePlaylist);

	// dynamic (live) events for playlist items
	//$('#playlist .item').live('click',doSomething);

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
	$('*').not('#search').bind('keydown','down',player.next);
	$('*').not('#search').bind('keydown','up',player.prev);
	$('*').not('#search').bind('keydown','space',player.playPause);

//	$('*').not('#search').bind('keydown','up',player.hoverNext);
//	$('*').not('#search').bind('keydown','down',player.hoverPrev);
//	$('*').not('#search').bind('keydown','return',player.selectThis);


	// click to search on nowPlaying
	$('#nowPlaying .artist, #nowPlaying .album').click(function()
	{
		var query = $(this).text();
		$('#search').val(query);
		searcher.search();
	});

	$('#stop').click(player.stop);
	$(document).bind('keydown','esc',player.stop);

	// load a playlist by ID from hash in URL
	player.loadHash();
	window.onhashchange = player.loadHash;
}

player.loadHash = function()
{
	if (document.location.hash)
		// load the playlist id given, without the hash
		player.loadPlaylist( document.location.hash.slice(1) );
}

// enqueue an item using the metadata
player.enqueue = function (meta)
{
	// create an element to represent the item
	item = $('<div class="item">'+
	'<div class="artist">'+meta.artist+'</div><div class="title">'+meta.title+'</div>'+
	'<div class="album">'+meta.album+'</div></div>');

	// attach metadata to the item
	item.data('meta',meta);

	// add event to select on click
	item.click(player.selectThis);

	// attach it to the DOM, playlist
	item.hide().fadeIn().appendTo('#playlist');

	// make sure there is no message
	$('#player .message').hide();

	// play the item on first add (to empty playlist) or add to idle playlist
	if (player.state == 'stopped' || !$('#playlist').length)
	{
		// each will select just that item...
		item.each(player.selectThis);
		player.play();
	}
	else
	{
		var length = $("#playlist")[0].scrollHeight;
	        $("#playlist").stop().animate({scrollTop:length});
	}
}

// select item on the playlist, playing if appropiate
//  (as an element in 'this' context)
player.selectThis = function ()
{
	// highlight the item as currently playing, clearing others
	$('#playlist .item').removeClass('selected');
	$(this).addClass('selected');

	// scroll the item on the playlist into view (around half way down list)
	// find position relative to the top of the list, remove half the
	// height of the list.
	var offset = $('#playlist .selected').offset().top 
		+ $('#playlist').scrollTop() 
		- $('#playlist').offset().top
		- $('#playlist').height()/4;


	// update the meta area with album art etc. Forcing not-null
	// so fields are always updated
	var meta = $(this).data('meta');
	$('#nowPlaying .title').text(String(meta.title));
	$('#nowPlaying .album').text(String(meta.album));
	$('#nowPlaying .artist').text(String(meta.artist));
	$('#nowPlaying .year').text(String(meta.year));

	// albumArt
	if (meta.albumArtId)
		$('#albumArt').html('<img src="albumArt.php?id='+meta.albumArtId+'" />');
	else
		$('#albumArt').empty();

	// play the file
	player.audio.setAttribute('src', 'download.php?id='+meta.id);

	//$('#playlist').scrollTop(offset);
	// animate to offset, clearing any other previous, possibly conflicting
	// animations
	$('#playlist').stop().animate({scrollTop:offset});

	// play it if appropiate (it always is!)
	//if (player.state == 'playing')
		player.play();
}

// play the item currently selected on the playlist, from start
player.play = function ()
{
	if (!player.audio.src)
		return;

	player.state = 'playing';

	// make sure the controls are set right
	$('#play').hide();
	$('#pause').show();

	player.audio.play();
}

// select the next song, play if playing already, returns false
// so can be used to override normal events
player.next = function ()
{
	var item = $('#playlist .selected').next();

	// if there is no next item, default to the first item (repeat all)
	if (!item.length)
		item = $('#playlist .item:first-child');

	item.each(player.selectThis);

	return false;
}

// select the previous song, play if playing already, returns false
// so can be used to override normal events
player.prev = function ()
{
	$('#playlist .selected').prev().each(player.selectThis);
	return false;
}

player.playPause = function ()
{
	switch (player.state)
	{
		case 'paused':
		case 'stopped':
			player.play();
		break;
		case 'playing':
			player.pause();
		break;
	}
	return false;
}

player.pause = function ()
{
			
	player.audio.pause();
	// update icon
	$('#pause').hide();
	$('#play').show();
	// update state
	player.state = 'paused';

	return false;
}

player.stop = function ()
{
	// pause it, resetting counter
	player.audio.pause();
	if (player.audio.currentTime)
		player.audio.currentTime = 0;

	// update icon
	$('#pause').hide();
	$('#play').show();

	// update state
	player.state = 'stopped';
}

player.playlistIDs = function ()
{
	// get an array of playlist elements
	elements = $('#playlist .item').get();

	// iterate over the elements, collecting IDs
	ids = Array();
	for (var i in elements)
	{
		var id = $(elements[i]).data('meta').id;
		ids.push(id);
	}

	return ids;
}

//empty playlist, reset player
player.empty = function()
{
	player.audio.src = null;
	player.stop();

	if (!$('#playlist .item').length)
	{
		$('#player .message').hide().fadeIn().text('Playlist is already empty');
		return
	}

	$('#albumArt,#nowPlaying .title,#nowPlaying .album,#nowPlaying .artist,#nowPlaying .year').empty();
	$('#albumArt img').attr('src',null);

	$('#playlist .item').css('z-index',2000).fadeOut(function(){
		$(this).remove();
	});
}

// load a playlist from the server by playlist ID
player.loadPlaylist = function(id)
{
	// set off a request for the list
	$.ajax(
	{
		data:{load:id},
		url: "playlist.php",
		success: function(items)
		{
			if (items.error)
			{
				$('#player .message').show().text(items.error);
				return;
			}

			for (var i in items)
				player.enqueue(items[i]);

			// stop the playlist from scolling to the bottom
			$('#playlist').stop();

			// clear message
			$('#player .message').hide();
		}
	});
	// prepare playlist, bypassing fade out for messages
	$('#playlist').empty();
	player.empty();
	$('#player .message').show().text('Loading playlist...');
}

// save the current playlist on the server by posting IDs.
// informs the user of the new link containing the URL
player.sharePlaylist = function()
{
	if (!$('#playlist .item').length)
	{
		$('#player .message').hide().fadeIn().text('Nothing to share yet!');
		return
	}

	var baseURL = document.location.toString().replace(/#.+$/,'');
	var idsCsv = player.playlistIDs().toString();

	// set off a request for the id
	$.ajax(
	{
		data:{save:idsCsv},
		url: "playlist.php",
		success: function(data)
		{
			if (data.error)
			{
				alert(data.error);
				return;
			}

			var url = baseURL+'#'+data.id;

			$('#player .message').show().html('<p>Playlist published to </p><a href="'+url+'">'+url+'</a>');
		}
	});

	
	$('#playlist').empty();
	player.empty();
	$('#player .message').show().text('Publishing playlist...');
}
