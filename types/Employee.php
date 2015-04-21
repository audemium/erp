<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Employee extends GenericItem {
		public function printItemBody($id) {
			global $dbh;
			global $TYPES;
			global $SETTINGS;
			
			$requestTimeOff = ($id == $_SESSION['employeeID']) ? 'addEnabled' : 'addDisabled';
			
			$sth = $dbh->prepare(
				'SELECT SUM(endTime - startTime) AS used
				FROM vacationRequests
				WHERE employeeID = :employeeID AND status = "A"');
			$sth->execute([':employeeID' => $id]);
			$row = $sth->fetch();
			$hoursUsed = $row['used'] / 3600;
			
			$sth = $dbh->prepare(
				'SELECT vacationTotal
				FROM employees
				WHERE employeeID = :employeeID');
			$sth->execute([':employeeID' => $id]);
			$row = $sth->fetch();
			$hoursAvailable = $row['vacationTotal'] - $hoursUsed;
			
			$return = '<section>
				<h2>Time Tracking</h2>
				<div class="sectionData">
					<dl>
						<dt>Hours Used</dt>
						<dd>'.$hoursUsed.'</dd>
					</dl>
					<dl>
						<dt>Hours Available</dt>
						<dd>'.$hoursAvailable.'</dd>
					</dl>
					<br><br>
					<div class="customAddLink" id="customAdd1"><a class="controlAdd '.$requestTimeOff.'" href="#">Request Time Off</a></div>
					<table class="customTable dataTable" style="width:100%;">
						<thead style="font-weight:bold;">
							<tr>
								<th>Date</th>
								<th>Start Time</th>
								<th>End Time</th>
								<th>Submitted</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>';
							$sth = $dbh->prepare(
								'SELECT *
								FROM vacationRequests
								WHERE employeeID = :employeeID');
							$sth->execute([':employeeID' => $id]);
							while ($row = $sth->fetch()) {
								switch ($row['status']) {
									case 'P':
										$status = 'Pending';
										break;
									case 'A':
										$status = 'Approved';
										break;
									case 'D':
										$status = 'Denied';
										break;
								}
								$return .= '<tr><td data-sort="'.$row['startTime'].'">'.date($SETTINGS['dateFormat'], $row['startTime']).'</td>';
								$return .= '<td>'.date($SETTINGS['timeFormat'], $row['startTime']).'</td>';
								$return .= '<td>'.date($SETTINGS['timeFormat'], $row['endTime']).'</td>';
								$return .= '<td>'.date($SETTINGS['dateTimeFormat'], $row['submitTime']).'</td>';
								$return .= '<td>'.$status.'</td></tr>';
							}
						$return .= '</tbody>
					</table>
				</div>
			</section>
			<section>
				<h2>Paystubs</h2>
				<div class="sectionData">
				</div>
			</section>
			<section>
				<h2>Changes Made</h2>
				<div class="sectionData">
					<table class="dataTable stripe row-border" id="changesMadeTable"> 
						<thead>
							<tr>
								<th class="dateTimeHeader">Time</th>
								<th>Item</th>
								<th>Type</th>
								<th>Changes</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="4" class="tableFooter">
									<a href="#">View All</a>
								</td>
							</tr>
						</tfoot>
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
		
		public function customAjax($id, $data) {
			global $dbh;
			global $TYPES;
			$return = ['status' => 'success'];
			
			if ($data['subAction'] == 'add') {
				//add subAction
				if ($data['date'] == '') {
					$return['status'] = 'fail';
					$return['date'] = 'Required';
				}
				if ($data['startTime'] == '') {
					$return['status'] = 'fail';
					$return['startTime'] = 'Required';
				}
				if ($data['endTime'] == '') {
					$return['status'] = 'fail';
					$return['endTime'] = 'Required';
				}
				$start = strtotime($data['date'].' '.$data['startTime']);
				$end = strtotime($data['date'].' '.$data['endTime']);
				
				if ($return['status'] != 'fail') {
					if ($start === false || $end === false) {
						$return['status'] = 'fail';
						$return['date'] = 'Unrecognized date/time format.  Please change settings.';
					}
					else {
						if ($end < $start) {
							$return['status'] = 'fail';
							$return['endTime'] = 'End Time must be after Start Time.';
						}
						
						if ($return['status'] != 'fail') {
							$sth = $dbh->prepare(
								'INSERT INTO vacationRequests (vacationRequestID, employeeID, submitTime, startTime, endTime, status)
								VALUES(null, :employeeID, UNIX_TIMESTAMP(), :startTime, :endTime, "P")');
							$sth->execute([':employeeID' => $id, ':startTime' => $start, ':endTime' => $end]);
							//TODO: should a call to addChange be here? or is it already logged enough?
						}
					}
				}
			}
			elseif ($data['subAction'] == 'changesMadeHistory') {
				$return['html'] = '';
				$limit = ($_POST['limit'] == -1) ? 100000 : $_POST['limit'];
				
				$sth = $dbh->prepare(
					'SELECT *
					FROM changes
					WHERE employeeID = :employeeID
					ORDER BY changeTime DESC
					LIMIT :limit');
				$sth->execute([':employeeID' => $id, ':limit' => $limit]);
				while ($row = $sth->fetch()) {
					$return['html'] .= '<tr><td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
					$return['html'] .= '<td>'.getLinkedName($row['type'], $row['id']).'</td>';
					$return['html'] .= '<td>'.$TYPES[$row['type']]['formalName'].'</td>';
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
			}
			
			return $return;
		}
		
		public function printPopups() {
			$return = 
			'<div class="popup" id="customPopup1">
				<div>
					<a class="close" title="Close">X</a>
					<div>
						<h1>Request Time Off</h1>
						<section>
							<h2></h2>
							<div class="sectionData">
								<ul>
									<li>
										<label for="startTime">Start Time</label>
										<input type="text" autocomplete="off" name="startTime">
									</li>
									<li>
										<label for="date">Date</label>
										<input type="text" autocomplete="off" name="date">
									</li>
								</ul>
								<ul>
									<li>
										<label for="endTime">End Time</label>
										<input type="text" autocomplete="off" name="endTime">
									</li>
								</ul>
							</div>
						</section>
						<div id="btnSpacer">
							<button id="customBtn1">Request</button>
						</div>
					</div>
				</div>
			</div>';
			return $return;
		}
	}
?>