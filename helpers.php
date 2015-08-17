<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	/* formatNumber */
	function formatNumber($amount) {
		global $SETTINGS;
		
		//determine how many digits after the decimal $amount has, then format it to that number of digits
		$temp = strrchr($amount, '.');
		$digits = ($temp === false) ? 0 : strlen(substr($temp, 1));
		$return = number_format($amount, $digits, $SETTINGS['decimalFormat'], $SETTINGS['thousandsSeparator']);
		
		return $return;
	}
	
	/* formatCurrency */
	function formatCurrency($amount, $displayZero = false) {
		global $SETTINGS;
		
		if (empty($amount) && $displayZero == false) {
			$return = '';
		}
		elseif ($amount < 0) {
			$temp = $SETTINGS['currencySymbol'].number_format(abs($amount), 2, $SETTINGS['decimalFormat'], $SETTINGS['thousandsSeparator']);
			$return = ($SETTINGS['negativeCurrencyFormat'] == 0) ? '-'.$temp : '('.$temp.')';
		}
		else {
			$return = $SETTINGS['currencySymbol'].number_format($amount, 2, $SETTINGS['decimalFormat'], $SETTINGS['thousandsSeparator']);
		}
		
		return $return;
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
	function addChange($type, $id, $employeeID, $action, $data) {
		//$action must be either A (add), E (edit) or D (delete)
		global $dbh;
	
		$sth = $dbh->prepare(
			'INSERT INTO changes (type, id, employeeID, changeTime, action, data)
			VALUES(:type, :id, :employeeID, UNIX_TIMESTAMP(), :action, :data)');
		$sth->execute([':type' => $type, ':id' => $id, ':employeeID' => $employeeID, ':action' => $action, ':data' => $data]);
	}
	
	/* parseValue */
	function parseValue($type, $item) {
		//htmlspecialchars is in the object function
		global $TYPES;
		
		$factoryItem = Factory::createItem($type);
		$parsed = $factoryItem->parseValue($type, $item);
		foreach ($parsed as $field => $value) {
			if (isset($TYPES[$type]['fields'][$field]) && $TYPES[$type]['fields'][$field]['verifyData'][1] == 'id') {
				$parsed[$field] = (!is_null($value)) ? getLinkedName($TYPES[$type]['fields'][$field]['verifyData'][2], $value) : '';
			}
		}
		
		return $parsed;
	}
	
	/* parseSubTypeValue */
	function parseSubTypeValue($type, $subType, $action, $item, $format) {
		//unlike in parseValue, values are linked in $factoryItem->parseSubTypeValue because it sometimes returns a string, and therefore this function couldn't fix up each piece
		//like parseValue, htmlspecialchars is in the object function
		//$action must be A, E or D if $format is 'str', but can be null if $format is 'arr'
		
		$factoryItem = Factory::createItem($type);
		$parsed = $factoryItem->parseSubTypeValue($subType, $action, $item, $format);
		
		return $parsed;
	}
	
	/* parseHistory */
	function parseHistory($type, $changes) {
		global $TYPES;
		
		foreach ($changes as $changeKey => $change) {
			$dataStr = '';
			if ($change['data'] == '') {
				$dataStr = 'Item deleted.';
			}
			else {
				$data = json_decode($change['data'], true);
				if (isset($data['subType'])) {
					$subType = $data['subType'];
					unset($data['subType']);
					$dataStr .= parseSubTypeValue($type, $subType, $change['action'], $data, 'str');
				}
				else {
					$parsed = parseValue($type, $data);
					if ($type == 'discount' && isset($data['hideType'])) {
						//special change code to hide discountType if it was artificially added
						unset($parsed['hideType']);
						unset($parsed['discountType']);
					}
					foreach ($parsed as $key => $value) {
						$dataStr .= '<b>'.$TYPES[$type]['fields'][$key]['formalName'].':</b> '.$value.' ';
					}
				}
			}
			
			$changes[$changeKey]['data'] = $dataStr;
		}
		
		return $changes;
	}
	
	/* clean Data */
	function cleanData($type, $subType, $data) {
		global $SETTINGS;
		global $TYPES;
		
		$fields = ($subType === null) ? $TYPES[$type]['fields'] : $TYPES[$type]['subTypes'][$subType]['fields'];
		foreach ($data as $key => $value) {
			if (isset($fields[$key]['verifyData']) && ($fields[$key]['verifyData'][1] == 'int' || $fields[$key]['verifyData'][1] == 'dec')) {
				//strip out thousands separator
				$data[$key] = str_replace($SETTINGS['thousandsSeparator'], '', $data[$key]);	
				//make sure decimal is a period
				$data[$key] = str_replace($SETTINGS['decimalFormat'], '.', $data[$key]);	
			}
		}
		
		return $data;
	}
	
	/* verifyData */
	function verifyData($type, $subType, $data) {
		global $dbh;
		global $SETTINGS;
		global $TYPES;
		$return = ['status' => 'success'];
		
		$fields = ($subType === null) ? $TYPES[$type]['fields'] : $TYPES[$type]['subTypes'][$subType]['fields'];
		foreach ($data as $key => $value) {
			if (!isset($fields[$key]['verifyData'])) {
				$return['status'] = 'fail';
				$return[$key] = 'Could not verify data';
			}
			else {
				$attributes = $fields[$key]['verifyData'];
				//determine if the field is required
				if (is_array($attributes[0]) === true) {
					$required = ($data[$attributes[0][0]] == $attributes[0][1]) ? true : false;
				}
				else {
					$required = ($attributes[0] == 1) ? true : false;
				}
				
				if ($required == true && $value == '' && $key != 'managerID') {
					//UI will only let someone choose a blank manager if it's allowed, but technically this won't verify it
					$return['status'] = 'fail';
					$return[$key] = 'Required';
				}
				elseif ($value != '') {
					//this section gets hit if there is some value, regardless of $required
					if ($attributes[1] == 'str') {
						if (strlen($value) > $attributes[2]) {
							$return['status'] = 'fail';
							$return[$key] = 'Must be '.$attributes[2].' or fewer characters';
						}
					}
					if ($attributes[1] == 'int') {
						if (!filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => $attributes[2]]])) {
							$return['status'] = 'fail';
							$return[$key] = 'Must be a positive integer';
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
						if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
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
					if ($attributes[1] == 'date') {
						$date = DateTime::createFromFormat($SETTINGS['dateFormat'].'|', $value);
						if ($date === false) {
							$return['status'] = 'fail';
							$return[$key] = 'Unrecognized date format';
						}
						else {
							//if this is the end date, loop through until you find the start date, then if end is less than start, fail it
							if ($attributes[2] == 'end') {
								foreach ($data as $tempKey => $tempValue) {
									if ($fields[$tempKey]['verifyData'][1] == 'date' && $fields[$tempKey]['verifyData'][2] == 'start') {
										$tempDate = DateTime::createFromFormat($SETTINGS['dateFormat'].'|', $tempValue);
										if ($tempDate !== false) {
											if ($date->getTimestamp() < $tempDate->getTimestamp()) {
												$return['status'] = 'fail';
												$return[$key] = 'End Date must be after Start Date';
											}
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
			//slightly risky, as we could send a field that relies on another field to parse, but I'm going to say that's unlikely
			$item[$field] = $option;
			$parsed = parseValue($type, $item);
			$return .= '<option value="'.$option.'"'.$selected.'>'.$parsed[$field].'</option>';
		}
		
		return $return;
	}
	
	/* updateHierarchy */
	function updateHierarchy($action, $parentID, $childID) {
		global $dbh;
		
		if ($action == 'add') {
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
		elseif ($action == 'delete') {
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