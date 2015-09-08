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

<!DOCTYPE html>
<html>
<head>
	<title>Audemium ERP Installer</title>
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="../images/favicon-16x16.png">
	
	<link type="text/css" rel="stylesheet" href="../vendor/qtip/jquery.qtip.min.css">
	<link type="text/css" rel="stylesheet" href="../css/styles.css">
	<style type="text/css">
		input, select {
			width: 150px !important;
		}
		#defaultPopup .sectionData {
			margin-bottom: 30px;
			text-align: center;
		}
		#settingsBlock {
			background-color: #E5E5E5;
			height: 300px;
			overflow: auto;
			text-align: left;
		}
	</style>
	
	<script type="text/javascript" src="../vendor/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../vendor/qtip/jquery.qtip.min.js"></script>
	<script type="text/javascript" src="../js/helpers.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			//add close for all actions
			$('.popup').on('click', '.close', function(event) {
				$('.popup').hide();
				event.preventDefault();
			});
			
			//qtip
			buildTooltips();
			function buildTooltips() {
				$('#data [title]').qtip({
					'style': {'classes': 'qtip-tipsy-custom'},
					'position': {
						'my': 'left center',
						'at': 'right center'
					},
					show: {'event': 'focus'},
					hide: {'event': 'blur'}
				});
			}
			
			//generate username on page load and when changing either of the name fields
			$('#firstName, #lastName').keyup(debounce(function(event) {
				generateUser();
			}, 250));
			generateUser();
			function generateUser() {
				if ($('#firstName').val() != '' && $('#lastName').val() != '') {
					var temp = $('#firstName').val().charAt(0).toLowerCase() + $('#lastName').val().charAt(0).toLowerCase() + '1';
					$('#user').val(temp);
				}
				else {
					$('#user').val('');
				}
			}
			
			//submit installer form
			$('#installBtn').click(function() {
				var noErrors = true;
				$('#data .invalid').qtip('destroy', true);
				$('#data .invalid').removeClass('invalid');
				buildTooltips(); //rebuild the default tooltips
				$('#data input, #data select').each(function() {
					if ($(this).val() == '') {
						noErrors = false;
						$(this).addClass('invalid');
					}
				});
				if ($('#password').val().length < 10) {
					noErrors = false;
					$('#password').addClass('invalid');
					$('#password').qtip('option', 'show.ready', true);
					$('#password').qtip('option', 'hide.event', false);
				}
				if ($('#password').val() != $('#retypePassword').val()) {
					noErrors = false;
					$('#retypePassword').addClass('invalid');
					$('#retypePassword').qtip({
						'content': 'Password must match',
						'style': {'classes': 'qtip-tipsy-custom'},
						'position': {
							'my': 'left center',
							'at': 'right center'
						},
						show: {'ready': true},
						hide: {'event': false}
					});
				}
				
				if (noErrors == true) {
					var ajaxData = $('#data input, #data select').serializeArray();
					$.ajax({
						url: 'install.php',
						type: 'POST',
						data: ajaxData
					}).done(function(data) {
						if (data.status == 'success') {
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
										show: {'ready': true},
										hide: {'event': false}
									});
								}
							});
						}
					});
				}
			});
		});
	</script>
</head>

<body>
	<img id="logo" src="../images/logo.png" alt="logo">
	
	<div id="content">
		<div id="topControls">
			<div id="topControlLeft"></div>
			<div id="topControlCenter"></div>
			<div id="topControlRight"></div>
		</div>
		<div id="data">
			<h1>Audemium ERP Installer</h1>
			<section>
				<h2>Database Information</h2>
				<div class="sectionData">
					Only MySQL is currently supported as the database server.
					<br><br>
					<ul>
						<li>
							<label for="dbHost">DB Host</label>
							<input type="text" name="dbHost">
						</li>
						<li>
							<label for="dbName">DB Name</label>
							<input type="text" name="dbName">
						</li>
					</ul>
					<ul>
						<li>
							<label for="dbUser">DB Username</label>
							<input type="text" name="dbUser" title="This user must be able to create and alter databases and insert data.">
						</li>
						<li>
							<label for="dbPassword">DB Password</label>
							<input type="password" name="dbPassword">
						</li>
					</ul>
				</div>
			</section>
			<section>
				<h2>Administrator</h2>
				<div class="sectionData">
					This must be the CEO of the company, as the user created here is added at the top of the organizational hierarchy.
					<br><br>
					<ul>
						<li>
							<label for="firstName">First Name</label>
							<input type="text" name="firstName" id="firstName">
						</li>
						<li>
							<label for="lastName">Last Name</label>
							<input type="text" name="lastName" id="lastName">
						</li>
						<li>
							<label for="workEmail">Email</label>
							<input type="text" name="workEmail" title="This must be a valid email if you want to be able to do a password reset.">
						</li>
					</ul>
					<ul>
						<li>
							<label for="user">Admin Username</label>
							<input type="text" name="username" id="user" title="This is automatically created from the name entered. Write it down to log in." readonly >
						</li>
						<li>
							<label for="password">Admin Password</label>
							<input type="password" name="password" id="password" title="Minimum 10 characters">
						</li>
						<li>
							<label for="retypePassword">Retype Password</label>
							<input type="password" name="retypePassword" id="retypePassword">
						</li>
					</ul>
					<div style="clear: both;"></div><br>
					The following information is used to complete the admin's employee profile. Other information, like salary, is left blank and can be edited later.
					<br><br>
					<ul>
						<li>
							<label for="position">Position</label>
							<input type="text" name="position" title="Position Name (ex. CEO, Founder or Owner)">
						</li>
					</ul>
					<ul>
						<li>
							<label for="location">Location</label>
							<input type="text" name="location" title="Name of the location where the employee usually works (ex. Home, Main St. Store, Store #1054)">
						</li>
					</ul>
				</div>
			</section>
			<section>
				<h2>Company Information</h2>
				<div class="sectionData">
					<ul>
						<li>
							<label for="companyName">Company Name</label>
							<input type="text" name="companyName">
						</li>
					</ul>
					<ul>
						<li>
							<label for="accounting">Accounting Basis</label>
							<select name="accounting">
								<option value=""></option>
								<option value="accrual">Accrual</option>
								<option value="cash">Cash</option>
							</select>
						</li>
					</ul>
				</div>
			</section>
			<div class="btnSpacer">
				<button id="installBtn">Install</button>
			</div>
		</div>
		<?php
			require('../footer.php');
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
