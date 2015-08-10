<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Order extends GenericItem {
		public function printItemBody($id) {
			global $dbh;
			global $TYPES;
			
			//Line Items section
			$return = '<section>
				<h2>Line Items</h2>
				<div class="sectionData">
					<div class="customAddLink" id="customAdd2"><a class="controlAdd addEnabled" href="#">Add Line Item</a></div>
					<table class="customTable" style="width:100%;">
						<thead>
							<tr>
								<th class="textLeft">Item</th>
								<th class="textRight">Quantity</th>
								<th class="textRight">Unit Price</th>
								<th class="textRight">Item Total</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>';
							$lineItemTable = self::getLineItemTable($id);
							
							foreach ($lineItemTable[0] as $line) {
								//TODO: possibly might want to use self::parseSubTypeValue here, but I'd have to rearrange self::getLineItemTable a bit first
								$padding = 8 + (50 * $line['indent']);
								if ($line['type'] == 'service' || $line['type'] == 'product') {
									if (!is_null($line['recurring'])) {
										$recurringStr = ' (occurs monthly on day '.$line['recurring'][0].' from '.formatDate($line['recurring'][1]).' to '.formatDate($line['recurring'][2]).')';
										$editStr = '';
									}
									else {
										$recurringStr = '';
										$editStr = '<a class="controlEdit editEnabled" href="#" data-type="'.$line['type'].'" data-id="'.$line['uniqueID'].'" data-quantity="'.formatNumber($line['quantity']).'"></a>';
									}
									$dateStr = (isset($line['date'])) ? formatDate($line['date']).': ' : '';
									$return .= '<tr><td style="padding-left: '.$padding.'px;">'.$dateStr.'<a href="item.php?type='.$line['type'].'&id='.$line['id'].'">'.$line['name'].'</a>'.$recurringStr.'</td>';
									$return .= '<td class="textRight">'.formatNumber($line['quantity']).'</td>';
									$return .= '<td class="textRight">&nbsp;'.formatCurrency($line['unitPrice']).'</td>';   //added a space here to line up unit prices with the negative unit prices on discounts
									$return .= '<td class="textRight">'.formatCurrency($line['lineAmount']).'</td>';
									$return .= '<td class="textCenter">'.$editStr.'</td>';
									$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="'.$line['type'].'" data-id="'.$line['uniqueID'].'"></a></td></tr>';
								}
								elseif ($line['type'] == 'discount') {
									$unitPrice = ($line['discountType'] == 'C') ? formatCurrency(-$line['discountAmount']) : $line['discountAmount'].'%';
									$return .= '<tr><td style="padding-left: '.$padding.'px;">Discount: <a href="item.php?type=discount&id='.$line['id'].'">'.$line['name'].'</a></td>';
									$return .= '<td></td>';
									$return .= '<td class="textRight">'.$unitPrice.'</td>';
									$return .= '<td class="textRight">'.formatCurrency(-$line['lineAmount']).'</td>';
									$return .= '<td></td>';
									$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="discount" data-id="'.$line['uniqueID'].'"></a></td></tr>';
								}
							}
							
							$return .= '<tr class="totalSeparator"><td colspan="3">Total:</td>';
							$return .= '<td class="textRight">'.formatCurrency($lineItemTable[1]).'</td>';
							$return .= '<td colspan="2"></td></tr>';
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
								<th class="textLeft">Date</th>
								<th class="textLeft">Type</th>
								<th class="textRight">Amount</th>
								<th></th>
							</tr>
						</thead>
						<tbody>';
							$sth = $dbh->prepare(
								'SELECT *
								FROM orderPayments
								WHERE orderID = :orderID');
							$sth->execute([':orderID' => $id]);
							while ($row = $sth->fetch()) {
								$parsed = self::parseSubTypeValue('payment', null, $row, 'arr');
								$subTotal += $row['paymentAmount'];
								$return .= '<tr><td class="textLeft">'.$parsed['date'].'</td>';
								$return .= '<td class="textLeft">'.$parsed['paymentType'].'</td>';
								$return .= '<td class="textRight">'.$parsed['paymentAmount'].'</td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="payment" data-id="'.$row['paymentID'].'"></a></td></tr>';
							}
							$return .= '<tr class="totalSeparator"><td colspan="2">Total:</td>';
							$return .= '<td class="textRight">'.formatCurrency($subTotal).'</td>';
							$return .= '<td></td></tr>';
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			//Invoice section
			//TODO: figure out how email is going to work (maybe a popup that gives you HTML to copy into the email?
			$return .= '<section>
				<h2>Invoice</h2>
				<div class="sectionData" style="text-align: center;">
					<a href="#" class="controlEmail" style="margin-right: 25%;">Email Invoice (currently disabled)</a>
					<a href="pdf.php?type=order&id='.$id.'&pdfID=1" class="controlPDF">Generate PDF Invoice</a>
				</div>
			</section>';
			
			return $return;
		}
		
		public function getName($type, $id) {
			return 'Order #'.$id;
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
				if (isset($TYPES['order']['subTypes'][$subType]['fields'][$field]) && $TYPES['order']['subTypes'][$subType]['fields'][$field]['verifyData'][1] == 'id') {
					$parsed[$field] = (!is_null($value)) ? getLinkedName($TYPES['order']['subTypes'][$subType]['fields'][$field]['verifyData'][2], $value) : '';
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
					elseif ($subType == 'service') {
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
					elseif ($subType == 'discountOrder') {
						switch ($field) {
							case 'subID':
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
				elseif ($subType == 'service') {
					$dataStr .= 'service. ';
				}
				elseif ($subType == 'discountOrder') {
					$dataStr .= ($action == 'A') ? 'discount to the order. ' : 'discount from the order. ';
				}
				elseif ($subType == 'discountProduct' || $subType == 'discountService') {
					$dataStr .= 'discount. ';
				}
				
				foreach ($parsed as $key => $value) {
					$dataStr .= '<b>'. $TYPES['order']['subTypes'][$subType]['fields'][$key]['formalName'].':</b> '.$value.' ';
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
				$return['options'] = [];
				if ($data['subType'] == 'preDiscount') {
					//list all products and services on the order, plus an order line
					$sth = $dbh->prepare(
						'SELECT CONCAT("P", orderProductID) AS id, name, recurringID, parentRecurringID, date
						FROM products, orders_products
						WHERE orderID = 1 AND products.productID = orders_products.productID
						UNION
						SELECT CONCAT("S", orderServiceID) AS id, name, recurringID, parentRecurringID, date
						FROM services, orders_services
						WHERE orderID = 1 AND services.serviceID = orders_services.serviceID');
					$sth->execute([':orderID' => $id]);
					$return['options'][] = ['value' => 'O', 'text' => 'Order'];
					while ($row = $sth->fetch()) {
						if ($row['recurringID'] !== null) {
							$text = 'Recurring: '.$row['name'];
						}
						elseif ($row['parentRecurringID'] !== null) {
							$text = formatDate($row['date']).': '.$row['name'];
						}
						else {
							$text = $row['name'];
						}
						$return['options'][] = ['value' => $row['id'], 'text' => htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')];
					}
				}
				else {
					$discounts = [];
					if ($data['subType'] == 'discount') {
						//get the list of discounts applied to the item already so we don't apply the same discount twice to the same item
						$temp = [substr($data['subID'], 0, 1), substr($data['subID'], 1)];
						$sth = $dbh->prepare(
							'SELECT discountID
							FROM orders_discounts
							WHERE orderID = :orderID AND appliesToType = :appliesToType AND appliesToID = :appliesToID');
						$sth->execute([':orderID' => $id, ':appliesToType' => $temp[0], ':appliesToID' => $temp[1]]);
						while ($row = $sth->fetch()) {
							$discounts[] = $row['discountID'];
						}
					}
					$sth = $dbh->prepare(
						'SELECT '.$TYPES[$data['subType']]['idName'].', name
						FROM '.$TYPES[$data['subType']]['pluralName'].'
						WHERE active = 1');
					$sth->execute();
					while ($row = $sth->fetch()) {
						if ($data['subType'] != 'discount' || !in_array($row[0], $discounts)) {
							$return['options'][] = ['value' => $row[0], 'text' => htmlspecialchars($row[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')];
						}
					}
				}
			}
			elseif ($data['subAction'] == 'add') {
				//add subAction
				$return['status'] = 'fail';
				$subType = $data['subType'];
				unset($data['subAction']);
				unset($data['subType']);
				if ($subType == 'discount') {
					//for discounts, subID contains a one letter indication of the type of item (O = order, P = product, S = service), and the unique subID
					$itemType = substr($data['subID'], 0, 1);
					$uniqueID = substr($data['subID'], 1);
					if ($itemType == 'O') {
						$itemTypeFull = 'order';
						$subType = 'discountOrder';
					}
					elseif ($itemType == 'P') {
						$itemTypeFull = 'product';
						$subType = 'discountProduct';
					}
					elseif ($itemType == 'S') {
						$itemTypeFull = 'service';
						$subType = 'discountService';
					}
					
					if ($itemType == 'O') {
						$data['subID'] = 0;
					}
					else {
						$sth = $dbh->prepare(
							'SELECT '.$TYPES[$itemTypeFull]['idName'].'
							FROM orders_'.$TYPES[$itemTypeFull]['pluralName'].'
							WHERE order'.$TYPES[$itemTypeFull]['formalName'].'ID = :uniqueID');
						$sth->execute([':uniqueID' => $uniqueID]);
						$row = $sth->fetch();
						$data['subID'] = $row[0];
					}
				}
				$data = cleanData('order', $subType, $data);
				$return = verifyData('order', $subType, $data);
				
				if ($return['status'] != 'fail') {
					if ($subType == 'payment') {
						$date = strtotime($data['date']);
						$sth = $dbh->prepare(
							'INSERT INTO orderPayments (orderID, date, paymentType, paymentAmount)
							VALUES(:orderID, :date, :paymentType, :paymentAmount)');
						$sth->execute([':orderID' => $id, ':date' => $date, ':paymentType' => $data['paymentType'], ':paymentAmount' => $data['paymentAmount']]);
						$changeData = ['subType' => 'payment', 'date' => $date, 'paymentType' => $data['paymentType'], 'paymentAmount' => $data['paymentAmount']];
					}
					elseif ($subType == 'product' || $subType == 'service') {
						$sth = $dbh->prepare(
							'SELECT quantity
							FROM orders_'.$TYPES[$subType]['pluralName'].'
							WHERE orderID = :orderID AND '.$TYPES[$subType]['idName'].' = :subID');
						$sth->execute([':orderID' => $id, ':subID' => $data['subID']]);
						$result = $sth->fetchAll();
						if (count($result) == 1 && $data['recurring'] == 'no') {
							//if the product or service is already present in the expense AND we aren't doing a recurring item, add the quantity to the existing row
							$totalQuantity = $data['quantity'] + $result[0]['quantity'];
							$sth = $dbh->prepare(
								'UPDATE orders_'.$TYPES[$subType]['pluralName'].'
								SET quantity = :quantity
								WHERE orderID = :orderID AND '.$TYPES[$subType]['idName'].' = :subID');
							$sth->execute([':quantity' => $totalQuantity, ':orderID' => $id, ':subID' => $data['subID']]);
							$changeAction = 'E';  //this is technically an edit, not an add
							$changeData = ['subType' => $subType, 'subID' => $data['subID'], 'quantity' => $totalQuantity];
						}
						else {
							//get defaultPrice
							$sth = $dbh->prepare(
								'SELECT defaultPrice
								FROM '.$TYPES[$subType]['pluralName'].'
								WHERE '.$TYPES[$subType]['idName'].' = :subID');
							$sth->execute([':subID' => $data['subID']]);
							$row = $sth->fetch();
							$defaultPrice = $row['defaultPrice'];
							
							if ($data['recurring'] == 'yes') {
								$startDate = strtotime($data['startDate']);
								$endDate = strtotime($data['endDate']);
								//add the recurring item
								$sth = $dbh->prepare(
									'SELECT MAX(recurringID) AS recurringID
									FROM orders_'.$TYPES[$subType]['pluralName']);
								$sth->execute();
								$result = $sth->fetchAll();
								$recurringID = $result[0]['recurringID'] + 1;
								$sth = $dbh->prepare(
									'INSERT INTO orders_'.$TYPES[$subType]['pluralName'].' (orderID, '.$TYPES[$subType]['idName'].', unitPrice, quantity, recurringID, dayOfMonth, startDate, endDate)
									VALUES(:orderID, :subID, :unitPrice, :quantity, :recurringID, :dayOfMonth, :startDate, :endDate)');
								$sth->execute([':orderID' => $id, ':subID' => $data['subID'], ':unitPrice' => $defaultPrice, ':quantity' => $data['quantity'], ':recurringID' => $recurringID, ':dayOfMonth' => $data['dayOfMonth'], ':startDate' => $startDate, ':endDate' => $endDate]);
								
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
											'INSERT INTO orders_'.$TYPES[$subType]['pluralName'].' (orderID, '.$TYPES[$subType]['idName'].', date, unitPrice, quantity, parentRecurringID)
											VALUES(:orderID, :subID, :date, :unitPrice, :quantity, :parentRecurringID)');
										$sth->execute([':orderID' => $id, ':subID' => $data['subID'], ':date' => $timestamp, ':unitPrice' => $defaultPrice, ':quantity' => $data['quantity'], ':parentRecurringID' => $recurringID]);
									}
								}
								$changeData = ['subType' => $subType, 'subID' => $data['subID'], 'unitPrice' => $defaultPrice, 'quantity' => $data['quantity'], 'recurring' => $data['recurring'], 'interval' => $data['interval'], 'dayOfMonth' => $data['dayOfMonth'], 'startDate' => $startDate, 'endDate' => $endDate];
							}
							else {
								//get date of order
								$sth = $dbh->prepare(
									'SELECT date
									FROM orders
									WHERE orderID = :orderID');
								$sth->execute([':orderID' => $id]);
								$row = $sth->fetch();
								
								$sth = $dbh->prepare(
									'INSERT INTO orders_'.$TYPES[$subType]['pluralName'].' (orderID, '.$TYPES[$subType]['idName'].', date, unitPrice, quantity)
									VALUES(:orderID, :subID, :date, :unitPrice, :quantity)');
								$sth->execute([':orderID' => $id, ':subID' => $data['subID'], ':date' => $row['date'], ':unitPrice' => $defaultPrice, ':quantity' => $data['quantity']]);
								$changeData = ['subType' => $subType, 'subID' => $data['subID'], 'unitPrice' => $defaultPrice, 'quantity' => $data['quantity']];
							}
						}
					}
					elseif ($subType == 'discountOrder' || $subType == 'discountProduct' || $subType == 'discountService') {
						//get discountType and discountAmount
						$sth = $dbh->prepare(
							'SELECT discountType, discountAmount
							FROM discounts
							WHERE discountID = :discountID');
						$sth->execute([':discountID' => $data['discountID']]);
						$row = $sth->fetch();
						$discountType = $row['discountType'];
						$discountAmount = $row['discountAmount'];
						
						$sth = $dbh->prepare(
							'INSERT INTO orders_discounts (orderID, discountID, appliesToType, appliesToID, discountType, discountAmount)
							VALUES(:orderID, :discountID, :appliesToType, :appliesToID, :discountType, :discountAmount)');
						$sth->execute([':orderID' => $id, ':discountID' => $data['discountID'], ':appliesToType' => $itemType, ':appliesToID' => $uniqueID, ':discountType' => $discountType, ':discountAmount' => $discountAmount]);
						$changeData = ['subType' => $subType, 'subID' => $data['subID'], 'discountID' => $data['discountID']];
						
						//determine if the appliesTo item is a recurring item, and if so, add a discount to past recurrences if they don't already have this discount
						//TODO: user could possibly make a decision to apply this to past and future recurrences, or just future
						if ($itemType != 'O') {
							$sth = $dbh->prepare(
								'SELECT recurringID
								FROM orders_'.$TYPES[$itemTypeFull]['pluralName'].'
								WHERE order'.$TYPES[$itemTypeFull]['formalName'].'ID = :uniqueID');	
							$sth->execute([':uniqueID' => $uniqueID]);
							$row = $sth->fetch();
							if ($row['recurringID'] != null) {
								$recurringID = $row['recurringID'];
								$sth = $dbh->prepare(
									'SELECT order'.$TYPES[$itemTypeFull]['formalName'].'ID AS uniqueID
									FROM orders_'.$TYPES[$itemTypeFull]['pluralName'].'
									WHERE parentRecurringID = :parentRecurringID AND order'.$TYPES[$itemTypeFull]['formalName'].'ID NOT IN(
										SELECT appliesToID
										FROM orders_discounts
										WHERE discountID = :discountID AND appliesToType = :appliesToType
									)');
								$sth->execute([':parentRecurringID' => $recurringID, ':discountID' => $data['discountID'], 'appliesToType' => $itemType]);
								while ($row = $sth->fetch()) {
									$sth2 = $dbh->prepare(
										'INSERT INTO orders_discounts (orderID, discountID, appliesToType, appliesToID, discountType, discountAmount)
										VALUES(:orderID, :discountID, :appliesToType, :appliesToID, :discountType, :discountAmount)');
									$sth2->execute([':orderID' => $id, ':discountID' => $data['discountID'], ':appliesToType' => $itemType, ':appliesToID' => $row['uniqueID'], ':discountType' => $discountType, ':discountAmount' => $discountAmount]);
								}
							}
						}
					}
					
					self::updateAmountDue($id);
					$temp = (isset($changeAction)) ? $changeAction : 'A';
					addChange('order', $id, $_SESSION['employeeID'], $temp, json_encode($changeData));
				}
			}
			elseif ($data['subAction'] == 'edit') {
				//edit subAction
				$subType = $data['subType'];
				unset($data['subAction']);
				unset($data['subType']);
				
				$sth = $dbh->prepare(
					'SELECT '.$TYPES[$subType]['idName'].' AS id
					FROM orders_'.$TYPES[$subType]['pluralName'].'
					WHERE order'.$TYPES[$subType]['formalName'].'ID = :uniqueID');	
				$sth->execute([':uniqueID' => $data['subID']]);
				$row = $sth->fetch();
				$uniqueID = $data['subID'];
				$data['subID'] = $row['id'];
				$data = cleanData('order', $subType, $data);
				$return = verifyData('order', $subType, $data);
				
				if ($return['status'] != 'fail') {
					$sth = $dbh->prepare(
						'UPDATE orders_'.$TYPES[$subType]['pluralName'].'
						SET quantity = :quantity
						WHERE order'.$TYPES[$subType]['formalName'].'ID = :uniqueID');
					$sth->execute([':quantity' => $data['quantity'], ':uniqueID' => $uniqueID]);
					
					self::updateAmountDue($id);
					addChange('order', $id, $_SESSION['employeeID'], 'E', json_encode(['subType' => $subType, 'subID' => $data['subID'], 'quantity' => $data['quantity']]));
				}
			}
			elseif ($data['subAction'] == 'delete') {
				//delete subAction
				if ($data['subType'] == 'payment') {
					$sth = $dbh->prepare(
						'SELECT date, paymentAmount
						FROM orderPayments
						WHERE paymentID = :paymentID');
					$sth->execute([':paymentID' => $data['subID']]);
					$row = $sth->fetch();
					
					$sth = $dbh->prepare(
						'DELETE FROM orderPayments
						WHERE paymentID = :paymentID');
					$sth->execute([':paymentID' => $data['subID']]);
					
					$changeData = ['subType' => 'payment', 'date' => $row['date'], 'paymentAmount' => $row['paymentAmount']];
				}
				elseif ($data['subType'] == 'product' || $data['subType'] == 'service') {
					$appliesToType = ($data['subType'] == 'product') ? 'P' : 'S';
					$sth = $dbh->prepare(
						'SELECT '.$TYPES[$data['subType']]['idName'].' AS id, recurringID, unitPrice, quantity
						FROM orders_'.$TYPES[$data['subType']]['pluralName'].'
						WHERE order'.$TYPES[$data['subType']]['formalName'].'ID = :uniqueID');
					$sth->execute([':uniqueID' => $data['subID']]);
					$row = $sth->fetch();
					$recurring = ($row['recurringID'] === null) ? 'no' : 'yes';
					
					//delete item discounts and children's discounts (if any)
					$sth = $dbh->prepare(
						'DELETE FROM orders_discounts
						WHERE appliesToType = :appliesToType AND (appliesToID IN (
							SELECT order'.$TYPES[$data['subType']]['formalName'].'ID 
							FROM orders_'.$TYPES[$data['subType']]['pluralName'].' 
							WHERE parentRecurringID = :recurringID
						) OR appliesToID = :appliesToID)');
					$sth->execute([':appliesToType' => $appliesToType, ':recurringID' => $row['recurringID'], ':appliesToID' => $data['subID']]);
					
					//delete item and children (if any)
					$sth = $dbh->prepare(
						'DELETE FROM orders_'.$TYPES[$data['subType']]['pluralName'].'
						WHERE order'.$TYPES[$data['subType']]['formalName'].'ID = :uniqueID OR parentRecurringID = :recurringID');
					$sth->execute([':uniqueID' => $data['subID'], ':recurringID' => $row['recurringID']]);
					
					$changeData = ['subType' => $data['subType'], 'subID' => $row['id'], 'unitPrice' => $row['unitPrice'], 'quantity' => $row['quantity'], 'recurring' => $recurring];
				}
				elseif ($data['subType'] == 'discount') {
					$sth = $dbh->prepare(
						'SELECT discountID, appliesToType, appliesToID
						FROM orders_discounts
						WHERE orderDiscountID = :orderDiscountID');
					$sth->execute([':orderDiscountID' => $data['subID']]);
					$row = $sth->fetch();
					if ($row['appliesToType'] == 'O') {
						$subType = 'discountOrder';
					}
					elseif ($row['appliesToType'] == 'P') {
						$subType = 'discountProduct';
					}
					elseif ($row['appliesToType'] == 'S') {
						$subType = 'discountService';
					}
					
					$sth = $dbh->prepare(
						'DELETE FROM orders_discounts
						WHERE orderDiscountID = :orderDiscountID');
					$sth->execute([':orderDiscountID' => $data['subID']]);
					
					$changeData = ['subType' => $subType, 'subID' => $row['appliesToID'], 'discountID' => $row['discountID']];
				}
				
				self::updateAmountDue($id);
				addChange('order', $id, $_SESSION['employeeID'], 'D', json_encode($changeData));
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
						<div class="btnSpacer">
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
											<option value="service">Service</option>
											<option value="preDiscount">Discount</option>
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
						<div class="btnSpacer">
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
										<label for="quantity">Quantity</label>
										<input type="text" name="quantity" autocomplete="off" value="">
									</li>
								</ul>
							</div>
						</section>
						<div class="btnSpacer">
							<button id="customBtn3">Edit</button>
						</div>
					</div>
				</div>
			</div>';
			return $return;
		}
		
		public function generatePDF($id, $pdfID) {
			global $dbh;
			global $SETTINGS;
			$filename = 'Default';
			$html = '';
		
			if ($pdfID == 1) {
				//TODO: make this look a bit nicer
				//TODO: padding doesn't work in tables in tcpdf, another option might be dompdf (https://github.com/dompdf/dompdf)
				$filename = 'Invoice';
				$html = '<body style="font-size:1em;">
					<div style="width:640px; margin:0 auto;">
					<span style="font-weight:bold; font-size:1.5em;">'.$SETTINGS['companyName'].'</span>
					<div style="border-bottom: 2px solid #E5E5E5; margin:5px 0;">&nbsp;</div><br>
					Thank you for your order.  Your invoice is below.<br><br>';
					//get order info
					$sth = $dbh->prepare(
						'SELECT date
						FROM orders
						WHERE orderID = :orderID');
					$sth->execute([':orderID' => $id]);
					$row = $sth->fetch();
					$html .= 
						'<b>Order ID:</b> '.$id.'<br>
						<b>Order Date:</b> '.formatDate($row['date']).'<br><br>
						<table style="width:100%;">
							<thead>
								<tr style="font-weight:bold;">
									<th style="width:40%;">Item</th>
									<th style="text-align:center; width:20%;">Quantity</th>
									<th style="text-align:center; width:20%;">Unit Price</th>
									<th style="text-align:right; width:20%;">Item Total</th>
								</tr>
							</thead>
							<tbody>';
								//get line items
								$lineItemTable = self::getLineItemTable($id);
								
								foreach ($lineItemTable[0] as $line) {
									if ($line['type'] == 'service' || $line['type'] == 'product') {
										$recurringStr = (!is_null($line['recurring'])) ? ' (occurs monthly on day '.$line['recurring'][0].' from '.formatDate($line['recurring'][1]).' to '.formatDate($line['recurring'][2]).')' : '';
										$dateStr = (isset($line['date'])) ? formatDate($line['date']).': ' : '';
										$html .= '<tr><td style="width:40%;">'.$dateStr.$line['name'].$recurringStr.'</td>';
										$html .= '<td style="text-align:center; width:20%;">'.formatNumber($line['quantity']).'</td>';
										$html .= '<td style="text-align:center; width:20%;">'.formatCurrency($line['unitPrice']).'</td>';
										$html .= '<td style="text-align:right; width:20%;">'.formatCurrency($line['lineAmount']).'</td></tr>';
									}
									elseif ($line['type'] == 'discount') {
										$unitPrice = ($line['discountType'] == 'C') ? formatCurrency(-$line['discountAmount']) : $line['discountAmount'].'%';
										$html .= '<tr><td>Discount: '.$line['name'].'</td><td></td>';
										$html .= '<td style="text-align:center;">'.$unitPrice.'</td>';
										$html .= '<td style="text-align:right;">'.formatCurrency(-$line['lineAmount']).'</td></tr>';
									}
								}
							$html .= '</tbody>
						</table>';
						//find amount paid
						$sth = $dbh->prepare(
							'SELECT SUM(paymentAmount)
							FROM orderPayments
							WHERE orderID = :orderID');
						$sth->execute([':orderID' => $id]);
						$row = $sth->fetch();
						$paidAmount = $row['SUM(paymentAmount)'];
						//print totals
						$html .= '<table style="width:100%; text-align:right;"><tbody>';
						$html .= '<tr><td>Total:</td><td>'.formatCurrency($lineItemTable[1]).'</td></tr>';
						$html .= '<tr><td>Amount Paid:</td><td>'.formatCurrency($paidAmount, true).'</td></tr>';
						$html .= '<tr style="font-weight: bold;"><td>Amount Due:</td><td>'.formatCurrency($lineItemTable[1] - $paidAmount, true).'</td></tr>';
						$html .= '</tbody></table>
					</div>
				</body>
				';
			}
			
			return [$filename, $html];
		}
		
		public static function editHook($id, $data) {
			global $dbh;
			
			if (isset($data['date'])) {
				//update the date on the associated subTypes
				$sth = $dbh->prepare(
					'UPDATE orders_products
					SET date = :date
					WHERE orderID = :orderID AND recurringID IS NULL AND parentRecurringID IS NULL');
				$sth->execute([':orderID' => $id, ':date' => $data['date']]);
				
				$sth = $dbh->prepare(
					'UPDATE orders_services
					SET date = :date
					WHERE orderID = :orderID AND recurringID IS NULL AND parentRecurringID IS NULL');
				$sth->execute([':orderID' => $id, ':date' => $data['date']]);
			}
		}
		
		/* Local Helper Functions */
		
		private static function updateAmountDue($id) {
			global $dbh;
			$exactSubTotal = 0;
			
			//get discounts (ORDER BY clause is to ensure cash discounts are applied before percentage discounts(order matters!))
			$sth = $dbh->prepare(
				'SELECT appliesToType, appliesToID, discountType, discountAmount
				FROM orders_discounts
				WHERE orderID = :orderID
				ORDER BY discountType');
			$sth->execute([':orderID' => $id]);
			while ($row = $sth->fetch()) {
				if ($row['appliesToType'] == 'O') {
					$discounts['O'][] = [$row['discountType'], $row['discountAmount']];
				}
				else {
					$discounts[$row['appliesToType'].$row['appliesToID']][] = [$row['discountType'], $row['discountAmount']];
				}
			}
			
			//get services and products
			for ($i = 0; $i < 2; $i++) {
				if ($i == 0) {
					$uniqueIDName = 'orderServiceID';
					$table = 'orders_services';
					$shortType = 'S';
				}
				else {
					$uniqueIDName = 'orderProductID';
					$table = 'orders_products';
					$shortType = 'P';
				}
			
				$sth = $dbh->prepare(
					'SELECT '.$uniqueIDName.' AS uniqueID, quantity, unitPrice, lineAmount
					FROM '.$table.'
					WHERE orderID = :orderID AND recurringID IS NULL');
				$sth->execute([':orderID' => $id]);
				while ($row = $sth->fetch()) {
					$newLineAmount = $row['quantity'] * $row['unitPrice'];
					if (isset($discounts[$shortType.$row['uniqueID']])) {
						//apply any discounts to this item
						foreach ($discounts[$shortType.$row['uniqueID']] as $discount) {
							$discountAmount = ($discount[0] == 'P') ? $newLineAmount * ($discount[1] / 100) : $row['quantity'] * $discount[1];
							$newLineAmount -= $discountAmount;
						}
					}
					$exactSubTotal += $newLineAmount;
					$lines[$shortType.$row['uniqueID']] = [$row['lineAmount'], $newLineAmount];
				}
			}
			
			//apply order discounts
			if (isset($discounts['O'])) {
				$tempSubTotal = $exactSubTotal;
				foreach ($discounts['O'] as $discount) {
					//apply order discount directly to the order to ensure accuracy
					$discountAmount = ($discount[0] == 'P') ? $exactSubTotal * ($discount[1] / 100) : $discount[1];
					$exactSubTotal -= $discountAmount;
					
					//then apply the order discount to each line individually
					foreach ($lines as $key => $line) {
						$discountAmount = ($discount[0] == 'P') ? $line[1] * ($discount[1] / 100) : $line[1] * ($discount[1] / $tempSubTotal);
						$lines[$key][1] -= $discountAmount;
					}
				}
			}
			
			//round each line to get the value that we're going to store in the db, and find out what that adds up to
			//also round the $exactSubTotal just in case
			$lineSubTotal = 0;
			foreach ($lines as $key => $line) {
				$lines[$key][1] = round($line[1], 2);
				$lineSubTotal += $lines[$key][1];
			}
			$exactSubTotal = round($exactSubTotal, 2);
			
			//if the lines don't add up to the real subTotal, add or subtract $0.01 from each line until it does
			if ($lineSubTotal != $exactSubTotal) {
				$diff = round($lineSubTotal - $exactSubTotal, 2);
				$i = 0;
				$keys = array_keys($lines);
				$count = count($lines);
				while ($diff != 0) {
					if ($diff > 0) {
						$lines[$keys[$i]][1] -= 0.01;
						$diff -= 0.01;
					}
					else {
						$lines[$keys[$i]][1] += 0.01;
						$diff -= 0.01;
					}
					$i++;
					if ($i = $count) {
						$i = 0;
					}
				}
			}
			
			//update lineAmounts as needed
			foreach ($lines as $key => $line) {
				if ($line[0] != $line[1]) {
					$itemType = substr($key, 0, 1);
					$uniqueID = substr($key, 1);
					$table = ($itemType == 'S') ? 'orders_services' : 'orders_products';
					$uniqueIDName = ($itemType == 'S') ? 'orderServiceID' : 'orderProductID';
				
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
				FROM orderPayments
				WHERE orderID = :orderID');
			$sth->execute([':orderID' => $id]);
			$row = $sth->fetch();
			
			//update amountDue
			$amountDue = $exactSubTotal - $row['paymentAmount'];
			$sth = $dbh->prepare(
				'UPDATE orders
				SET amountDue = :amountDue
				WHERE orderID = :orderID');
			$sth->execute([':amountDue' => $amountDue, ':orderID' => $id]);
		}
		
		private function getLineItemTable($id) {
			//returns: [[array of lines where fields are determined by the type field], subTotal]
			global $dbh;
			$subTotal = 0;
			$lines = [];
		
			//get discounts (ORDER BY clause is to ensure cash discounts are applied before percentage discounts(order matters!))
			$sth = $dbh->prepare(
				'SELECT orderDiscountID, discounts.discountID, name, orders_discounts.discountType, orders_discounts.discountAmount, appliesToType, appliesToID
				FROM discounts, orders_discounts
				WHERE orderID = :orderID AND discounts.discountID = orders_discounts.discountID
				ORDER BY orders_discounts.discountType');
			$sth->execute([':orderID' => $id]);
			while ($row = $sth->fetch()) {
				if ($row['appliesToType'] == 'O') {
					$discounts['O'][] = [$row['orderDiscountID'], $row['discountID'], htmlspecialchars($row['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $row['discountType'], $row['discountAmount']];
				}
				else {
					$discounts[$row['appliesToType'].$row['appliesToID']][] = [$row['orderDiscountID'], $row['discountID'], htmlspecialchars($row['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $row['discountType'], $row['discountAmount']];
				}
			}
			
			for ($i = 0; $i < 2; $i++) {
				if ($i == 0) {
					$uniqueIDName = 'orderServiceID';
					$idName = 'serviceID';
					$table = 'services';
					$table2 = 'orders_services';
					$type = 'service';
					$shortType = 'S';
				}
				else {
					$uniqueIDName = 'orderProductID';
					$idName = 'productID';
					$table = 'products';
					$table2 = 'orders_products';
					$type = 'product';
					$shortType = 'P';
				}
				//get children of recurring rows
				$sth = $dbh->prepare(
					'SELECT '.$uniqueIDName.' AS uniqueID, '.$table.'.'.$idName.' AS id, name, date, unitPrice, quantity, parentRecurringID
					FROM '.$table.', '.$table2.'
					WHERE orderID = :orderID AND '.$table.'.'.$idName.' = '.$table2.'.'.$idName.' AND parentRecurringID IS NOT NULL');
				$sth->execute([':orderID' => $id]);
				while ($row = $sth->fetch()) {
					$children[$shortType.$row['parentRecurringID']][] = [$row['uniqueID'], $row['id'], htmlspecialchars($row['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $row['date'], $row['unitPrice'], $row['quantity']];
				}
				
				//get items
				$sth = $dbh->prepare(
					'SELECT '.$uniqueIDName.' AS uniqueID, '.$table.'.'.$idName.' AS id, name, unitPrice, quantity, recurringID, dayOfMonth, startDate, endDate
					FROM '.$table.', '.$table2.'
					WHERE orderID = :orderID AND '.$table.'.'.$idName.' = '.$table2.'.'.$idName.' AND parentRecurringID IS NULL');
				$sth->execute([':orderID' => $id]);
				while ($row = $sth->fetch()) {
					if (!is_null($row['recurringID'])) {
						$recurring = [$row['dayOfMonth'], $row['startDate'], $row['endDate']];
						$lineAmount = '';
					}
					else {
						$recurring = null;
						$lineAmount = $row['quantity'] * $row['unitPrice'];
						$subTotal += $lineAmount;
						
					}
					$lines[] = [
						'type' => $type,
						'uniqueID' => $row['uniqueID'],
						'id' => $row['id'],
						'name' => htmlspecialchars($row['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
						'unitPrice' => $row['unitPrice'],
						'quantity' => ($row['quantity'] + 0),
						'lineAmount' => $lineAmount,
						'recurring' => $recurring,
						'indent' => 0
					];
					
					//apply any discounts to this item
					if (isset($discounts[$shortType.$row['uniqueID']])) {
						foreach ($discounts[$shortType.$row['uniqueID']] as $discount) {
							if (!is_null($row['recurringID'])) {
								$discountAmount = '';
							}
							else {
								$discountAmount = ($discount[3] == 'P') ? $lineAmount * ($discount[4] / 100) : $row['quantity'] * $discount[4];
								$subTotal -= $discountAmount;
							}
							$lines[] = [
								'type' => 'discount',
								'uniqueID' => $discount[0],
								'id' => $discount[1],
								'name' => $discount[2],
								'discountType' => $discount[3],
								'discountAmount' => $discount[4],
								'lineAmount' => $discountAmount,
								'indent' => 1
							];
						}
					}
					
					//get child recurring rows if this is a parent recurring row
					if (isset($children[$shortType.$row['recurringID']])) {
						foreach ($children[$shortType.$row['recurringID']] as $child) {
							$lineAmount = $child[4] * $child[5];
							$subTotal += $lineAmount;
							$lines[] = [
								'type' => $type,
								'uniqueID' => $child[0],
								'id' => $child[1],
								'name' => $child[2],
								'date' => $child[3],
								'unitPrice' => $child[4],
								'quantity' => ($child[5] + 0),
								'lineAmount' => $lineAmount,
								'recurring' => null,
								'indent' => 1
							];
					
							//apply discounts to child recurring rows
							if (isset($discounts[$shortType.$child[0]])) {
								foreach ($discounts[$shortType.$child[0]] as $discount) {
									$discountAmount = ($discount[3] == 'P') ? $lineAmount * ($discount[4] / 100) : $row['quantity'] * $discount[4];
									$subTotal -= $discountAmount;
									$lines[] = [
										'type' => 'discount',
										'uniqueID' => $discount[0],
										'id' => $discount[1],
										'name' => $discount[2],
										'discountType' => $discount[3],
										'discountAmount' => $discount[4],
										'lineAmount' => $discountAmount,
										'indent' => 2
									];
								}
							}
						}
					}
				}
			}
			
			//apply order discounts
			if (isset($discounts['O'])) {
				foreach ($discounts['O'] as $discount) {
					$discountAmount = ($discount[3] == 'P') ? ($subTotal) * ($discount[4] / 100) : $discount[4];
					$subTotal -= $discountAmount;
					$lines[] = [
						'type' => 'discount',
						'uniqueID' => $discount[0],
						'id' => $discount[1],
						'name' => $discount[2],
						'discountType' => $discount[3],
						'discountAmount' => $discount[4],
						'lineAmount' => $discountAmount,
						'indent' => 0
					];
				}
			}
			
			//round $subTotal just in case
			return [$lines, round($subTotal, 2)];
		}
	}
?>