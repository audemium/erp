<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/
?>

<img id="logo" src="images/logo.png" alt="logo">
<div id="accountBar"><a href="account.php" style="margin-right:20px;">Account Settings</a> <a href="logout.php">Sign out</a></div>
<div id="searchDiv">
	<input type="text" id="searchTerm" placeholder="Search">
	<div id="searchResults">Type to begin searching...</div>
</div>
<nav>
	<ul>
		<li id="search">
			<div id="searchLink"><div style="background-image:url('images/icons/search.png');" title="Search"></div></div>
		</li>
		<li id="index">
			<a href="index.php"><div style="background-image:url('images/icons/home.png');" title="Home"></div></a>
		</li>
		<?php
			foreach ($TYPES as $key => $value) {
				echo '<li id="'.$key.'">';
				echo '<a href="list.php?type='.$key.'"><div style="background-image:url(\'images/icons/'.$key.'.png\');" title="'.$value['formalPluralName'].'"></div></a>';
				echo '</li>';
			}
		?>
	</ul>
</nav>