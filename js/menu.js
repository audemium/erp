$(document).ready(function() {
	//determine the selectedPage and apply style
	if (typeof type !== 'undefined') {
		$('#' + type + ' div').css('background-position', '0 -32px');
	}
	
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
			$('#searchLink').children().css('background-position', '0 0');
			$('#searchDiv').hide();
		}
		else {
			$('#searchLink').children().css('background-position', '0 -32px');
			$('#searchDiv').show();
			$('#searchTerm').val('');
			$('#searchResults').html('Type to begin searching...');
			$('#searchTerm').focus();
		}
	});
	
	//search function
	$('#searchTerm').keyup(debounce(function(event) {
		if ($('#searchTerm').val() != '') {
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
						keyImg = (result.type == 'employee' || result.type == 'order') ? result.type : 'star';
						searchHtml += '<a href="item.php?type=' + result.type + '&id=' + result.id + '"><div class = "resultItem">';
						searchHtml += '<img src="images/icons/' + keyImg + '.png" alt="' + result.name + '"><br>';
						searchHtml += result.name;
						searchHtml += '</div></a>';
					});
					$('#searchResults').html(searchHtml);
				}
				else {
					//TODO: error message
				}
			});
		}
		else {
			$('#searchResults').html('Type to begin searching...');
		}
	}, 250));
	
	$(window).resize(function() {
		var searchWidth = $(window).width() - 124 - 60;
		$('#searchDiv').css('width', searchWidth + 'px');
	});
	
	$(window).resize();
});