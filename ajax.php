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
		$return = ['status' => 'fail'];
		
		$sth = $dbh->prepare(
			'SELECT employeeID, password, timeZone
			FROM employees
			WHERE username = :username');
		$sth->execute([':username' => $_POST['username']]);
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
				$return = ['status' => 'success', 'redirect' => $redirect];
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
		$return = ['status' => 'fail'];
		
		//TODO: get search results
		unset($return);
		$results = [
			['name' => 'Nicholas Anderson', 'type' => 'employeee', 'image' => 'user_blue_32.png', 'url' => 'employees.php?id=1'],
			['name' => 'Website Development', 'type' => 'service', 'image' => 'basket_32.png', 'url' => 'services.php?id=1'],
			['name' => 'Owner', 'type' => 'position', 'image' => 'user_business_32.png', 'url' => 'positions.php?id=1']
		];
		$return = ['status' => 'success', 'results' => $results];
		
		echo json_encode($return);
	}
	
	/*
		Function: add
		Inputs: 
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'add') {
		$return = ['status' => 'success'];
		
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
			'<h1>Add employee</h1>
			<section>
				<h2>Basic Information</h2>
				<div class="sectionData">
					<ul>
						<li>
							<label for="firstName">First Name</label>
							<input type="text" name="firstName" autocomplete="off">
						</li>
						<li>
							<label for="lastName">Last Name</label>
							<input type="text" name="lastName" autocomplete="off">
						</li>
						<li>
							<label for="payType">Pay Type</label>
							<select name="payType">
								<option value=""></option>
								<option value="H">Hourly</option>
								<option value="S">Salary</option>
							</select>
						</li>
						<li>
							<label for="payAmount">Pay Amount</label>
							<input type="text" name="payAmount" autocomplete="off">
						</li>
					</ul>
					<ul>
						<li>
							<label for="locationID">Location</label>
							<select name="locationID">
								<option value=""></option>
								'.$locationOptions.'
							</select>
						</li>
						<li>
							<label for="positionID">Position</label>
							<select name="positionID">
								<option value=""></option>
								'.$positionOptions.'
							</select>
						</li>
					</ul>
				</div>
			</section>
			<section>
				<h2>Personal Information</h2>
				<div class="sectionData">
					<ul>
						<li>
							<label for="address">Address</label>
							<input type="text" name="address" autocomplete="off">
						</li>
					</ul>
				</div>
			</section>
			<div id="btnSpacer"><button id="addBtn">Add</button></div>';
		
		echo json_encode($return);
	}
	
	/*
		Function: addSave
		Inputs: 
		Outputs: 
	*/
	if ($_POST['action'] == 'addSave') {
		//verify data
		foreach ($_POST as $key => $value) {
			if ($key != 'action' && $key != 'type') {
				$data[$key] = $value;
			}
		}
		$return = verifyData($_POST['type'], $data);
		
		if ($return['status'] != 'fail') {
			foreach ($data as $key => $value) {
				$keyArr[] = $key;
				$placeholderArr[] = '?';
				$valArr[] = $value;
				$changeData[$key] = $value;
			}
			$tableName = $TYPES[$_POST['type']]['pluralName'];
			$idName = $TYPES[$_POST['type']]['idName'];
			
			if ($_POST['type'] == 'employee') {
				//generate username
				$alphaUser = strtolower(substr($_POST['firstName'], 0, 1).substr($_POST['lastName'], 0, 1));
				$sth = $dbh->prepare(
					'SELECT MAX(CAST(SUBSTRING(username, 3, LENGTH(username) - 2) AS UNSIGNED)) AS maxID
					FROM employees
					WHERE username LIKE '.$dbh->quote($alphaUser.'%'));
				$sth->execute();
				$row = $sth->fetch();
				$idNum = (is_null($row['maxID'])) ? 1 : $row['maxID'] + 1;
				$keyArr[] = 'username';
				$placeholderArr[] = '?';
				$valArr[] = $alphaUser.$idNum;
				
				//generate temp password (excludes 0, o, 1, l and vowels)
				$characters = 'bcdfghjkmnpqrstvwxyz23456789';
				$length = strlen($characters) - 1;
				$password = '';
				for ($i = 0; $i < 8; $i++) {
					$password .= $characters[mt_rand(0, $length)];
				}
				$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
				$keyArr[] = 'password';
				$placeholderArr[] = '?';
				$valArr[] = $hash;
			}
			
			//add to changes table
			addChange($_POST['type'], $id, $_SESSION['employeeID'], json_encode($changeData));
			
			//do the insert
			$sth = $dbh->prepare(
				'INSERT INTO '.$tableName.' ('.$idName.', active, '.implode(', ', $keyArr).')
				VALUES(null, 1, '.implode(', ', $placeholderArr).')');
			$sth->execute($valArr);
			$id = $dbh->lastInsertId();
			
			if ($_POST['type'] == 'employee') {
				$return['html'] = 
					'<h1>Add employee</h1>
					<div style="text-align: center;"><br>
						Employee <a href="employees_item.php?id='.$id.'">'.$_POST['firstName'].' '.$_POST['lastName'].'</a> was added.<br>
						Save the username and temporary password below to give to the employee.<br><br>
						<b>Username: </b>'.$alphaUser.$idNum.'<br>
						<b>Temporary Password: </b>'.$password.'
					</div>';
			}
		}
		
		echo json_encode($return);
	}
	
	/*
		Function: edit
		Inputs: id
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'edit') {
		$return = ['status' => 'success'];
		
		//get current values
		$sth = $dbh->prepare(
			'SELECT username, firstName, lastName, payType, payAmount, locationID, positionID, active
			FROM employees
			WHERE employeeID = :employeeID');
		$sth->execute([':employeeID' => $_POST['id']]);
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
			'<h1>Edit employee</h1>
			<section>
				<h2>Basic Information</h2>
				<div class="sectionData">
					<ul>
						<li>
							<label for="firstName">First Name</label>
							<input type="text" name="firstName" autocomplete="off" value="'.$employee['firstName'].'">
						</li>
						<li>
							<label for="lastName">Last Name</label>
							<input type="text" name="lastName" autocomplete="off" value="'.$employee['lastName'].'">
						</li>
						<li>
							<label for="payType">Pay Type</label>
							<select name="payType">
								'.$payTypeOptions.'
							</select>
						</li>
						<li>
							<label for="payAmount">Pay Amount</label>
							<input type="text" name="payAmount" autocomplete="off" value="'.$employee['payAmount'].'">
						</li>
					</ul>
					<ul>
						<li>
							<label for="locationID">Location</label>
							<select name="locationID">
								'.$locationOptions.'
							</select>
						</li>
						<li>
							<label for="positionID">Position</label>
							<select name="positionID">
								'.$positionOptions.'
							</select>
						</li>
					</ul>
				</div>
			</section>
			<section>
				<h2>Personal Information</h2>
				<div class="sectionData">
					<ul>
						<li>
							<label for="address">Address</label>
							<input type="text" name="address" autocomplete="off">
						</li>
					</ul>
				</div>
			</section>
			<div id="btnSpacer"><button id="editBtn">Edit</button></div>';
		
		echo json_encode($return);
	}
	
	/*
		Function: editMany
		Inputs: 
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'editMany') {
		$return = ['status' => 'success'];
		
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
			'<h1>Edit employees</h1>
			<section>
				<h2>Basic Information</h2>
				<div class="sectionData">
					<ul>
						<li>
							<input type="checkbox">
							<label for="firstName">First Name</label>
							<input type="text" name="firstName" autocomplete="off" disabled>
						</li>
						<li>
							<input type="checkbox">
							<label for="lastName">Last Name</label>
							<input type="text" name="lastName" autocomplete="off" disabled>
						</li>
						<li>
							<input type="checkbox">
							<label for="payType">Pay Type</label>
							<select name="payType" disabled>
								<option value=""></option>
								<option value="H">Hourly</option>
								<option value="S">Salary</option>
							</select>
						</li>
						<li>
							<input type="checkbox">
							<label for="payAmount">Pay Amount</label>
							<input type="text" name="payAmount" autocomplete="off" disabled>
						</li>
					</ul>
					<ul>
						<li>
							<input type="checkbox">
							<label for="locationID">Location</label>
							<select name="locationID" disabled>
								'.$locationOptions.'
							</select>
						</li>
						<li>
							<input type="checkbox">
							<label for="positionID">Position</label>
							<select name="positionID" disabled>
								'.$positionOptions.'
							</select>
						</li>
					</ul>
				</div>
			</section>
			<section>
				<h2>Personal Information</h2>
				<div class="sectionData">
					<ul>
						<li>
							<input type="checkbox">
							<label for="address">Address</label>
							<input type="text" name="address" autocomplete="off" disabled>
						</li>
					</ul>
				</div>
			</section>
			<div id="btnSpacer"><button id="editBtn">Edit</button></div>';
		
		echo json_encode($return);
	}
	
	/*
		Function: editSave
		Inputs: 
		Outputs: 
	*/
	if ($_POST['action'] == 'editSave') {
		//verify data
		foreach ($_POST as $key => $value) {
			if ($key != 'action' && $key != 'type' && $key != 'id') {
				$data[$key] = $value;
			}
		}
		$return = verifyData($_POST['type'], $data);
		
		if ($return['status'] != 'fail') {
			$idArr = explode(',', $_POST['id']);
			$idArrSafe = [];
			foreach ($data as $key => $value) {
				$updateArr[] = $key.' = ?';
				$valArr[] = $value;
				$changeData[$key] = $value;
			}
			$updateStr = implode(', ', $updateArr);
			$tableName = $TYPES[$_POST['type']]['pluralName'];
			$idName = $TYPES[$_POST['type']]['idName'];
			
			//add to changes table, include only what actually changed for each item
			foreach ($idArr as $id) {
				$sth = $dbh->prepare(
					'SELECT * FROM '.$tableName.'
					WHERE '.$idName.' = :id');
				$sth->execute([':id' => $id]);
				$row = $sth->fetch();
				if ($row['active'] == 1) {
					$idArrSafe[] = $dbh->quote($id);
					$tempData = [];
					foreach ($changeData as $key => $value) {
						if ($row[$key] != $value) {
							$tempData[$key] = $value;
						}
					}
					if (count($tempData) > 0) {
						addChange($_POST['type'], $id, $_SESSION['employeeID'], json_encode($tempData));
					}
				}
			}
			
			//do the update
			$ids = implode(',', $idArrSafe);
			$sth = $dbh->prepare(
				'UPDATE '.$tableName.'
				SET '.$updateStr.'
				WHERE '.$idName.' IN ('.$ids.')');
			$sth->execute($valArr);
		}
		
		echo json_encode($return);
	}
	
	/*
		Function: delete
		Inputs: 
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'delete') {
		$return = ['status' => 'success'];
		
		$return['html'] = 
			'<h1>Delete '.$_POST['type'].'</h1><br>
			<div style="text-align: center;">Are you sure you want to delete <b>'.getName($_POST['type'], $_POST['id']).'</b>?  This will mark the '.$_POST['type'].' as inactive, but will retain historical information.</div>
			<div id="btnSpacer"><button id="deleteBtn">Delete</button></div>';
		
		echo json_encode($return);
	}
	
	/*
		Function: deleteMany
		Inputs: 
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'deleteMany') {
		$return = ['status' => 'success'];
		$pluralName = $TYPES[$_POST['type']]['pluralName'];
		
		$return['html'] = 
			'<h1>Delete '.$pluralName.'</h1><br>
			<div style="text-align: center;">Are you sure you want to delete these '.$pluralName.'?  This will mark the '.$pluralName.' as inactive, but will retain historical information.</div>
			<div id="btnSpacer"><button id="deleteBtn">Delete</button></div>';
		
		echo json_encode($return);
	}
	
	/*
		Function: deleteSave
		Inputs: 
		Outputs: 
	*/
	if ($_POST['action'] == 'deleteSave') {
		$return = ['status' => 'success'];
		
		$idArr = explode(',', $_POST['id']);
		$idArrSafe = [];
		$tableName = $TYPES[$_POST['type']]['pluralName'];
		$idName = $TYPES[$_POST['type']]['idName'];
		
		//add to changes table
		foreach ($idArr as $id) {
			$sth = $dbh->prepare(
				'SELECT active FROM '.$tableName.'
				WHERE '.$idName.' = :id');
			$sth->execute([':id' => $id]);
			$row = $sth->fetch();
			if ($row['active'] == 1) {
				$idArrSafe[] = $dbh->quote($id);
				addChange($_POST['type'], $id, $_SESSION['employeeID'], '');
			}
		}
		
		//mark items as inactive
		$ids = implode(',', $idArrSafe);
		$sth = $dbh->prepare(
			'UPDATE '.$tableName.'
			SET active = 0
			WHERE '.$idName.' IN ('.$ids.')');
		$sth->execute();
		
		echo json_encode($return);
	}
?>