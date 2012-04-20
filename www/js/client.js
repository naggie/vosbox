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

var searcher = {};
var player = {};

// point SM2 to the SWF library files for flash fallback
soundManager.url = 'js/SoundManager2/';
soundManager.useHTML5Audio = true;
soundManager.preferFlash = false;
soundManager.flashVersion = 9;
// occupies an 8x8px square, flash gives priority to very visible objects
//soundManager.useHighPerformance = true;

soundManager.ontimeout(function(status){
	alert('No MP3 codec available using HTML5 or flash. Please enable flash, or use Google chrome');
});

// global gritter potions
$.extend($.gritter.options, { 
        position: 'bottom-right',
});

// jQuery will fire this callback when the DOM is ready
$(function (){
	// nowhere should be selectable
	$('body').disableSelection();

	if($.browser.msie && (parseInt($.browser.version) < 9) ){
		alert('Update your browser, please');
		return;
	}

	soundManager.onready(player.init);
	searcher.init();
});

// returns a DOM element representing a song on the playlist.
// contains data('meta') which is currently what the object given to it is
// Planned: individual key based metadata (artist, album etc)
function createItem (result){
	// add the HTML
	item = $('<div class="item"><div class="removeButton">&times;</div>'+
	'<div class="artist">'+result.artist+'</div><div class="title">'+result.title+'</div>'+
	'<span class="album">'+result.album+'</span><span class="time">'+result.time+'</span></div>');

	var icon = $('<div class="icon"></div>').prependTo(item);

	// attach metadata
	item.data('meta',result);

	if (!result.albumArtId) return item;

	var src = 'api/albumArt.php?id='+result.albumArtId;
	var img = $('<img />').attr('src',src).prependTo(icon);

	img.hide().load(function(){
		$(this).fadeIn();
	});

	return item;
}

// returns original search result from item. Storage method will change soon
function parseItem (item){
	return $(item).data('meta');
}


searcher.init = function (){
	// f to focus search
	$(document).bind('keyup', 'f', function (){
		$('#search').focus().select();
	});

	// CTRL+A to add all results to playlist
	$(document).bind('keydown','ctrl+a',searcher.enqueueAll);
	$('#enqueueAll').click(searcher.enqueueAll);

	$('#search').click(function (){
		$(this).select();
	});

	// override form submit
	$('#searcher form').submit(function(){
		searcher.search();
		return false;
	});

	// dragging over the items should not highlight any text
	$('#searchResults').disableSelection();

	$('#doSearch').click(searcher.search);


	$('#searcher .message').show().text('To begin, search for music in the box above');
}

searcher.search = function (){
	$.ajax(
	{
		data:{keywords:$('#search').val()},
		url: "api/search.php",
		success: searcher.showResults
	});

	// reset results area
	$('#searchResults').empty().scrollTop(0);
	$('#searcher .message').show().text('Searching...');

	// when used as a callback, replace the default action
	return false;
}

// given an array of nodes, display them
searcher.showResults = function (results){
	if (results.error){
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

searcher.addResult = function (result){

	// add to search results area
	var item = createItem(result).appendTo('#searchResults');

	// click to enqueue
	item.rightClick(function (){
		meta = parseItem(this);
		player.enqueue(meta);
		$(this).remove();
	});

	// click to play immediately
	item.click(function (){
		meta = parseItem(this);
		player.enqueue(meta,true);
		$(this).remove();
	});
}

// enqueue everything currently in the search result area
searcher.enqueueAll = function (){
	$('#searchResults .item').each(function (){
		player.enqueue(parseItem(this));
	});

	// stop the playlist from scolling to the bottom
	$('#playlist').stop();

	// remove items from playlist
	$('#searchResults').empty();	
	$('#searcher .message').show().text('All search results added!');

	// allow override if being used as callback
	return false;
}

// modify CSS to make search pane obscure player, fading everything in
// without player visible. This allows CSS to define the full view,
// using pure JS to handle the dynamic UI.
player.init = function (){
	// controls: events
	$('#next').click(player.next);
	$('#prev').click(player.prev);
	$('#pause,#play').click(function(){player.sound.togglePause()});
	$('#stop').click(function(){player.sound.stop()});

	$('#empty').click(player.empty);
	$('#share').click(player.sharePlaylist);
	
	$('#downloadSelected').click(player.downloadSelected);

	$('#shuffle').click(function(){
		$('#playlist').shuffle();
	});

	// uses jqueryUI
	$('#playlist').sortable({
		axis: "y",
		placeholder: "placeholder"
	});

	$(document).bind('keydown','s',function(){
		$('#playlist').shuffle();
	});

	// seek
	$('#controls .progress').click(function(e){
		if (!player.sound) return false;

		// translate X click pos to time in song
		var offset = e.pageX - $(this).offset().left;
		var proportion = offset/$(this).width();
		var newTime = proportion*player.sound.duration;

		// if user is seeking, they almost certainly want the song to play
		player.sound.play();

		// set the new time. Note that, for some unknown reason,
		// this does not always work...
		player.sound.setPosition(newTime);
	});
	
	// if not searching, up and down are prev and next
	$(document).bind('keydown','right',player.next);
	$(document).bind('keydown','left',player.prev);
	$(document).bind('keydown','down',player.next);
	$(document).bind('keydown','up',player.prev);
	$(document).bind('keydown','space',function(){
		player.sound.togglePause();
		return false;
	});

	// click to search on nowPlaying
	$('#nowPlaying .artist').click(function(){
		var query = $(this).text();
		$('#search').val(query);
		searcher.search();
	});

	$('#albumArt,#nowPlaying .album').click(function(){
		var query = $('#nowPlaying .artist').text()+' - '+$('#nowPlaying .album').text();
		$('#search').val(query);
		searcher.search();		
	});

	$(document).bind('keydown','esc',function(){player.sound.stop()});
	$(document).bind('keydown','d',player.downloadSelected);

	// load a playlist by ID from hash in URL
	if (document.location.hash)
		// load the playlist id given, without the hash
		player.loadPlaylist( document.location.hash.slice(1) );
	// try to resume the old playlist
	else if(!player.resume())
		// if not, the user will probably want to search immediately for
		// new songs. So include them.
		$('#search').focus();

	window.onhashchange = function(){
		// load the playlist id given, without the hash
		player.loadPlaylist( document.location.hash.slice(1) );
	}

	// bind event to hibernate playlist
	window.onbeforeunload = player.hibernate;
	// define default audiofile behaviour
	soundManager.defaultOptions = {
		whileplaying : player.updateElapsed,
		whileloading : player.updateDataBar,
		onfinish : player.next,
		onresume : function(){
			$('#play').hide();
			$('#pause').show();	
		},
		onplay : function(){
			$('#play').hide();
			$('#pause').show();	
		},

		onpause : function(){
			$('#pause').hide();
			$('#play').show();	
		},
		onstop: function(){
			$('#pause').hide();
			$('#play').show();	
		},
	};
}

// enqueue an item using the metadata
player.enqueue = function (meta,playNow){
	var item = createItem(meta);

	// first item? play it regardless!
	if (!$('#playlist .item').length)
		playNow = true;

	// add event to select on click
	item.click(player.playThis);

	// add event to remove on right click
	item.rightClick(function(){
		if ($(this).hasClass('selected'))
			player.next();
	
		$(this).remove();
	});

	$('.item .removeButton').click(function(){
		if ($(this).parent().hasClass('selected'))
			player.next();
	
		$(this).parent().remove();
	});

	// attach it to the DOM, next or end of playlist
	if (playNow && $('#playlist .item').length)
		// right after the currently playing item
		item.hide().fadeIn().insertAfter('#playlist > div.selected');
	else{
		// add/scroll to bottom
		item.hide().fadeIn().appendTo('#playlist');
		var length = $("#playlist")[0].scrollHeight;
	        $("#playlist").stop().animate({scrollTop:length});
	}

	if (playNow) item.each(player.playThis);

	// make sure there is no message
	$('#player .message').hide();
}

player.downloadSelected = function(){
	if (!$('#playlist .item').length)
	{
		$('#player .message').hide().fadeIn().text('Nothing to download yet!');
		return
	}

	var id = parseItem('#playlist .selected').id;

	document.location = 'api/download.php?id='+id;
}


// select item on the playlist, playing if appropiate
//  (as an element in 'this' context)
player.playThis = function (){
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
	var meta = parseItem(this);
	$('#nowPlaying .title').text(String(meta.title));
	$('#nowPlaying .album').text(String(meta.album));
	$('#nowPlaying .artist').text(String(meta.artist));
	$('#nowPlaying .year').text(String(meta.year));

	// albumArt
	if (meta.albumArtId)
		$('#albumArt').html('<img src="api/albumArt.php?id='+meta.albumArtId+'" />');
	else
		$('#albumArt').empty();

	if (player.sound)
		player.sound.destruct();

	$('#pause').show();	
	$('#play').hide();
	$('#controls .progress .bar').css('width',0);

	// play the file
	player.sound = soundManager.createSound({
    		id : meta.id, 
    		url : 'api/download.php?id='+meta.id,
	});

	// preload the next one...

	//$('#playlist').scrollTop(offset);
	// animate to offset, clearing any other previous, possibly conflicting
	// animations
	$('#playlist').stop().animate({scrollTop:offset});

	player.sound.play();
}

// select the next song, play if playing already, returns false
// so can be used to override normal events
player.next = function (){
	var item = $('#playlist .selected').next();

	// if there is no next item, default to the first item (repeat all)
	if (!item.length)
		item = $('#playlist .item:first-child');

	item.each(player.playThis);

	return false;
}

// select the previous song, play if playing already, returns false
// so can be used to override normal events
player.prev = function (){
	$('#playlist .selected').prev().each(player.playThis);
	return false;
}

// update the progress bar for time elapsed during the song
// to be sent as a callback to whileplaying
player.updateElapsed = function(){
	// calculate percentage of time passed on current song
	var percent = 100*this.position/this.duration;

	// set progress
	$('#controls .progress > .bar#elapsed').css('width',percent+'%');
}

// update the progress bar for bytes downloaded
// to be sent as a callback to whileplaying
player.updateDataBar = function(){
	// calculate percentage of time passed on current song
	var percent = 100*this.bytesLoaded/this.bytesTotal;

	// set progress
	$('#controls .progress > .bar#data').css('width',percent+'%');
}


// return an array of playlist objects
player.getPlaylistObjects = function (){
	// get an array of playlist elements
	elements = $('#playlist .item').get();

	// iterate over the elements, collecting IDs
	objects = Array();

	for (var i in elements)
		objects.push( parseItem(elements[i]) );

	return objects;
}

//empty playlist, reset player
player.empty = function(){
	if (player.sound)
		player.sound.destruct();

	if (!$('#playlist .item').length){
		$('#player .message').hide().fadeIn().text('Playlist is already empty');
		return
	}

	$('#albumArt,#nowPlaying .title,#nowPlaying .album,#nowPlaying .artist,#nowPlaying .year').empty();
	$('#albumArt img').attr('src',null);

	$('#playlist .item').css('z-index',2000).fadeOut(function(){
		$(this).remove();
	});

	$('#pause').hide();
	$('#play').show();
	$('#controls .progress .bar').css('width',0);
}

// load a playlist from the server by playlist ID
player.loadPlaylist = function(id){
	// set off a request for the list
	$.ajax(
	{
		data:{load:id},
		url: "api/playlist.php",
		type: 'POST',
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
	if (!$('#playlist .item').length){
		$('#player .message').hide().fadeIn().text('Nothing to share yet!');
		return
	}

	// get array of playlist ids
	var objects = player.getPlaylistObjects();
	var ids = Array();

	for (var i in objects)
		ids.push( objects[i].id );

	var baseURL = document.location.toString().replace(/#.+$/,'');

	// set off a request for the id
	$.ajax({
		// include a comma separated array of IDs
		data:{save:ids.toString()},
		type: 'POST',
		url: "api/playlist.php",
		success: function(data){
			if (data.error){
				alert(data.error);
				return;
			}

			var url = baseURL+'#'+data.id;

			$.gritter.add({
					title:'Playlist published!',
					text:'<p>Share this link with your friends:</p> <p><a href="'+url+'">'+url+'</a></p>',
					sticky:true,
					time:1000,
			});
		}
	});

	$.gritter.add({title:'One moment...',text:'Publishing playlist'});
}

// save the playlist locally
player.hibernate = function(){
	localStorage.playlist = JSON.stringify( player.getPlaylistObjects() );
}

// load the playlist from last session
player.resume = function(){
	// for some reason this fixes an error when using SSL ...?
	// Apparently trying to parse a null string into JSON is ILLEGAL!
	if (!localStorage.playlist)
		return false;

	var items = JSON.parse(localStorage.playlist);

	if (items.length){
		for (var i in items)
			player.enqueue(items[i]);

		// stop the playlist from scolling to the bottom
		$('#playlist').stop();

		// clear message
		$('#player .message').hide();
		return true;
	}
	else
		return false;
}
