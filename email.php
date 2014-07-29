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
		$sth->execute([':orderID' => $_GET['orderID']]);
		$row = $sth->fetch();
		$html .= 
			'<b>Order ID:</b> '.$_GET['orderID'].'<br>
			<b>Order Time:</b> '.formatDateTime($row['orderTime']).'<br><br>';
			
		//call invoice function
		$item = Factory::createItem('order');
		$html .= $item->printItemBody($_GET['orderID']);
			
		$html .= '</div></body></html';
		
		echo $html;
	}
?>