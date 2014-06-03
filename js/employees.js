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
			$('#popup > div > div').html(data.html);
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
					$('#popup .invalid').removeClass('invalid');
					if (data.status == 'success') {
						$('#popup > div > div').html(data.html);
					}
					else {
						$.each(data, function(key, value) {
							if (key != 'status') {
								$('#popup [name=' + key + ']').addClass('invalid');
								$('#popup [name=' + key + ']').qtip({
									'content': value,
									'style': {'classes': 'qtip-tipsy-custom'},
									'position': {
										'my': 'bottom center',
										'at': 'top center'
									},
									show: {'event': 'focus'},
									hide: {'event': 'blur'}
								});
							}
						});
					}
				});
			});
		});
		event.preventDefault();
	});
	
	//edit
	$('#controlsEdit').click(function(event) {
		if ($('#controlsEdit').hasClass('controlsEditEnabled')) {
			var ajaxData;
			var $checked = $('.selectCheckbox:checked');
			if ($checked.length == 0) {
				ajaxData = {'action': 'edit', 'type': 'employee', 'id': id};
			}
			else if ($checked.length == 1) {
				ajaxData = {'action': 'edit', 'type': 'employee', 'id': $checked.attr('id')};
			}
			else {
				ajaxData = {'action': 'editMany', 'type': 'employee'};
			}
			
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: ajaxData
			}).done(function(data) {
				$('#popup > div > div').html(data.html);
				$('#popup').show();
				if (ajaxData.action == 'editMany') {
					$('#popup input[type="checkbox"]').click(function() {
						$(this).next().next().prop('disabled', !$(this).prop('checked'));
					});
				}
				
				$('#editBtn').click(function() {
					//if action is editMany, make sure we selected at least one field to change
					if ($checked.length > 1 && $('#popup input[type="checkbox"]:checked').length == 0) {
						return;
					}
					
					//get the ids we're changing
					var selectedIDs = [];
					if ($checked.length == 0) {
						selectedIDs.push(id);
					}
					else {
						$.each($checked, function(index, value) {
							selectedIDs.push(value.id);
						});
					}
					var idStr = selectedIDs.join();
					
					ajaxData = $('#popup input[type!="checkbox"], #popup select').serializeArray();
					ajaxData.push({'name': 'action', 'value': 'editSave'});
					ajaxData.push({'name': 'type', 'value': 'employee'});
					ajaxData.push({'name': 'id', 'value': idStr});
					
					$.ajax({
						url: 'ajax.php',
						type: 'POST',
						data: ajaxData
					}).done(function(data) {
						$('#popup .invalid').removeClass('invalid');
						if (data.status == 'success') {
							location.reload();
						}
						else {
							$.each(data, function(key, value) {
								if (key != 'status') {
									$('#popup [name=' + key + ']').addClass('invalid');
									$('#popup [name=' + key + ']').qtip({
										'content': value,
										'style': {'classes': 'qtip-tipsy-custom'},
										'position': {
											'my': 'bottom center',
											'at': 'top center'
										},
										show: {'event': 'focus'},
										hide: {'event': 'blur'}
									});
								}
							});
						}
					});
				});
			});
		}
		event.preventDefault();
	});
	
	//delete
	$('#controlsDelete').click(function(event) {
		if ($('#controlsDelete').hasClass('controlsDeleteEnabled')) {
			var ajaxData;
			var $checked = $('.selectCheckbox:checked');
			if ($checked.length == 0) {
				ajaxData = {'action': 'delete', 'type': 'employee', 'id': id};
			}
			else if ($checked.length == 1) {
				ajaxData = {'action': 'delete', 'type': 'employee', 'id': $checked.attr('id')};
			}
			else {
				ajaxData = {'action': 'deleteMany', 'type': 'employee'};
			}
		
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: ajaxData
			}).done(function(data) {
				$('#popup > div > div').html(data.html);
				$('#popup').show();
				
				$('#deleteBtn').click(function() {
					//get the ids we're changing
					var selectedIDs = [];
					if ($checked.length == 0) {
						selectedIDs.push(id);
					}
					else {
						$.each($checked, function(index, value) {
							selectedIDs.push(value.id);
						});
					}
					var idStr = selectedIDs.join();
				
					$.ajax({
						url: 'ajax.php',
						type: 'POST',
						data: {
							'action': 'deleteSave',
							'type': 'employee',
							'id': idStr
						}
					}).done(function(data) {
						location.reload();
					});
				});
			});
		}
		event.preventDefault();
	});
});