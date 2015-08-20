$(document).ready(function() {
	//set up dataTables
	$('.dataTable').not('#historyTable').DataTable({
		'paging': false,
		'dom': 't',
		'order': [0, 'desc'],
		'autoWidth': false,
		'columnDefs': [
			{'width': '150px', 'targets': 'dateTimeHeader'}
		]
	});
	
	//set up datetimepicker
	$('.timeInput').timepicker({
		timeFormat: timeFormatJS,
		stepMinute: 15
	});
	$('.dateInput').datepicker({
		dateFormat: dateFormatJS
	});
	
	//get initial history datatable, set up view all click
	$.ajax({
		url: 'ajax.php',
		type: 'POST',
		data: {
			'action': 'history',
			'type': type,
			'id': id,
			'limit': 5
		}
	}).done(function(data) {
		$('#historyTable tbody').html(data.html);
		$('#historyTable').DataTable({
			'paging': false,
			'dom': 't',
			'order': [0, 'desc'],
			'autoWidth': false,
			'columnDefs': [
				{'width': '150px', 'targets': 'dateTimeHeader'}
			]
		});
	});
	$('#historyTable .tableFooter a').click(function(event) {
		$.ajax({
			url: 'ajax.php',
			type: 'POST',
			data: {
				'action': 'history',
				'type': type,
				'id': id,
				'limit': -1
			}
		}).done(function(data) {
			var temp = $('#historyTable').DataTable();
			temp.destroy();
			$('#historyTable tbody').html(data.html);
			$('#historyTable').DataTable({
				'paging': false,
				'dom': 't',
				'order': [0, 'desc'],
				'autoWidth': false,
				'columnDefs': [
					{'width': '150px', 'targets': 'dateTimeHeader'}
				]
			});
			$('#historyTable .tableFooter a').remove();
		});
		event.preventDefault();
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
	if (active == 1) {
		$('#topControlCenter .controlEdit').addClass('editEnabled').removeClass('editDisabled');
		$('#topControlCenter .controlDelete').addClass('deleteEnabled').removeClass('deleteDisabled');
		$('#topControlCenter .controlEdit, #topControlCenter .controlDelete').removeAttr('title');
	}
	
	//add attachments
	$('#addAttachment').click(function(event) {
		$('#attachmentPopup').show();
		$('#attachmentPopup input').val('');
		//re-enable fields due to Firefox "feature"
		$('#attachmentBtn').prop('disabled', false);
		$('#attachmentPopup input').prop('disabled', false);
		//need to remove errors and reset progress bar css in case this is after an upload error
		$('#attachmentPopup .invalid').qtip('destroy', true);
		$('#attachmentPopup .invalid').removeClass('invalid');
		$('.meter > span').css('width', '0%');
		$('.meter > span').css('border-radius', '16px 0 0 16px');
		
		$('#attachmentBtn').click(function() {
			var formData = new FormData($('#attachmentPopup form').get(0));
			formData.append('action', 'addAttachment');
			formData.append('type', type);
			formData.append('id', id);
			$('#attachmentBtn').prop('disabled', true);
			$('#attachmentPopup input').prop('disabled', true);
			$('#attachmentBtn').addClass('buttonDisabled');

			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				cache: false,
				processData: false,
				contentType: false,
				data: formData,
				xhr: function() {
					myXhr = $.ajaxSettings.xhr();
					if (myXhr.upload) {
						myXhr.upload.addEventListener('progress', uploadProgress, false);
					}
					return myXhr;
				}
			}).done(function(data) {
				$('#attachmentPopup .invalid').qtip('destroy', true);
				$('#attachmentPopup .invalid').removeClass('invalid');
				if (data.status == 'success') {
					location.reload();
				}
				else {
					$('#attachmentBtn').prop('disabled', false);
					$('#attachmentPopup input').prop('disabled', false);
					$('#attachmentBtn').removeClass('buttonDisabled');
					$('#attachmentPopup input').addClass('invalid');
					$('#attachmentPopup input').qtip({
						'content': data.uploadFile,
						'style': {'classes': 'qtip-tipsy-custom'},
						'position': {
							'my': 'bottom center',
							'at': 'top center'
						},
						show: {
							'event': false,
							'ready': true
						},
						hide: {
							'event': 'click',
							'target': $('#attachmentPopup .close')
						}
					});
				}
			});
		});
		event.preventDefault();
	});
	
	function uploadProgress(event) {
		if (event.lengthComputable) {
			var percent = Math.round((event.loaded / event.total) * 100);
			$('.meter > span').css('width', percent + '%');
			if (percent > 98) {
				$('.meter > span').css('border-radius', '16px');
			}
		}
	}
	
	//delete attachments
	$('.attachmentTable .controlDelete').click(function(event) {
		var $button = $(this);
		if ($button.hasClass('deleteEnabled')) {
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: {
					'action': 'deleteAttachment',
					'type': type,
					'id': id,
					'subID':  $button.data('id')
				}
			}).done(function(data) {
				location.reload();
			});
		}
		event.preventDefault();
	});
});