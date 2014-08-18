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
	<div id="searchResults">Type to begin searching...</div>
</div>
<nav>
	<ul>
		<li id="search">
			<!-- searchLink div is to make the hover js have the same number of parents for all elements-->
			<div id="searchLink"><img src="images/icons/search.png" title="Search" alt="search"></div>
		</li>
		<li id="index">
			<a href="index.php"><img src="images/icons/home.png" title="Home" alt="home"></a>
		</li>
		<?php
			foreach ($TYPES as $key => $value) {
				//TODO: get rid of $keyImg when we have real icons for everything
				$keyImg = ($key == 'employee' || $key == 'order') ? $key : 'star';
			
				echo '<li id="'.$key.'">';
				echo '<a href="list.php?type='.$key.'"><img src="images/icons/'.$keyImg.'.png" title="'.$value['formalPluralName'].'" alt="'.$key.'"></a>';
				echo '</li>';
			}
		?>
		<li id="logout">
			<a href="logout.php"><img src="images/icons/logout.png" title="Sign out" alt="sign out"></a>
		</li>
	</ul>
</nav>