<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
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
								<th>Item</th>
								<th class="textCenter">Quantity</th>
								<th class="textCenter">Unit Price</th>
								<th class="textRight">Item Total</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>';
							$lineItemTable = self::getLineItemTable($id);
							
							foreach ($lineItemTable[0] as $line) {
								$padding = 8 + (50 * $line['indent']);
								if ($line['type'] == 'service' || $line['type'] == 'product') {
									if (!is_null($line['recurring'])) {
										$recurringStr = ' (occurs monthly on day '.$line['recurring'][0].' from '.formatDate($line['recurring'][1]).' to '.formatDate($line['recurring'][2]).')';
										$editStr = '';
									}
									else {
										$recurringStr = '';
										$editStr = '<a class="controlEdit editEnabled" href="#" data-type="'.$line['type'].'" data-id="'.$line['uniqueID'].'" data-quantity="'.$line['quantity'].'"></a>';
									}
									$dateStr = (isset($line['date'])) ? formatDate($line['date']).': ' : '';
									$return .= '<tr><td style="padding-left: '.$padding.'px;">'.$dateStr.'<a href="item.php?type='.$line['type'].'&id='.$line['id'].'">'.$line['name'].'</a>'.$recurringStr.'</td>';
									$return .= '<td class="textCenter">'.$line['quantity'].'</td>';
									$return .= '<td class="textCenter">&nbsp;'.formatCurrency($line['unitPrice']).'</td>';   //added a space here to line up unit prices with the negative unit prices on discounts
									$return .= '<td class="textRight">'.formatCurrency($line['lineAmount']).'</td>';
									$return .= '<td class="textCenter">'.$editStr.'</td>';
									$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="'.$line['type'].'" data-id="'.$line['uniqueID'].'"></a></td></tr>';
								}
								elseif ($line['type'] == 'discount') {
									$unitPrice = ($line['discountType'] == 'C') ? formatCurrency(-$line['discountAmount']) : $line['discountAmount'].'%';
									$return .= '<tr><td style="padding-left: '.$padding.'px;">Discount: <a href="item.php?type=discount&id='.$line['id'].'">'.$line['name'].'</a></td><td></td>';
									$return .= '<td class="textCenter">'.$unitPrice.'</td>';
									$return .= '<td class="textRight">'.formatCurrency(-$line['lineAmount']).'</td><td></td>';
									$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="discount" data-id="'.$line['uniqueID'].'"></a></td></tr>';
								}
							}
							
							$return .= '<tr style="font-weight: bold;"><td>Total:</td><td></td><td></td><td class="textRight">'.formatCurrency($lineItemTable[1]).'</td><td></td><td></td></tr>';
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
								FROM orderPayments
								WHERE orderID = :orderID');
							$sth->execute([':orderID' => $id]);
							while ($row = $sth->fetch()) {
								$paymentType = '';
								if ($row['paymentType'] == 'CA') {
									$paymentType = 'Cash';
								}
								elseif ($row['paymentType'] == 'CH') {
									$paymentType = 'Check';
								}
								elseif ($row['paymentType'] == 'CR') {
									$paymentType = 'Credit Card';
								}
								$subTotal += $row['paymentAmount'];
								$return .= '<tr><td>'.formatDate($row['date']).'</td>';
								$return .= '<td class="textCenter">'.$paymentType.'</td>';
								$return .= '<td class="textRight">'.formatCurrency($row['paymentAmount']).'</td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="payment" data-id="'.$row['paymentID'].'"></a></td></tr>';
							}
							$return .= '<tr style="font-weight: bold;"><td>Total:</td><td></td><td class="textRight">'.formatCurrency($subTotal).'</td><td></td></tr>';
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
					case 'amountDue':
						$parsed[$field] = formatCurrency($value);
						break;
					default:
						$parsed[$field] = $value;
				}
			}
			
			return $parsed;
		}
		
		public function customAjax($id, $data) {
			global $dbh;
			global $TYPES;
			$return = ['status' => 'success'];
			
			if ($data['subAction'] == 'list') {
				//list subAction
				$return['options'] = [];
				if ($data['itemType'] == 'preDiscount') {
					//list all products and services on the order, plus an order line
					$sth = $dbh->prepare(
						'SELECT CONCAT("P", orderProductID), name, recurringID, date
						FROM products, orders_products
						WHERE orderID = 1 AND products.productID = orders_products.productID
						UNION
						SELECT CONCAT("S", orderServiceID), name, recurringID, date
						FROM services, orders_services
						WHERE orderID = 1 AND services.serviceID = orders_services.serviceID');
					$sth->execute([':orderID' => $id]);
					$return['options'][] = ['value' => 'O', 'text' => 'Order'];
					while ($row = $sth->fetch()) {
						if ($row[2] !== null) {
							$text = 'Recurring: '.$row[1];
						}
						elseif ($row[3] !== null) {
							$text = formatDate($row[3]).': '.$row[1];
						}
						else {
							$text = $row[1];
						}
						$return['options'][] = ['value' => $row[0], 'text' => htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')];
					}
				}
				else {
					$discounts = [];
					if ($data['itemType'] == 'discount') {
						//get the list of discounts applied to the item already so we don't apply the same discount twice to the same item
						$temp = [substr($data['itemID'], 0, 1), substr($data['itemID'], 1)];
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
						'SELECT '.$TYPES[$data['itemType']]['idName'].', name
						FROM '.$TYPES[$data['itemType']]['pluralName'].'
						WHERE active = 1');
					$sth->execute();
					while ($row = $sth->fetch()) {
						if ($data['itemType'] != 'discount' || !in_array($row[0], $discounts)) {
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
				if ($subType == 'payment') {
					$fields = [
						'date' => ['verifyData' => [1, 'dateTime', '']],
						'paymentType' => ['verifyData' => [1, 'opt', ['CA', 'CH', 'CR']]],
						'paymentAmount' => ['verifyData' => [1, 'dec', [12, 2]]]
					];
				}
				elseif ($subType == 'product') {
					$required = ($data['recurring'] == 'yes') ? 1 : 0;
					$fields = [
						'itemID' => ['verifyData' => [1, 'id', 'product']],
						'quantity' => ['verifyData' => [1, 'int', 4294967295]],
						'recurring' => ['verifyData' => [1, 'opt', ['yes', 'no']]],
						'interval' => ['verifyData' => [$required, 'opt', ['monthly']]],
						'dayOfMonth' => ['verifyData' => [$required, 'opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]]],
						'startDate' => ['verifyData' => [$required, 'dateTime', 'start']],
						'endDate' => ['verifyData' => [$required, 'dateTime', 'end']]
					];
				}
				elseif ($subType == 'service') {
					$required = ($data['recurring'] == 'yes') ? 1 : 0;
					$fields = [
						'itemID' => ['verifyData' => [1, 'id', 'service']],
						'quantity' => ['verifyData' => [1, 'dec', [12, 2]]],
						'recurring' => ['verifyData' => [1, 'opt', ['yes', 'no']]],
						'interval' => ['verifyData' => [$required, 'opt', ['monthly']]],
						'dayOfMonth' => ['verifyData' => [$required, 'opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]]],
						'startDate' => ['verifyData' => [$required, 'dateTime', 'start']],
						'endDate' => ['verifyData' => [$required, 'dateTime', 'end']]
					];
				}
				elseif ($subType == 'discount') {
					//for discounts, itemID contains a one letter indication of the type of item (O = order, P = product, S = service), and the unique itemID
					$itemType = substr($data['itemID'], 0, 1);
					$uniqueID = substr($data['itemID'], 1);
					if ($itemType == 'O') {
						$itemTypeFull = 'order';
					}
					elseif ($itemType == 'P') {
						$itemTypeFull = 'product';
					}
					elseif ($itemType == 'S') {
						$itemTypeFull = 'service';
					}
					
					if ($itemType != 'O') {
						$sth = $dbh->prepare(
							'SELECT '.$TYPES[$itemTypeFull]['idName'].'
							FROM orders_'.$TYPES[$itemTypeFull]['pluralName'].'
							WHERE order'.$TYPES[$itemTypeFull]['formalName'].'ID = :uniqueID');
						$sth->execute([':uniqueID' => $uniqueID]);
						$row = $sth->fetch();
						$data['itemID'] = $row[0];
					}
					else {
						$data['itemID'] = $uniqueID;
					}
					if ($itemType == 'O') {
						$fields = [
							'itemID' => ['verifyData' => [0, 'int', 0]],
							'discountID' => ['verifyData' => [1, 'id', 'discount']]
						];
					}
					elseif ($itemType == 'P') {
						$fields = [
							'itemID' => ['verifyData' => [1, 'id', 'product']],
							'discountID' => ['verifyData' => [1, 'id', 'discount']]
						];
					}
					elseif ($itemType == 'S') {
						$fields = [
							'itemID' => ['verifyData' => [1, 'id', 'service']],
							'discountID' => ['verifyData' => [1, 'id', 'discount']]
						];
					}
				}
				$return = verifyData(null, $data, $fields);
				
				if ($return['status'] != 'fail') {
					if ($subType == 'payment') {
						$date = strtotime($data['date']);
						$sth = $dbh->prepare(
							'INSERT INTO orderPayments (orderID, date, paymentType, paymentAmount)
							VALUES(:orderID, :date, :paymentType, :paymentAmount)');
						$sth->execute([':orderID' => $id, ':date' => $date, ':paymentType' => $data['paymentType'], ':paymentAmount' => $data['paymentAmount']]);
						$changeData = ['type' => 'payment', 'id' => $dbh->lastInsertId(), 'date' => $date, 'paymentType' => $data['paymentType'], 'paymentAmount' => $data['paymentAmount']];
					}
					elseif ($subType == 'product' || $subType == 'service') {
						$sth = $dbh->prepare(
							'SELECT quantity
							FROM orders_'.$TYPES[$subType]['pluralName'].'
							WHERE orderID = :orderID AND '.$TYPES[$subType]['idName'].' = :itemID');
						$sth->execute([':orderID' => $id, ':itemID' => $data['itemID']]);
						$result = $sth->fetchAll();
						if (count($result) == 1 && $data['recurring'] == 'no') {
							//if the product or service is already present in the expense AND we aren't doing a recurring item, add the quantity to the existing row
							$sth = $dbh->prepare(
								'UPDATE orders_'.$TYPES[$subType]['pluralName'].'
								SET quantity = :quantity
								WHERE orderID = :orderID AND '.$TYPES[$subType]['idName'].' = :itemID');
							$sth->execute([':quantity' => $data['quantity'] + $result[0]['quantity'], ':orderID' => $id, ':itemID' => $data['itemID']]);
							$changeData = ['type' => $subType, 'id' => $data['itemID'], 'quantity' => $data['quantity'] + $result[0]['quantity']];
						}
						else {
							//get defaultPrice
							$sth = $dbh->prepare(
								'SELECT defaultPrice
								FROM '.$TYPES[$subType]['pluralName'].'
								WHERE '.$TYPES[$subType]['idName'].' = :itemID');
							$sth->execute([':itemID' => $data['itemID']]);
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
									VALUES(:orderID, :itemID, :unitPrice, :quantity, :recurringID, :dayOfMonth, :startDate, :endDate)');
								$sth->execute([':orderID' => $id, ':itemID' => $data['itemID'], ':unitPrice' => $defaultPrice, ':quantity' => $data['quantity'], ':recurringID' => $recurringID, ':dayOfMonth' => $data['dayOfMonth'], ':startDate' => $startDate, ':endDate' => $endDate]);
								
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
											VALUES(:orderID, :itemID, :date, :unitPrice, :quantity, :parentRecurringID)');
										$sth->execute([':orderID' => $id, ':itemID' => $data['itemID'], ':date' => $timestamp, ':unitPrice' => $defaultPrice, ':quantity' => $data['quantity'], ':parentRecurringID' => $recurringID]);
									}
								}
								$changeData = ['type' => $subType, 'id' => $data['itemID'], 'unitPrice' => $defaultPrice, 'quantity' => $data['quantity'], 'startDate' => $data['startDate'], 'endDate' => $data['endDate']];
							}
							else {
								$sth = $dbh->prepare(
									'INSERT INTO orders_'.$TYPES[$subType]['pluralName'].' (orderID, '.$TYPES[$subType]['idName'].', unitPrice, quantity)
									VALUES(:orderID, :itemID, :unitPrice, :quantity)');
								$sth->execute([':orderID' => $id, ':itemID' => $data['itemID'], ':unitPrice' => $defaultPrice, ':quantity' => $data['quantity']]);
								$changeData = ['type' => $subType, 'id' => $data['itemID'], 'unitPrice' => $defaultPrice, 'quantity' => $data['quantity']];
							}
						}
					}
					elseif ($subType == 'discount') {
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
						$changeData = ['type' => 'discount', 'id' => $data['discountID'], 'appliesToType' => $itemType, 'appliesToID' => $uniqueID, 'discountType' => $discountType, 'discountAmount' => $discountAmount];
						
						//determine if the appliesTo item is a recurring item, and if so, add a discount to past recurrences if they don't already have this discount
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
					addChange('order', $id, $_SESSION['employeeID'], json_encode($changeData));
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
				if ($subType == 'product') {
					$fields = [
						'subID' => ['verifyData' => [1, 'id', 'product']],
						'quantity' => ['verifyData' => [1, 'int', 4294967295]]
					];
				}
				elseif ($subType == 'service') {
					$fields = [
						'subID' => ['verifyData' => [1, 'id', 'service']],
						'quantity' => ['verifyData' => [1, 'dec', [12, 2]]]
					];
				}
				$return = verifyData(null, $data, $fields);
				
				if ($return['status'] != 'fail') {
					$sth = $dbh->prepare(
						'UPDATE orders_'.$TYPES[$subType]['pluralName'].'
						SET quantity = :quantity
						WHERE order'.$TYPES[$subType]['formalName'].'ID = :uniqueID');
					$sth->execute([':quantity' => $data['quantity'], ':uniqueID' => $uniqueID]);
					
					self::updateAmountDue($id);
					addChange('order', $id, $_SESSION['employeeID'], json_encode(['type' => $subType, 'id' => $data['subID'], 'quantity' => $data['quantity']]));
				}
			}
			elseif ($data['subAction'] == 'delete') {
				//delete subAction
				if ($data['subType'] == 'payment') {
					$sth = $dbh->prepare(
						'DELETE FROM orderPayments
						WHERE paymentID = :paymentID');
					$sth->execute([':paymentID' => $data['subID']]);
					$changeData = ['type' => 'payment', 'id' => $data['subID']];
				}
				elseif ($data['subType'] == 'product' || $data['subType'] == 'service') {
					$appliesToType = ($data['subType'] == 'product') ? 'P' : 'S';
					$sth = $dbh->prepare(
						'SELECT recurringID 
						FROM orders_'.$TYPES[$data['subType']]['pluralName'].'
						WHERE order'.$TYPES[$data['subType']]['formalName'].'ID = :uniqueID');
					$sth->execute([':uniqueID' => $data['subID']]);
					$result = $sth->fetchAll();
					$recurringID = $result[0]['recurringID'];
					
					//delete item discounts and children's discounts (if any)
					$sth = $dbh->prepare(
						'DELETE FROM orders_discounts
						WHERE appliesToType = :appliesToType AND (appliesToID IN (
							SELECT order'.$TYPES[$data['subType']]['formalName'].'ID 
							FROM orders_'.$TYPES[$data['subType']]['pluralName'].' 
							WHERE parentRecurringID = :recurringID
						) OR appliesToID = :appliesToID)');
					$sth->execute([':appliesToType' => $appliesToType, ':recurringID' => $recurringID, ':appliesToID' => $data['subID']]);
					
					//item and children (if any)
					$sth = $dbh->prepare(
						'DELETE FROM orders_'.$TYPES[$data['subType']]['pluralName'].'
						WHERE order'.$TYPES[$data['subType']]['formalName'].'ID = :uniqueID OR parentRecurringID = :recurringID');
					$sth->execute([':uniqueID' => $data['subID'], ':recurringID' => $recurringID]);

					$changeData = ['type' => $data['subType'], 'id' => $data['subID']];
				}
				elseif ($data['subType'] == 'discount') {
					$sth = $dbh->prepare(
						'DELETE FROM orders_discounts
						WHERE orderDiscountID = :orderDiscountID');
					$sth->execute([':orderDiscountID' => $data['subID']]);
					$changeData = ['type' => 'discount', 'id' => $data['subID'],];
				}
				
				self::updateAmountDue($id);
				addChange('order', $id, $_SESSION['employeeID'], json_encode($changeData));
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
						'SELECT MIN(changeTime)
						FROM changes
						WHERE type = "order" AND id = :id');
					$sth->execute([':id' => $id]);
					$row = $sth->fetch();
					$html .= 
						'<b>Order ID:</b> '.$id.'<br>
						<b>Order Time:</b> '.formatDateTime($row['MIN(changeTime)']).'<br><br>
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
										$html .= '<td style="text-align:center; width:20%;">'.$line['quantity'].'</td>';
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
						$html .= '<tr><td>Amount Paid:</td><td>'.formatCurrency($paidAmount).'</td></tr>';
						$html .= '<tr style="font-weight: bold;"><td>Amount Due:</td><td>'.formatCurrency($lineItemTable[1] - $paidAmount).'</td></tr>';
						$html .= '</tbody></table>
					</div>
				</body>
				';
			}
			
			return [$filename, $html];
		}
		
		/* Local Helper Functions */
		
		private static function updateAmountDue($id) {
			global $dbh;
			$subTotal = 0;
			
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
					'SELECT '.$uniqueIDName.' AS uniqueID, quantity, unitPrice
					FROM '.$table.'
					WHERE orderID = :orderID AND recurringID IS NULL');
				$sth->execute([':orderID' => $id]);
				while ($row = $sth->fetch()) {
					$lineAmount = $row['quantity'] * $row['unitPrice'];
					$subTotal += $lineAmount;
					
					//apply any discounts to this item
					if (isset($discounts[$shortType.$row['uniqueID']])) {
						foreach ($discounts[$shortType.$row['uniqueID']] as $discount) {
							$discountAmount = ($discount[0] == 'P') ? $lineAmount * ($discount[1] / 100) : $row['quantity'] * $discount[1];
							$lineAmount -= $discountAmount;
							$subTotal -= $discountAmount;
						}
					}
				}
			}
			
			//apply order discounts
			if (isset($discounts['O'])) {
				foreach ($discounts['O'] as $discount) {
					$discountAmount = ($discount[0] == 'P') ? ($subTotal) * ($discount[1] / 100) : $discount[1];
					$subTotal -= $discountAmount;
				}
			}
			
			//get payments
			$sth = $dbh->prepare(
				'SELECT SUM(paymentAmount) AS paymentAmount
				FROM orderPayments
				WHERE orderID = :orderID');
			$sth->execute([':orderID' => $id]);
			$row = $sth->fetch();
			
			$amountDue = $subTotal - $row['paymentAmount'];
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
			
			return [$lines, $subTotal];
		}
	}
?>