<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Expense extends GenericItem {
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
								'SELECT products.productID, products.name AS productName, locations.locationID, locations.name AS locationName, quantity, unitPrice
								FROM products, locations, expenses_products
								WHERE expenseID = :expenseID AND products.productID = expenses_products.productID AND locations.locationID = expenses_products.locationID');
							$sth->execute([':expenseID' => $id]);
							while ($row = $sth->fetch()) {
								$lineAmount = $row['quantity'] * $row['unitPrice'];
								$subTotal += $lineAmount;
								$return .= '<tr><td><a href="item.php?type=product&id='.$row['productID'].'">'.$row['productName'].'</a></td>';
								$return .= '<td><a href="item.php?type=location&id='.$row['locationID'].'">'.$row['locationName'].'</a></td>';
								$return .= '<td class="textCenter">'.($row['quantity'] + 0).'</td>';
								$return .= '<td class="textCenter">'.formatCurrency($row['unitPrice']).'</td>';
								$return .= '<td class="textRight">'.formatCurrency($lineAmount).'</td>';
								$return .= '<td class="textCenter"><a class="controlEdit editEnabled" href="#" data-type="product" data-id="'.$row['productID'].'-'.$row['locationID'].'" data-unitprice="'.$row['unitPrice'].'" data-quantity="'.($row['quantity'] + 0).'"></a></td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="product" data-id="'.$row['productID'].'-'.$row['locationID'].'"></a></td></tr>';
							}
							
							//get other expenses
							$sth = $dbh->prepare(
								'SELECT expenseOtherID, name, quantity, unitPrice
								FROM expenseOthers
								WHERE expenseID = :expenseID');
							$sth->execute([':expenseID' => $id]);
							while ($row = $sth->fetch()) {
								$lineAmount = $row['quantity'] * $row['unitPrice'];
								$subTotal += $lineAmount;
								$return .= '<tr><td>'.$row['name'].'</td>';
								$return .= '<td></td>';
								$return .= '<td class="textCenter">'.($row['quantity'] + 0).'</td>';
								$return .= '<td class="textCenter">'.formatCurrency($row['unitPrice']).'</td>';
								$return .= '<td class="textRight">'.formatCurrency($lineAmount).'</td>';
								$return .= '<td class="textCenter"><a class="controlEdit editEnabled" href="#" data-type="other" data-id="'.$row['expenseOtherID'].'" data-unitprice="'.$row['unitPrice'].'" data-quantity="'.($row['quantity'] + 0).'"></a></td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-type="other" data-id="'.$row['expenseOtherID'].'"></a></td></tr>';
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
								FROM expensePayments
								WHERE expenseID = :expenseID');
							$sth->execute([':expenseID' => $id]);
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
			
			return $return;
		}
		
		public function getName($type, $id) {
			return 'Expense #'.$id;
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
				$return['products'] = [];
				$return['locations'] = [];

				$sth = $dbh->prepare(
					'SELECT productID, name
					FROM products
					WHERE active = 1');
				$sth->execute();
				while ($row = $sth->fetch()) {
					$return['products'][] = ['value' => $row[0], 'text' => $row[1]];
				}
				
				$sth = $dbh->prepare(
					'SELECT locationID, name
					FROM locations
					WHERE active = 1');
				$sth->execute();
				while ($row = $sth->fetch()) {
					$return['locations'][] = ['value' => $row[0], 'text' => $row[1]];
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
						'productID' => ['verifyData' => [1, 'id', 'product']],
						'locationID' => ['verifyData' => [1, 'id', 'location']],
						'unitPrice' => ['verifyData' => [1, 'dec', [12, 2]]],
						'quantity' => ['verifyData' => [1, 'int', 4294967295]]
					];
				}
				elseif ($subType == 'other') {
					$fields = [
						'name' => ['verifyData' => [1, 'str', 200]],
						'unitPrice' => ['verifyData' => [1, 'dec', [12, 2]]],
						'quantity' => ['verifyData' => [1, 'dec', [12, 2]]]
					];
				}
				$return = verifyData(null, $data, $fields);
				
				if ($return['status'] != 'fail') {
					if ($subType == 'payment') {
						$sth = $dbh->prepare(
							'INSERT INTO expensePayments (paymentID, expenseID, paymentType, paymentAmount)
							VALUES(null, :expenseID, :paymentType, :paymentAmount)');
						$sth->execute([':expenseID' => $id, ':paymentType' => $data['paymentType'], ':paymentAmount' => $data['paymentAmount']]);
						$changeData = ['type' => 'payment', 'id' => $dbh->lastInsertId(), 'paymentType' => $data['paymentType'], 'paymentAmount' => $data['paymentAmount']];
					}
					elseif ($subType == 'product') {
						$sth = $dbh->prepare(
							'SELECT quantity
							FROM expenses_products
							WHERE expenseID = :expenseID AND productID = :productID AND locationID = :locationID AND unitPrice = :unitPrice');
						$sth->execute([':expenseID' => $id, ':productID' => $data['productID'], ':locationID' => $data['locationID'], ':unitPrice' => $data['unitPrice']]);
						$result = $sth->fetchAll();
						if (count($result) == 1) {
							//if the product is already present in the expense, add the quantity to the existing row
							$sth = $dbh->prepare(
								'UPDATE expenses_products
								SET quantity = :quantity
								WHERE expenseID = :expenseID AND productID = :productID AND locationID = :locationID AND unitPrice = :unitPrice');
							$sth->execute([':quantity' => $data['quantity'] + $result[0]['quantity'], ':expenseID' => $id, ':productID' => $data['productID'], ':locationID' => $data['locationID'], ':unitPrice' => $data['unitPrice']]);
							$changeData = ['type' => 'product', 'id' => $data['productID'], 'quantity' => $data['quantity'] + $result[0]['quantity']];
						}
						else {
							$sth = $dbh->prepare(
								'INSERT INTO expenses_products (expenseID, productID, locationID, unitPrice, quantity)
								VALUES(:expenseID, :productID, :locationID, :unitPrice, :quantity)');
							$sth->execute([':expenseID' => $id, ':productID' => $data['productID'], ':locationID' => $data['locationID'], ':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity']]);
							$changeData = ['type' => 'product', 'id' => $data['productID'], 'locationID' => $data['locationID'], 'unitPrice' => $data['unitPrice'], 'quantity' => $data['quantity']];
						}
					}
					elseif ($subType == 'other') {
						$sth = $dbh->prepare(
							'SELECT quantity
							FROM expenseOthers
							WHERE expenseID = :expenseID AND name = :name AND unitPrice = :unitPrice');
						$sth->execute([':expenseID' => $id, ':name' => $data['name'], ':unitPrice' => $data['unitPrice']]);
						$result = $sth->fetchAll();
						if (count($result) == 1) {
							//if the item is already present in the expense, add the quantity to the existing row
							$sth = $dbh->prepare(
								'UPDATE expenseOthers
								SET quantity = :quantity
								WHERE expenseID = :expenseID AND name = :name AND unitPrice = :unitPrice');
							$sth->execute([':quantity' => $data['quantity'] + $result[0]['quantity'], ':expenseID' => $id, ':name' => $data['name'], ':unitPrice' => $data['unitPrice']]);
							$changeData = ['type' => 'other', 'name' => $data['name'], 'quantity' => $data['quantity'] + $result[0]['quantity']];
						}
						else {
							$sth = $dbh->prepare(
								'INSERT INTO expenseOthers (expenseOtherID, expenseID, name, unitPrice, quantity)
								VALUES(null, :expenseID, :name, :unitPrice, :quantity)');
							$sth->execute([':expenseID' => $id, ':name' => $data['name'], ':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity']]);
							$changeData = ['type' => 'other', 'name' => $data['name'], 'unitPrice' => $data['unitPrice'], 'quantity' => $data['quantity']];
						}
					}
					
					self::updateAmountDue($id);
					addChange('expense', $id, $_SESSION['employeeID'], json_encode($changeData));
				}
			}
			elseif ($data['subAction'] == 'edit') {
				//edit subAction
				$subType = $data['subType'];
				unset($data['subAction']);
				unset($data['subType']);
				if ($subType == 'product') {
					$ids = explode('-', $data['subID']);
					$data['productID'] = $ids[0];
					$data['locationID'] = $ids[1];
					$fields = [
						'productID' => ['verifyData' => [1, 'id', 'product']],
						'locationID' => ['verifyData' => [1, 'id', 'location']],
						'unitPrice' => ['verifyData' => [1, 'dec', [12, 2]]],
						'quantity' => ['verifyData' => [1, 'int', 4294967295]]
					];
				}
				elseif ($subType == 'other') {
					$fields = [
						'unitPrice' => ['verifyData' => [1, 'dec', [12, 2]]],
						'quantity' => ['verifyData' => [1, 'dec', [12, 2]]]
					];
				}
				$subID = $data['subID']; //needed for expensesOther but not expenses_products
				unset($data['subID']);
				$return = verifyData(null, $data, $fields);
				
				if ($return['status'] != 'fail') {
					if ($subType == 'product') {
						$sth = $dbh->prepare(
							'UPDATE expenses_products
							SET unitPrice = :unitPrice, quantity = :quantity
							WHERE expenseID = :expenseID AND productID = :productID AND locationID = :locationID');
						$sth->execute([':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity'], ':expenseID' => $id, ':productID' => $data['productID'], ':locationID' => $data['locationID']]);
					}
					elseif ($subType == 'other') {
						$sth = $dbh->prepare(
							'UPDATE expenseOthers
							SET unitPrice = :unitPrice, quantity = :quantity
							WHERE expenseOtherID = :expenseOtherID');
						$sth->execute([':unitPrice' => $data['unitPrice'], ':quantity' => $data['quantity'], ':expenseOtherID' => $subID]);
					}
					
					self::updateAmountDue($id);
					//TODO: fix changeData when I figure out a format
					addChange('expense', $id, $_SESSION['employeeID'], json_encode(['type' => $subType, 'unitPrice' => $data['unitPrice'], 'quantity' => $data['quantity']]));
				}
			}
			elseif ($data['subAction'] == 'delete') {
				//delete subAction
				if ($data['subType'] == 'payment') {
					$sth = $dbh->prepare(
						'DELETE FROM expensePayments
						WHERE paymentID = :paymentID');
					$sth->execute([':paymentID' => $data['subID']]);
					$changeData = ['type' => 'payment', 'id' => $data['subID']];
				}
				elseif ($data['subType'] == 'product') {
					$ids = explode('-', $data['subID']);
					$sth = $dbh->prepare(
						'DELETE FROM expenses_products
						WHERE expenseID = :expenseID AND productID = :productID AND locationID = :locationID');
					$sth->execute([':expenseID' => $id, ':productID' => $ids[0], ':locationID' => $ids[1]]);
					//TODO: fix changeData when I figure out a format
					$changeData = ['type' => 'product', 'id' => $data['subID']];
				}
				elseif ($data['subType'] == 'other') {
					$sth = $dbh->prepare(
						'DELETE FROM expenseOthers
						WHERE expenseOtherID = :expenseOtherID');
					$sth->execute([':expenseOtherID' => $data['subID']]);
					//TODO: fix changeData when I figure out a format
					$changeData = ['type' => 'other', 'id' => $data['subID']];
				}
				
				self::updateAmountDue($id);
				addChange('expense', $id, $_SESSION['employeeID'], json_encode($changeData));
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
											<option value="other">Other Expense</option>
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
		
		/* Local Helper Functions */
		
		private static function updateAmountDue($id) {
			global $dbh;
			$subTotal = 0;
			
			//get product expenses
			$sth = $dbh->prepare(
				'SELECT quantity, unitPrice
				FROM expenses_products
				WHERE expenseID = :expenseID');
			$sth->execute([':expenseID' => $id]);
			while ($row = $sth->fetch()) {
				$lineAmount = $row['quantity'] * $row['unitPrice'];
				$subTotal += $lineAmount;
			}
			
			//get other expenses
			$sth = $dbh->prepare(
				'SELECT quantity, unitPrice
				FROM expenseOthers
				WHERE expenseID = :expenseID');
			$sth->execute([':expenseID' => $id]);
			while ($row = $sth->fetch()) {
				$lineAmount = $row['quantity'] * $row['unitPrice'];
				$subTotal += $lineAmount;
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