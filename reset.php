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
	<title>Change Password</title>
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
		}
		p {
			text-align: left;
			margin: 40px 0;
		}
		#loginBox label {
			width: 250px;
		}
		#loginBox li {
			padding: 3px 0;
		}
		#reqTitle {
			text-align: center;
			font-weight: bold;
			margin-top: 40px;
		}
		#reqList {
			margin: 0 0 20px 0;
		}
	</style>
	
	<script type="text/javascript">
		$(document).ready(function() {
			$('#changeBtn').click(function() {
				var ajaxData = $('#loginBox input').serializeArray();
				ajaxData.push(
					{'name': 'action', 'value': 'changePassword'}
				);
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: ajaxData
				}).done(function(data) {
					$('#loginBox .invalid').qtip('destroy', true);
					$('#loginBox .invalid').removeClass('invalid');
					if (data.status == 'success') {
						window.location.replace(data.redirect);
					}
					else {
						$.each(data, function(key, value) {
							if (key != 'status') {
								$('#loginBox [name=' + key + ']').addClass('invalid');
								$('#loginBox [name=' + key + ']').qtip({
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
			});
		});
	</script>
</head>

<body>
	<div id="loginBox">
		<h1>Change Password</h1>
		<p>Welcome to <?php echo $SETTINGS['companyName']; ?>! This is the first time you have logged in to your account, so we need to change your password to one that only you know. Enter your new password below.</p>
		<ul style="list-style-type:none;">
			<li>
				<label for="password">New Password</label>
				<input type="password" name="newPassword">
			</li>
			<li>
				<label for="retypePassword">Retype New Password</label>
				<input type="password" name="retypePassword">
			</li>
		</ul>
		<div id="reqTitle">Password Requirements</div>
		<ul id="reqList">
			<li>Must be at least 10 characters</li>
			<li>Simple passwords are not allowed</li>
			<li>Using a <a href="https://en.wikipedia.org/wiki/List_of_password_managers" target="_blank">password manager</a> is strongly recommended</li>
		</ul>
		<div class="btnSpacer">
			<button id="changeBtn">Change</button>
		</div>
	</div>
	<?php
		require('footer.php');
	?>
</body>
</html>
