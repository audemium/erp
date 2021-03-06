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
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $TYPES[$_GET['type']]['formalPluralName']; ?></title>
	<?php
		require('head.php');
	?>
	
	<script type="text/javascript">
		var type = '<?php echo $_GET['type']; ?>';
		//echo date/time formats
		var dateFormatJS = '<?php echo $SETTINGS['dateFormatJS'] ?>';
		var timeFormatJS = '<?php echo $SETTINGS['timeFormatJS'] ?>';
	</script>
	<script type="text/javascript" src="js/list.js"></script>
</head>

<body>
	<?php
		require('menu.php');
	?>
	<div id="content">
		<div id="topControls">
			<div id="topControlLeft">
				<input type="text" id="filter" placeholder="Filter">
			</div>
			<div id="topControlCenter">
				<a class="controlAdd addEnabled" href="#">Add</a>
				<a class="controlEdit editDisabled" href="#" title="Select one or more rows to edit">Edit</a>
				<a class="controlDelete deleteDisabled" href="#" title="Select one or more rows to delete">Delete</a>
			</div>
			<!--<div id="topControlRight">
				<a class="settings" href="#"></a>
			</div>-->
		</div>
		<table id="itemTable" class="stripe row-border"> 
			<thead>
				<tr>
					<?php
						//check to see if the user has a custom config, otherwise use default
						$sth = $dbh->prepare(
							'SELECT columnOrder 
							FROM columns
							WHERE type = :type AND employeeID = :employeeID');
						$sth->execute([':type' => $_GET['type'], ':employeeID' => $_SESSION['employeeID']]);
						$result = $sth->fetchAll();
						if (count($result) > 0) {
							$columns = explode(',', $result[0]['columnOrder']);
						}
						else {
							$columns = $SETTINGS['columns'][$_GET['type']];
						}
						
						//print column headers
						echo '<th></th>';
						foreach ($columns as $column) {
							$formalName = (!isset($TYPES[$_GET['type']]['fields'][$column]) && $column == 'name') ? 'Name' : $TYPES[$_GET['type']]['fields'][$column]['formalName'];
							echo '<th>'.$formalName.'</th>';
						}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
					$sth = $dbh->prepare(
						'SELECT *
						FROM '.$TYPES[$_GET['type']]['pluralName'].'
						WHERE active = 1');
					$sth->execute();
					while ($row = $sth->fetch()) {
						$id = $row[$TYPES[$_GET['type']]['idName']];
						$item = parseValue($_GET['type'], $row);
						
						echo '<tr><td class="selectCol"><input type="checkbox" class="selectCheckbox" id="'.$id.'"></td>';
						foreach ($columns as $column) {
							if ($column == 'name' || $column == 'orderID' || $column == 'expenseID') {
								$temp = getLinkedName($_GET['type'], $id);
							}
							else {
								$temp = $item[$column];
							}
							echo '<td>'.$temp.'</td>';
						}
						echo '</tr>';
					}
				?>
			</tbody>
		</table>
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
</body>
</html>
