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
	<link type="text/css" rel="stylesheet" href="css/login.css">
	<script type="text/javascript" src="js/login.js"></script>
</head>

<body>
	<img id="logo" src="images/logo.png" alt="logo">
	<div id="loginBox">
		<h2>Sign in</h2>
		<form id="loginForm">
			<input type="text" id="username" placeholder="Username" autofocus>
			<div id="usernameError" class="errorMessage">Please enter a username.</div>
			<br>
			<input type="password" id="password" placeholder="Password">
			<div id="passwordError" class="errorMessage">Please enter a password.</div>
			<br>
			<div id="loginError">Incorrect username or password.</div>
			<a href="#">Forgot password?</a>
			<br><br>
			<button type="submit" id="loginBtn">Submit</button>
		</form>
	</div>
</body>
</html>
