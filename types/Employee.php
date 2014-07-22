<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Employee extends Item {
		public function printItemBody($id) {
			global $dbh;
			global $TYPES;
		
			$return = '<section>
				<h2>Changes Made</h2>
				<div class="sectionData">
					<table class="datatable stripe row-border"> 
						<thead>
							<tr>
								<td>Time</td>
								<td>Item</td>
								<td>Type</td>
								<td>Changes</td>
							</tr>
						</thead>
						<tbody>';
							$sth = $dbh->prepare(
								'SELECT *
								FROM changes
								WHERE employeeID = :employeeID');
							$sth->execute([':employeeID' => $_GET['id']]);
							while ($row = $sth->fetch()) {
								$return .= '<tr><td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
								$return .= '<td>'.getLinkedName($row['type'], $row['id']).'</td>';
								$return .= '<td>'.$TYPES[$row['type']]['formalName'].'</td>';
								$dataStr = '';
								if ($row['data'] == '') {
									$dataStr = 'Item deleted.';
								}
								else {
									$data = json_decode($row['data'], true);
									foreach ($data as $key => $value) {
										$value = parseValue($row['type'], $key, $value);
										$dataStr .= '<b>'.$TYPES[$row['type']]['fields'][$key]['formalName'].':</b> '.$value.' ';
									}
								}
								$return .= '<td>'.$dataStr.'</td></tr>';
							}
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			return $return;
		}
		
		public function getName($type, $id) {
			global $dbh;
			
			$sth = $dbh->prepare(
				'SELECT firstName, lastName
				FROM employees
				WHERE employeeID = :id');
			$sth->execute([':id' => $id]);
			$row = $sth->fetch();
			
			return $row['firstName'].' '.$row['lastName'];
		}
		
		public function parseValue($type, $field, $value) {
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
			
			return $parsed;
		}
		
		public function generateTypeOptions($type) {
			global $dbh;
			
			$sth = $dbh->prepare(
				'SELECT employeeID, firstName, lastName, username
				FROM employees
				WHERE active = 1
				ORDER BY firstName, lastName');
			$sth->execute();
			while ($row = $sth->fetch()) {
				$return[] = [$row['employeeID'], $row['firstName'].' '.$row['lastName'].' ('.$row['username'].')'];
			}
			
			return $return;
		}
	}
?>