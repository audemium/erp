<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Order extends GenericItem {
		public function printItemBody($id) {
			global $dbh;
			global $TYPES;
			
			//Line Items section
			$subTotal = 0;
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
							//get discounts
							$discounts = ['S' => [], 'P' => [], 'O' => []];
							$sth = $dbh->prepare(
								'SELECT discounts.discountID, name, discountType, discountAmount, appliesToType, appliesToID
								FROM discounts, orders_discounts
								WHERE orderID = :orderID AND discounts.discountID = orders_discounts.discountID');
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
								$return .= '<tr><td><a href="item.php?type=service&id='.$row['serviceID'].'">'.$row['name'].'</a></td>';
								$return .= '<td class="textCenter">'.($row['quantity'] + 0).'</td>'; //remove extra zeros and decimals
								$return .= '<td class="textCenter">'.formatCurrency($row['unitPrice']).'</td>';
								$lineAmount = $row['quantity'] * $row['unitPrice'];
								$subTotal += $lineAmount;
								$return .= '<td class="textRight">'.formatCurrency($lineAmount).'</td>';
								$return .= '<td class="textCenter"><a class="controlEdit editEnabled" href="#" data-type="service" data-id="'.$row['serviceID'].'" data-quantity="'.($row['quantity'] + 0).'"></a></td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="service" data-id="'.$row['serviceID'].'"></a></td></tr>';
								
								//apply any discounts to this item
								if (count($discounts['S']) > 0) {
									foreach ($discounts['S'][$row['serviceID']] as $discount) {
										$return .= '<tr><td style="padding-left: 50px;">Discount: <a href="item.php?type=discount&id='.$discount[0].'">'.$discount[1].'</a></td><td></td><td></td>';
										$discountAmount = ($discount[2] == 'P') ? $lineAmount * ($discount[3] / 100) : $row['quantity'] * $discount[3];
										$subTotal -= $discountAmount;
										$return .= '<td class="textRight">-'.formatCurrency($discountAmount).'</td>';
										$return .= '<td></td>';
										$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="discount" data-id="'.$discount[0].'" data-appliestotype="S" data-appliestoid="'.$row['serviceID'].'"></a></td></tr>';
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
								$return .= '<tr><td><a href="item.php?type=product&id='.$row['productID'].'">'.$row['name'].'</a></td>';
								$return .= '<td class="textCenter">'.($row['quantity'] + 0).'</td>'; //remove extra zeros and decimals
								$return .= '<td class="textCenter">'.formatCurrency($row['unitPrice']).'</td>';
								$lineAmount = $row['quantity'] * $row['unitPrice'];
								$subTotal += $lineAmount;
								$return .= '<td class="textRight">'.formatCurrency($lineAmount).'</td>';
								$return .= '<td class="textCenter"><a class="controlEdit editEnabled" href="#" data-type="product" data-id="'.$row['productID'].'" data-quantity="'.($row['quantity'] + 0).'"></a></td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="product" data-id="'.$row['productID'].'"></a></td></tr>';
								
								//apply any discounts to this item
								if (count($discounts['P']) > 0) {
									foreach ($discounts['P'][$row['productID']] as $discount) {
										$return .= '<tr><td style="padding-left: 50px;">Discount: <a href="item.php?type=discount&id='.$discount[0].'">'.$discount[1].'</a></td><td></td><td></td>';
										$discountAmount = ($discount[2] == 'P') ? $lineAmount * ($discount[3] / 100) : $row['quantity'] * $discount[3];
										$subTotal -= $discountAmount;
										$return .= '<td class="textRight">-'.formatCurrency($discountAmount).'</td>';
										$return .= '<td></td>';
										$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="discount" data-id="'.$discount[0].'" data-appliestotype="P" data-appliestoid="'.$row['productID'].'"></a></td></tr>';
									}
								}
							}
							
							//apply order discounts
							if (count($discounts['O']) > 0) {
								foreach ($discounts['O'] as $discount) {
									$return .= '<tr><td>Discount: <a href="item.php?type=discount&id='.$discount[0].'">'.$discount[1].'</a></td><td></td><td></td>';
									$discountAmount = ($discount[2] == 'P') ? ($subTotal) * ($discount[3] / 100) : $discount[3];
									$subTotal -= $discountAmount;
									$return .= '<td class="textRight">-'.formatCurrency($discountAmount).'</td>';
									$return .= '<td></td>';
									$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="discount" data-id="'.$discount[0].'" data-appliestotype="O" data-appliestoid="0"></a></td></tr>';
								}
							}
							
							$return .= '<tr style="font-weight: bold;"><td>Total:</td><td></td><td></td><td class="textRight">'.formatCurrency($subTotal).'</td><td></td><td></td></tr>';
						
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
								FROM payments
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
								$return .= '<tr><td>'.$row['paymentID'].'</td>';
								$return .= '<td class="textCenter">'.$paymentType.'</td>';
								$return .= '<td class="textRight">'.formatCurrency($row['paymentAmount']).'</td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="payment" data-id="'.$row['paymentID'].'"></a></td></tr>';
								$subTotal += $row['paymentAmount'];
							}
							$return .= '<tr style="font-weight: bold;"><td>Total:</td><td></td><td class="textRight">'.formatCurrency($subTotal).'</td><td></td></tr>';
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			return $return;
		}
		
		public function getName($type, $id) {
			return 'Order #'.$id;
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
						'SELECT CONCAT("P", products.productID), name FROM orders_products, products
						WHERE orderID = :orderID AND orders_products.productID = products.productID
						UNION
						SELECT CONCAT("S", services.serviceID), name FROM orders_services, services
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
							'SELECT discountID FROM orders_discounts
							WHERE orderID = :orderID AND appliesToType = :appliesToType AND appliesToID = :appliesToID');
						$sth->execute([':orderID' => $id, ':appliesToType' => $temp[0], ':appliesToID' => $temp[1]]);
						while ($row = $sth->fetch()) {
							$discounts[] = $row['discountID'];
						}
					}
					$sth = $dbh->prepare(
						'SELECT '.$TYPES[$data['itemType']]['idName'].', name FROM '.$TYPES[$data['itemType']]['pluralName'].'
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
							'discountID' => ['verifyData' => [1, 'id', 'discount']]
						];
						//remove itemID for orders as it messes up verifyData
						unset($data['itemID']);
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
				//add itemID back for orders
				if ($subType == 'discount' && $itemType == 'O') {
					$data['itemID'] = 0;
				}
				
				if ($return['status'] != 'fail') {
					if ($subType == 'payment') {
						$sth = $dbh->prepare(
							'INSERT INTO payments (paymentID, orderID, paymentType, paymentAmount)
							VALUES(null, :orderID, :paymentType, :paymentAmount)');
						$sth->execute([':orderID' => $id, ':paymentType' => $data['paymentType'], ':paymentAmount' => $data['paymentAmount']]);
						$changeData = ['type' => 'payment', 'id' => $dbh->lastInsertId(), 'paymentType' => $data['paymentType'], 'paymentAmount' => $data['paymentAmount']];
					}
					elseif ($subType == 'product' || $subType == 'service') {
						$sth = $dbh->prepare(
							'SELECT quantity FROM orders_'.$TYPES[$subType]['pluralName'].'
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
								'SELECT defaultPrice FROM '.$TYPES[$subType]['pluralName'].'
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
						$sth = $dbh->prepare(
							'INSERT INTO orders_discounts (orderID, discountID, appliesToType, appliesToID)
							VALUES(:orderID, :discountID, :appliesToType, :appliesToID)');
						$sth->execute([':orderID' => $id, ':discountID' => $data['discountID'], ':appliesToType' => $itemType, ':appliesToID' => $data['itemID']]);
						$changeData = ['type' => 'discount', 'id' => $data['discountID'], 'appliesToType' => $itemType, 'appliesToID' => $data['itemID'], 'discountNote' => 'add'];
					}
					
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
					
					addChange('order', $id, $_SESSION['employeeID'], json_encode(['type' => $subType, 'id' => $data['subID'], 'quantity' => $data['quantity']]));
				}
			}
			elseif ($data['subAction'] == 'delete') {
				//delete subAction
				if ($data['subType'] == 'payment') {
					$sth = $dbh->prepare(
						'DELETE FROM payments
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
					$changeData = ['type' => 'payment', 'id' => $data['subID']];
				}
				elseif ($data['subType'] == 'discount') {
					$sth = $dbh->prepare(
						'DELETE FROM orders_discounts
						WHERE orderID = :orderID AND discountID = :discountID AND appliesToType = :appliesToType AND appliesToID = :appliesToID');
					$sth->execute([':orderID' => $id, ':discountID' => $data['subID'], ':appliesToType' => $data['appliesToType'], ':appliesToID' => $data['appliesToID']]);
					$changeData = ['type' => 'discount', 'id' => $data['subID'], 'appliesToType' => $data['appliesToType'], 'appliesToID' => $data['appliesToID'], 'discountNote' => 'delete'];
				}
				
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
										<input type="text" name="quantity" autocomplete="off" value=""></li>
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
	}
?>