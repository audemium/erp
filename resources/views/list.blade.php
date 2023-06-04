@extends('app')

@section('title', '')

@section('content')
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
					/*//check to see if the user has a custom config, otherwise use default
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
					}*/
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				/*$sth = $dbh->prepare(
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
				}*/
			?>
		</tbody>
	</table>
@endsection
