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
		h1 {
			margin: 0 0 40px;
		}
		input {
			margin-bottom: 20px;
			padding: 7px !important;
			width: 300px !important;
		}
		#loginError {
			color: #E52D2D;
			visibility: hidden;
			margin-bottom: 20px;
		}
	</style>
	
	<script type="text/javascript">
		$(document).ready(function() {
			$('#loginForm').submit(function(event) {
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: {
						'action': 'login',
						'username': $('#username').val(),
						'password': $('#password').val()
					}
				}).done(function(data) {
					$('.invalid').qtip('destroy', true);
					$('.invalid').removeClass('invalid');
					$('#loginError').css('visibility', 'hidden');
					
					if (data.status == 'success') {
						window.location.replace(data.redirect);
					}
					else if (data.status == 'popup') {
						$('#username').val('');
						$('#password').val('');
						$('#loginError').css('visibility', 'visible');
					}
					else {
						$.each(data, function(key, value) {
							if (key != 'status') {
								$('#' + key).addClass('invalid');
								$('#' + key).qtip({
									'content': value,
									'style': {'classes': 'qtip-tipsy-custom'},
									'position': {
										'my': 'left center',
										'at': 'right center'
									},
									show: {'event': 'focus'},
									hide: {'event': 'blur'}
								});
							}
						});
					}
				});
				event.preventDefault();
			});
		});
	</script>
</head>

<body>
	<div id="loginBox">
		<h1>Sign in to <?php echo $SETTINGS['companyName']; ?></h1>
		<form id="loginForm">
			<input type="text" id="username" placeholder="Username" autofocus>
			<input type="password" id="password" placeholder="Password">
			<div id="loginError">Incorrect username or password.</div>
			<a href="forgot.php">Forgot password?</a>
			<div class="btnSpacer">
				<button type="submit" id="loginBtn">Submit</button>
			</div>
		</form>
	</div>
	<?php
		require('footer.php');
	?>
</body>
</html>
