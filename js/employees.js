$(document).ready(function() {
	//change controls and header when hitting the top of the page
	//TODO: fix column width changing when header hits the top
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
		'dom': 'rti',
		'order': [1, 'asc'],
		'columnDefs': [
			{'orderable': false, 'targets': 0},
			{'searchable': false, 'targets': 0}
		]
	});
	$('#filter').on('keyup', function() {
		table.search(this.value).draw();
	});
	
	//checkboxes
	$('.selectCheckbox').click(function() {
		if ($('.selectCheckbox:checked').length > 0) {
			$('#controlsEdit').addClass('controlsEditEnabled').removeClass('controlsEditDisabled');
			$('#controlsDelete').addClass('controlsDeleteEnabled').removeClass('controlsDeleteDisabled');
			$('#controlsEdit, #controlsDelete').qtip('disable');
		}
		else {
			$('#controlsEdit').addClass('controlsEditDisabled').removeClass('controlsEditEnabled');
			$('#controlsDelete').addClass('controlsDeleteDisabled').removeClass('controlsDeleteEnabled');
			$('#controlsEdit, #controlsDelete').qtip('enable');
		}
	});
	
	//qtip
	$('#controls [title]').qtip({
		'style': {'classes': 'qtip-tipsy-custom'},
		'position': {
			'my': 'bottom center',
			'at': 'top center',
			'adjust': {'y': -12}
		}
	});
	
	//add
	$('#controlsAdd').click(function(event) {
		alert('add');
		event.preventDefault();
	});
	
	//edit
	$('#controlsEdit').click(function(event) {
		if ($('#controlsEdit').hasClass('controlsEditEnabled')) {
			alert('edit');
		}
		event.preventDefault();
	});
	
	//delete
	$('#controlsDelete').click(function(event) {
		if ($('#controlsDelete').hasClass('controlsDeleteEnabled')) {
			alert('delete');
		}
		event.preventDefault();
	});
});