<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/
?>

<img id="logo" src="images/logo.png" alt="logo">
<div id="searchDiv">
	<input type="text" id="searchTerm" placeholder="Search">
	<div id="searchResults"></div>
</div>
<nav>
	<ul>
		<li id="search">
			<!-- searchLink div is to make the hover js have the same number of parents for all elements-->
			<div id="searchLink"><img src="images/icons/search_32.png" title="Search"></div>
		</li>
		<li id="index">
			<a href="index.php"><img src="images/icons/home_32.png" title="Home"></a>
		</li>
		<li id="employees">
			<a href="employees.php"><img src="images/icons/users_32.png" title="Employees"></a>
		</li>
		<li id="finances">
			<a href="finances.php"><img src="images/icons/money_32.png" title="Finances"></a>
			<!--
				possibly going to get rid of the badges
				<div id="financesBadge" class="badge">3</div>
			-->
		</li>
	</ul>
</nav>