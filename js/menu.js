$(document).ready(function() {
	//determine the selectedPage and apply style
    var url = window.location.pathname;
    var filename = url.substring(url.lastIndexOf('/') + 1, url.lastIndexOf('.'));
	$('#' + filename).addClass('selectedPage');
	
	//show tooltips when hovering over menu items
	$('nav img').hover(
		function() {
			var eleID = $(this).parent().parent().attr('id');
			$('#' + eleID + 'Tooltip').show();
		},
		function() {
			var eleID = $(this).parent().parent().attr('id');
			$('#' + eleID + 'Tooltip').hide();
		}
	);
	
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