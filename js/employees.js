$(document).ready(function() {
	//change controls and header when hitting the top of the page
	$('#controls').data('top', $('#controls').offset().top);
	$(window).scroll(function() {
		if ($(window).scrollTop() > $('#controls').data('top')) { 
			$('#controls').css({
				'position': 'fixed',
				'top': '0',
				'width': $('#content').width(),
				'border-radius': '0 0 0 0'
			});
			$('thead').css({
				'position': 'fixed',
				'top': $('#controls').css('height'),
				'width': $('#content').width(),
				'border-radius': '0 0 8px 8px'
			}); 
		}
		else {
			$('#controls').css({
				'position': 'static',
				'top': 'auto',
				'width': '100%',
				'border-radius': '8px 8px 0 0'
			});
			$('thead').css({
				'position': 'static',
				'top': 'auto',
				'width': '100%',
				'border-radius': '0 0 0 0'
			});
		}
	});

	//set up datatables
    var table = $('#employeesTable').DataTable({
		'paging': false,
		'dom': 'rti'
	});
	$('#filter').on('keyup', function() {
		table.search(this.value).draw();
	});
});