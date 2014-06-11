$(document).ready(function() {
	//determine the selectedPage and apply style
    var url = window.location.search;
	var filename = 'index';
	if (url != '') {
		var stop = url.lastIndexOf('&');
		filename = (stop == -1) ? url.substring(url.lastIndexOf('type') + 5) : url.substring(url.lastIndexOf('type') + 5, stop);
	}
	$('#' + filename).addClass('selectedPage');
	
	//set qtip globals
	$.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
		'show': {
			'delay': 0,
			'effect': false
		},
		'hide': {'effect': false}
	});
	//activate menu qtips
	$('nav [title]').qtip({
		'style': {'classes': 'qtip-tipsy-custom qtip-tipsy-custom-menu'},
		'position': {
			'my': 'center left',
			'at': 'center right',
			'adjust': {'x': 16},
		}
	});
	
	//show or hide search box
	$('#searchLink').click(function() {
		if ($('#searchDiv').is(':visible')) {
			$('#searchDiv').hide();
		}
		else {
			$('#searchDiv').show();
			$('#searchTerm').val('');
			$('#searchTerm').focus();
		}
	});
	
	//search function
	$('#searchTerm').keyup(function() {
		$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: {
					'action': 'search',
					'term': $('#searchTerm').val()
				}
			}).done(function(data) {
				if (data.status == 'success') {
					var searchHtml = '';
					$.each(data.results, function(index, result) {
						searchHtml += '<a href="' + result.url + '"><div class = "resultItem">';
						searchHtml += '<img src="images/icons/' + result.image + '" alt="' + result.name + '"><br>';
						searchHtml += result.name;
						searchHtml += '</div></a>';
					});
					$('#searchResults').html(searchHtml);
				}
				else {
					//TODO: error message
				}
			});
	});
	
	$(window).resize(function() {
		var searchWidth = $(window).width() - 124 - 60;
		$('#searchDiv').css('width', searchWidth + 'px');
	});
	
	$(window).resize();
});