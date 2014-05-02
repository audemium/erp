$(document).ready(function() {
	//add close for all actions
	$('#popup').on('click', '#close', function(event) {
		$('#popup').hide();
		event.preventDefault();
	});
	
	//add
	$('#controlsAdd').click(function(event) {
		$.ajax({
			url: 'ajax.php',
			type: 'POST',
			data: {
				'action': 'add',
				'type': 'employee'
			}
		}).done(function(data) {
			$('#popup > div').html(data.html);
			$('#popup').show();
			
			$('#addBtn').click(function() {
				var ajaxData = $('#popup input[type!="checkbox"], #popup select').serializeArray();
				ajaxData.push({'name': 'action', 'value': 'addSave'});
				ajaxData.push({'name': 'type', 'value': 'employee'});
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: ajaxData
				}).done(function(data) {
				});
			});
		});
		event.preventDefault();
	});
	
	//edit
	$('#controlsEdit').click(function(event) {
		if ($('#controlsEdit').hasClass('controlsEditEnabled')) {
			var ajaxData;
			if ($('.selectCheckbox:checked').length == 0) {
				ajaxData = {'action': 'edit', 'type': 'employee', 'id': id};
			}
			else if ($('.selectCheckbox:checked').length == 1) {
				ajaxData = {'action': 'edit', 'type': 'employee', 'id': $('.selectCheckbox:checked').attr('id')};
			}
			else {
				ajaxData = {'action': 'editMany', 'type': 'employee'};
			}
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: ajaxData
			}).done(function(data) {
				$('#popup > div').html(data.html);
				$('#popup').show();
				if (ajaxData.action == 'editMany') {
					$('#popup input[type="checkbox"]').click(function() {
						$(this).next().next().prop('disabled', !$(this).prop('checked'));
					});
				}
				
				$('#editBtn').click(function() {
					var selectedIDs = $('.selectCheckbox:checked')
						.map(function() { return this.id; })
						.get();
					var action = ($('.selectCheckbox:checked').length > 1) ? 'editMany' : 'edit';
					ajaxData = {'action': action, 'type': 'employee', 'id': selectedIDs};
				});
			});
		}
		event.preventDefault();
	});
	
	//delete
	$('#controlsDelete').click(function(event) {
		if ($('#controlsDelete').hasClass('controlsDeleteEnabled')) {
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: {
					'action': 'delete',
					'type': 'employee'
				}
			}).done(function(data) {
				$('#popup > div').html(data.html);
				$('#popup').show();
			});
		}
		event.preventDefault();
	});
});