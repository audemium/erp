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
	
	<script type="text/javascript" src="js/employees.js"></script>
	<script type="text/javascript">
		var id = <?php echo $_GET['id']; ?>;
		var active = <?php echo $item['active']; ?>;
	
		$(document).ready(function() {
			//set up datatables
			var table = $('#changesTable, #historyTable').DataTable({
				'paging': false,
				'dom': 't',
				'order': [0, 'desc'],
				'autoWidth': false,
				'columnDefs': [
					{'width': '125px', 'targets': 0}
				]
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
				$('#controlsEdit').addClass('controlsEditEnabled').removeClass('controlsEditDisabled');
				$('#controlsDelete').addClass('controlsDeleteEnabled').removeClass('controlsDeleteDisabled');
				$('#controlsEdit, #controlsDelete').qtip('disable');
			}
		});
	</script>
</head>

<body>
	<?php
		require('menu.php');
	?>
	<div id="content">
		<div id="controls">
			<div id="controlsLeft"></div>
			<div id="controlsCenter">
				<a id="controlsAdd" class="controlsAddEnabled" href="#">Add</a>
				<a id="controlsEdit" class="controlsEditDisabled" href="#" title="Item is inactive and cannot be edited.">Edit</a>
				<a id="controlsDelete" class="controlsDeleteDisabled" href="#" title="Item is inactive and cannot be deleted.">Delete</a>
			</div>
			<div id="controlsRight">
				<a class="settings" href="#"></a>
			</div>
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
				
				if ($_GET['type'] == 'employee') {
					echo '<section>
						<h2>Changes Made</h2>
						<div class="sectionData">
							<table id="changesTable" class="stripe row-border"> 
								<thead>
									<tr>
										<td>Time</td>
										<td>Item</td>
										<td>Type</td>
										<td>Changes</td>
									</tr>
								</thead>
								<tbody>';
									$sth = $dbh->prepare(
										'SELECT *
										FROM changes
										WHERE employeeID = :employeeID');
									$sth->execute([':employeeID' => $_GET['id']]);
									while ($row = $sth->fetch()) {
										echo '<tr><td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
										echo '<td><a href="item.php?type='.$row['type'].'&id='.$row['id'].'">'.getName($row['type'], $row['id']).'</a></td>';
										echo '<td>'.$TYPES[$row['type']]['formalName'].'</td>';
										$dataStr = '';
										if ($row['data'] == '') {
											$dataStr = 'Item deleted.';
										}
										else {
											$data = json_decode($row['data'], true);
											foreach ($data as $key => $value) {
												$value = parseValue($row['type'], $key, $value);
												$dataStr .= '<b>'.$TYPES[$row['type']]['fields'][$key]['formalName'].':</b> '.$value.' ';
											}
										}
										echo '<td>'.$dataStr.'</td></tr>';
									}
								echo '</tbody>
							</table>
						</div>
					</section>';
				}
			?>
			<section>
				<h2>History</h2>
				<div class="sectionData">
					<table id="historyTable" class="stripe row-border"> 
						<thead>
							<tr>
								<td>Time</td>
								<td>Employee</td>
								<td>Changes</td>
							</tr>
						</thead>
						<tbody>
							<?php
								$sth = $dbh->prepare(
									'SELECT *
									FROM changes
									WHERE type = :type AND id = :id');
								$sth->execute([':type' => $_GET['type'], ':id' => $_GET['id']]);
								while ($row = $sth->fetch()) {
									echo '<tr><td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
									echo '<td><a href="item.php?type=employee&id='.$row['employeeID'].'">'.getName('employee', $row['employeeID']).'</a></td>';
									$dataStr = '';
									if ($row['data'] == '') {
										$dataStr = 'Item deleted.';
									}
									else {
										$data = json_decode($row['data'], true);
										foreach ($data as $key => $value) {
											$value = parseValue($row['type'], $key, $value);
											$dataStr .= '<b>'.$TYPES[$row['type']]['fields'][$key]['formalName'].':</b> '.$value.' ';
										}
									}
									echo '<td>'.$dataStr.'</td></tr>';
								}
							?>
						</tbody>
					</table>
				</div>
			</section>
		</div>
	</div>
	<div id="popup">
		<div>
			<a id="close" title="Close">X</a>
			<div></div>
		</div>
	</div>
</body>
</html>
