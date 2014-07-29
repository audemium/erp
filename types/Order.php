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
			$subTotal = 0;
		
			$return = '<section>
				<h2>Line Items</h2>
				<div class="sectionData">
					<table class="orderTable" style="width:100%;">
						<thead style="font-weight:bold;">
							<tr>
								<th>Item</th>
								<th style="text-align:center;">Quantity</th>
								<th style="text-align:center;">Unit Price</th>
								<th style="text-align:right;">Item Total</th>
							</tr>
						</thead>
						<tbody>';
							//get discounts
							$discounts = ['S' => [], 'P' => [], 'O' => []];
							$sth = $dbh->prepare(
								'SELECT name, type, amount, appliesToType, appliesToID
								FROM discounts, orders_discounts
								WHERE orderID = :orderID AND discounts.discountID = orders_discounts.discountID');
							$sth->execute([':orderID' => $id]);
							while ($row = $sth->fetch()) {
								if ($row['appliesToType'] == 'O') {
									$discounts['O'][] = [$row['name'], $row['type'], $row['amount']];
								}
								else {
									$discounts[$row['appliesToType']][$row['appliesToID']][] = [$row['name'], $row['type'], $row['amount']];
								}
							}
							
							//get services
							$sth = $dbh->prepare(
								'SELECT services.serviceID, name, quantity, unitPrice
								FROM services, orders_services
								WHERE orderID = :orderID AND services.serviceID = orders_services.serviceID');
							$sth->execute([':orderID' => $id]);
							while ($row = $sth->fetch()) {
								$return .= '<tr><td>'.$row['name'].'</td>';
								$return .= '<td style="text-align:center;">'.($row['quantity'] + 0).'</td>'; //remove extra zeros and decimals
								$return .= '<td style="text-align:center;">'.formatCurrency($row['unitPrice']).'</td>';
								$lineAmount = $row['quantity'] * $row['unitPrice'];
								$subTotal += $lineAmount;
								$return .= '<td style="text-align:right;">'.formatCurrency($lineAmount).'</td></tr>';
								
								//apply any discounts to this item
								if (count($discounts['S']) > 0) {
									foreach ($discounts['S'][$row['serviceID']] as $discount) {
										$return .= '<tr><td style="padding-left: 50px;">Discount: '.$discount[0].'</td><td></td><td></td>';
										$discountAmount = ($discount[1] == 'P') ? $lineAmount * ($discount[2] / 100) : $row['quantity'] * $discount[2];
										$subTotal -= $discountAmount;
										$return .= '<td style="text-align:right;">-'.formatCurrency($discountAmount).'</td></tr>';
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
								$return .= '<tr><td>'.$row['name'].'</td>';
								$return .= '<td style="text-align:center;">'.($row['quantity'] + 0).'</td>'; //remove extra zeros and decimals
								$return .= '<td style="text-align:center;">'.formatCurrency($row['unitPrice']).'</td>';
								$lineAmount = $row['quantity'] * $row['unitPrice'];
								$subTotal += $lineAmount;
								$return .= '<td style="text-align:right;">'.formatCurrency($lineAmount).'</td></tr>';
								
								//apply any discounts to this item
								if (count($discounts['P']) > 0) {
									foreach ($discounts['P'][$row['productID']] as $discount) {
										$return .= '<tr><td style="padding-left: 50px;">Discount: '.$discount[0].'</td><td></td><td></td>';
										$discountAmount = ($discount[1] == 'P') ? $lineAmount * ($discount[2] / 100) : $row['quantity'] * $discount[2];
										$subTotal -= $discountAmount;
										$return .= '<td style="text-align:right;">-'.formatCurrency($discountAmount).'</td></tr>';
									}
								}
							}
							
							//apply order discounts
							if (count($discounts['O']) > 0) {
								foreach ($discounts['O'] as $discount) {
									$return .= '<tr><td>Discount: '.$discount[0].'</td><td></td><td></td>';
									$discountAmount = ($discount[1] == 'P') ? ($subTotal) * ($discount[2] / 100) : $discount[2];
									$subTotal -= $discountAmount;
									$return .= '<td style="text-align:right;">-'.formatCurrency($discountAmount).'</td></tr>';
								}
							}
							$return .= '</tbody></table>';
							
							//find amount paid
							$sth = $dbh->prepare(
								'SELECT SUM(amount)
								FROM payments
								WHERE orderID = :orderID');
							$sth->execute([':orderID' => $id]);
							$row = $sth->fetch();
							$paidAmount = $row['SUM(amount)'];
							
							//print totals
							$return .= '<table style="width:100%; text-align:right;"><tbody>';
							$return .= '<tr><td>Total:</td><td>'.formatCurrency($subTotal).'</td></tr>';
							$return .= '<tr><td>Amount Paid:</td><td>'.formatCurrency($paidAmount).'</td></tr>';
							$return .= '<tr style="font-weight: bold;"><td>Amount Due:</td><td>'.formatCurrency($subTotal - $paidAmount).'</td></tr>';
						
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