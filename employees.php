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
?>

<!DOCTYPE html>
<html>
<head>
	<title>Employees</title>
	<?php
		require('head.php');
	?>
	
	<script type="text/javascript" src="js/employees.js"></script>
</head>

<body>
	<?php
		require('menu.php');
	?>
	<div id="content">
		<div id="controls">
			<div id="controlsLeft">
				<input type="text" id="filter" placeholder="Filter">
			</div>
			<div id="controlsCenter">
				<a id="controlsAdd" class="controlsAddEnabled" href="#">Add</a>
				<a id="controlsEdit" class="controlsEditDisabled" href="#" title="Select one or more rows to edit.">Edit</a>
				<a id="controlsDelete" class="controlsDeleteDisabled" href="#" title="Select one or more rows to delete.">Delete</a>
			</div>
			<div class="settings"></div>
		</div>
		<table id="employeesTable" class="stripe row-border"> 
			<thead>
				<tr>
					<?php
						//get all column names for the table
						$sth = $dbh->prepare(
							'SELECT columnID, name 
							FROM displayColumns 
							WHERE type = "employee"'
						);
						$sth->execute();
						while ($row = $sth->fetch()) {
							$columnNames[$row['columnID']] = $row['name'];
						}
					
						//check to see if the user has a custom config, otherwise use default
						$sth = $dbh->prepare(
							'SELECT employees_displayColumns.columnID 
							FROM employees_displayColumns, displayColumns
							WHERE employees_displayColumns.columnID = displayColumns.columnID AND employeeID = :employeeID AND type = "employee"'
						);
						$sth->execute(array(':employeeID' => $_SESSION['employeeID']));
						$result = $sth->fetchAll();
						if (count($result) > 0) {
							foreach ($result as $row) {
								$columns[] = $row['columnID'];
							}
						}
						else {
							$columns = $SETTINGS['columns']['employees'];
						}
						
						//print column headers
						echo '<th></th>';
						foreach ($columns as $column) {
							echo '<th>'.$columnNames[$column].'</th>';
						}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
					$sth = $dbh->prepare(
						'SELECT employeeID, username, firstName, lastName, payType, payAmount, locations.name AS locationName, positions.name AS positionName
						FROM employees, locations, positions
						WHERE employees.active = 1 AND employees.locationID = locations.locationID AND employees.positionID = positions.positionID'
					);
					$sth->execute();
					while ($row = $sth->fetch()) {
						$i = 0;
						while ($i < 25) {
							echo '<tr><td class="selectCol"><input type="checkbox" class="selectCheckbox"></td>';
							foreach ($columns as $column) {
								switch ($column) {
									case 1: //Name
										echo '<td><a href="employees_view.php?id='.$row['employeeID'].'">'.$row['firstName'].' '.$row['lastName'].'</a></td>';
										break;
									case 2: //Username
										echo '<td>'.$row['username'].'</td>';
										break;
									case 3: //Position
										echo '<td>'.$row['positionName'].'</td>';
										break;
									case 4: //Location
										echo '<td>'.$row['locationName'].'</td>';
										break;
									case 5: //Pay Type
										$payType = ($row['payType'] == 'S') ? 'Salary' : 'Hourly';
										echo '<td>'.$payType.'</td>';
										break;
									case 6: //Pay Amount
										echo '<td>'.formatCurrency($row['payAmount']).'</td>';
										break;
									default:
										echo '<td>Unknown Column</td>';
								}
							}
							echo '</tr>';
							$i++;
						}
					}
				?>
			</tbody>
		</table>
	</div>
</body>
</html>
