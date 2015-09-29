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
			if (isset($TYPES[$type]['fields'][$field]) && $TYPES[$type]['fields'][$field]['typeData'][0] == 'id') {
				$parsed[$field] = (!is_null($value)) ? getLinkedName($TYPES[$type]['fields'][$field]['typeData'][1], $value) : '';
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
			if (!isset($fields[$key])) {
				unset($data[$key]);
			}
			else {
				if ($fields[$key]['typeData'][0] == 'int' || $fields[$key]['typeData'][0] == 'dec') {
					//strip out thousands separator
					$data[$key] = str_replace($SETTINGS['thousandsSeparator'], '', $data[$key]);	
					//make sure decimal is a period
					$data[$key] = str_replace($SETTINGS['decimalFormat'], '.', $data[$key]);	
				}
			}
		}
		
		return $data;
	}
	
	/* verifyData */
	function verifyData($type, $subType, $action, $data) {
		global $dbh;
		global $SETTINGS;
		global $TYPES;
		$return = ['status' => 'success'];
		
		$fields = ($subType === null) ? $TYPES[$type]['fields'] : $TYPES[$type]['subTypes'][$subType]['fields'];
		foreach ($fields as $fieldName => $fieldInfo) {
			//determine if the field is required
			if (is_array($fieldInfo['requiredData'][$action]) === true) {
				$required = ($data[$fieldInfo['requiredData'][$action][0]] == $fieldInfo['requiredData'][$action][1]) ? true : false;
			}
			else {
				$required = ($fieldInfo['requiredData'][$action] == 1) ? true : false;
			}
			
			if ($required == true && $data[$fieldName] == '' && $fieldName != 'managerID') {
				//UI will only let someone choose a blank manager if it's allowed, but technically this won't verify it
				$return['status'] = 'fail';
				$return[$fieldName] = 'Required';
			}
			elseif ($data[$fieldName] != '') {
				//this section gets hit if there is some value, regardless of $required
				if ($fieldInfo['typeData'][0] == 'str') {
					if (strlen($data[$fieldName]) > $fieldInfo['typeData'][1]) {
						$return['status'] = 'fail';
						$return[$fieldName] = 'Must be '.$fieldInfo['typeData'][1].' or fewer characters';
					}
				}
				if ($fieldInfo['typeData'][0] == 'int') {
					if (filter_var($data[$fieldName], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => $fieldInfo['typeData'][1]]]) === false) {
						$return['status'] = 'fail';
						$return[$fieldName] = 'Must be a positive integer';
					}
				}
				if ($fieldInfo['typeData'][0] == 'id') {
					$sth = $dbh->prepare(
						'SELECT  '.$TYPES[$fieldInfo['typeData'][1]]['idName'].'
						FROM '.$TYPES[$fieldInfo['typeData'][1]]['pluralName'].'
						WHERE '.$TYPES[$fieldInfo['typeData'][1]]['idName'].' = :value AND active = 1');
					$sth->execute([':value' => $data[$fieldName]]);
					$result = $sth->fetchAll();
					if (count($result) != 1) {
						$return['status'] = 'fail';
						$return[$fieldName] = 'Selected item does not exist or is inactive';
					}
				}
				if ($fieldInfo['typeData'][0] == 'dec') {
					//test with salary: 10(pass) 10,000(pass) 10000(pass) 10.10(pass) 10.1234(fail) 123456789012.12(fail) 12345678901.1(fail)
					if (filter_var($data[$fieldName], FILTER_VALIDATE_FLOAT) === false) {
						$return['status'] = 'fail';
						$return[$fieldName] = 'Must be a decimal';
					}
					else {
						$temp = strrpos($data[$fieldName], '.');
						if ($temp === false) {
							$data[$fieldName] .= '.';
							$temp = strrpos($data[$fieldName], '.');
						}
						$length = strlen(substr($data[$fieldName], $temp + 1));
						if ($length < $fieldInfo['typeData'][1][1]) {
							$data[$fieldName] .= str_repeat('0', $fieldInfo['typeData'][1][1] - $length);
							$length = strlen(substr($data[$fieldName], $temp + 1));
						}
						if ($length > $fieldInfo['typeData'][1][1]) {
							$return['status'] = 'fail';
							$return[$fieldName] = 'Must have '.$fieldInfo['typeData'][1][1].' or fewer digits after the decimal';
						}
						else {
							if (strlen(str_replace([',', '.'], '', $data[$fieldName])) > $fieldInfo['typeData'][1][0]) {
								$return['status'] = 'fail';
								$return[$fieldName] = 'Must have '.$fieldInfo['typeData'][1][0].' or fewer digits';
							}
						}
					}
				}
				if ($fieldInfo['typeData'][0] == 'opt') {
					if (!in_array($data[$fieldName], $fieldInfo['typeData'][1])) {
						$return['status'] = 'fail';
						$return[$fieldName] = 'Invalid value';
					}
				}
				if ($fieldInfo['typeData'][0] == 'email') {
					if (filter_var($data[$fieldName], FILTER_VALIDATE_EMAIL) === false) {
						$return['status'] = 'fail';
						$return[$fieldName] = 'Must be an email address';
					}
				}
				if ($fieldInfo['typeData'][0] == 'date') {
					$date = DateTime::createFromFormat($SETTINGS['dateFormat'].'|', $data[$fieldName]);
					if ($date === false) {
						$return['status'] = 'fail';
						$return[$fieldName] = 'Unrecognized date format';
					}
					else {
						//if this is the end date, loop through until you find the start date, then if end is less than start, fail it
						if ($fieldInfo['typeData'][1] == 'end') {
							foreach ($fields as $tempFieldName => $tempFieldInfo) {
								if ($tempFieldInfo['typeData'][0] == 'date' && $tempFieldInfo['typeData'][1] == 'start') {
									$tempDate = DateTime::createFromFormat($SETTINGS['dateFormat'].'|', $data[$tempFieldName]);
									if ($tempDate !== false) {
										if ($date->getTimestamp() < $tempDate->getTimestamp()) {
											$return['status'] = 'fail';
											$return[$fieldName] = 'End Date must be after Start Date';
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
		$options = $TYPES[$type]['fields'][$field]['typeData'][1];
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