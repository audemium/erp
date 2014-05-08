<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	require_once('init.php');
	
	/*
		Function: generateInvoice
		Inputs: orderID
		Outputs: html
	*/
	
	if ($_GET['action'] == 'generateInvoice') {
		$subTotal = 0;
		$html = 
			'<!DOCTYPE html>
			<html>
			<head>
				<meta charset="UTF-8">
				<title></title>
			</head>
			<body style="font-family: arial;">
				<div style="width:640px; margin:0 auto;">
					<span style="font-weight:bold; font-size:1.5em;">Galaxy Think</span>
					<div style="border-bottom: 2px solid #E5E5E5; margin:5px 0;">&nbsp;</div><br>
					Thank you for your order.  Your invoice is below.<br><br>';
		
		//get order info
		$sth = $dbh->prepare(
			'SELECT orderTime
			FROM orders
			WHERE orderID = :orderID');
		$sth->execute(array(':orderID' => $_GET['orderID']));
		$row = $sth->fetch();
		$html .= 
			'<b>Order ID:</b> '.$_GET['orderID'].'<br>
			<b>Order Time:</b> '.formatDateTime($row['orderTime']).'<br><br>
			<table style="width:100%;">
				<thead style="font-weight:bold;">
					<tr>
						<td>Item</td>
						<td style="text-align:center;">Quantity</td>
						<td style="text-align:center;">Unit Price</td>
						<td style="text-align:right;">Item Total</td>
					</tr>
				</thead>
				<tbody>
			';
		
		//get discounts
		$discounts = array('S' => array(), 'P' => array(), 'O' => array());
		$sth = $dbh->prepare(
			'SELECT name, type, amount, appliesToType, appliesToID
			FROM discounts, orders_discounts
			WHERE orderID = :orderID AND discounts.discountID = orders_discounts.discountID');
		$sth->execute(array(':orderID' => $_GET['orderID']));
		while ($row = $sth->fetch()) {
			if ($row['appliesToType'] == 'O') {
				$discounts['O'][] = array($row['name'], $row['type'], $row['amount']);
			}
			else {
				$discounts[$row['appliesToType']][$row['appliesToID']][] = array($row['name'], $row['type'], $row['amount']);
			}
		}
		
		//get services
		$sth = $dbh->prepare(
			'SELECT services.serviceID, name, quantity, unitPrice
			FROM services, orders_services
			WHERE orderID = :orderID AND services.serviceID = orders_services.serviceID');
		$sth->execute(array(':orderID' => $_GET['orderID']));
		while ($row = $sth->fetch()) {
			$html .= '<tr><td>'.$row['name'].'</td>';
			$html .= '<td style="text-align:center;">'.($row['quantity'] + 0).'</td>'; //remove extra zeros and decimals
			$html .= '<td style="text-align:center;">'.formatCurrency($row['unitPrice']).'</td>';
			$lineAmount = $row['quantity'] * $row['unitPrice'];
			$subTotal += $lineAmount;
			$html .= '<td style="text-align:right;">'.formatCurrency($lineAmount).'</td></tr>';
			
			//apply any discounts to this item
			if (count($discounts['S']) > 0) {
				foreach ($discounts['S'][$row['serviceID']] as $discount) {
					$html .= '<tr><td style="padding-left: 50px;">Discount: '.$discount[0].'</td><td></td><td></td>';
					$discountAmount = ($discount[1] == 'P') ? $lineAmount * ($discount[2] / 100) : $row['quantity'] * $discount[2];
					$subTotal -= $discountAmount;
					$html .= '<td style="text-align:right;">-'.formatCurrency($discountAmount).'</td></tr>';
				}
			}
		}
		
		//get products
		$sth = $dbh->prepare(
			'SELECT products.productID, name, quantity, unitPrice
			FROM products, orders_products
			WHERE orderID = :orderID AND products.productID = orders_products.productID');
		$sth->execute(array(':orderID' => $_GET['orderID']));
		while ($row = $sth->fetch()) {
			$html .= '<tr><td>'.$row['name'].'</td>';
			$html .= '<td style="text-align:center;">'.($row['quantity'] + 0).'</td>'; //remove extra zeros and decimals
			$html .= '<td style="text-align:center;">'.formatCurrency($row['unitPrice']).'</td>';
			$lineAmount = $row['quantity'] * $row['unitPrice'];
			$subTotal += $lineAmount;
			$html .= '<td style="text-align:right;">'.formatCurrency($lineAmount).'</td></tr>';
			
			//apply any discounts to this item
			if (count($discounts['P']) > 0) {
				foreach ($discounts['P'][$row['productID']] as $discount) {
					$html .= '<tr><td style="padding-left: 50px;">Discount: '.$discount[0].'</td><td></td><td></td>';
					$discountAmount = ($discount[1] == 'P') ? $lineAmount * ($discount[2] / 100) : $row['quantity'] * $discount[2];
					$subTotal -= $discountAmount;
					$html .= '<td style="text-align:right;">-'.formatCurrency($discountAmount).'</td></tr>';
				}
			}
		}
		
		//apply order discounts
		if (count($discounts['O']) > 0) {
			foreach ($discounts['O'] as $discount) {
				$html .= '<tr><td>Discount: '.$discount[0].'</td><td></td><td></td>';
				$discountAmount = ($discount[1] == 'P') ? ($subTotal) * ($discount[2] / 100) : $discount[2];
				$subTotal -= $discountAmount;
				$html .= '<td style="text-align:right;">-'.formatCurrency($discountAmount).'</td></tr>';
			}
		}
		$html .= '</tbody></table>';
		
		//find amount paid
		$sth = $dbh->prepare(
			'SELECT SUM(amount)
			FROM payments
			WHERE orderID = :orderID');
		$sth->execute(array(':orderID' => $_GET['orderID']));
		$row = $sth->fetch();
		$paidAmount = $row['SUM(amount)'];
		
		//print totals
		$html .= '<table style="width:100%; text-align:right;"><tbody>';
		$html .= '<tr><td>Total:</td><td>'.formatCurrency($subTotal).'</td></tr>';
		$html .= '<tr><td>Amount Paid:</td><td>'.formatCurrency($paidAmount).'</td></tr>';
		$html .= '<tr style="font-weight: bold;"><td>Amount Due:</td><td>'.formatCurrency($subTotal - $paidAmount).'</td></tr>';
		$html .= '</tbody></table></div></body></html';
		
		echo $html;
	}
?>