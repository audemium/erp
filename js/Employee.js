$(document).ready(function() {
	//add
	$('#customAdd1').click(function(event) {
		$('#customPopup1').show();
		$('#customPopup1 input[type!="checkbox"], #customPopup1 select').val('');
		$('#customPopup1 .invalid').qtip('destroy', true);
		$('#customPopup1 .invalid').removeClass('invalid');
		
		$('#customBtn1').click(function() {
			var ajaxData = $('#customPopup1 input[type!="checkbox"], #customPopup1 select').serializeArray();
			ajaxData.push(
				{'name': 'action', 'value': 'customAjax'},
				{'name': 'type', 'value': type},
				{'name': 'id', 'value': id},
				{'name': 'subAction', 'value': 'add'},
				{'name': 'subType', 'value': 'vacationRequest'}
			);
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: ajaxData
			}).done(function(data) {
				$('#customPopup1 .invalid').qtip('destroy', true);
				$('#customPopup1 .invalid').removeClass('invalid');
				if (data.status == 'success') {
					location.reload();
				}
				else {
					$.each(data, function(key, value) {
						if (key != 'status') {
							$('#customPopup1 [name=' + key + ']').addClass('invalid');
							$('#customPopup1 [name=' + key + ']').qtip({
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