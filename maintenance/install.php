<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	header('Content-Type: application/json');
	require('../helpers.php');
	require('../types/types.php');
	$return = ['status' => 'success'];
	
	//if settings.php already exists, fail first to prevent people from getting db things they shouldn't
	if (file_exists('../settings.php')) {
		$return['status'] = 'popup';
		$return['html'] = 'A settings file already exists. Please delete this file if you want to install Audemium ERP.';
	}
	
	//try to connect to db
	if ($return['status'] == 'success') {
		try {
			$dbh = new PDO(
				'mysql:host='.$_POST['dbHost'].';',
				$_POST['dbUser'],
				$_POST['dbPassword'],
				[
					PDO::ATTR_PERSISTENT => false,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
					//default for emulate prepares is true, which allows things like using the same parameter multiple times
				]
			);
		}
		catch (PDOException $e) {
			$return['status'] = 'popup';
			switch ($e->getCode()) {
				case 2003:
				case 2005:
					$return['html'] = "We can't connect to your MySQL server. Please check your connection information.";
					break;
				case 1045:
					$return['html'] = 'Database access was denied, please check your DB username and DB password.';
					break;
				default:
					$return['html'] = 'Unknown Error: '.$e->getMessage();
			}
		}
	}
	//check if the database already exists
	if ($return['status'] == 'success') {
		$sth = $dbh->prepare(
			'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbName');
		$sth->execute([':dbName' => $_POST['dbName']]);
		$result = $sth->fetchAll();
		if (count($result) == 1) {
			$return['status'] = 'fail';
			$return['dbName'] = 'This database already exists. Either delete the database or change the name.';
		}
	}
	
	//verify data that will be going into the db
	if ($return['status'] == 'success') {
		$data = [
			'username' => $_POST['username'],
			'firstName' => $_POST['firstName'],
			'lastName' => $_POST['lastName'],
			'payType' => 'S',
			'workEmail' => $_POST['workEmail']
		];
		$employee = verifyData('employee', null, 'add', $data);
		
		$data = [
			'name' => $_POST['position'],
		];
		$position = verifyData('position', null, 'add', $data);
		if (isset($position['name'])) {
			$position['position'] = $position['name'];
			unset($position['name']);
		}
		
		$data = [
			'name' => $_POST['location'],
		];
		$location = verifyData('location', null, 'add', $data);
		if (isset($location['name'])) {
			$location['location'] = $location['name'];
			unset($location['name']);
		}
		
		$accounting = [];
		if ($_POST['accounting'] != 'accrual' && $_POST['accounting'] != 'cash') {
			$accounting['status'] = 'fail';
			$accounting['accounting'] = 'Must be Accrual or Cash';
		}
		
		$return = array_merge($employee, $position, $location, $accounting);
		if ($employee['status'] == 'fail' || $position['status'] == 'fail' || $location['status'] == 'fail' || $accounting['status'] == 'fail') {
			$return['status'] = 'fail';
		}
	}
	
	//try to write settings.php, if we can't, we'll send it in the final section
	if ($return['status'] == 'success') {
		$settings = '<?php
	//Audemium ERP Version
	$VERSION = \'0.8.2\';
	
	//Database
	$SETTINGS[\'dbServer\'] = \''.addcslashes($_POST['dbHost'], "'").'\';
	$SETTINGS[\'dbName\'] = \''.addcslashes($_POST['dbName'], "'").'\';
	$SETTINGS[\'dbUser\'] = \''.addcslashes($_POST['dbUser'], "'").'\';
	$SETTINGS[\'dbPassword\'] = \''.addcslashes($_POST['dbPassword'], "'").'\';
	
	//Demo mode (disables password reset if true)
	$SETTINGS[\'demoMode\'] = false;
	
	//Company information
	$SETTINGS[\'companyName\'] = \''.addcslashes($_POST['companyName'], "'").'\';
	
	//Accounting basis, values are cash OR accrual
	$SETTINGS[\'accounting\'] = \''.addcslashes($_POST['accounting'], "'").'\';
	
	//Default timezone (see http://php.net/manual/en/timezones.php for options)
	$SETTINGS[\'timeZone\'] = \'America/New_York\';
	
	//Date/time formats (see http://php.net/manual/en/function.date.php for options)
	$SETTINGS[\'dateTimeFormat\'] = \'d-M-Y g:i A\';
	$SETTINGS[\'dateFormat\'] = \'d-M-Y\';
	$SETTINGS[\'timeFormat\'] = \'g:i A\';
	
	//Date/time formats in Javascript (see http://trentrichardson.com/examples/timepicker/ for options)
	$SETTINGS[\'dateFormatJS\'] = \'dd-M-yy\';
	$SETTINGS[\'timeFormatJS\'] = \'h:mm TT\';
	
	//Currency and number formatting
	$SETTINGS[\'currencySymbol\'] = \'$\';			//can be a character or html entity
	$SETTINGS[\'negativeCurrencyFormat\'] = 0;	//0 = negative sign ex. -$10.00		1 = parentheses ex. ($10.00)
	$SETTINGS[\'decimalFormat\'] = \'.\';
	$SETTINGS[\'thousandsSeparator\'] = \',\';
	
	//Email
	$SETTINGS[\'SMTP\'] = [
		\'host\' => \'\',
		\'port\' => 587,
		\'auth\' => true,
		\'encryption\' => \'tls\',
		\'username\' => \'\',
		\'password\' => \'\'
	];
	
	//Columns for list views
	$SETTINGS[\'columns\'][\'employee\'] = [\'name\', \'positionID\', \'locationID\', \'payType\', \'payAmount\'];
	$SETTINGS[\'columns\'][\'order\'] = [\'orderID\', \'customerID\', \'employeeID\'];
	$SETTINGS[\'columns\'][\'location\'] = [\'name\', \'address\', \'city\', \'state\', \'zip\'];
	$SETTINGS[\'columns\'][\'position\'] = [\'name\'];
	$SETTINGS[\'columns\'][\'product\'] = [\'name\', \'defaultPrice\'];
	$SETTINGS[\'columns\'][\'service\'] = [\'name\', \'defaultPrice\'];
	$SETTINGS[\'columns\'][\'customer\'] = [\'name\'];
	$SETTINGS[\'columns\'][\'discount\'] = [\'name\', \'discountType\', \'discountAmount\'];
	$SETTINGS[\'columns\'][\'expense\'] = [\'expenseID\', \'supplierID\', \'employeeID\'];
	$SETTINGS[\'columns\'][\'supplier\'] = [\'name\'];
?>';
		$fileStatus = file_put_contents('../settings.php', $settings);
	}
	
	//create database and tables
	if ($return['status'] == 'success') {
		try {
			require('database.php');
			
			$sth = $dbh->prepare('CREATE DATABASE `'.$_POST['dbName'].'` DEFAULT CHARACTER SET utf8');
			$sth->execute();
			$sth = $dbh->prepare('USE `'.$_POST['dbName'].'`');
			$sth->execute();
			
			foreach ($sql as $version) {
				foreach ($version as $query) {
					$sth = $dbh->prepare($query);
					$sth->execute();
				}
			}
		}
		catch (PDOException $e) {
			$return['status'] = 'popup';
			$return['html'] = 'Something went wrong. It\'s possible that the database user you provided doesn\'t have the right privileges to create a database.';
			$return['sql'] = 'Unknown Error: '.$e->getMessage();
		}
	}
	
	//insert starter data
	if ($return['status'] == 'success') {
		try {
			//employee
			$password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 10]);
			$sth = $dbh->prepare(
				'INSERT INTO employees
				VALUES(null, :username, :password, 0, :firstName, :lastName, "S", 0.00, "", "", "", "", :workEmail, 1, 1, 0, 0, "", 1)');
			$sth->execute([':username' => $_POST['username'], ':password' => $password, ':firstName' => $_POST['firstName'], ':lastName' => $_POST['lastName'], ':workEmail' => $_POST['workEmail']]);
			$changes = ['firstName' => $_POST['firstName'], 'lastName' => $_POST['lastName'], 'payType' => 'S', 'payAmount' => '0', 'locationID' => '1', 'positionID' => '1', 'managerID' => '', 'vacationTotal' => '0', 'address' => '', 'city' => '', 'state' => '', 'zip' => '', 'workEmail' => $_POST['workEmail']];
			$sth = $dbh->prepare(
				'INSERT INTO changes
				VALUES(null, "employee", 1, 1, UNIX_TIMESTAMP(), "A", :changes)');
			$sth->execute([':changes' => json_encode($changes)]);
			
			//position
			$sth = $dbh->prepare(
				'INSERT INTO positions
				VALUES(null, :position, 1)');
			$sth->execute([':position' => $_POST['position']]);
			$changes = ['name' => $_POST['position']];
			$sth = $dbh->prepare(
				'INSERT INTO changes
				VALUES(null, "position", 1, 1, UNIX_TIMESTAMP(), "A", :changes)');
			$sth->execute([':changes' => json_encode($changes)]);
			
			//location
			$sth = $dbh->prepare(
				'INSERT INTO locations
				VALUES(null, :location, "", "", "", "", 1)');
			$sth->execute([':location' => $_POST['location']]);
			$changes = ['name' => $_POST['location'], 'address' => '', 'city' => '', 'state' => '', 'zip' => ''];
			$sth = $dbh->prepare(
				'INSERT INTO changes
				VALUES(null, "location", 1, 1, UNIX_TIMESTAMP(), "A", :changes)');
			$sth->execute([':changes' => json_encode($changes)]);
		}
		catch (PDOException $e) {
			$return['status'] = 'popup';
			$return['html'] = 'Something went wrong. It\'s possible that the database user you provided doesn\'t have the right privileges to insert data.';
		}
	}
	
	//success, tell user to delete maintenance folder and remind them of their username
	if ($return['status'] == 'success') {
		$fileStr = ($fileStatus === false) ? ', but we were not able to write the settings file. Copy the text below into a file named settings.php in the top Audemium ERP directory' : '';
		$return['html'] = 'The installation was successful'.$fileStr.'. For security, delete the maintenance folder. As a reminder, your username is <b>'.$_POST['username'].'</b>. You can log in <a href="../index.php">here</a>.';
		if ($fileStatus === false) {
			$return['html'] .= '<br><br><pre id="settingsBlock">';
			$return['html'] .= htmlspecialchars($settings, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$return['html'] .= '</pre>';
		}
	}
	
	echo json_encode($return);
?>