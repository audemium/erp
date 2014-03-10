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
	<title>erp</title>
	<?php
		require('head.php');
	?>
</head>

<body>
	<img id="logo" src="images/logo.png" alt="logo">
	<nav>
		<ul>
			<li><img src="images/icons/search_32.png"></li>
			<li id="selectedPage"><img src="images/icons/home_32.png"></li>
			<li><img src="images/icons/users_32.png"></li>
			<li><img src="images/icons/money_32.png"><div id="moneyBadge" class="badge">3</div></li>
		</ul>
	</nav>
	<div id="content">
		content<br><br><br><br><br><br><br><br>hello<br><br><br><br><br><br><br><br><br>goodbye<br><br><br><br><br><br><br><br><br><br>looooooong cat<br><br><br><br><br><br><br><br><br><br>longer<br><br><br><br><br><br><br>yeah
		
	</div>
</body>
</html>
