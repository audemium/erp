<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	require_once('init.php');
	
	$sth = $dbh->prepare(
		'SELECT *
		FROM '.$TYPES[$_GET['type']]['pluralName'].'
		WHERE '.$TYPES[$_GET['type']]['idName'].' = :id');
	$sth->execute([':id' => $_GET['id']]);
	$item = $sth->fetch();
	$parsed = parseValue($_GET['type'], $item);
	$name = getName($_GET['type'], $_GET['id']);
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $name; ?></title>
	<?php
		require('head.php');
	?>
	
	<script type="text/javascript">
		var type = '<?php echo $_GET['type']; ?>';
		var id = <?php echo $_GET['id']; ?>;
		var active = <?php echo $item['active']; ?>;
		//echo date/time formats
		var dateFormatJS = '<?php echo $SETTINGS['dateFormatJS'] ?>';
		var timeFormatJS = '<?php echo $SETTINGS['timeFormatJS'] ?>';
	
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
			$('#controls [title]').qtip({
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
	</script>
	<?php
		$file = 'js/'.$TYPES[$_GET['type']]['formalName'].'.js';
		if (file_exists($file)) {
			echo '<script type="text/javascript" src="'.$file.'"></script>';
		}
	?>
</head>

<body>
	<?php
		require('menu.php');
	?>
	<div id="content">
		<div id="topControls">
			<div id="topControlLeft"></div>
			<div id="topControlCenter">
				<a class="controlAdd addEnabled" href="#">Add</a>
				<a class="controlEdit editDisabled" href="#" title="Item is inactive and cannot be edited.">Edit</a>
				<a class="controlDelete deleteDisabled" href="#" title="Item is inactive and cannot be deleted.">Delete</a>
			</div>
			<div id="topControlRight"></div>
		</div>
		<div id="data">
			<h1><?php echo $name; ?></h1>
			<?php
				foreach ($TYPES[$_GET['type']]['formData'] as $key => $section) {
					echo '<section><h2>'.$key.'</h2><div class="sectionData">';
					foreach ($section as $column) {
						echo '<dl>';
						foreach ($column as $field) {
							echo '<dt>'.$TYPES[$_GET['type']]['fields'][$field]['formalName'].'</dt>';
							echo '<dd>'.$parsed[$field].'</dd>';
						}
						echo '</dl>';
					}
					echo '</div></section>';
				}
				
				$factoryItem = Factory::createItem($_GET['type']);
				echo $factoryItem->printItemBody($_GET['id']);
				echo $factoryItem->printAttachments($_GET['type'], $_GET['id']);
			?>
			<section>
				<h2>History</h2>
				<div class="sectionData">
					<table class="dataTable stripe row-border" id="historyTable"> 
						<thead>
							<tr>
								<th class="dateTimeHeader">Time</th>
								<th>Modified By</th>
								<th>Changes</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3" class="tableFooter">
									<a href="#">View All</a>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</section>
		</div>
		<?php
			require('footer.php');
		?>
	</div>
	<div class="popup" id="defaultPopup">
		<div>
			<a class="close" title="Close">X</a>
			<div></div>
		</div>
	</div>
	<div class="popup" id="attachmentPopup">
		<div>
			<a class="close" title="Close">X</a>
			<div>
				<h1>Add Attachment</h1>
				<section>
					<h2></h2>
					<div class="sectionData">
						<form action="#" enctype="multipart/form-data" method="post">
							<label for="uploadFile">File (max size <?php echo ini_get('upload_max_filesize'); ?>)</label>
							<input type="file" name="uploadFile">
						</form>
						<div class="meter">
							<span style="width:0%;"></span>
						</div>
					</div>
				</section>
				<div id="btnSpacer">
					<button id="attachmentBtn">Add</button>
				</div>
			</div>
		</div>
	</div>
	<?php
		echo $factoryItem->printPopups();
	?>
</body>
</html>
