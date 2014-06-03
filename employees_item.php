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
		'SELECT username, firstName, lastName, payType, payAmount, locations.name AS locationName, positions.name AS positionName, employees.active AS active
		FROM employees, locations, positions
		WHERE employees.employeeID = :employeeID AND employees.locationID = locations.locationID AND employees.positionID = positions.positionID'
	);
	$sth->execute([':employeeID' => $_GET['id']]);
	$row = $sth->fetch();
	$row['active'] = ($row['active'] == 1) ? 'Yes' : 'No';
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $row['firstName'].' '.$row['lastName']; ?></title>
	<?php
		require('head.php');
	?>
	
	<script type="text/javascript" src="js/employees.js"></script>
	<script type="text/javascript">
		var id = <?php echo $_GET['id']; ?>;
	
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
				<a id="controlsEdit" class="controlsEditEnabled" href="#">Edit</a>
				<a id="controlsDelete" class="controlsDeleteEnabled" href="#">Delete</a>
			</div>
			<div id="controlsRight">
				<a class="settings" href="#"></a>
			</div>
		</div>
		<div id="data">
			<h1><?php echo $row['firstName'].' '.$row['lastName']; ?></h1>
			<section>
				<h2>Basic Information</h2>
				<div class="sectionData">
					<dl>
						<dt>First Name</dt>
						<dd><?php echo $row['firstName']; ?></dd>
						
						<dt>Last Name</dt>
						<dd><?php echo $row['lastName']; ?></dd>
						
						<dt>Pay Type</dt>
						<dd><?php echo parseValue('employee', 'payType', $row['payType']); ?></dd>
						
						<dt>Pay Amount</dt>
						<dd><?php echo parseValue('employee', 'payAmount', $row['payAmount']); ?></dd>
					</dl>
					<dl>
						<dt>Location</dt>
						<dd><?php echo $row['locationName']; ?></dd>
						
						<dt>Position</dt>
						<dd><?php echo $row['positionName']; ?></dd>
						
						<dt>Active</dt>
						<dd><?php echo $row['active']; ?></dd>
					</dl>
				</div>
			</section>
			<section>
				<h2>Personal Information</h2>
				<div class="sectionData">
					<dl>
						<dt>Address</dt>
						<dd>Test</dd>
					</dl>
				</div>
			</section>
			<section>
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
						<tbody>
							<?php
								$sth = $dbh->prepare(
									'SELECT *
									FROM changes
									WHERE employeeID = :employeeID'
								);
								$sth->execute([':employeeID' => $_GET['id']]);
								while ($row = $sth->fetch()) {
									echo '<tr>';
									echo '<td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
									echo '<td><a href="'.$row['type'].'s_item.php?id='.$row['id'].'">'.getName($row['type'], $row['id']).'</a></td>';
									echo '<td>'.$TYPES[$row['type']]['formalName'].'</td>';
									$dataStr = '';
									$data = json_decode($row['data'], true);
									foreach ($data as $key => $value) {
										$value = parseValue($row['type'], $key, $value);
										$dataStr .= '<b>'.$TYPES[$row['type']]['fields'][$key]['formalName'].':</b> '.$value.' ';
									}
									echo '<td>'.$dataStr.'</td>';
									echo '</tr>';
								}
							?>
						</tbody>
					</table>
				</div>
			</section>
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
									WHERE type = "employee" AND id = :id'
								);
								$sth->execute([':id' => $_GET['id']]);
								while ($row = $sth->fetch()) {
									echo '<tr>';
									echo '<td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
									echo '<td><a href="employees_view.php?id='.$row['employeeID'].'">'.getName('employee', $row['employeeID']).'</a></td>';
									$dataStr = '';
									$data = json_decode($row['data'], true);
									foreach ($data as $key => $value) {
										$value = parseValue($row['type'], $key, $value);
										$dataStr .= '<b>'.$TYPES[$row['type']]['fields'][$key]['formalName'].':</b> '.$value.' ';
									}
									echo '<td>'.$dataStr.'</td>';
									echo '</tr>';
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
