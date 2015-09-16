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
	<title>Account Settings</title>
	<?php
		require('head.php');
	?>
	
	<style type="text/css">
		label {
			width: 250px;
		}
		#reqList {
			list-style-type: disc;
			margin-left: 30px;
			width: 45%;
		}
		#defaultPopup .sectionData {
			margin-bottom: 30px;
			text-align: center;
		}
	</style>
	
	<script type="text/javascript">
		$(document).ready(function() {
			$('#accountSaveBtn').click(function() {
				$('#accountSaveBtn').prop('disabled', true);
				$('#accountSaveBtn').addClass('buttonDisabled');
				var ajaxData = $('#data input').serializeArray();
				ajaxData.push(
					{'name': 'action', 'value': 'changePassword'}
				);
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: ajaxData
				}).done(function(data) {
					$('#data .invalid').removeClass('invalid');
					$('#accountSaveBtn').prop('disabled', false);
					$('#accountSaveBtn').removeClass('buttonDisabled');
					if (data.status == 'success') {
						$('#data input').val('');
						$('#defaultPopup > div > div > h1').html('Success');
						$('#defaultPopup .sectionData').html(data.html);
						$('#defaultPopup').show();
					}
					else if (data.status == 'popup') {
						$('#defaultPopup > div > div > h1').html('Error');
						$('#defaultPopup .sectionData').html(data.html);
						$('#defaultPopup').show();
					}
					else {
						$.each(data, function(key, value) {
							if (key != 'status') {
								$('#data [name=' + key + ']').addClass('invalid');
								$('#data [name=' + key + ']').qtip({
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
	<?php
		require('menu.php');
	?>
	<div id="content">
		<div id="topControls">
			<div id="topControlLeft"></div>
			<div id="topControlCenter"></div>
			<div id="topControlRight"></div>
		</div>
		<div id="data">
			<h1>Account Settings</h1>
			<section>
				<h2>Change Password</h2>
				<div class="sectionData">
					<ul>
						<li>
							<label for="password">Current Password</label>
							<input type="password" name="password">
						</li>
						<li>
							<label for="password">New Password</label>
							<input type="password" name="newPassword">
						</li>
						<li>
							<label for="retypePassword">Retype New Password</label>
							<input type="password" name="retypePassword">
						</li>
					</ul>
					<b>Password Requirements</b>
					<ul id="reqList">
						<li>Must be at least 10 characters</li>
						<li>Simple passwords are not allowed</li>
						<li>Using a <a href="https://en.wikipedia.org/wiki/List_of_password_managers" target="_blank">password manager</a> is strongly recommended</li>
					</ul>
				</div>
			</section>
			<div class="btnSpacer">
				<button id="accountSaveBtn">Save</button>
			</div>
		</div>
		<?php
			require('footer.php');
		?>
	</div>
	<div class="popup" id="defaultPopup">
		<div>
			<a class="close" title="Close">X</a>
			<div>
				<h1></h1>
				<section>
					<h2>Details</h2>
					<div class="sectionData">
					</div>
				</section>
			</div>
		</div>
	</div>
</body>
</html>
