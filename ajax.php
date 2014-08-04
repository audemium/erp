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
		
		$return['html'] = '<h1>Add '.$_POST['type'].'</h1>';
		foreach ($TYPES[$_POST['type']]['formData'] as $key => $section) {
			$return['html'] .= '<section><h2>'.$key.'</h2><div class="sectionData">';
			foreach ($section as $column) {
				$return['html'] .= '<ul>';
				foreach ($column as $field) {
					$formalName = $TYPES[$_POST['type']]['fields'][$field]['formalName'];
					$attributes = $TYPES[$_POST['type']]['fields'][$field]['verifyData'];
					if ($attributes[1] == 'int' || $attributes[1] == 'str' || $attributes[1] == 'dec') {
						$return['html'] .= '<li><label for="'.$field.'">'.$formalName.'</label>';
						$return['html'] .= '<input type="text" name="'.$field.'" autocomplete="off"></li>';
					}
					elseif ($attributes[1] == 'id' || $attributes[1] == 'opt') {
						$return['html'] .= '<li><label for="'.$field.'">'.$formalName.'</label><select name="'.$field.'">';
						$return['html'] .= ($attributes[1] == 'id') ? generateTypeOptions($attributes[2], true) : generateFieldOptions($_POST['type'], $field, true);
						$return['html'] .= '</select></li>';
					}
				}
				$return['html'] .= '</ul>';
			}
			$return['html'] .= '</div></section>';
		}
		$return['html'] .= '<div id="btnSpacer"><button id="addBtn">Add</button></div>';
		
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
		//manual check for managerID because it's required, but not checked in verifyData
		if (array_key_exists('managerID', $data) && $data['managerID'] == '') {
			$return['status'] = 'fail';
			$return['managerID'] = 'Required';
		}
		
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
				$numUser = (is_null($row['maxID'])) ? 1 : $row['maxID'] + 1;
				$keyArr[] = 'username';
				$placeholderArr[] = '?';
				$valArr[] = $alphaUser.$numUser;
				
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
			
			//do the insert
			$sth = $dbh->prepare(
				'INSERT INTO '.$tableName.' ('.$idName.', active, '.implode(', ', $keyArr).')
				VALUES(null, 1, '.implode(', ', $placeholderArr).')');
			$sth->execute($valArr);
			$id = $dbh->lastInsertId();
			
			//add to changes table
			addChange($_POST['type'], $id, $_SESSION['employeeID'], json_encode($changeData));
			
			if ($_POST['type'] == 'employee') {
				//update hierarchy table
				updateHierarchy('add', $_POST['managerID'], $id);
			
				$return['html'] = 
					'<h1>Add employee</h1>
					<div style="text-align: center;"><br>
						Employee <a href="item.php?type=employee&id='.$id.'">'.$_POST['firstName'].' '.$_POST['lastName'].'</a> was added.<br>
						Save the username and temporary password below to give to the employee.<br><br>
						<b>Username: </b>'.$alphaUser.$numUser.'<br>
						<b>Temporary Password: </b>'.$password.'
					</div>';
			}
		}
		
		echo json_encode($return);
	}
	
	/*
		Function: edit
		Inputs: type, id
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'edit') {
		$return = ['status' => 'success'];
		
		//get current values
		$sth = $dbh->prepare(
			'SELECT *
			FROM '.$TYPES[$_POST['type']]['pluralName'].'
			WHERE '.$TYPES[$_POST['type']]['idName'].' = :id');
		$sth->execute([':id' => $_POST['id']]);
		$item = $sth->fetch();
		
		$return['html'] = '<h1>Edit '.$_POST['type'].'</h1>';
		foreach ($TYPES[$_POST['type']]['formData'] as $key => $section) {
			$return['html'] .= '<section><h2>'.$key.'</h2><div class="sectionData">';
			foreach ($section as $column) {
				$return['html'] .= '<ul>';
				foreach ($column as $field) {
					$formalName = $TYPES[$_POST['type']]['fields'][$field]['formalName'];
					$attributes = $TYPES[$_POST['type']]['fields'][$field]['verifyData'];
					if ($attributes[1] == 'int' || $attributes[1] == 'str' || $attributes[1] == 'dec') {
						$return['html'] .= '<li><label for="'.$field.'">'.$formalName.'</label>';
						$return['html'] .= '<input type="text" name="'.$field.'" autocomplete="off" value="'.$item[$field].'"></li>';
					}
					elseif ($attributes[1] == 'id' || $attributes[1] == 'opt') {
						$empty = ($attributes[0] == 0 || ($field == 'managerID' && $item[$field] == 0)) ? true : false; //allow empty field if it's not required, or if the item is the top employee
						$return['html'] .= '<li><label for="'.$field.'">'.$formalName.'</label><select name="'.$field.'">';
						$return['html'] .= ($attributes[1] == 'id') ? generateTypeOptions($attributes[2], $empty, $item[$field]) : generateFieldOptions($_POST['type'], $field, $empty, $item[$field]);
						$return['html'] .= '</select></li>';
					}
				}
				$return['html'] .= '</ul>';
			}
			$return['html'] .= '</div></section>';
		}
		$return['html'] .= '<div id="btnSpacer"><button id="editBtn">Edit</button></div>';
		
		echo json_encode($return);
	}
	
	/*
		Function: editMany
		Inputs: type
		Outputs: status (success / fail), html
	*/
	if ($_POST['action'] == 'editMany') {
		$return = ['status' => 'success'];
		
		$return['html'] = '<h1>Edit '.$TYPES[$_POST['type']]['pluralName'].'</h1>';
		foreach ($TYPES[$_POST['type']]['formData'] as $key => $section) {
			$return['html'] .= '<section><h2>'.$key.'</h2><div class="sectionData">';
			foreach ($section as $column) {
				$return['html'] .= '<ul>';
				foreach ($column as $field) {
					$formalName = $TYPES[$_POST['type']]['fields'][$field]['formalName'];
					$attributes = $TYPES[$_POST['type']]['fields'][$field]['verifyData'];
					if ($attributes[1] == 'int' || $attributes[1] == 'str' || $attributes[1] == 'dec') {
						$return['html'] .= '<li><input type="checkbox">';
						$return['html'] .= '<label for="'.$field.'">'.$formalName.'</label>';
						$return['html'] .= '<input type="text" name="'.$field.'" autocomplete="off" disabled></li>';
					}
					elseif ($attributes[1] == 'id' || $attributes[1] == 'opt') {
						$return['html'] .= '<li><input type="checkbox">';
						$return['html'] .= '<label for="'.$field.'">'.$formalName.'</label><select name="'.$field.'" disabled>';
						$return['html'] .= ($attributes[1] == 'id') ? generateTypeOptions($attributes[2], true) : generateFieldOptions($_POST['type'], $field, true);
						$return['html'] .= '</select></li>';
					}
				}
				$return['html'] .= '</ul>';
			}
			$return['html'] .= '</div></section>';
		}
		$return['html'] .= '<div id="btnSpacer"><button id="editBtn">Edit</button></div>';
		
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
		//manual check for managerID (ONLY for editMany (I think only for editMany because otherwise you can't edit the CEO)) because it's required, but not checked in verifyData
		if (array_key_exists('managerID', $data) && $data['managerID'] == '' && count(explode(',', $_POST['id'])) > 1) {
			$return['status'] = 'fail';
			$return['managerID'] = 'Required';
		}
		
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
						//TODO: fix the if test below to not add managerID when editing the CEO
						if ($row[$key] != $value) {
							$tempData[$key] = $value;
						}
					}
					if (count($tempData) > 0) {
						addChange($_POST['type'], $id, $_SESSION['employeeID'], json_encode($tempData));
					}
					//if we're changing an employee's manager, remove the current links and then add the new ones
					if (isset($tempData['managerID'])) {
						updateHierarchy('delete', null, $id);
						updateHierarchy('add', $tempData['managerID'], $id);
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
				//if we're deleting an employee, remove them from the hierarchy
				if ($_POST['type'] == 'employee') {
					updateHierarchy('delete', null, $id);
				}
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