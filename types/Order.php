<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Order extends Item {
		public function printItemBody($id) {
			global $dbh;
			global $TYPES;
			
			//Line Items section
			$subTotal = 0;
			$return = '<section>
				<h2>Line Items</h2>
				<div class="sectionData">
					<table class="customTable" style="width:100%;">
						<thead style="font-weight:bold;">
							<tr>
								<th>Item</th>
								<th class="textCenter">Quantity</th>
								<th class="textCenter">Unit Price</th>
								<th class="textRight">Item Total</th>
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
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#"></a></td></tr>';
								
								//apply any discounts to this item
								if (count($discounts['S']) > 0) {
									foreach ($discounts['S'][$row['serviceID']] as $discount) {
										$return .= '<tr><td style="padding-left: 50px;">Discount: <a href="item.php?type=discount&id='.$discount[0].'">'.$discount[1].'</a></td><td></td><td></td>';
										$discountAmount = ($discount[2] == 'P') ? $lineAmount * ($discount[3] / 100) : $row['quantity'] * $discount[3];
										$subTotal -= $discountAmount;
										$return .= '<td class="textRight">-'.formatCurrency($discountAmount).'</td>';
										$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#"></a></td></tr>';
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
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#"></a></td></tr>';
								
								//apply any discounts to this item
								if (count($discounts['P']) > 0) {
									foreach ($discounts['P'][$row['productID']] as $discount) {
										$return .= '<tr><td style="padding-left: 50px;">Discount: <a href="item.php?type=discount&id='.$discount[0].'">'.$discount[1].'</a></td><td></td><td></td>';
										$discountAmount = ($discount[2] == 'P') ? $lineAmount * ($discount[3] / 100) : $row['quantity'] * $discount[3];
										$subTotal -= $discountAmount;
										$return .= '<td class="textRight">-'.formatCurrency($discountAmount).'</td>';
										$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#"></a></td></tr>';
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
									$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#"></a></td></tr>';
								}
							}
							
							$return .= '<tr style="font-weight: bold;"><td>Total:</td><td></td><td></td><td class="textRight">'.formatCurrency($subTotal).'</td><td></td></tr>';
						
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			//Payments section
			$subTotal = 0;
			$return .= '<section>
				<h2>Payments</h2>
				<div class="sectionData">
					<table class="customTable" style="width:100%;">
						<thead style="font-weight:bold;">
							<tr>
								<th>Payment Number</th>
								<th class="textCenter">Time</th>
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
								$return .= '<td class="textCenter">'.formatDateTime($row['paymentTime']).'</td>';
								$return .= '<td class="textCenter">'.$paymentType.'</td>';
								$return .= '<td class="textRight">'.formatCurrency($row['paymentAmount']).'</td>';
								$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#"></a></td></tr>';
								$subTotal += $row['paymentAmount'];
							}
							$return .= '<tr style="font-weight: bold;"><td>Total:</td><td></td><td></td><td class="textRight">'.formatCurrency($subTotal).'</td><td></td></tr>';
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			return $return;
		}
		
		public function getName($type, $id) {
			return 'Order #'.$id;
		}
	}
?>