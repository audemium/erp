<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	require_once('init.php');
	
	$sth = $dbh->prepare(
		'SELECT *
		FROM '.$TYPES[$_GET['type']]['pluralName'].'
		WHERE '.$TYPES[$_GET['type']]['idName'].' = :id');
	$sth->execute([':id' => $_GET['id']]);
	$item = $sth->fetch();
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
							echo '<dd>'.parseValue($_GET['type'], $field, $item[$field]).'</dd>';
						}
						echo '</dl>';
					}
					echo '</div></section>';
				}
				
				$factoryItem = Factory::createItem($_GET['type']);
				echo $factoryItem->printItemBody($_GET['id']);
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
	<?php
		echo $factoryItem->printPopups();
	?>
</body>
</html>
