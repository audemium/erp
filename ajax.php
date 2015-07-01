<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	require_once('init.php');
	if (empty($_SESSION['loggedIn']) && $_POST['action'] != 'login') {
		$_SESSION['loginDestination'] = $_SERVER['HTTP_REFERER'];
		http_response_code(401);
		die();
	}
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
		Function: search
		Inputs: 
		Outputs: status (success / fail), results
	*/
	if ($_POST['action'] == 'search') {
		$sql = [];
		$results = [];
		$termSafe = $dbh->quote('%'.$_POST['term'].'%');
		
		foreach ($TYPES as $key => $type) {
			if (isset($type['fields']['firstName']) && isset($type['fields']['lastName'])) {
				$sql[] = 'SELECT "'.$key.'" AS type, '.$type['idName'].' AS id, firstName AS name1, lastName AS name2 FROM '.$type['pluralName'].' WHERE firstName LIKE '.$termSafe.' OR lastName LIKE '.$termSafe.' OR CONCAT(firstName, " ", lastName) LIKE '.$termSafe;
			}
			elseif (isset($type['fields']['name'])) {
				$sql[] = 'SELECT "'.$key.'" AS type, '.$type['idName'].' AS id, name AS name1, "" AS name2 FROM '.$type['pluralName'].' WHERE name LIKE '.$termSafe;
			}
			else {
				$sql[] = 'SELECT "'.$key.'" AS type, '.$type['idName'].' AS id, "" AS name1, "" AS name2 FROM '.$type['pluralName'].' WHERE '.$type['idName'].' = :term2';
			}
		}
		$sqlStr = implode(' UNION ', $sql);
		
		$sth = $dbh->prepare($sqlStr);
		$sth->execute([':term2' => $_POST['term']]);
		while ($row = $sth->fetch()) {
			if ($row['name1'] != '' && $row['name2'] != '') {
				$name = $row['name1'].' '.$row['name2'];
			}
			elseif ($row['name1'] != '') {
				$name = $row['name1'];
			}
			else {
				$name = $TYPES[$row['type']]['formalName'].' #'.$row['id'];
			}
			$results[] = ['type' => $row['type'], 'id' => $row['id'], 'name' => htmlspecialchars($name, ENT_QUOTES | ENT_HTML5, 'UTF-8')];
		}
		
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
					if ($attributes[1] == 'int' || $attributes[1] == 'str' || $attributes[1] == 'dec' || $attributes[1] == 'email') {
						$return['html'] .= '<li><label for="'.$field.'">'.$formalName.'</label>';
						$return['html'] .= '<input type="text" name="'.$field.'" autocomplete="off"></li>';
					}
					elseif ($attributes[1] == 'id' || $attributes[1] == 'opt') {
						$return['html'] .= '<li><label for="'.$field.'">'.$formalName.'</label><select name="'.$field.'">';
						$return['html'] .= ($attributes[1] == 'id') ? generateTypeOptions($attributes[2], true) : generateFieldOptions($_POST['type'], $field, true);
						$return['html'] .= '</select></li>';
					}
					elseif ($attributes[1] == 'disp') {
						$return['html'] .= '<li>&nbsp;</li>';
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
		$data = $_POST;
		unset($data['action']);
		unset($data['type']);
		$return = verifyData($_POST['type'], $data, null);
		//manual check for managerID because it's required, but not checked in verifyData
		if (array_key_exists('managerID', $data) && $data['managerID'] == '') {
			$return['status'] = 'fail';
			$return['managerID'] = 'Required';
		}
		
		if ($return['status'] != 'fail') {
			foreach ($data as $key => $value) {
				$keyArr[] = $key;
				$placeholderArr[] = '?';
				$valArr[] = (empty($value)) ? null : $value;
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
					WHERE username LIKE :username');
				$sth->execute([':username' => $alphaUser.'%']);
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
			else {
				//add the type and id to redirect to the new item
				$return['type'] = $_POST['type'];
				$return['id'] = $id;
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
					if ($attributes[1] == 'int' || $attributes[1] == 'str' || $attributes[1] == 'dec' || $attributes[1] == 'email') {
						$return['html'] .= '<li><label for="'.$field.'">'.$formalName.'</label>';
						$return['html'] .= '<input type="text" name="'.$field.'" autocomplete="off" value="'.$item[$field].'"></li>';
					}
					elseif ($attributes[1] == 'id' || $attributes[1] == 'opt') {
						$empty = ($attributes[0] == 0 || ($field == 'managerID' && $item[$field] == 0)) ? true : false; //allow empty field if it's not required, or if the item is the top employee
						$return['html'] .= '<li><label for="'.$field.'">'.$formalName.'</label><select name="'.$field.'">';
						$return['html'] .= ($attributes[1] == 'id') ? generateTypeOptions($attributes[2], $empty, $item[$field]) : generateFieldOptions($_POST['type'], $field, $empty, $item[$field]);
						$return['html'] .= '</select></li>';
					}
					elseif ($attributes[1] == 'disp') {
						$return['html'] .= '<li>&nbsp;</li>';
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
		$data = $_POST;
		unset($data['action']);
		unset($data['type']);
		unset($data['id']);
		$return = verifyData($_POST['type'], $data, null);
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
						if (($key != 'managerID' && $row[$key] != $value) || ($key == 'managerID' && $value != '' && $row[$key] != $value)) {
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
	
	/*
		Function: history
		Inputs: 
		Outputs: 
	*/
	if ($_POST['action'] == 'history') {
		$return = ['status' => 'success', 'html' => ''];
		
		$limit = ((int)$_POST['limit'] == -1) ? 100000 : (int)$_POST['limit'];  //cast as int because we can't use a placeholder for LIMIT
		$sth = $dbh->prepare(
			'SELECT *
			FROM changes
			WHERE type = :type AND id = :id
			ORDER BY changeTime DESC
			LIMIT '.$limit);
		$sth->execute([':type' => $_POST['type'], ':id' => $_POST['id']]);
		while ($row = $sth->fetch()) {
			$return['html'] .= '<tr><td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
			$return['html'] .= '<td>'.getLinkedName('employee', $row['employeeID']).'</td>';
			$dataStr = '';
			if ($row['data'] == '') {
				$dataStr = 'Item deleted.';
			}
			else {
				$data = json_decode($row['data'], true);
				if (isset($data['type'])) {
					//TODO: fix this subtype stuff, it prints out basic stuff, but don't parse things, and added/removed is sometimes wrong
					if ($data['type'] == 'payment') {
						if (count($data) == 2) {
							$dataStr .= 'Removed Payment #'.$data['id'].'. ';
						}
						else {
							$dataStr .= 'Added Payment #'.$data['id'].'. ';
							$type = $data['type'];
							unset($data['type']);
							unset($data['id']);
							foreach ($data as $key => $value) {
								//$value = parseValue($type, $key, $value);
								$dataStr .= '<b>'.$key.':</b> '.$value.' ';
							}
						}
					}
					else {
						if (count($data) == 2) {
							$dataStr .= 'Removed '.getLinkedName($data['type'], $data['id']).'. ';
						}
						else {
							$dataStr .= 'Added '.getLinkedName($data['type'], $data['id']).'. ';
							$type = $data['type'];
							unset($data['type']);
							unset($data['id']);
							foreach ($data as $key => $value) {
								//$value = parseValue($type, $key, $value);
								$dataStr .= '<b>'.$key.':</b> '.$value.' ';
							}
						}
					}
				}
				else {
					foreach ($data as $key => $value) {
						$value = parseValue($row['type'], $key, $value);
						$dataStr .= '<b>'.$TYPES[$row['type']]['fields'][$key]['formalName'].':</b> '.$value.' ';
					}
				}
			}
			$return['html'] .= '<td>'.$dataStr.'</td></tr>';
		}
		
		echo json_encode($return);
	}
	
	/*
		Function: addAttachment
		Inputs: 
		Outputs: 
	*/
	if ($_POST['action'] == 'addAttachment') {
		$return = ['status' => 'fail'];
		
		if ($_FILES['uploadFile']['error'] == 0) {
			$pos = strrpos($_FILES['uploadFile']['name'], '.');
			$name = ($pos !== false) ? substr($_FILES['uploadFile']['name'], 0, $pos) : $_FILES['uploadFile']['name'];
			$extension = ($pos !== false) ? substr($_FILES['uploadFile']['name'], $pos + 1) : '';
			$file = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($file, $_FILES['uploadFile']['tmp_name']);
			
			$sth = $dbh->prepare(
				'INSERT INTO attachments (type, id, employeeID, uploadTime, name, extension, mime)
				VALUES(:type, :id, :employeeID, UNIX_TIMESTAMP(), :name, :extension, :mime)');
			$sth->execute([':type' => $_POST['type'], ':id' => $_POST['id'], ':employeeID' => $_SESSION['employeeID'], ':name' => $name, ':extension' => $extension, ':mime' => $mime]);
			$attachmentID = $dbh->lastInsertId();
			
			if (move_uploaded_file($_FILES['uploadFile']['tmp_name'], 'attachments/'.$attachmentID)) {
				$return = ['status' => 'success'];
				//TODO: add change line
			}
			else {
				$sth = $dbh->prepare(
					'DELETE FROM attachments
					WHERE attachmentID = :attachmentID');
				$sth->execute([':attachmentID' => $attachmentID]);
				$return['uploadFile'] = 'Server error';
			}
		}
		else {
			$error = 'Server error';
			if ($_FILES['uploadFile']['error'] == 1) {
				$error = 'File exceeds max size ('.ini_get('upload_max_filesize').')';
			}
			$return['uploadFile'] = $error;
		}
		
		echo json_encode($return);
	}
	
	/*
		Function: deleteAttachment
		Inputs: 
		Outputs: 
	*/
	if ($_POST['action'] == 'deleteAttachment') {
		$return = ['status' => 'fail'];
		
		if (unlink('attachments/'.$_POST['subID'])) {
			$sth = $dbh->prepare(
				'DELETE FROM attachments
				WHERE attachmentID = :attachmentID');
			$sth->execute([':attachmentID' => $_POST['subID']]);
			$return = ['status' => 'success'];
			//TODO: add change line
		}
		
		echo json_encode($return);
	}
	
	/*
		Function: customAjax
		Inputs: 
		Outputs: 
	*/
	if ($_POST['action'] == 'customAjax') {
		$data = $_POST;
		unset($data['action']);
		unset($data['type']);
		unset($data['id']);
	
		$factoryItem = Factory::createItem($_POST['type']);
		$return = $factoryItem->customAjax($_POST['id'], $data);
		
		echo json_encode($return);
	}
?>