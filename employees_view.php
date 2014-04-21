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
	$sth->execute(array(':employeeID' => $_GET['id']));
	$row = $sth->fetch();
	$row['payType'] = ($row['payType'] == 'S') ? 'Salary' : 'Hourly';
	$row['active'] = ($row['active'] == 1) ? 'Yes' : 'No';
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $row['firstName'].' '.$row['lastName']; ?></title>
	<?php
		require('head.php');
	?>
	
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
				});
				event.preventDefault();
			});
			
			//edit
			$('#controlsEdit').click(function(event) {
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: {
						'action': 'edit',
						'type': 'employee',
						'id': id
					}
				}).done(function(data) {
					$('#popup > div').html(data.html);
					$('#popup').show();
				});
				event.preventDefault();
			});
			
			//delete
			$('#controlsDelete').click(function(event) {
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: {
						'action': 'delete',
						'type': 'employee',
						'id': id
					}
				}).done(function(data) {
					$('#popup > div').html(data.html);
					$('#popup').show();
				});
				event.preventDefault();
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
			<div class="settings"></div>
		</div>
		<div id="data">
			<h1><?php echo $row['firstName'].' '.$row['lastName']; ?></h1>
			<h2>Basic Information</h2>
				<table class="nonDatatable">
					<tr>
						<td class="fieldLabel">First Name:</td>
						<td><?php echo $row['firstName']; ?></td>
						<td class="fieldLabel">Location:</td>
						<td><?php echo $row['locationName']; ?></td>
					</tr>
					<tr>
						<td class="fieldLabel">Last Name:</td>
						<td><?php echo $row['lastName']; ?></td>
						<td class="fieldLabel">Position:</td>
						<td><?php echo $row['positionName']; ?></td>
					</tr>
					<tr>
						<td class="fieldLabel">Pay Type:</td>
						<td><?php echo $row['payType']; ?></td>
						<td class="fieldLabel">Active:</td>
						<td><?php echo $row['active']; ?></td>
					</tr>
					<tr>
						<td class="fieldLabel">Pay Amount:</td>
						<td><?php echo formatCurrency($row['payAmount']); ?></td>
					</tr>
				</table>
			<h2>Changes Made</h2>
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
							$sth->execute(array(':employeeID' => $_GET['id']));
							while ($row = $sth->fetch()) {
								echo '<tr>';
								echo '<td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
								echo '<td><a href="'.$row['type'].'s_view.php?id='.$row['id'].'">'.getName($row['type'], $row['id']).'</a></td>';
								echo '<td>'.$TYPES[$row['type']]['formalName'].'</td>';
								$dataStr = '';
								$data = json_decode($row['data'], true);
								foreach ($data as $key => $value) {
									if ($key == 'Pay Amount') {
										$value = formatCurrency($value);
									}
									$dataStr .= '<b>'.$key.':</b> '.$value.' ';
								}
								echo '<td>'.$dataStr.'</td>';
								echo '</tr>';
							}
						?>
					</tbody>
				</table>
			<h2>History</h2>
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
								WHERE type = "employee" AND id = :employeeID'
							);
							$sth->execute(array(':employeeID' => $_GET['id']));
							while ($row = $sth->fetch()) {
								echo '<tr>';
								echo '<td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
								echo '<td><a href="employees_view.php?id='.$row['employeeID'].'">'.getName('employee', $row['id']).'</a></td>';
								$dataStr = '';
								$data = json_decode($row['data'], true);
								foreach ($data as $key => $value) {
									if ($key == 'Pay Amount') {
										$value = formatCurrency($value);
									}
									$dataStr .= '<b>'.$key.':</b> '.$value.' ';
								}
								echo '<td>'.$dataStr.'</td>';
								echo '</tr>';
							}
						?>
					</tbody>
				</table>
		</div>
	</div>
	<div id="popup">
		<div></div>
	</div>
</body>
</html>
