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
	<title>Sign in</title>
	<?php
		require('head.php');
	?>
	<style type="text/css">
		#loginBox {
			box-shadow: 1px 3px 8px #BBBBBB;
			background-color: #FFFFFF;
			border-radius: 16px;
			margin: 200px auto;
			padding: 40px 20px;
			width: 500px;
			text-align: center;
		}
		h2 {
			margin: 0 0 40px;
		}
		input {
			border: 1px solid #AAAAAA;
			border-radius: 4px;
			box-shadow: 0 0 3px #CCCCCC, 0 10px 15px #EEEEEE inset;
			margin-bottom: 20px;
			padding: 7px;
			width: 300px;
		}
		.valid {
			border-color: #28921F;
			box-shadow: 0 0 5px #5CD053;
		}
		.invalid {
			border-color: #E52D2D;
			box-shadow: 0 0 5px #D45252;
		}
		.errorMessage {
			color: #E52D2D;
			font-size: 12px;
			margin: -15px 0 0 100px;
			text-align: left;
			visibility: hidden;
		}
		#loginError {
			color: #E52D2D;
			visibility: hidden;
		}
		#loginBtn {
			color: #FFFFFF;
			font-weight: bold;
			border-radius: 4px;
			padding: 7px 25px;
			background: #1A87ED;
			border: 1px solid #1A87ED;
		}
		#loginBtn:hover {
			box-shadow: 1px 1px 2px 2px #BBB;
		}
	</style>
	
	<script type="text/javascript">
		$(document).ready(function() {
			$('input').focusout(function() {
				if ($(this).val() == '') {
					$(this).addClass('invalid');
					$(this).next().css('visibility', 'visible');
				}
				else {
					$(this).removeClass('invalid');
					$(this).next().css('visibility', 'hidden');
				}
			});

			$('#loginForm').submit(function(event) {
				//password is first so username gets focus if both are empty
				if ($('#password').val() == '') {
					$('#password').addClass('invalid');
					$('#passwordError').show();
					$('#password').focus();
				}
				if ($('#username').val() == '') {
					$('#username').addClass('invalid');
					$('#usernameError').show();
					$('#username').focus();
				}
				
				if ($('#username').val() != '' && $('#password').val() != '') {
					$.ajax({
						url: 'ajax.php',
						type: 'POST',
						data: {
							'action': 'login',
							'username': $('#username').val(),
							'password': $('#password').val()
						}
					}).done(function(data) {
						if (data.status == 'success') {
							window.location.replace(data.redirect);
						}
						else {
							$('#username').val('');
							$('#password').val('');
							$('#loginError').css('visibility', 'visible');
						}
					});
				}
				event.preventDefault();
			});
		});
	</script>
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
