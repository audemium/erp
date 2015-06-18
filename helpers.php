<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	/* formatCurrency */
	function formatCurrency($amount) {
		global $SETTINGS;
		
		if (empty($amount)) {
			return '';
		}
		elseif ($amount < 0) {
			if ($SETTINGS['negativeCurrencyFormat'] == 0) {
				return '-$'.number_format(abs($amount), 2);
			}
			else {
				return '($'.number_format(abs($amount), 2).')';
			}
		}
		else {
			return '$'.number_format($amount, 2);
		}
	}
	
	/* formatDate */
	function formatDate($unixTS) {
		global $SETTINGS;
		
		return date($SETTINGS['dateFormat'], $unixTS);
	}
	
	/* formatDateTime */
	function formatDateTime($unixTS) {
		global $SETTINGS;
		
		return date($SETTINGS['dateTimeFormat'], $unixTS);
	}
	
	/* getName */
	function getName($type, $id) {
		$factoryItem = Factory::createItem($type);
		return htmlspecialchars($factoryItem->getName($type, $id), ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
	
	/* getLinkedName */
	function getLinkedName($type, $id) {
		return '<a href="item.php?type='.$type.'&id='.$id.'">'.getName($type, $id).'</a>';
	}
	
	/* addChange */
	function addChange($type, $id, $employeeID, $data) {
		global $dbh;
	
		$sth = $dbh->prepare(
			'INSERT INTO changes (type, id, employeeID, changeTime, data)
			VALUES(:type, :id, :employeeID, UNIX_TIMESTAMP(), :data)');
		$sth->execute([':type' => $type, ':id' => $id, ':employeeID' => $employeeID, ':data' => $data]);
	}
	
	/* parseValue */
	function parseValue($type, $field, $value) {
		global $TYPES;
		
		if ($TYPES[$type]['fields'][$field]['verifyData'][1] == 'id') {
			$parsed = (!is_null($value)) ? getLinkedName($TYPES[$type]['fields'][$field]['verifyData'][2], $value) : '';
		}
		else {
			$factoryItem = Factory::createItem($type, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$parsed = htmlspecialchars($factoryItem->parseValue($type, $field, $value));
		}
		
		return $parsed;
	}
	
	/* verifyData */
	function verifyData($type, $data, $fields) {
		global $dbh;
		global $TYPES;
		$return = ['status' => 'success'];
		
		if ($fields === null) {
			$fields = $TYPES[$type]['fields'];
		}
		foreach ($data as $key => $value) {
			if (!isset($fields[$key]['verifyData'])) {
				$return['status'] = 'fail';
				$return[$key] = 'Could not verify data';
			}
			else {
				$attributes = $fields[$key]['verifyData'];
				if ($attributes[0] == 1 && $value == '' && $key != 'managerID') { //UI will only let someone choose a blank manager if it's allowed, but technically this won't verify it
					$return['status'] = 'fail';
					$return[$key] = 'Required';
				}
				elseif ($value != '') {
					if ($attributes[1] == 'str') {
						if (strlen($value) > $attributes[2]) {
							$return['status'] = 'fail';
							$return[$key] = 'Must be '.$attributes[2].' or fewer characters';
						}
					}
					if ($attributes[1] == 'int') {
						if (!filter_var($value, FILTER_VALIDATE_INT, ['min_range' => 0, 'max_range' => $attributes[2]])) {
							$return['status'] = 'fail';
							$return[$key] = 'Must be an integer';
						}
					}
					if ($attributes[1] == 'id') {
						$sth = $dbh->prepare(
							'SELECT  '.$TYPES[$attributes[2]]['idName'].'
							FROM '.$TYPES[$attributes[2]]['pluralName'].'
							WHERE '.$TYPES[$attributes[2]]['idName'].' = :value AND active = 1');
						$sth->execute([':value' => $value]);
						$result = $sth->fetchAll();
						if (count($result) != 1) {
							$return['status'] = 'fail';
							$return[$key] = 'Selected item does not exist or is inactive';
						}
					}
					if ($attributes[1] == 'dec') {
						//test with salary: 10(pass) 10,000(pass) 10000(pass) 10.10(pass) 10.1234(fail) 123456789012.12(fail) 12345678901.1(fail)
						if (!filter_var($value, FILTER_VALIDATE_FLOAT, ['flags' => FILTER_FLAG_ALLOW_THOUSAND])) {
							$return['status'] = 'fail';
							$return[$key] = 'Must be a decimal';
						}
						else {
							$temp = strrpos($value, '.');
							if ($temp === false) {
								$value .= '.';
								$temp = strrpos($value, '.');
							}
							$length = strlen(substr($value, $temp + 1));
							if ($length < $attributes[2][1]) {
								$value .= str_repeat('0', $attributes[2][1] - $length);
								$length = strlen(substr($value, $temp + 1));
							}
							if ($length > $attributes[2][1]) {
								$return['status'] = 'fail';
								$return[$key] = 'Must have '.$attributes[2][1].' or fewer digits after the decimal';
							}
							else {
								if (strlen(str_replace([',', '.'], '', $value)) > $attributes[2][0]) {
									$return['status'] = 'fail';
									$return[$key] = 'Must have '.$attributes[2][0].' or fewer digits';
								}
							}
						}
					}
					if ($attributes[1] == 'opt') {
						if (!in_array($value, $attributes[2])) {
							$return['status'] = 'fail';
							$return[$key] = 'Invalid value';
						}
					}
					if ($attributes[1] == 'email') {
						if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
							$return['status'] = 'fail';
							$return[$key] = 'Must be an email address';
						}
					}
					if ($attributes[1] == 'dateTime') {
						if (strtotime($value) === false) {
							$return['status'] = 'fail';
							$return[$key] = 'Unrecognized date/time format.';
						}
						else {
							//if this is the end date, loop through until you find the start date, then if end is less than start, fail it
							if ($attributes[2] == 'end') {
								foreach ($data as $tempKey => $tempValue) {
									if ($fields[$tempKey]['verifyData'][1] == 'dateTime' && $fields[$tempKey]['verifyData'][2] == 'start') {
										if (strtotime($value) < strtotime($tempValue)) {
											$return['status'] = 'fail';
											$return[$key] = 'End Time must be after Start Time.';
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		return $return;
	}
	
	/* generateTypeOptions */
	function generateTypeOptions($type, $empty, $value = '') {
		$return = ($empty == true)? '<option value=""></option>' : '';
		
		$factoryItem = Factory::createItem($type);
		$optArr = $factoryItem->generateTypeOptions($type);
		foreach ($optArr as $opt) {
			$selected = ($opt[0] == $value) ? ' selected' : '';
			$return .= '<option value="'.$opt[0].'"'.$selected.'>'.htmlspecialchars($opt[1], ENT_QUOTES | ENT_HTML5, 'UTF-8').'</option>';
		}
		
		return $return;
	}
	
	/* generateFieldOptions */
	function generateFieldOptions($type, $field, $empty, $value = '') {
		global $TYPES;
		
		$return = ($empty == true) ? '<option value=""></option>' : '';
		$options = $TYPES[$type]['fields'][$field]['verifyData'][2];
		foreach ($options as $option) {
			$selected = ($option == $value) ? ' selected' : '';
			$return .= '<option value="'.$option.'"'.$selected.'>'.parseValue($type, $field, $option).'</option>';
		}
		
		return $return;
	}
	
	/* updateHierarchy */
	function updateHierarchy($type, $parentID, $childID) {
		global $dbh;
		
		if ($type == 'add') {
			$sth = $dbh->prepare(
				'INSERT INTO hierarchy (parentID, childID, depth)
				VALUES(:childID, :childID, 0)');
			$sth->execute([':childID' => $childID]);
		
			$sth = $dbh->prepare(
				'INSERT INTO hierarchy (parentID, childID, depth)
				SELECT p.parentID, c.childID, p.depth + c.depth + 1
				FROM hierarchy p, hierarchy c
				WHERE p.childID = :parentID AND c.parentID = :childID');
			$sth->execute([':parentID' => $parentID, ':childID' => $childID]);
		}
		elseif ($type == 'delete') {
			$sth = $dbh->prepare(
				'DELETE link FROM hierarchy p, hierarchy link, hierarchy c, hierarchy to_delete
				WHERE p.parentID = link.parentID AND c.childID = link.childID
				AND p.childID = to_delete.parentID AND c.parentID = to_delete.childID
				AND (to_delete.parentID = :childID OR to_delete.childID = :childID)
				AND to_delete.depth < 2');
			$sth->execute([':childID' => $childID]);
		}
	}
	
	///////////////     SESSION FUNCTIONS     ///////////////
	function dbSessionOpen() {
		return true;
	}
	
	function dbSessionClose() {
		return true;
	}

	function dbSessionRead($sessionID) {
		global $dbh;
		
		$sth = $dbh->prepare(
			'SELECT sessionData
			FROM sessions
			WHERE sessionID = :sessionID');
		$sth->execute([':sessionID' => $sessionID]);
		$result = $sth->fetchAll();
		if (count($result) == 1) {
			return $result[0]['sessionData'];
		}
		else {
			return '';
		}
	}

	function dbSessionWrite($sessionID, $sessionData) {
		global $dbh;
		
		$creationTime = time();
		$sth = $dbh->prepare(
			'REPLACE INTO sessions (sessionID, creationTime, sessionData)
			VALUES(:sessionID, :creationTime, :sessionData)');
		$sth->execute([':sessionID' => $sessionID, ':creationTime' => $creationTime, ':sessionData' => $sessionData]);
		return true;
	}

	function dbSessionDestroy($sessionID) {
		global $dbh;
		
		$sth = $dbh->prepare(
			'DELETE FROM sessions
			WHERE sessionID = :sessionID');
		$sth->execute([':sessionID' => $sessionID]);
		return true;
	}

	function dbSessionGc($maxlifetime) {
		global $dbh;
		
		$deletionTime = time() - $maxlifetime;
		$sth = $dbh->prepare(
			'DELETE FROM sessions
			WHERE creationTime < :deletionTime');
		$sth->execute([':deletionTime' => $deletionTime]);
		return true;
	}
?>