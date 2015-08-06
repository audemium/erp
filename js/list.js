$(document).ready(function() {
	//change topControls and header when hitting the top of the page
	//TODO: fix column width changing when header hits the top
	$('#topControls').data('top', $('#topControls').offset().top);
	$(window).scroll(function() {
		if ($(window).scrollTop() > $('#topControls').data('top')) { 
			$('#topControls').css({
				'position': 'fixed',
				'top': '0',
				'width': $('#content').width(),
				'border-radius': '0 0 0 0'
			});
			$('thead').css({
				'position': 'fixed',
				'top': $('#topControls').css('height'),
				'width': $('#content').width(),
				'border-radius': '0 0 8px 8px'
			}); 
		}
		else {
			$('#topControls').css({
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

	//set up dataTables
	var table = $('#itemTable').DataTable({
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
		checkCheckboxes();
	});
	
	//qtip
	$('#topControls [title]').qtip({
		'style': {'classes': 'qtip-tipsy-custom'},
		'position': {
			'my': 'bottom center',
			'at': 'top center',
			'adjust': {'y': -12}
		}
	});
	
	//run in any case browser cached any checks
	checkCheckboxes();
});

function checkCheckboxes() {
	if ($('.selectCheckbox:checked').length > 0) {
		$('#topControlCenter .controlEdit').addClass('editEnabled').removeClass('editDisabled');
		$('#topControlCenter .controlDelete').addClass('deleteEnabled').removeClass('deleteDisabled');
		$('#topControlCenter .controlEdit, #topControlCenter .controlDelete').qtip('disable');
	}
	else {
		$('#topControlCenter .controlEdit').addClass('editDisabled').removeClass('editEnabled');
		$('#topControlCenter .controlDelete').addClass('deleteDisabled').removeClass('deleteEnabled');
		$('#topControlCenter .controlEdit, #topControlCenter .controlDelete').qtip('enable');
	}
}