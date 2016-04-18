<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Employee extends GenericItem {
		public function printItemBody($id) {
			global $dbh;
			global $SETTINGS;
			
			$sth = $dbh->prepare(
				'SELECT SUM(vacationHours) AS vacationHours
				FROM timesheetHours, timesheets
				WHERE employeeID = :employeeID AND timesheets.timesheetID = timesheetHours.timesheetID');
			$sth->execute([':employeeID' => $id]);
			$row = $sth->fetch();
			$hoursUsed = $row['vacationHours'] + 0;
			
			$sth = $dbh->prepare(
				'SELECT vacationTotal
				FROM employees
				WHERE employeeID = :employeeID');
			$sth->execute([':employeeID' => $id]);
			$row = $sth->fetch();
			$hoursAvailable = $row['vacationTotal'] - $hoursUsed;
			
			//Time Tracking section
			$return = '<section>
				<h2>Time Tracking</h2>
				<div class="sectionData">
					<dl>
						<dt>Vacation Used</dt>
						<dd>'.$hoursUsed.' hours</dd>
					</dl>
					<dl>
						<dt>Vacation Available</dt>
						<dd>'.$hoursAvailable.' hours</dd>
					</dl>
					<br><br>
					<table class="dataTable  stripe row-border" style="width:100%;">
						<thead>
							<tr>
								<th class="textLeft">Timesheet</th>
								<th class="textLeft">Period Start</th>
								<th class="textLeft">Period End</th>
								<th class="textLeft">Status</th>
							</tr>
						</thead>
						<tbody>';
							$sth = $dbh->prepare(
								'SELECT timesheetID, firstDate, lastDate, status
								FROM timesheets
								WHERE employeeID = :employeeID');
							$sth->execute([':employeeID' => $id]);
							while ($row = $sth->fetch()) {
								$return .= '<tr><td><a href="#" class="customViewTimesheet" id="'.$row['timesheetID'].'">Timesheet #'.$row['timesheetID'].'</a></td>';
								$return .= '<td data-sort="'.$row['firstDate'].'">'.date($SETTINGS['dateFormat'], $row['firstDate']).'</td>';
								$return .= '<td data-sort="'.$row['lastDate'].'">'.date($SETTINGS['dateFormat'], $row['lastDate']).'</td>';
								switch ($row['status']) {
									case 'E':
										$temp = 'Edit';
										break;
									case 'P':
										$temp = 'Pending Approval';
										break;
									case 'A':
										$temp = 'Approved';
										break;
									case 'D':
										$temp = 'Not Approved';
										break;
									default:
										$temp = '<a href="#" class="customViewPaystub" id="'.$row['status'].'">Paystub #'.$row['status'].'</a>';
								}
								$return .= '<td>'.$temp.'</td></tr>';
							}
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			//Paystubs section
			$return .= '<section>
				<h2>Paystubs</h2>
				<div class="sectionData">
					<table class="dataTable  stripe row-border" style="width:100%;">
						<thead>
							<tr>
								<th class="textLeft">Paystub</th>
								<th class="textLeft">Date</th>
								<th class="textLeft">Related Timesheet</th>
								<th class="textLeft">Gross Pay</th>
							</tr>
						</thead>
						<tbody>';
							$sth = $dbh->prepare(
								'SELECT paystubID, date, timesheetID, grossPay
								FROM paystubs
								WHERE employeeID = :employeeID');
							$sth->execute([':employeeID' => $id]);
							while ($row = $sth->fetch()) {
								$return .= '<tr><td><a href="#" class="customViewPaystub" id="'.$row['paystubID'].'">Paystub #'.$row['paystubID'].'</a></td>';
								$return .= '<td data-sort="'.$row['date'].'">'.date($SETTINGS['dateFormat'], $row['date']).'</td>';
								$return .= '<td><a href="#" class="customViewTimesheet" id="'.$row['timesheetID'].'">Timesheet #'.$row['timesheetID'].'</a></td>';
								$return .= '<td>'.formatCurrency($row['grossPay'], true).'</td></tr>';
							}
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			//Changes Made section
			$return .= '<section>
				<h2>Changes Made</h2>
				<div class="sectionData">
					<table class="dataTable stripe row-border" id="changesMadeTable"> 
						<thead>
							<tr>
								<th class="dateTimeHeader textLeft">Time</th>
								<th class="textLeft">Item</th>
								<th class="textLeft">Type</th>
								<th class="textLeft">Changes</th>
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
		
		public function parseValue($type, $item) {
			foreach ($item as $field => $value) {
				switch ($field) {
					case 'payType':
						$parsed[$field] = ($value == 'S') ? 'Salary' : 'Hourly';
						break;
					case 'payAmount':
						$parsed[$field] = formatCurrency($value, true);
						break;
					default:
						$parsed[$field] = $value;
				}
				if (isset($parsed[$field]) && $field != 'payAmount') {
					$parsed[$field] = htmlspecialchars($parsed[$field], ENT_QUOTES | ENT_HTML5, 'UTF-8');
				}
			}
			
			return $parsed;
		}
		
		public function parseSubTypeValue($subType, $action, $item, $format) {
			$dataStr = '';
			$parsed = [];
			
			foreach ($item as $field => $value) {
				$parsed[$field] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			}
			
			if ($format == 'str') {
				//if we want a string format
				if ($action == 'A') {
					$dataStr = 'Added ';
				}
				elseif ($action == 'D') {
					$dataStr = 'Deleted ';
				}
				
				$dataStr .= 'attachment '.$parsed['name'].'.'.$parsed['extension'].'.';
				
				return $dataStr;
			}
			else {
				//otherwise return the array
				return $parsed;
			}
		}
		
		public function generateTypeOptions($type) {
			global $dbh;
			$return = [];
			
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
			global $SETTINGS;
			$return = ['status' => 'success'];
			
			if ($data['subAction'] == 'view') {
				//view subAction
				if ($data['subType'] == 'paystub') {
					$sth = $dbh->prepare(
						'SELECT date, grossPay, firstDate, lastDate, payType, payAmount
						FROM paystubs, timesheets
						WHERE paystubID = :paystubID AND paystubs.timesheetID = timesheets.timesheetID');
					$sth->execute([':paystubID' => $data['subID']]);
					$row = $sth->fetch();
					$return['date'] = formatDate($row['date']);
					$return['grossPay'] = formatCurrency($row['grossPay'], true);
					$return['startDate'] = formatDate($row['firstDate']);
					$return['endDate'] = formatDate($row['lastDate']);
					$parsed = self::parseValue('employee', ['payType' => $row['payType'], 'payAmount' => $row['payAmount']]);
					$return['payType'] = $parsed['payType'];
					$return['payAmount'] = $parsed['payAmount'];
					
					$sth = $dbh->prepare(
						'SELECT SUM(regularHours) AS regularHours, SUM(overtimeHours) AS overtimeHours, SUM(holidayHours) AS holidayHours, SUM(vacationHours) AS vacationHours
						FROM timesheetHours, paystubs
						WHERE paystubID = :paystubID AND paystubs.timesheetID = timesheetHours.timesheetID');
					$sth->execute([':paystubID' => $data['subID']]);
					$row = $sth->fetch();
					$return['regularHours'] = formatNumber($row['regularHours'] + 0).' hours';
					$return['overtimeHours'] = formatNumber($row['overtimeHours'] + 0).' hours';
					$return['holidayHours'] = formatNumber($row['holidayHours'] + 0).' hours';
					$return['vacationHours'] = formatNumber($row['vacationHours'] + 0).' hours';
				}
				elseif ($data['subType'] == 'timesheet') {
					$sth = $dbh->prepare(
						'SELECT firstDate, lastDate, status
						FROM timesheets
						WHERE timesheetID = :timesheetID');
					$sth->execute([':timesheetID' => $data['subID']]);
					$row = $sth->fetch();
					$firstDate = $row['firstDate'];
					$lastDate = $row['lastDate'];
					$return['timesheetStatus'] = $row['status'];
					
					//this will fill in days with no hours automatically, no row in timesheetHours is needed
					$sth = $dbh->prepare(
						'SELECT date, regularHours, overtimeHours, holidayHours, vacationHours
						FROM timesheetHours
						WHERE timesheetID = :timesheetID');
					$sth->execute([':timesheetID' => $data['subID']]);
					while ($row = $sth->fetch()) {
						$hours[$row['date']] = [$row['regularHours'], $row['overtimeHours'], $row['holidayHours'], $row['vacationHours']];
					}
					while($firstDate <= $lastDate) {
						$table[$firstDate] = (isset($hours[$firstDate])) ? $hours[$firstDate] : [0, 0, 0, 0];
						$firstDate += 86400;
					}
					
					$typeArr = ['r', 'o', 'h', 'v'];
					foreach ($table as $day => $dayArr) {
						if ($return['timesheetStatus'] == 'E' || $return['timesheetStatus'] == 'P') {
							$return['html'] .= '<tr><td>'.formatDate($day).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.date('l', $day).'</td>';
							for ($i = 0; $i < 4; $i++) {
								$temp = (empty($dayArr[$i] + 0)) ? '' : formatNumber($dayArr[$i] + 0);
								$return['html'] .= '<td><input type="text" name="'.$typeArr[$i].$day.'" autocomplete="off" value="'.$temp.'"></td>';
							}
							$return['html'] .= '</tr>';
						}
						else {
							$return['html'] .= '<tr><td>'.formatDate($day).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.date('l', $day).'</td>';
							for ($i = 0; $i < 4; $i++) {
								$temp = (empty($dayArr[$i] + 0)) ? '' : formatNumber($dayArr[$i] + 0);
								$return['html'] .= '<td>'.$temp.'</td>';
							}
							$return['html'] .= '</tr>';
						}
					}
				}
			}
			elseif ($data['subAction'] == 'edit') {
				if ($data['subType'] == 'timesheet') {
					$subID = $data['subID'];
					unset($data['subID']);
					unset($data['subAction']);
					unset($data['subType']);
					
					//make sure this timesheet has the correct status, get dates for later
					$sth = $dbh->prepare(
						'SELECT employeeID, firstDate, lastDate, status
						FROM timesheets
						WHERE timesheetID = :timesheetID');
					$sth->execute([':timesheetID' => $subID]);
					$row = $sth->fetch();
					$employeeID = $row['employeeID'];
					$firstDate = $row['firstDate'];
					$lastDate = $row['lastDate'];
					$timesheetStatus = $row['status'];
					
					if ($timesheetStatus == 'E' || $timesheetStatus == 'P') {
						foreach ($data as $key => $value) {
							$hourType = substr($key, 0, 1);
							$ts = substr($key, 1);
							$value = ($value == '') ? 0 : $value;
							//timesheet is not (and as far as I can think, cannot due to the many variable fields) be a subtype, so we need to manually clean and verify the data
							$value = str_replace($SETTINGS['thousandsSeparator'], '', $value);
							$value = str_replace($SETTINGS['decimalFormat'], '.', $value);
							if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
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
								if ($length < 2) {
									$value .= str_repeat('0', 2 - $length);
									$length = strlen(substr($value, $temp + 1));
								}
								if ($length > 2) {
									$return['status'] = 'fail';
									$return[$key] = 'Must have 2 or fewer digits after the decimal';
								}
								else {
									if ($value < 0 || $value > 24) {
										$return['status'] = 'fail';
										$return[$key] = 'Must be between 0 and 24';
									}
								}
							}

							$typeArr = ['r', 'o', 'h', 'v'];
							$i = array_search($hourType, $typeArr);
							if ($i === false) {
								//if we don't get the right hour type, fail with no error because this would never happen normally
								$return['status'] = 'fail';
							}
							else {
								$newHours[$ts][$i] = $value;
							}
						}
					}
					else {
						$return['status'] = 'fail';
					}
					
					//get old hours and then do the update
					if ($return['status'] != 'fail') {
						$sth = $dbh->prepare(
							'SELECT date, regularHours, overtimeHours, holidayHours, vacationHours
							FROM timesheetHours
							WHERE timesheetID = :timesheetID');
						$sth->execute([':timesheetID' => $subID]);
						while ($row = $sth->fetch()) {
							$oldHours[$row['date']] = [$row['regularHours'], $row['overtimeHours'], $row['holidayHours'], $row['vacationHours']];
						}
						while($firstDate <= $lastDate) {
							$oldHours[$firstDate] = (isset($oldHours[$firstDate])) ? $oldHours[$firstDate] : [0, 0, 0, 0];
							$firstDate += 86400;
						}
						
						//TODO: check to see if employee has enough vacation time to make the change
						//need to do it in a foreach loop right here because we need the oldHours to detect the change in vacation time and also do it before we start changing the db
						
						//loop through old hours because we know the ts will be right, whereas the ts in new hours could be malicious
						foreach ($oldHours as $key => $value) {
							if ($oldHours[$key][0] != $newHours[$key][0] || $oldHours[$key][1] != $newHours[$key][1] || $oldHours[$key][2] != $newHours[$key][2] || $oldHours[$key][3] != $newHours[$key][3]) {
								if ($oldHours[$key][0] == 0 && $oldHours[$key][1] == 0 && $oldHours[$key][2] == 0 && $oldHours[$key][3] == 0) {
									//no hours -> some hours
									$sth = $dbh->prepare(
										'INSERT INTO timesheetHours (timesheetID, date, regularHours, overtimeHours, holidayHours, vacationHours)
										VALUES(:timesheetID, :date, :regular, :overtime, :holiday, :vacation)');
									$sth->execute([':timesheetID' => $subID, ':date' => $key, ':regular' => $newHours[$key][0], ':overtime' => $newHours[$key][1], ':holiday' => $newHours[$key][2], ':vacation' => $newHours[$key][3]]);
								}
								elseif ($newHours[$key][0] == 0 && $newHours[$key][1] == 0 && $newHours[$key][2] == 0 && $newHours[$key][3] == 0) {
									//some hours -> no hours
									$sth = $dbh->prepare(
										'DELETE FROM timesheetHours
										WHERE timesheetID = :timesheetID AND date = :date');
									$sth->execute([':timesheetID' => $subID, ':date' => $key]);
								}
								else {
									//some hours -> different hours
									$sth = $dbh->prepare(
										'UPDATE timesheetHours
										SET regularHours = :regular, overtimeHours = :overtime, holidayHours = :holiday, vacationHours = :vacation
										WHERE timesheetID = :timesheetID AND date = :date');
									$sth->execute([':regular' => $newHours[$key][0], ':overtime' => $newHours[$key][1], ':holiday' => $newHours[$key][2], ':vacation' => $newHours[$key][3], ':timesheetID' => $subID, ':date' => $key]);
								}
							}
						}
					}
				}
			}
			elseif ($data['subAction'] == 'approve') {
				if ($data['subType'] == 'timesheet') {
					$sth = $dbh->prepare(
						'UPDATE timesheets
						SET status = "A"
						WHERE timesheetID = :timesheetID');
					$sth->execute([':timesheetID' => $data['subID']]);
				}
			}
			elseif ($data['subAction'] == 'changesMadeHistory') {
				$return['html'] = '';
				$limit = ((int)$_POST['limit'] == -1) ? 100000 : (int)$_POST['limit'];  //cast as int because we can't use a placeholder for LIMIT
				
				$sth = $dbh->prepare(
					'SELECT *
					FROM changes
					WHERE employeeID = :employeeID
					ORDER BY changeTime DESC
					LIMIT '.$limit);
					$sth->execute([':employeeID' => $id]);
				while ($row = $sth->fetch()) {
					$parsed = parseHistory($row['type'], [$row]);
					$return['html'] .= '<tr><td data-sort="'.$row['changeTime'].'">'.formatDateTime($row['changeTime']).'</td>';
					$return['html'] .= '<td>'.getLinkedName($row['type'], $row['id']).'</td>';
					$return['html'] .= '<td>'.$TYPES[$row['type']]['formalName'].'</td>';
					$return['html'] .= '<td>'.$parsed[0]['data'].'</td></tr>';
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
										<input type="text" class="timeInput" autocomplete="off" name="startTime">
									</li>
									<li>
										<label for="date">Date</label>
										<input type="text" class="dateInput" autocomplete="off" name="date">
									</li>
								</ul>
								<ul>
									<li>
										<label for="endTime">End Time</label>
										<input type="text" class="timeInput" autocomplete="off" name="endTime">
									</li>
								</ul>
							</div>
						</section>
						<div class="btnSpacer">
							<button id="customBtn1">Request</button>
						</div>
					</div>
				</div>
			</div>
			<div class="popup" id="customPopupPaystub">
				<div>
					<a class="close" title="Close">X</a>
					<div>
						<h1>Paystub #<span id="paystubID"></span></h1>
						<section>
							<h2>Details</h2>
							<div class="sectionData">
								<dl>
									<dt>Date</dt>
									<dd id="date"></dd>
									<dt>Period Start</dt>
									<dd id="startDate"></dd>
									<dt>Period End</dt>
									<dd id="endDate"></dd>
								</dl>
								<dl>
									<dt>Pay Type</dt>
									<dd id="payType"></dd>
									<dt>Pay Amount</dt>
									<dd id="payAmount"></dd>
								</dl>
							</div>
						</section>
						<section>
							<h2>Time Summary</h2>
							<div class="sectionData">
								<dl>
									<dt>Regular</dt>
									<dd id="regularHours"></dd>
									<dt>Overtime</dt>
									<dd id="overtimeHours"></dd>
								</dl>
								<dl>
									<dt>Holiday</dt>
									<dd id="holidayHours"></dd>
									<dt>Vacation</dt>
									<dd id="vacationHours"></dd>
								</dl>
							</div>
						</section>
						<section>
							<h2>Totals</h2>
							<div class="sectionData">
								<dl>
									<dt>Gross Pay</dt>
									<dd id="grossPay"></dd>
								</dl>
								<dl>
									<dt>Net Pay</dt>
									<dd>TBD</dd>
								</dl>
							</div>
						</section>
					</div>
				</div>
			</div>
			<div class="popup" id="customPopupTimesheet">
				<div>
					<a class="close" title="Close">X</a>
					<div>
						<h1>Timesheet #<span id="timesheetID"></span></h1>
						<section>
							<h2></h2>
							<div class="sectionData">
								<table style="width:100%;" id="tableTimesheet" class="customTable">
									<thead>
										<tr>
											<th class="textLeft">Date</th>
											<th class="textLeft">Regular</th>
											<th class="textLeft">Overtime</th>
											<th class="textLeft">Holiday</th>
											<th class="textLeft">Vacation</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</section>
						<div class="btnSpacer">
							<button id="customBtnTimesheetEdit">Edit</button>
							<button id="customBtnTimesheetApprove">Approve</button>
						</div>
					</div>
				</div>
			</div>';
			return $return;
		}
	}
?>