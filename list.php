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
	<title><?php echo $TYPES[$_GET['type']]['formalPluralName']; ?></title>
	<?php
		require('head.php');
	?>
	
	<script type="text/javascript" src="js/common.js"></script>
	<script type="text/javascript">
		var type = '<?php echo $_GET['type']; ?>';
	
		$(document).ready(function() {
			//change controls and header when hitting the top of the page
			//TODO: fix column width changing when header hits the top
			$('#controls').data('top', $('#controls').offset().top);
			$(window).scroll(function() {
				if ($(window).scrollTop() > $('#controls').data('top')) { 
					$('#controls').css({
						'position': 'fixed',
						'top': '0',
						'width': $('#content').width(),
						'border-radius': '0 0 0 0'
					});
					$('thead').css({
						'position': 'fixed',
						'top': $('#controls').css('height'),
						'width': $('#content').width(),
						'border-radius': '0 0 8px 8px'
					}); 
				}
				else {
					$('#controls').css({
						'position': 'static',
						'top': 'auto',
						'width': '100%',
						'border-radius': '8px 8px 0 0'
					});
					$('thead').css({
						'position': 'static',
						'top': 'auto',
						'width': '100%',
						'border-radius': '0 0 0 0'
					});
				}
			});

			//set up dataTables
			var table = $('#itemTable').DataTable({
				'paging': false,
				'dom': 'rti',
				'order': [1, 'asc'],
				'columnDefs': [
					{'orderable': false, 'targets': 0},
					{'searchable': false, 'targets': 0}
				]
			});
			$('#filter').on('keyup', function() {
				table.search(this.value).draw();
			});
			
			//checkboxes
			$('.selectCheckbox').click(function() {
				checkCheckboxes();
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
			
			//run in any case browser cached any checks
			checkCheckboxes();
		});
		
		function checkCheckboxes() {
			if ($('.selectCheckbox:checked').length > 0) {
				$('#controlsEdit').addClass('controlsEditEnabled').removeClass('controlsEditDisabled');
				$('#controlsDelete').addClass('controlsDeleteEnabled').removeClass('controlsDeleteDisabled');
				$('#controlsEdit, #controlsDelete').qtip('disable');
			}
			else {
				$('#controlsEdit').addClass('controlsEditDisabled').removeClass('controlsEditEnabled');
				$('#controlsDelete').addClass('controlsDeleteDisabled').removeClass('controlsDeleteEnabled');
				$('#controlsEdit, #controlsDelete').qtip('enable');
			}
		}
	</script>
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
			<div id="controlsRight">
				<a class="settings" href="#"></a>
			</div>
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
						echo '<tr><td class="selectCol"><input type="checkbox" class="selectCheckbox" id="'.$id.'"></td>';
						foreach ($columns as $column) {
							if ($column == 'name') {
								$temp = getLinkedName($_GET['type'], $id);
							}
							elseif ($column == 'orderID') {
								$temp = '<a href="item.php?type=order&id='.$id.'">'.$id.'</a>';
							}
							else {
								$temp = parseValue($_GET['type'], $column, $row[$column]);
							}
							echo '<td>'.$temp.'</td>';
						}
						echo '</tr>';
					}
				?>
			</tbody>
		</table>
	</div>
	<div id="popup">
		<div>
			<a id="close" title="Close">X</a>
			<div></div>
		</div>
	</div>
</body>
</html>
