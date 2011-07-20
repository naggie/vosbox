search = new Object();
search.placeholder = 'Search for music...';

$(document).ready(function()
{

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


if($.browser.msie && (parseInt($.browser.version) < 9) )
{
	$('body').text('Use a better browser :)');
	return;
}

$('#search').click(function (){
	$(this).val('');
});

// override submit
$('#searchBar form').submit(function(){
	search.do();
	$('#search').val('');
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
	// reset results area
	$('#searchResults').html('').scrollTop(0);

	for (var i in results)
		search.addResult(results[i]);
}

search.addResult = function (result)
{
	// display the widget
	$('#searchResults').append('<div class="item">'+
	'<div class="artist">'+result.artist+'</div><div class="name">'+result.title+'</div>'+
	'<div class="album">'+result.album+'</div></div>'
	);
}
