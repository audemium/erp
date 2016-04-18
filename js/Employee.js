$(document).ready(function() {
	//view
	$('.customViewPaystub').click(function(event) {
		var subID = $(this).attr('id');
		$('#customPopupPaystub').show();
		$.ajax({
			url: 'ajax.php',
			type: 'POST',
			data: {
				'action': 'customAjax',
				'type': type,
				'id': id,
				'subAction': 'view',
				'subType': 'paystub',
				'subID': subID
			}
		}).done(function(data) {
			if (data.status == 'success') {
				$('#customPopupPaystub #paystubID').text(subID);
				$.each(data, function(key, value) {
					if (key != 'status') {
						$('#customPopupPaystub #' + key).text(value);
					}
				});
			}
		});	
		
		event.preventDefault();
	});
	
	$('.customViewTimesheet').click(function(event) {
		var subID = $(this).attr('id');
		$('#customPopupTimesheet').show();
		$.ajax({
			url: 'ajax.php',
			type: 'POST',
			data: {
				'action': 'customAjax',
				'type': type,
				'id': id,
				'subAction': 'view',
				'subType': 'timesheet',
				'subID': subID
			}
		}).done(function(data) {
			if (data.status == 'success') {
				$('#customPopupTimesheet #timesheetID').text(subID);
				$('#tableTimesheet tbody').html(data.html);
				$('#customBtnTimesheetEdit').hide();
					$('#customBtnTimesheetApprove').hide();
				if (data.timesheetStatus == 'E') {
					$('#customBtnTimesheetEdit').show();
				}
				if (data.timesheetStatus == 'P') {
					$('#customBtnTimesheetEdit').show();
					$('#customBtnTimesheetApprove').show();
				}
				
				$('#customBtnTimesheetEdit').click(function() {
					var ajaxData = $('#customPopupTimesheet input').serializeArray();
					ajaxData.push(
						{'name': 'action', 'value': 'customAjax'},
						{'name': 'type', 'value': type},
						{'name': 'id', 'value': id},
						{'name': 'subAction', 'value': 'edit'},
						{'name': 'subType', 'value': 'timesheet'},
						{'name': 'subID', 'value': subID}
					);
					$.ajax({
						url: 'ajax.php',
						type: 'POST',
						data: ajaxData
					}).done(function(data) {
						$('#customPopupTimesheet .invalid').qtip('destroy', true);
						$('#customPopupTimesheet .invalid').removeClass('invalid');
						if (data.status == 'success') {
							location.reload();
						}
						else {
							$.each(data, function(key, value) {
								if (key != 'status') {
									$('#customPopupTimesheet [name=' + key + ']').addClass('invalid');
									$('#customPopupTimesheet [name=' + key + ']').qtip({
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
				
				$('#customBtnTimesheetApprove').click(function() {
					$.ajax({
						url: 'ajax.php',
						type: 'POST',
						data: {
							'action': 'customAjax',
							'type': type,
							'id': id,
							'subAction': 'approve',
							'subType': 'timesheet',
							'subID': subID
						}
					}).done(function(data) {
						location.reload();
					});
				});
			}
		});	
		
		event.preventDefault();
	});
	
	//get initial changes made datatable, set up view all click
	$.ajax({
		url: 'ajax.php',
		type: 'POST',
		data: {
			'action': 'customAjax',
			'subAction': 'changesMadeHistory',
			'type': type,
			'id': id,
			'limit': 5
		}
	}).done(function(data) {
		var temp = $('#changesMadeTable').DataTable();
		temp.destroy();
		$('#changesMadeTable tbody').html(data.html);
		$('#changesMadeTable').DataTable({
			'paging': false,
			'dom': 't',
			'order': [0, 'desc'],
			'autoWidth': false,
			'columnDefs': [
				{'width': '150px', 'targets': 'dateTimeHeader'}
			]
		});
	});
	$('#changesMadeTable .tableFooter a').click(function(event) {
		$.ajax({
			url: 'ajax.php',
			type: 'POST',
			data: {
				'action': 'customAjax',
				'subAction': 'changesMadeHistory',
				'type': type,
				'id': id,
				'limit': -1
			}
		}).done(function(data) {
			var temp = $('#changesMadeTable').DataTable();
			temp.destroy();
			$('#changesMadeTable tbody').html(data.html);
			$('#changesMadeTable').DataTable({
				'paging': false,
				'dom': 't',
				'order': [0, 'desc'],
				'autoWidth': false,
				'columnDefs': [
					{'width': '150px', 'targets': 'dateTimeHeader'}
				]
			});
			$('#changesMadeTable .tableFooter a').remove();
		});
		event.preventDefault();
	});
});