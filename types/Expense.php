<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Expense extends GenericItem {
		public function printItemBody($id) {
			global $dbh;
			global $TYPES;
			global $SETTINGS;
			
			//Line Items section
			$subTotal = 0;
			$return = '<section>
				<h2>Line Items</h2>
				<div class="sectionData">
					<div class="customAddLink" id="customAdd2"><a class="controlAdd addEnabled" href="#">Add Line Item</a></div>
					<table class="customTable" style="width:100%;">
						<thead>
							<tr>
								<th>Item</th>
								<th>Location</th>
								<th class="textCenter">Quantity</th>
								<th class="textCenter">Unit Price</th>
								<th class="textRight">Item Total</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>';							
							//get product expenses
							$sth = $dbh->prepare(
								'SELECT expenseProductID, productID, locationID, quantity, unitPrice, recurringID, dayOfMonth, startDate, endDate
								FROM expenses_products
								WHERE expenseID = :expenseID AND parentRecurringID IS NULL');
							$sth->execute([':expenseID' => $id]);
							while ($row = $sth->fetch()) {
								$parsed = self::parseSubTypeValue('product', null, $row, 'arr');
								if (!is_null($row['recurringID'])) {
									$recurringStr = ' (occurs monthly on day '.$parsed['dayOfMonth'].' from '.$parsed['startDate'].' to '.$parsed['endDate'].')';
									$editStr = '';
									$lineAmount = '';
								}
								else {
									$recurringStr = '';
									$editStr = '<a class="controlEdit editEnabled" href="#" data-type="product" data-id="'.$row['expenseProductID'].'" data-unitprice="'.$row['unitPrice'].'" data-quantity="'.($row['quantity'] + 0).'"></a>';
									$lineAmount = $row['quantity'] * $row['unitPrice'];
									$subTotal += $lineAmount;
								}
								$return .= '<tr><td>'.$parsed['productID'].$recurringStr.'</td>';
								$return .= '<td>'.$parsed['locationID'].'</td>';
								$return .= '<td class="textCenter">'.$parsed['quantity'].'</td>';
								$return .= '<td class="textCenter">'.$parsed['unitPrice'].'</td>';
								$return .= '<td class="textRight">'.formatCurrency($lineAmount).'</td>';
								$return .= '<td class="textCenter">'.$editStr.'</td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="product" data-id="'.$row['expenseProductID'].'"></a></td></tr>';
								
								//get child recurring rows if this is a parent recurring row
								if (!is_null($row['recurringID'])) {
									$sth2 = $dbh->prepare(
										'SELECT expenseProductID, productID, locationID, date, quantity, unitPrice
										FROM expenses_products
										WHERE expenseID = :expenseID AND parentRecurringID = :recurringID
										ORDER BY date');
									$sth2->execute([':expenseID' => $id, ':recurringID' => $row['recurringID']]);
									while ($row2 = $sth2->fetch()) {
										$parsed = self::parseSubTypeValue('product', null, $row2, 'arr');
										$lineAmount = $row2['quantity'] * $row2['unitPrice'];
										$subTotal += $lineAmount;
										$return .= '<tr><td style="padding-left: 50px;">'.formatDate($row2['date']).': '.$parsed['productID'].'</td>';
										$return .= '<td>'.$parsed['locationID'].'</td>';
										$return .= '<td class="textCenter">'.$parsed['quantity'].'</td>';
										$return .= '<td class="textCenter">'.$parsed['unitPrice'].'</td>';
										$return .= '<td class="textRight">'.formatCurrency($lineAmount).'</td>';
										$return .= '<td class="textCenter"><a class="controlEdit editEnabled" href="#" data-type="product" data-id="'.$row2['expenseProductID'].'" data-unitprice="'.$row2['unitPrice'].'" data-quantity="'.($row2['quantity'] + 0).'"></a></td>';
										$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="product" data-id="'.$row2['expenseProductID'].'"></a></td></tr>';
									}
								}
							}
							
							//get other expenses
							$sth = $dbh->prepare(
								'SELECT expenseOtherID, name, quantity, unitPrice, recurringID, dayOfMonth, startDate, endDate
								FROM expenseOthers
								WHERE expenseID = :expenseID AND parentRecurringID IS NULL');
							$sth->execute([':expenseID' => $id]);
							while ($row = $sth->fetch()) {
								$parsed = self::parseSubTypeValue('other', null, $row, 'arr');
								if (!is_null($row['recurringID'])) {
									$recurringStr = ' (occurs monthly on day '.$parsed['dayOfMonth'].' from '.$parsed['startDate'].' to '.$parsed['endDate'].')';
									$editStr = '';
									$lineAmount = '';
								}
								else {
									$recurringStr = '';
									$editStr = '<a class="controlEdit editEnabled" href="#" data-type="other" data-id="'.$row['expenseOtherID'].'" data-unitprice="'.$row['unitPrice'].'" data-quantity="'.($row['quantity'] + 0).'"></a>';
									$lineAmount = $row['quantity'] * $row['unitPrice'];
									$subTotal += $lineAmount;
								}
								$return .= '<tr><td>'.$parsed['name'].$recurringStr.'</td>';
								$return .= '<td></td>';
								$return .= '<td class="textCenter">'.$parsed['quantity'].'</td>';
								$return .= '<td class="textCenter">'.$parsed['unitPrice'].'</td>';
								$return .= '<td class="textRight">'.formatCurrency($lineAmount).'</td>';
								$return .= '<td class="textCenter">'.$editStr.'</td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="other" data-id="'.$row['expenseOtherID'].'"></a></td></tr>';
								
								//get child recurring rows if this is a parent recurring row
								if (!is_null($row['recurringID'])) {
									$sth2 = $dbh->prepare(
										'SELECT expenseOtherID, name, date, quantity, unitPrice
										FROM expenseOthers
										WHERE expenseID = :expenseID AND parentRecurringID = :recurringID
										ORDER BY date');
									$sth2->execute([':expenseID' => $id, ':recurringID' => $row['recurringID']]);
									while ($row2 = $sth2->fetch()) {
										$parsed = self::parseSubTypeValue('other', null, $row2, 'arr');
										$lineAmount = $row2['quantity'] * $row2['unitPrice'];
										$subTotal += $lineAmount;
										$return .= '<tr><td style="padding-left: 50px;">'.formatDate($row2['date']).': '.$parsed['name'].'</td>';
										$return .= '<td></td>';
										$return .= '<td class="textCenter">'.$parsed['quantity'].'</td>';
										$return .= '<td class="textCenter">'.$parsed['unitPrice'].'</td>';
										$return .= '<td class="textRight">'.formatCurrency($lineAmount).'</td>';
										$return .= '<td class="textCenter"><a class="controlEdit editEnabled" href="#" data-type="other" data-id="'.$row2['expenseOtherID'].'" data-unitprice="'.$row2['unitPrice'].'" data-quantity="'.($row2['quantity'] + 0).'"></a></td>';
										$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="other" data-id="'.$row2['expenseOtherID'].'"></a></td></tr>';
									}
								}
							}
							
							$return .= '<tr style="font-weight: bold;"><td>Total:</td><td></td><td></td><td></td><td class="textRight">'.formatCurrency($subTotal).'</td><td></td><td></td></tr>';
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			//Payments section
			$subTotal = 0;
			$return .= '<section>
				<h2>Payments</h2>
				<div class="sectionData">
					<div class="customAddLink" id="customAdd1"><a class="controlAdd addEnabled" href="#">Add Payment</a></div>
					<table class="customTable" style="width:100%;">
						<thead>
							<tr>
								<th>Date</th>
								<th class="textCenter">Type</th>
								<th class="textRight">Amount</th>
								<th></th>
							</tr>
						</thead>
						<tbody>';
							$sth = $dbh->prepare(
								'SELECT *
								FROM expensePayments
								WHERE expenseID = :expenseID');
							$sth->execute([':expenseID' => $id]);
							while ($row = $sth->fetch()) {
								$parsed = self::parseSubTypeValue('payment', null, $row, 'arr');
								$subTotal += $row['paymentAmount'];
								$return .= '<tr><td>'.$parsed['date'].'</td>';
								$return .= '<td class="textCenter">'.$parsed['paymentType'].'</td>';
								$return .= '<td class="textRight">'.$parsed['paymentAmount'].'</td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="payment" data-id="'.$row['paymentID'].'"></a></td></tr>';
							}
							$return .= '<tr style="font-weight: bold;"><td>Total:</td><td></td><td class="textRight">'.formatCurrency($subTotal).'</td><td></td></tr>';
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			return $return;
		}
		
		public function getName($type, $id) {
			return 'Expense #'.$id;
		}
		
		public function parseValue($type, $item) {
			foreach ($item as $field => $value) {
				switch ($field) {
					case 'date':
						$parsed[$field] = formatDate($value);
						break;
					case 'amountDue':
						$parsed[$field] = formatCurrency($value);
						break;
					default:
						$parsed[$field] = $value;
				}
				if (isset($parsed[$field]) && $field != 'amountDue') {
					$parsed[$field] = htmlspecialchars($parsed[$field], ENT_QUOTES | ENT_HTML5, 'UTF-8');
				}
			}
			
			return $parsed;
		}
		
		public function parseSubTypeValue($subType, $action, $item, $format) {
			global $TYPES;
			$dataStr = '';
			$parsed = [];
			
			foreach ($item as $field => $value) {
				//check to see if it's an id first, then do the switch statement
				if (isset($TYPES['expense']['subTypes'][$subType]['fields'][$field]) && $TYPES['expense']['subTypes'][$subType]['fields'][$field]['verifyData'][1] == 'id') {
					$parsed[$field] = (!is_null($value)) ? getLinkedName($TYPES['expense']['subTypes'][$subType]['fields'][$field]['verifyData'][2], $value) : '';
				}
				else {
					if ($subType == 'payment') {
						switch ($field) {
							case 'date':
								$parsed[$field] = formatDate($value);
								break;
							case 'paymentType':
								if ($value == 'CA') {
									$parsed[$field] = 'Cash';
								}
								elseif ($value == 'CH') {
									$parsed[$field] = 'Check';
								}
								elseif ($value == 'CR') {
									$parsed[$field] = 'Credit Card';
								}
								break;
							case 'paymentAmount':
								$parsed[$field] = formatCurrency($value);
								break;
							default:
								$parsed[$field] = $value;
						}
					}
					elseif ($subType == 'product') {
						switch ($field) {
							case 'unitPrice':
								$parsed[$field] = formatCurrency($value);
								break;
							case 'quantity':
								$parsed[$field] = formatNumber($value);
								break;
							case 'recurring':
								if ($value == 'yes') {
									$parsed[$field] = 'Yes';
								}
								elseif ($value == 'no') {
									$parsed[$field] = 'No';
								}
								break;
							case 'interval':
								if ($value == 'monthly') {
									$parsed[$field] = 'Monthly';
								}
								break;
							case 'startDate':
							case 'endDate':
								$parsed[$field] = formatDate($value);
								break;
							default:
								$parsed[$field] = $value;
						}
					}
					elseif ($subType == 'other') {
						switch ($field) {
							case 'unitPrice':
								$parsed[$field] = formatCurrency($value);
								break;
							case 'quantity':
								$parsed[$field] = formatNumber($value + 0);
								break;
							case 'recurring':
								if ($value == 'yes') {
									$parsed[$field] = 'Yes';
								}
								elseif ($value == 'no') {
									$parsed[$field] = 'No';
								}
								break;
							case 'interval':
								if ($value == 'monthly') {
									$parsed[$field] = 'Monthly';
								}
								break;
							case 'startDate':
							case 'endDate':
								$parsed[$field] = formatDate($value);
								break;
							default:
								$parsed[$field] = $value;
						}
					}
					else {
						$parsed[$field] = $value;
					}
					if (isset($parsed[$field]) && $field != 'paymentAmount' && $field != 'unitPrice') {
						$parsed[$field] = htmlspecialchars($parsed[$field], ENT_QUOTES | ENT_HTML5, 'UTF-8');
					}
				}
			}
			
			if ($format == 'str') {
				//if we want a string format
				if ($action == 'A') {
					$dataStr = 'Added ';
				}
				elseif ($action == 'E') {
					$dataStr = 'Edited ';
				}
				elseif ($action == 'D') {
					$dataStr = 'Deleted ';
				}
				
				if ($subType == 'payment') {
					$dataStr .= 'payment. ';
				}
				elseif ($subType == 'product') {
					$dataStr .= 'product. ';
				}
				elseif ($subType == 'other') {
					$dataStr .= 'other expense. ';
				}
				
				foreach ($parsed as $key => $value) {
					$dataStr .= '<b>'. $TYPES['expense']['subTypes'][$subType]['fields'][$key]['formalName'].':</b> '.$value.' ';
				}
				
				return $dataStr;
			}
			else {
				//otherwise return the array
				return $parsed;
			}
		}
		
		public function customAjax($id, $data) {
			global $dbh;
			global $TYPES;
			$return = ['status' => 'success'];
			
			if ($data['subAction'] == 'list') {
				//list subAction
				$return['products'] = generateTypeOptions('product', true);
				$return['locations'] = generateTypeOptions('location', true);
			}
			elseif ($data['subAction'] == 'add') {
				//add subAction
				$return['status'] = 'fail';
				$subType = $data['subType'];
				unset($data['subAction']);
				unset($data['subType']);
				$return = verifyData('expense', $subType, $data);
				
				if ($return['status'] != 'fail') {
					if ($subType == 'payment') {
						$date = strtotime($data['date']);
						$sth = $dbh->prepare(
							'INSERT INTO expensePayments (expenseID, date, paymentType, paymentAmount)
							VALUES(:expenseID, :date, :paymentType, :paymentAmount)');
						$sth->execute([':expenseID' => $id, ':date' => $date, ':paymentType' => $data['paymentType'], ':paymentAmount' => $data['paymentAmount']]);
						$changeData = ['subType' => 'payment', 'date' => $date, 'paymentType' => $data['paymentType'], 'paymentAmount' => $data['paymentAmount']];
					}
					elseif ($subType == 'product') {
						$sth = $dbh->prepare(
							'SELECT quantity
							FROM expenses_products
							WHERE expenseID = :expenseID AND productID = :productID AND locationID = :locationID AND unitPrice = :unitPrice');
						$sth->execute([':expenseID' => $id, ':productID' => $data['productID'], ':locationID' => $data['locationID'], ':unitPrice' => $data['unitPrice']]);
						$result = $sth->fetchAll();
						if (count($result) == 1 && $data['recurring'] == 'no') {
							//if the product is already present in the expense AND we aren't doing a recurring item, add the quantity to the existing row
							$totalQuantity = $data['quantity'] + $result[0]['quantity'];
							$sth = $dbh->prepare(
								'UPDATE expenses_products
								SET quantity = :quantity
								WHERE expenseID = :expenseID AND productID = :productID AND locationID = :locationID AND unitPrice = :unitPrice');
							$sth->execute([':quantity' => $totalQuantity, ':expenseID' => $id, ':productID' => $data['productID'], ':locationID' => $data['locationID'], ':unitPrice' => $data['unitPrice']]);
							$changeAction = 'E';  //this is technically an edit, not an add
							$changeData = ['subType' => 'product', 'productID' => $data['productID'], 'locationID' => $data['locationID'], 'unitPrice' => $data['unitPrice'], 'quantity' => $totalQuantity];
						}
						else {
							if ($data['recurring'] == 'yes') {
								$startDate = strtotime($data['startDate']);
								$endDate = strtotime($data['endDate']);
								//add the recurring item
								$sth = $dbh->prepare(
									'SELECT MAX(recurringID) AS recurringID
									FROM expenses_products');
								$sth->execute();
								$result = $sth->fetchAll();
								$recurringID = $result[0]['recurringID'] + 1;
								$sth = $dbh->prepare(
									'INSERT INTO expenses_products (expenseID, productID, locationID, unitPrice, quantity, recurringID, dayOfMonth, startDate, endDate)
									VALUES(:expenseID, :productID, :locationID, :unitPrice, :quantity, :recurringID, :dayOfMonth, :startDate, :endDate)');
								$sth->execute([':expenseID' => $id, ':productID' => $data['productID'], ':locationID' => $data['locationID'], ':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity'], ':recurringID' => $recurringID, ':dayOfMonth' => $data['dayOfMonth'], ':startDate' => $startDate, ':endDate' => $endDate]);
								
								//add occasions from start date to now
								$temp = new DateTime();
								$temp->setTimestamp($startDate);
								$patternStart = new DateTime($data['dayOfMonth'].'-'.$temp->format('M').'-'.$temp->format('Y'));
								$interval = new DateInterval('P1M');
								$now = new DateTime();
								$period = new DatePeriod($patternStart, $interval, $now);
								foreach ($period as $date) {
									$timestamp = $date->getTimestamp();
									if ($timestamp >= $startDate && $timestamp <= $endDate) {
										$sth = $dbh->prepare(
											'INSERT INTO expenses_products (expenseID, productID, locationID, date, unitPrice, quantity, parentRecurringID)
											VALUES(:expenseID, :productID, :locationID, :date, :unitPrice, :quantity, :parentRecurringID)');
										$sth->execute([':expenseID' => $id, ':productID' => $data['productID'], ':locationID' => $data['locationID'], ':date' => $timestamp, ':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity'], ':parentRecurringID' => $recurringID]);
									}
								}
								$changeData = ['subType' => 'product', 'productID' => $data['productID'], 'locationID' => $data['locationID'], 'unitPrice' => $data['unitPrice'], 'quantity' => $data['quantity'], 'recurring' => $data['recurring'], 'interval' => $data['interval'], 'dayOfMonth' => $data['dayOfMonth'], 'startDate' => $startDate, 'endDate' => $endDate];
							}
							else {
								//get date of expense
								$sth = $dbh->prepare(
									'SELECT date
									FROM expenses
									WHERE expenseID = :expenseID');
								$sth->execute([':expenseID' => $id]);
								$row = $sth->fetch();
								
								$sth = $dbh->prepare(
									'INSERT INTO expenses_products (expenseID, productID, locationID, date, unitPrice, quantity)
									VALUES(:expenseID, :productID, :locationID, :date, :unitPrice, :quantity)');
								$sth->execute([':expenseID' => $id, ':productID' => $data['productID'], ':locationID' => $data['locationID'], ':date' => $row['date'], ':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity']]);
								$changeData = ['subType' => 'product', 'productID' => $data['productID'], 'locationID' => $data['locationID'], 'unitPrice' => $data['unitPrice'], 'quantity' => $data['quantity']];
							}
						}
					}
					elseif ($subType == 'other') {
						$sth = $dbh->prepare(
							'SELECT quantity
							FROM expenseOthers
							WHERE expenseID = :expenseID AND name = :name AND unitPrice = :unitPrice');
						$sth->execute([':expenseID' => $id, ':name' => $data['name'], ':unitPrice' => $data['unitPrice']]);
						$result = $sth->fetchAll();
						if (count($result) == 1 && $data['recurring'] == 'no') {
							//if the item is already present in the expense AND we aren't doing a recurring item, add the quantity to the existing row
							$totalQuantity = $data['quantity'] + $result[0]['quantity'];
							$sth = $dbh->prepare(
								'UPDATE expenseOthers
								SET quantity = :quantity
								WHERE expenseID = :expenseID AND name = :name AND unitPrice = :unitPrice');
							$sth->execute([':quantity' => $totalQuantity, ':expenseID' => $id, ':name' => $data['name'], ':unitPrice' => $data['unitPrice']]);
							$changeAction = 'E';  //this is technically an edit, not an add
							$changeData = ['subType' => 'other', 'name' => $data['name'], 'unitPrice' => $data['unitPrice'], 'quantity' => $totalQuantity];
						}
						else {
							if ($data['recurring'] == 'yes') {
								$startDate = strtotime($data['startDate']);
								$endDate = strtotime($data['endDate']);
								//add the recurring item
								$sth = $dbh->prepare(
									'SELECT MAX(recurringID) AS recurringID
									FROM expenseOthers');
								$sth->execute();
								$result = $sth->fetchAll();
								$recurringID = $result[0]['recurringID'] + 1;
								$sth = $dbh->prepare(
									'INSERT INTO expenseOthers (expenseID, name, unitPrice, quantity, recurringID, dayOfMonth, startDate, endDate)
									VALUES(:expenseID, :name, :unitPrice, :quantity, :recurringID, :dayOfMonth, :startDate, :endDate)');
								$sth->execute([':expenseID' => $id, ':name' => $data['name'], ':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity'], ':recurringID' => $recurringID, ':dayOfMonth' => $data['dayOfMonth'], ':startDate' => $startDate, ':endDate' => $endDate]);
								
								//add occasions from start date to now
								$temp = new DateTime();
								$temp->setTimestamp($startDate);
								$patternStart = new DateTime($data['dayOfMonth'].'-'.$temp->format('M').'-'.$temp->format('Y'));
								$interval = new DateInterval('P1M');
								$now = new DateTime();
								$period = new DatePeriod($patternStart, $interval, $now);
								foreach ($period as $date) {
									$timestamp = $date->getTimestamp();
									if ($timestamp >= $startDate && $timestamp <= $endDate) {
										$sth = $dbh->prepare(
											'INSERT INTO expenseOthers (expenseID, name, date, unitPrice, quantity, parentRecurringID)
											VALUES(:expenseID, :name, :date, :unitPrice, :quantity, :parentRecurringID)');
										$sth->execute([':expenseID' => $id, ':name' => $data['name'], ':date' => $timestamp, ':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity'], ':parentRecurringID' => $recurringID]);
									}
								}
								$changeData = ['subType' => 'other', 'name' => $data['name'], 'unitPrice' => $data['unitPrice'], 'quantity' => $data['quantity'], 'recurring' => $data['recurring'], 'interval' => $data['interval'], 'dayOfMonth' => $data['dayOfMonth'], 'startDate' => $startDate, 'endDate' => $endDate];
							}
							else {
								//get date of expense
								$sth = $dbh->prepare(
									'SELECT date
									FROM expenses
									WHERE expenseID = :expenseID');
								$sth->execute([':expenseID' => $id]);
								$row = $sth->fetch();
								
								$sth = $dbh->prepare(
									'INSERT INTO expenseOthers (expenseID, name, date, unitPrice, quantity)
									VALUES(:expenseID, :name, :date, :unitPrice, :quantity)');
								$sth->execute([':expenseID' => $id, ':name' => $data['name'], ':date' => $row['date'], ':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity']]);
								$changeData = ['subType' => 'other', 'name' => $data['name'], 'unitPrice' => $data['unitPrice'], 'quantity' => $data['quantity']];
							}
						}
					}
					
					self::updateAmountDue($id);
					$temp = (isset($changeAction)) ? $changeAction : 'A';
					addChange('expense', $id, $_SESSION['employeeID'], $temp, json_encode($changeData));
				}
			}
			elseif ($data['subAction'] == 'edit') {
				//edit subAction
				$subType = $data['subType'];
				unset($data['subAction']);
				unset($data['subType']);
				$subID = $data['subID'];
				unset($data['subID']);
				$return = verifyData('expense', $subType, $data);
				
				if ($return['status'] != 'fail') {
					if ($subType == 'product') {
						$sth = $dbh->prepare(
							'UPDATE expenses_products
							SET unitPrice = :unitPrice, quantity = :quantity
							WHERE expenseProductID = :expenseProductID');
						$sth->execute([':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity'], ':expenseProductID' => $subID]);
						$changeData = ['subType' => 'product', 'productID' => $data['productID'], 'locationID' => $data['locationID'], 'unitPrice' => $data['unitPrice'], 'quantity' => $data['quantity']];
					}
					elseif ($subType == 'other') {
						$sth = $dbh->prepare(
							'UPDATE expenseOthers
							SET unitPrice = :unitPrice, quantity = :quantity
							WHERE expenseOtherID = :expenseOtherID');
						$sth->execute([':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity'], ':expenseOtherID' => $subID]);
						$changeData = ['subType' => 'other', 'name' => $data['name'], 'unitPrice' => $data['unitPrice'], 'quantity' => $data['quantity']];
					}
					
					self::updateAmountDue($id);
					addChange('expense', $id, $_SESSION['employeeID'], 'E', json_encode($changeData));
				}
			}
			elseif ($data['subAction'] == 'delete') {
				//delete subAction
				if ($data['subType'] == 'payment') {
					$sth = $dbh->prepare(
						'SELECT date, paymentAmount FROM orderPayments
						WHERE paymentID = :paymentID');
					$sth->execute([':paymentID' => $data['subID']]);
					$row = $sth->fetch();
					
					$sth = $dbh->prepare(
						'DELETE FROM expensePayments
						WHERE paymentID = :paymentID');
					$sth->execute([':paymentID' => $data['subID']]);
					
					$changeData = ['subType' => 'payment', 'date' => $row['date'], 'paymentAmount' => $row['paymentAmount']];
				}
				elseif ($data['subType'] == 'product') {
					$sth = $dbh->prepare(
						'SELECT productID, locationID, unitPrice, quantity, recurringID
						FROM expenses_products 
						WHERE expenseProductID = :expenseProductID');
					$sth->execute([':expenseProductID' => $data['subID']]);
					$row = $sth->fetch();
					$recurring = ($row['recurringID'] === null) ? 'no' : 'yes';
					
					//delete item and children (if any)
					$sth = $dbh->prepare(
						'DELETE FROM expenses_products
						WHERE expenseProductID = :expenseProductID OR parentRecurringID = :recurringID');
					$sth->execute([':expenseProductID' => $data['subID'], ':recurringID' => $row['recurringID']]);
					
					$changeData = ['subType' => 'product', 'productID' => $row['productID'], 'locationID' => $row['locationID'], 'unitPrice' => $row['unitPrice'], 'quantity' => $row['quantity'], 'recurring' => $recurring];
				}
				elseif ($data['subType'] == 'other') {
					$sth = $dbh->prepare(
						'SELECT name, unitPrice, quantity, recurringID 
						FROM expenseOthers 
						WHERE expenseOtherID = :expenseOtherID');
					$sth->execute([':expenseOtherID' => $data['subID']]);
					$row = $sth->fetch();
					$recurring = ($row['recurringID'] === null) ? 'no' : 'yes';
					
					//delete item and children (if any)
					$sth = $dbh->prepare(
						'DELETE FROM expenseOthers
						WHERE expenseOtherID = :expenseOtherID OR parentRecurringID = :recurringID');
					$sth->execute([':expenseOtherID' => $data['subID'], ':recurringID' => $row['recurringID']]);
					
					$changeData = ['subType' => 'other', 'name' => $row['name'], 'unitPrice' => $row['unitPrice'], 'quantity' => $row['quantity'], 'recurring' => $recurring];
				}
				
				self::updateAmountDue($id);
				addChange('expense', $id, $_SESSION['employeeID'], 'D', json_encode($changeData));
			}
			
			return $return;
		}
		
		public function printPopups() {
			$return = 
			'<div class="popup" id="customPopup1">
				<div>
					<a class="close" title="Close">X</a>
					<div>
						<h1>Add Payment</h1>
						<section>
							<h2></h2>
							<div class="sectionData">
								<ul>
									<li>
										<label for="paymentType">Type</label>
										<select name="paymentType">
											<option value=""></option>
											<option value="CA">Cash</option>
											<option value="CH">Check</option>
											<option value="CR">Credit Card</option>
										</select>
									</li>
									<li>
										<label for="paymentAmount">Amount</label>
										<input type="text" autocomplete="off" name="paymentAmount">
									</li>
								</ul>
								<ul>
									<li>
										<label for="date">Date</label>
										<input type="text" class="dateInput" autocomplete="off" name="date">
									</li>
								</ul>
							</div>
						</section>
						<div id="btnSpacer">
							<button id="customBtn1">Add</button>
						</div>
					</div>
				</div>
			</div>
			<div class="popup" id="customPopup2">
				<div>
					<a class="close" title="Close">X</a>
					<div>
						<h1>Add Line Item</h1>
						<section>
							<h2></h2>
							<div class="sectionData">
								<ul>
									<li>
										<label for="itemType">Type</label>
										<select name="itemType">
											<option value=""></option>
											<option value="product">Product</option>
											<option value="other">Other Expense</option>
										</select>
									</li>
								</ul>
								<ul>
									<li>
										<label for="interval">Interval</label>
										<select name="interval">
											<option value=""></option>
											<option value="monthly">Monthly</option>
										</select>
									</li>
									<li>
										<label for="dayOfMonth">Day of Month</label>
										<select name="dayOfMonth">
											<option value=""></option>
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
											<option value="4">4</option>
											<option value="5">5</option>
											<option value="6">6</option>
											<option value="7">7</option>
											<option value="8">8</option>
											<option value="9">9</option>
											<option value="10">10</option>
											<option value="11">11</option>
											<option value="12">12</option>
											<option value="13">13</option>
											<option value="14">14</option>
											<option value="15">15</option>
											<option value="16">16</option>
											<option value="17">17</option>
											<option value="18">18</option>
											<option value="19">19</option>
											<option value="20>20</option>
											<option value="21">21</option>
											<option value="22">22</option>
											<option value="23">23</option>
											<option value="24">24</option>
											<option value="25">25</option>
											<option value="26">26</option>
											<option value="27">27</option>
											<option value="28">28</option>
										</select>
									</li>
									<li>
										<label for="startDate">Start Date</label>
										<input type="text" class="dateInput" autocomplete="off" name="startDate">
									</li>
									<li>
										<label for="endDate">End Date</label>
										<input type="text" class="dateInput" autocomplete="off" name="endDate">
									</li>
								</ul>
							</div>
						</section>
						<div id="btnSpacer">
							<button id="customBtn2">Add</button>
						</div>
					</div>
				</div>
			</div>
			<div class="popup" id="customPopup3">
				<div>
					<a class="close" title="Close">X</a>
					<div>
						<h1>Edit Line Item</h1>
						<section>
							<h2></h2>
							<div class="sectionData">
								<ul>
									<li>
										<label for="unitPrice">Unit Price</label>
										<input type="text" name="unitPrice" autocomplete="off" value="">
									</li>
									<li>
										<label for="quantity">Quantity</label>
										<input type="text" name="quantity" autocomplete="off" value="">
									</li>
								</ul>
							</div>
						</section>
						<div id="btnSpacer">
							<button id="customBtn3">Edit</button>
						</div>
					</div>
				</div>
			</div>';
			return $return;
		}
		
		public static function editHook($id, $data) {
			global $dbh;
			
			if (isset($data['date'])) {
				//update the date on the associated subTypes
				$sth = $dbh->prepare(
					'UPDATE expenses_products
					SET date = :date
					WHERE expenseID = :expenseID AND recurringID IS NULL AND parentRecurringID IS NULL');
				$sth->execute([':expenseID' => $id, ':date' => $data['date']]);
				
				$sth = $dbh->prepare(
					'UPDATE expenseOthers
					SET date = :date
					WHERE expenseID = :expenseID AND recurringID IS NULL AND parentRecurringID IS NULL');
				$sth->execute([':expenseID' => $id, ':date' => $data['date']]);
			}
		}
		
		/* Local Helper Functions */
		
		private static function updateAmountDue($id) {
			global $dbh;
			$subTotal = 0;
			
			//get product expenses
			$sth = $dbh->prepare(
				'SELECT expenseProductID, quantity, unitPrice, lineAmount
				FROM expenses_products
				WHERE expenseID = :expenseID AND recurringID IS NULL');
			$sth->execute([':expenseID' => $id]);
			while ($row = $sth->fetch()) {
				$lineAmount = $row['quantity'] * $row['unitPrice'];
				$lines['P'.$row['expenseProductID']] = [$row['lineAmount'], $lineAmount];
				$subTotal += $lineAmount;
			}
			
			//get other expenses
			$sth = $dbh->prepare(
				'SELECT expenseOtherID, quantity, unitPrice, lineAmount
				FROM expenseOthers
				WHERE expenseID = :expenseID AND recurringID IS NULL');
			$sth->execute([':expenseID' => $id]);
			while ($row = $sth->fetch()) {
				$lineAmount = $row['quantity'] * $row['unitPrice'];
				$lines['O'.$row['expenseOtherID']] = [$row['lineAmount'], $lineAmount];
				$subTotal += $lineAmount;
			}
			
			//update lineAmounts as needed
			foreach ($lines as $key => $line) {
				if ($line[0] != $line[1]) {
					$itemType = substr($key, 0, 1);
					$uniqueID = substr($key, 1);
					$table = ($itemType == 'P') ? 'expenses_products' : 'expenseOthers';
					$uniqueIDName = ($itemType == 'P') ? 'expenseProductID' : 'expenseOtherID';
				
					$sth = $dbh->prepare(
						'UPDATE '.$table.'
						SET lineAmount = :lineAmount
						WHERE '.$uniqueIDName.' = :uniqueID');
					$sth->execute([':lineAmount' => $line[1], ':uniqueID' => $uniqueID]);
				}
			}
			
			//get payments
			$sth = $dbh->prepare(
				'SELECT SUM(paymentAmount) AS paymentAmount
				FROM expensePayments
				WHERE expenseID = :expenseID');
			$sth->execute([':expenseID' => $id]);
			$row = $sth->fetch();
			
			$amountDue = $subTotal - $row['paymentAmount'];
			$sth = $dbh->prepare(
				'UPDATE expenses
				SET amountDue = :amountDue
				WHERE expenseID = :expenseID');
			$sth->execute([':amountDue' => $amountDue, ':expenseID' => $id]);
		}
	}
?>