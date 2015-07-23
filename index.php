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
	<title>Home</title>
	<?php
		require('head.php');
	?>
	<script type="text/javascript">
		$(document).ready(function() {
			$(window).resize(function() {
				var colWidth = ($(window).width() - 154 - 50 - 90 - 17) / 2;
				$('#col1, #col2').css('width', colWidth + 'px');
			});
			$(window).resize();
			
			//trigger AJAX calls for modules
			$('#col1 .module, #col2 .module').each(function() {
				var $module = $(this);
				$.ajax({
					url: 'module.php',
					type: 'POST',
					data: {
						'module':  $module.attr('id')
					}
				}).done(function(data) {
					$module.find('h2').html(data.title);
					$module.find('.moduleData').html(data.content);
					$module.find('.dataTable').DataTable({
						'paging': false,
						'dom': 't',
						'order': [0, 'desc'],
						'autoWidth': false,
						'columnDefs': [
							{'width': '150px', 'targets': 'dateTimeHeader'}
						]
					});
				});
			});
		});
	</script>
</head>

<body>
	<?php
		require('menu.php');
	?>
	<div id="content">
		<?php
			//TODO: get user's module setup from the db
			$modules = [['notifications', 'unpaidBills', 'unpaidOrders'], ['recentTransactions', 'income', 'expenses']];
		?>
		<div id="col1">
			<?php
				foreach ($modules[0] as $module) {
					echo '<div class="module" id="'.$module.'">';
						echo '<h2></h2>';
						echo '<div class="moduleData"></div>';
					echo '</div>';
				}
			?>
		</div>
		<div id="col2">
			<?php
				foreach ($modules[1] as $module) {
					echo '<div class="module" id="'.$module.'">';
						echo '<h2></h2>';
						echo '<div class="moduleData"></div>';
					echo '</div>';
				}
			?>
		</div>
		<div style="clear:both;"></div>
		<?php
			require('footer.php');
		?>
	</div>
</body>
</html>
