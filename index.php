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
	<title>Home</title>
	<?php
		require('head.php');
	?>
	<script type="text/javascript">
		$(document).ready(function() {
			$(window).resize(function() {
				var colWidth = ($(window).width() - 154 - 50 - 90) / 2;
				$('#col1, #col2').css('width', colWidth + 'px');
			});
			
			$(window).resize();
		});
	</script>
</head>

<body>
	<?php
		require('menu.php');
	?>
	<div id="content">
		<div id="col1">
			<div class="module">
				<h2>Notifications<a class="settings" href="#"></a></h2>
				Nothing to notify you of.
			</div>
			<div class="module">
				<h2>Products<a class="settings" href="#"></a></h2>
				Summary would go here?
			</div>
			<div class="module">
				<h2>Services<a class="settings" href="#"></a></h2>
				Summary would go here?
			</div>
		</div>
		<div id="col2">
			<div class="module">
				<h2>Customers<a class="settings" href="#"></a></h2>
				Summary would go here?
			</div>
			<div class="module">
				<h2>Finances<a class="settings" href="#"></a></h2>
				Summary would go here?
			</div>
		</div>
		<?php
			require('footer.php');
		?>
	</div>
</body>
</html>
