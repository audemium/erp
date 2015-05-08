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
						<thead style="font-weight:bold;">
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
								if ($line[0] == 'service' || $line[0] == 'product') {
									$return .= '<tr><td><a href="item.php?type='.$line[0].'&id='.$line[1].'">'.$line[2].'</a></td>';
									$return .= '<td class="textCenter">'.$line[3].'</td>';
									$return .= '<td class="textCenter">'.formatCurrency($line[4]).'</td>';
									$return .= '<td class="textRight">'.formatCurrency($line[5]).'</td>';
									$return .= '<td class="textCenter"><a class="controlEdit editEnabled" href="#" data-type="'.$line[0].'" data-id="'.$line[1].'" data-quantity="'.$line[3].'"></a></td>';
									$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="'.$line[0].'" data-id="'.$line[1].'"></a></td></tr>';
								}
								elseif ($line[0] == 'discount') {
									$padding = ($line[3] == 'S' || $line[3] == 'P') ? '<td style="padding-left: 50px;">' : '<td>';
									$return .= '<tr>'.$padding.'Discount: <a href="item.php?type=discount&id='.$line[1].'">'.$line[2].'</a></td><td></td><td></td>';
									$return .= '<td class="textRight">-'.formatCurrency($line[5]).'</td><td></td>';
									$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="discount" data-id="'.$line[1].'" data-appliestotype="'.$line[3].'" data-appliestoid="'.$line[4].'"></a></td></tr>';
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
						<thead style="font-weight:bold;">
							<tr>
								<th>Payment Number</th>
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
								$return .= '<tr><td>'.$row['paymentID'].'</td>';
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
		
		public function parseValue($type, $field, $value) {
			switch ($field) {
				case 'amountDue':
					$parsed = formatCurrency($value);
					break;
				default:
					$parsed = $value;
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
						'SELECT CONCAT("P", products.productID), name
						FROM orders_products, products
						WHERE orderID = :orderID AND orders_products.productID = products.productID
						UNION
						SELECT CONCAT("S", services.serviceID), name
						FROM orders_services, services
						WHERE orderID = :orderID AND orders_services.serviceID = services.serviceID');
					$sth->execute([':orderID' => $id]);
					$return['options'][] = ['value' => 'O', 'text' => 'Order'];	
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
				}
				while ($row = $sth->fetch()) {
					if ($data['itemType'] != 'discount' || !in_array($row[0], $discounts)) {
						$return['options'][] = ['value' => $row[0], 'text' => $row[1]];
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
						'paymentType' => ['verifyData' => [1, 'opt', ['CA', 'CH', 'CR']]],
						'paymentAmount' => ['verifyData' => [1, 'dec', [12, 2]]]
					];
				}
				elseif ($subType == 'product') {
					$fields = [
						'itemID' => ['verifyData' => [1, 'id', 'product']],
						'quantity' => ['verifyData' => [1, 'int', 4294967295]]
					];
				}
				elseif ($subType == 'service') {
					$fields = [
						'itemID' => ['verifyData' => [1, 'id', 'service']],
						'quantity' => ['verifyData' => [1, 'dec', [12, 2]]]
					];
				}
				elseif ($subType == 'discount') {
					//for discounts, itemID contains a one letter indication of the type of item (O = order, P = product, S = service), and the itemID
					$itemType = substr($data['itemID'], 0, 1);
					$data['itemID'] = substr($data['itemID'], 1);
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
						$sth = $dbh->prepare(
							'INSERT INTO orderPayments (orderID, paymentType, paymentAmount)
							VALUES(:orderID, :paymentType, :paymentAmount)');
						$sth->execute([':orderID' => $id, ':paymentType' => $data['paymentType'], ':paymentAmount' => $data['paymentAmount']]);
						$changeData = ['type' => 'payment', 'id' => $dbh->lastInsertId(), 'paymentType' => $data['paymentType'], 'paymentAmount' => $data['paymentAmount']];
					}
					elseif ($subType == 'product' || $subType == 'service') {
						$sth = $dbh->prepare(
							'SELECT quantity
							FROM orders_'.$TYPES[$subType]['pluralName'].'
							WHERE orderID = :orderID AND '.$TYPES[$subType]['idName'].' = :itemID');
						$sth->execute([':orderID' => $id, ':itemID' => $data['itemID']]);
						$result = $sth->fetchAll();
						if (count($result) == 1) {
							//if the product or service is already present in the order, add the quantity to the existing row
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
							$sth = $dbh->prepare(
								'INSERT INTO orders_'.$TYPES[$subType]['pluralName'].' (orderID, '.$TYPES[$subType]['idName'].', unitPrice, quantity)
								VALUES(:orderID, :itemID, :unitPrice, :quantity)');
							$sth->execute([':orderID' => $id, ':itemID' => $data['itemID'], ':unitPrice' => $row['defaultPrice'], ':quantity' => $data['quantity']]);
							$changeData = ['type' => $subType, 'id' => $data['itemID'], 'unitPrice' => $row['defaultPrice'], 'quantity' => $data['quantity']];
						}
					}
					elseif ($subType == 'discount') {
						//get discountType and discountAmount
						$sth = $dbh->prepare(
							'SELECT discountType, discountAmount FROM discounts
							WHERE discountID = :discountID');
						$sth->execute([':discountID' => $data['discountID']]);
						$row = $sth->fetch();
						$discountType = $row['discountType'];
						$discountAmount = $row['discountAmount'];
						
						$sth = $dbh->prepare(
							'INSERT INTO orders_discounts (orderID, discountID, appliesToType, appliesToID, discountType, discountAmount)
							VALUES(:orderID, :discountID, :appliesToType, :appliesToID, :discountType, :discountAmount)');
						$sth->execute([':orderID' => $id, ':discountID' => $data['discountID'], ':appliesToType' => $itemType, ':appliesToID' => $data['itemID'], ':discountType' => $discountType, ':discountAmount' => $discountAmount]);
						$changeData = ['type' => 'discount', 'id' => $data['discountID'], 'appliesToType' => $itemType, 'appliesToID' => $data['itemID'], 'discountType' => $discountType, 'discountAmount' => $discountAmount];
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
						WHERE orderID = :orderID AND '.$TYPES[$subType]['idName'].' = :subID');
					$sth->execute([':quantity' => $data['quantity'], ':orderID' => $id, ':subID' => $data['subID']]);
					
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
						'DELETE FROM orders_'.$TYPES[$data['subType']]['pluralName'].'
						WHERE orderID = :orderID AND '.$TYPES[$data['subType']]['idName'].' = :itemID');
					$sth->execute([':orderID' => $id, ':itemID' => $data['subID']]);
					$sth = $dbh->prepare(
						'DELETE FROM orders_discounts
						WHERE orderID = :orderID AND appliesToType = :appliesToType AND appliesToID = :appliesToID');
					$sth->execute([':orderID' => $id, ':appliesToType' => $appliesToType, ':appliesToID' => $data['subID']]);
					$changeData = ['type' => $data['subType'], 'id' => $data['subID']];
				}
				elseif ($data['subType'] == 'discount') {
					$sth = $dbh->prepare(
						'DELETE FROM orders_discounts
						WHERE orderID = :orderID AND discountID = :discountID AND appliesToType = :appliesToType AND appliesToID = :appliesToID');
					$sth->execute([':orderID' => $id, ':discountID' => $data['subID'], ':appliesToType' => $data['appliesToType'], ':appliesToID' => $data['appliesToID']]);
					$changeData = ['type' => 'discount', 'id' => $data['subID'], 'appliesToType' => $data['appliesToType'], 'appliesToID' => $data['appliesToID']];
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
								</ul>
								<ul>
									<li>
										<label for="paymentAmount">Amount</label>
										<input type="text" autocomplete="off" name="paymentAmount">
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
									if ($line[0] == 'service' || $line[0] == 'product') {
										$html .= '<tr><td style="width:40%;">'.$line[2].'</td>';
										$html .= '<td style="text-align:center; width:20%;">'.$line[3].'</td>';
										$html .= '<td style="text-align:center; width:20%;">'.formatCurrency($line[4]).'</td>';
										$html .= '<td style="text-align:right; width:20%;">'.formatCurrency($line[5]).'</td></tr>';
									}
									elseif ($line[0] == 'discount') {
										$padding = ($line[3] == 'S' || $line[3] == 'P') ? '<td style="padding-left: 50px;">' : '<td>';
										$html .= '<tr>'.$padding.'Discount: '.$line[2].'</td><td></td><td></td>';
										$html .= '<td style="text-align:right;">-'.formatCurrency($line[5]).'</td></tr>';
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
			$discounts = ['S' => [], 'P' => [], 'O' => []];
			$sth = $dbh->prepare(
				'SELECT discounts.discountID, name, orders_discounts.discountType, orders_discounts.discountAmount, appliesToType, appliesToID
				FROM discounts, orders_discounts
				WHERE orderID = :orderID AND discounts.discountID = orders_discounts.discountID
				ORDER BY orders_discounts.discountType');
			$sth->execute([':orderID' => $id]);
			while ($row = $sth->fetch()) {
				if ($row['appliesToType'] == 'O') {
					$discounts['O'][] = [$row['discountID'], $row['name'], $row['discountType'], $row['discountAmount']];
				}
				else {
					$discounts[$row['appliesToType']][$row['appliesToID']][] = [$row['discountID'], $row['name'], $row['discountType'], $row['discountAmount']];
				}
			}
			
			//get services
			$sth = $dbh->prepare(
				'SELECT services.serviceID, quantity, unitPrice
				FROM services, orders_services
				WHERE orderID = :orderID AND services.serviceID = orders_services.serviceID');
			$sth->execute([':orderID' => $id]);
			while ($row = $sth->fetch()) {
				$lineAmount = $row['quantity'] * $row['unitPrice'];
				$subTotal += $lineAmount;
				
				//apply any discounts to this item
				if (count($discounts['S']) > 0) {
					foreach ($discounts['S'][$row['serviceID']] as $discount) {
						$discountAmount = ($discount[2] == 'P') ? $lineAmount * ($discount[3] / 100) : $row['quantity'] * $discount[3];
						$lineAmount -= $discountAmount;
						$subTotal -= $discountAmount;
					}
				}
			}
			
			//get products
			$sth = $dbh->prepare(
				'SELECT products.productID, quantity, unitPrice
				FROM products, orders_products
				WHERE orderID = :orderID AND products.productID = orders_products.productID');
			$sth->execute([':orderID' => $id]);
			while ($row = $sth->fetch()) {
				$lineAmount = $row['quantity'] * $row['unitPrice'];
				$subTotal += $lineAmount;
				
				//apply any discounts to this item
				if (count($discounts['P']) > 0) {
					foreach ($discounts['P'][$row['productID']] as $discount) {
						$discountAmount = ($discount[2] == 'P') ? $lineAmount * ($discount[3] / 100) : $row['quantity'] * $discount[3];
						$lineAmount -= $discountAmount;
						$subTotal -= $discountAmount;
					}
				}
			}
			
			//apply order discounts
			if (count($discounts['O']) > 0) {
				foreach ($discounts['O'] as $discount) {
					$discountAmount = ($discount[2] == 'P') ? ($subTotal) * ($discount[3] / 100) : $discount[3];
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
			//returns: [[type (product, service, discount), id, name, quantity OR appliesToType (P, S, O), unitPrice OR appliesToID, lineTotal], subTotal]
			global $dbh;
			$subTotal = 0;
			$lines = [];
		
			//get discounts (ORDER BY clause is to ensure cash discounts are applied before percentage discounts(order matters!))
			$discounts = ['S' => [], 'P' => [], 'O' => []];
			$sth = $dbh->prepare(
				'SELECT discounts.discountID, name, orders_discounts.discountType, orders_discounts.discountAmount, appliesToType, appliesToID
				FROM discounts, orders_discounts
				WHERE orderID = :orderID AND discounts.discountID = orders_discounts.discountID
				ORDER BY orders_discounts.discountType');
			$sth->execute([':orderID' => $id]);
			while ($row = $sth->fetch()) {
				if ($row['appliesToType'] == 'O') {
					$discounts['O'][] = [$row['discountID'], $row['name'], $row['discountType'], $row['discountAmount']];
				}
				else {
					$discounts[$row['appliesToType']][$row['appliesToID']][] = [$row['discountID'], $row['name'], $row['discountType'], $row['discountAmount']];
				}
			}
			
			//get services
			$sth = $dbh->prepare(
				'SELECT services.serviceID, name, quantity, unitPrice
				FROM services, orders_services
				WHERE orderID = :orderID AND services.serviceID = orders_services.serviceID');
			$sth->execute([':orderID' => $id]);
			while ($row = $sth->fetch()) {
				$lineAmount = $row['quantity'] * $row['unitPrice'];
				$subTotal += $lineAmount;
				$lines[] = ['service', $row['serviceID'], $row['name'], ($row['quantity'] + 0), $row['unitPrice'], $lineAmount];
				
				//apply any discounts to this item
				if (count($discounts['S']) > 0) {
					foreach ($discounts['S'][$row['serviceID']] as $discount) {
						$discountAmount = ($discount[2] == 'P') ? $lineAmount * ($discount[3] / 100) : $row['quantity'] * $discount[3];
						$subTotal -= $discountAmount;
						$lines[] = ['discount', $discount[0], $discount[1], 'S', $row['serviceID'], $discountAmount];
					}
				}
			}
			
			//get products
			$sth = $dbh->prepare(
				'SELECT products.productID, name, quantity, unitPrice
				FROM products, orders_products
				WHERE orderID = :orderID AND products.productID = orders_products.productID');
			$sth->execute([':orderID' => $id]);
			while ($row = $sth->fetch()) {
				$lineAmount = $row['quantity'] * $row['unitPrice'];
				$subTotal += $lineAmount;
				$lines[] = ['product', $row['productID'], $row['name'], ($row['quantity'] + 0), $row['unitPrice'], $lineAmount];
				
				//apply any discounts to this item
				if (count($discounts['P']) > 0) {
					foreach ($discounts['P'][$row['productID']] as $discount) {
						$discountAmount = ($discount[2] == 'P') ? $lineAmount * ($discount[3] / 100) : $row['quantity'] * $discount[3];
						$subTotal -= $discountAmount;
						$lines[] = ['discount', $discount[0], $discount[1], 'P', $row['productID'], $discountAmount];
					}
				}
			}
			
			//apply order discounts
			if (count($discounts['O']) > 0) {
				foreach ($discounts['O'] as $discount) {
					$discountAmount = ($discount[2] == 'P') ? ($subTotal) * ($discount[3] / 100) : $discount[3];
					$subTotal -= $discountAmount;
					$lines[] = ['discount', $discount[0], $discount[1], 'O', 0, $discountAmount];
				}
			}
			
			return [$lines, $subTotal];
		}
	}
?>