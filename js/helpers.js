function debounce(fn, delay) {
	var timer = null;
	return function () {
		var context = this, args = arguments;
		clearTimeout(timer);
		timer = setTimeout(function () {
			fn.apply(context, args);
		}, delay);
	};
}

function resetRecurringOptions(popupID) {
	$(popupID + ' [name=interval], ' + popupID + ' [name=dayOfMonth], ' + popupID + ' [name=startDate], ' + popupID + ' [name=endDate]').val('');
	$(popupID + ' ul').eq(1).hide();
}

$(document).ready(function() {
	//when receiving the http code that indicates the user is logged out, redirect to the login page
	$(document).ajaxError(function(event, jqxhr, settings, exception) {
		if (jqxhr.status === 401) {
			window.location.href = 'login.php';
		}
	});

	//add close for all actions
	$('.popup').on('click', '.close', function(event) {
		$('.popup').hide();
		event.preventDefault();
	});
	
	//add
	$('#topControlCenter .controlAdd').click(function(event) {
		$.ajax({
			url: 'ajax.php',
			type: 'POST',
			data: {
				'action': 'add',
				'type': type
			}
		}).done(function(data) {
			$('#defaultPopup > div > div').html(data.html);
			$('.dateInput').datepicker({
				dateFormat: dateFormatJS,
				defaultDate: +7
			}).datepicker('setDate', new Date());
			$('#defaultPopup').show();
			
			$('#addBtn').click(function() {
				var ajaxData = $('#defaultPopup input[type!="checkbox"], #defaultPopup select').serializeArray();
				ajaxData.push(
					{'name': 'action', 'value': 'addSave'},
					{'name': 'type', 'value': type}
				);
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: ajaxData
				}).done(function(data) {
					$('#defaultPopup .invalid').removeClass('invalid');
					if (data.status == 'success') {
						if ('html' in data) {
							$('#defaultPopup > div > div').html(data.html);
						}
						else {
							location.href = 'item.php?type=' + data.type + '&id=' + data.id;
						}
					}
					else {
						$.each(data, function(key, value) {
							if (key != 'status') {
								$('#defaultPopup [name=' + key + ']').addClass('invalid');
								$('#defaultPopup [name=' + key + ']').qtip({
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
	$('#topControlCenter .controlEdit').click(function(event) {
		if ($(this).hasClass('editEnabled')) {
			var ajaxData;
			var $checked = $('.selectCheckbox:checked');
			if ($checked.length == 0) {
				//item view
				ajaxData = {'action': 'edit', 'type': type, 'id': id};
			}
			else if ($checked.length == 1) {
				//list view, one checked
				ajaxData = {'action': 'edit', 'type': type, 'id': $checked.attr('id')};
			}
			else {
				//list view, more than one checked
				ajaxData = {'action': 'editMany', 'type': type};
			}
			
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: ajaxData
			}).done(function(data) {
				$('#defaultPopup > div > div').html(data.html);
				$('.dateInput').datepicker({
					dateFormat: dateFormatJS
				});
				$('#defaultPopup').show();
				if (ajaxData.action == 'editMany') {
					$('#defaultPopup input[type="checkbox"]').click(function() {
						$(this).next().next().prop('disabled', !$(this).prop('checked'));
					});
				}
				
				$('#editBtn').click(function() {
					//if action is editMany, make sure we selected at least one field to change
					if ($checked.length > 1 && $('#defaultPopup input[type="checkbox"]:checked').length == 0) {
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
					
					ajaxData = $('#defaultPopup input[type!="checkbox"], #defaultPopup select').serializeArray();
					ajaxData.push(
						{'name': 'action', 'value': 'editSave'},
						{'name': 'type', 'value': type},
						{'name': 'id', 'value': idStr}
					);
					
					$.ajax({
						url: 'ajax.php',
						type: 'POST',
						data: ajaxData
					}).done(function(data) {
						$('#defaultPopup .invalid').removeClass('invalid');
						if (data.status == 'success') {
							location.reload();
						}
						else {
							$.each(data, function(key, value) {
								if (key != 'status') {
									$('#defaultPopup [name=' + key + ']').addClass('invalid');
									$('#defaultPopup [name=' + key + ']').qtip({
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
	$('#topControlCenter .controlDelete').click(function(event) {
		if ($(this).hasClass('deleteEnabled')) {
			var ajaxData;
			var $checked = $('.selectCheckbox:checked');
			if ($checked.length == 0) {
				//item view
				ajaxData = {'action': 'delete', 'type': type, 'id': id};
			}
			else if ($checked.length == 1) {
				//list view, one checked
				ajaxData = {'action': 'delete', 'type': type, 'id': $checked.attr('id')};
			}
			else {
				//list view, more than one checked
				ajaxData = {'action': 'deleteMany', 'type': type};
			}
		
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: ajaxData
			}).done(function(data) {
				$('#defaultPopup > div > div').html(data.html);
				$('#defaultPopup').show();
				
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
							'type': type,
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