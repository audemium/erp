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
	
	<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="js/jquery.qtip.min.js"></script>
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
					<th></th>
					<th>Name</th>
					<th>Position</th>
					<th>Office</th>
					<th>Age</th>
					<th>Start date</th>
					<th>Salary</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="selectCol"><input type="checkbox" class="selectCheckbox"></td>
					<td>Tiger Nixon</td>
					<td>System Architect</td>
					<td>Edinburgh</td>
					<td>61</td>
					<td>2011/04/25</td>
					<td>$320,800</td>
				</tr>
				<tr>
					<td class="selectCol"><input type="checkbox" class="selectCheckbox"></td>
					<td>Elephant Nixon</td>
					<td>System Architect</td>
					<td>Edinburgh</td>
					<td>61</td>
					<td>2011/04/25</td>
					<td>$320,800</td>
				</tr>
				<tr>
					<td class="selectCol"><input type="checkbox" class="selectCheckbox"></td>
					<td>Goat Nixon</td>
					<td>System Architect</td>
					<td>Edinburgh</td>
					<td>61</td>
					<td>2011/04/25</td>
					<td>$320,800</td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>
