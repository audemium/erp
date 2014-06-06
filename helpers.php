<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	/* formatCurrency */
	function formatCurrency($amount) {
		return '$'.number_format($amount, 2);
	}
	
	/* formatDateTime */
	function formatDateTime($unixTS) {
		return date('d-M-Y H:i', $unixTS);
	}
	
	/* getName */
	function getName($type, $id) {
		global $dbh;
		global $TYPES;
		
		if ($type == 'employee') {
			$sth = $dbh->prepare(
				'SELECT firstName, lastName
				FROM employees
				WHERE employeeID = :id');
		}
		else {
			$sth = $dbh->prepare(
				'SELECT name
				FROM '.$TYPES[$type]['pluralName'].'
				WHERE '.$TYPES[$type]['idName'].' = :id');
		}
		$sth->execute([':id' => $id]);
		$row = $sth->fetch();
		
		return ($type == 'employee') ? $row['firstName'].' '.$row['lastName'] : $row['name'];
	}
	
	/* addChange */
	function addChange($type, $id, $employeeID, $data) {
		global $dbh;
	
		$sth = $dbh->prepare(
			'INSERT INTO changes (changeID, type, id, employeeID, changeTime, data)
			VALUES(null, :type, :id, :employeeID, UNIX_TIMESTAMP(), :data)');
		$sth->execute([':type' => $type, ':id' => $id, ':employeeID' => $employeeID, ':data' => $data]);
	}
	
	/* parseValue */
	function parseValue($type, $field, $value) {
		global $TYPES;
		
		if ($type == 'employee') {
			if ($TYPES[$type]['fields'][$field]['verifyData'][1] == 'id') {
				$value = getName($TYPES[$type]['fields'][$field]['verifyData'][2], $value);
			}
			switch ($field) {
				case 'payType':
					$parsed = ($value == 'S') ? 'Salary' : 'Hourly';
					break;
				case 'payAmount':
					$parsed = formatCurrency($value);
					break;
				default:
					$parsed = $value;
			}
		}
		else {
			$parsed = $value;
		}
		
		return $parsed;
	}
	
	/* verifyData */
	function verifyData($type, $data) {
		global $dbh;
		global $TYPES;
		$return = ['status' => 'success'];
		
		foreach ($data as $key => $value) {
			if (isset($TYPES[$type]['fields'][$key]['verifyData']) != true) {
				$return['status'] = 'fail';
				$return[$key] = 'Could not verify data';
			}
			else {
				$attributes = $TYPES[$type]['fields'][$key]['verifyData'];
				if ($attributes[0] == 1 && $value == '') {
					$return['status'] = 'fail';
					$return[$key] = 'Required';
				}
				else {
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
							'SELECT  '.$TYPES[$type]['idName'].'
							FROM '.$TYPES[$type]['pluralName'].'
							WHERE '.$TYPES[$type]['idName'].' = :value AND active = 1');
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
				}
			}
		}
		
		return $return;
	}
	
	/* generateTypeOptions */
	function generateTypeOptions($type, $empty, $value = '') {
		global $dbh;
		global $TYPES;
		
		$return = ($empty == true)? '<option value=""></option>' : '';
		$sth = $dbh->prepare(
			'SELECT '.$TYPES[$type]['idName'].', name
			FROM '.$TYPES[$type]['pluralName'].'
			WHERE active = 1
			ORDER BY name');
		$sth->execute();
		while ($row = $sth->fetch()) {
			$selected = ($row[$TYPES[$type]['idName']] == $value) ? ' selected' : '';
			$return .= '<option value="'.$row[$TYPES[$type]['idName']].'"'.$selected.'>'.$row['name'].'</option>';
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
?>