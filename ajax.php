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
	header('Content-Type: application/json');
	
	/*
		Function: login
		Inputs: username, password
		Outputs: status (success / fail), redirect
	*/
	if ($_POST['action'] == 'login') {
		$return = array('status' => 'fail');
		
		$sth = $dbh->prepare(
			'SELECT employeeID, password, timeZone
			FROM employees
			WHERE username = :username');
		$sth->execute(array(':username' => $_POST['username']));
		$result = $sth->fetchAll();
		if (count($result) == 1) {
			if (password_verify($_POST['password'], $result[0]['password'])) {
				//user is verified
				session_regenerate_id(true);
				$_SESSION['loggedIn'] = true;
				$_SESSION['employeeID'] = $result[0]['employeeID'];
				$_SESSION['timeZone'] = $result[0]['timeZone'];
				$redirect = (isset($_SESSION['loginDestination']) && $_SESSION['loginDestination'] != '/login.php') ? $_SESSION['loginDestination'] : '/index.php';
				unset($return);
				$return = array('status' => 'success', 'redirect' => $redirect);
				unset($_SESSION['loginDestination']);
			}
		}
		
		echo json_encode($return);
	}
	
	/*
		Function: login
		Inputs: 
		Outputs: status (success / fail), results
	*/
	if ($_POST['action'] == 'search') {
		$return = array('status' => 'fail');
		
		//TODO: get search results
		unset($return);
		$results = array(
			array('name' => 'Nicholas Anderson', 'type' => 'employeee', 'image' => 'user_blue_32.png', 'url' => 'employees.php?id=1'),
			array('name' => 'Website Development', 'type' => 'service', 'image' => 'basket_32.png', 'url' => 'services.php?id=1'),
			array('name' => 'Owner', 'type' => 'position', 'image' => 'user_business_32.png', 'url' => 'positions.php?id=1')
		);
		$return = array('status' => 'success', 'results' => $results);
		
		echo json_encode($return);
	}
	
	/*
		Function: add
		Inputs: 
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'add') {
		$return = array('status' => 'success');
		
		//get list of locations
		$locationOptions = '';
		$sth = $dbh->prepare(
			'SELECT locationID, name
			FROM locations
			WHERE active = 1
			ORDER BY name');
		$sth->execute();
		while ($row = $sth->fetch()) {
			$locationOptions .= '<option value="'.$row['locationID'].'">'.$row['name'].'</option>';
		}
		
		//get list of positions
		$positionOptions = '';
		$sth = $dbh->prepare(
			'SELECT positionID, name
			FROM positions
			WHERE active = 1
			ORDER BY name');
		$sth->execute();
		while ($row = $sth->fetch()) {
			$positionOptions .= '<option value="'.$row['positionID'].'">'.$row['name'].'</option>';
		}
		
		$return['html'] = 
			'<a id="close" title="Close">X</a>
			<h1>Add employee</h1><br>
			<table class="popupTable">
				<tr>
					<td class="fieldLabel">First Name:</td>
					<td><input type="text" id="firstName"></td>
					<td class="fieldLabel">Location:</td>
					<td>
						<select id="locationID">
							<option value=""></option>
							'.$locationOptions.'
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldLabel">Last Name:</td>
					<td><input type="text" id="lastName"></td>
					<td class="fieldLabel">Position:</td>
					<td>
						<select id="positionID">
							<option value=""></option>
							'.$positionOptions.'
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldLabel">Pay Type:</td>
					<td>
						<select id="payType">
							<option value=""></option>
							<option value="H">Hourly</option>
							<option value="S">Salary</option>
						</select>
					</td>
					<td class="fieldLabel"></td>
					<td></td>
				</tr>
				<tr>
					<td class="fieldLabel">Pay Amount:</td>
					<td><input type="text" id="payAmount"></td>
				</tr>
			</table>
			<div id="btnSpacer"><button id="addBtn">Add</button></div>';
		
		echo json_encode($return);
	}
	
	/*
		Function: edit
		Inputs: id
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'edit') {
		$return = array('status' => 'success');
		
		//get current values
		$sth = $dbh->prepare(
			'SELECT username, firstName, lastName, payType, payAmount, locationID, positionID, active
			FROM employees
			WHERE employeeID = :employeeID '
		);
		$sth->execute(array(':employeeID' => $_POST['id']));
		$employee = $sth->fetch();
		
		//get list of locations
		$locationOptions = '';
		$sth = $dbh->prepare(
			'SELECT locationID, name
			FROM locations
			WHERE active = 1
			ORDER BY name');
		$sth->execute();
		while ($row = $sth->fetch()) {
			$selected = '';
			if ($employee['locationID'] == $row['locationID']) {
				$selected = ' selected';
			}
			$locationOptions .= '<option value="'.$row['locationID'].'"'.$selected.'>'.$row['name'].'</option>';
		}
		
		//get list of positions
		$positionOptions = '';
		$sth = $dbh->prepare(
			'SELECT positionID, name
			FROM positions
			WHERE active = 1
			ORDER BY name');
		$sth->execute();
		while ($row = $sth->fetch()) {
			$selected = '';
			if ($employee['positionID'] == $row['positionID']) {
				$selected = ' selected';
			}
			$positionOptions .= '<option value="'.$row['positionID'].'"'.$selected.'>'.$row['name'].'</option>';
		}
		
		//get list of payTypes
		$selected = ($employee['payType'] == 'H') ? ' selected' : '';
		$payTypeOptions = '<option value="H"'.$selected.'>Hourly</option>';
		$selected = ($employee['payType'] == 'S') ? ' selected' : '';
		$payTypeOptions .= '<option value="S"'.$selected.'>Salary</option>';
		
		
		$return['html'] = 
			'<a id="close" title="Close">X</a>
			<h1>Edit employee</h1><br>
			<table class="popupTable">
				<tr>
					<td class="fieldLabel">First Name:</td>
					<td><input type="text" id="firstName" value="'.$employee['firstName'].'"></td>
					<td class="fieldLabel">Location:</td>
					<td>
						<select id="locationID">
							<option value=""></option>
							'.$locationOptions.'
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldLabel">Last Name:</td>
					<td><input type="text" id="lastName" value="'.$employee['lastName'].'"></td>
					<td class="fieldLabel">Position:</td>
					<td>
						<select id="positionID">
							'.$positionOptions.'
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldLabel">Pay Type:</td>
					<td>
						<select id="payType">
							'.$payTypeOptions.'
						</select>
					</td>
					<td class="fieldLabel"></td>
					<td></td>
				</tr>
				<tr>
					<td class="fieldLabel">Pay Amount:</td>
					<td><input type="text" id="payAmount" value="'.$employee['payAmount'].'"></td>
				</tr>
			</table>
			<div id="btnSpacer"><button id="editBtn">Edit</button></div>';
		
		echo json_encode($return);
	}
	
	/*
		Function: delete
		Inputs: id
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'delete') {
		$return = array('status' => 'success');
		
		$return['html'] = 
			'<a id="close" title="Close">X</a>
			<h1>Delete employee</h1><br>
			<div class="popupText">Are you sure you want to delete this employee?  This will mark the employee as inactive, but will retain their information.</div>
			<div id="btnSpacer"><button id="deleteBtn">Delete</button></div>';
		
		echo json_encode($return);
	}
?>