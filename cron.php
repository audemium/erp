<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	function customAutoloader($class) {
		include 'types/'.$class.'.php';
	}
	spl_autoload_register('customAutoloader');
	
	require('settings.php');
	require('types/types.php');
	
	$dbh = new PDO(
		'mysql:host='.$SETTINGS['dbServer'].';dbname='.$SETTINGS['dbName'],
		$SETTINGS['dbUser'],
		$SETTINGS['dbPassword'],
		[
			PDO::ATTR_PERSISTENT => true
			//default for emulate prepares is true, which allows things like using the same parameter multiple times
		]
	);
	
	date_default_timezone_set('UTC');
	//if a default is set in settings.php, use that
	if (isset($SETTINGS['timeZone']) && $SETTINGS['timeZone'] != '') {
		date_default_timezone_set($SETTINGS['timeZone']);
	}
	//if user sets a time zone, use that
	if (isset($_SESSION['timeZone']) && $_SESSION['timeZone'] != '') {
		date_default_timezone_set($_SESSION['timeZone']);
	}
	
	$today = strtotime('today');
	$expenseItem = Factory::createItem('expense');
	
	//recurring expenses_products
	$sth = $dbh->prepare(
		'SELECT expenseID, productID, locationID, unitPrice, quantity, recurringID, dayOfMonth
		FROM expenses_products
		WHERE recurringID IS NOT NULL AND startDate <= :today AND endDate >= :today');
	$sth->execute([':today' => $today]);
	while ($row = $sth->fetch()) {
		$day = date('j', $today);
		if ($day == $row['dayOfMonth']) {
			$sth2 = $dbh->prepare(
				'INSERT INTO expenses_products (expenseID, productID, locationID, date, unitPrice, quantity, parentRecurringID)
				VALUES(:expenseID, :productID, :locationID, :date, :unitPrice, :quantity, :parentRecurringID)');
			$sth2->execute([':expenseID' => $row['expenseID'], ':productID' => $row['productID'], ':locationID' => $row['locationID'], ':date' => $today, ':unitPrice' => $row['unitPrice'], ':quantity' => $row['quantity'], ':parentRecurringID' => $row['recurringID']]);

			$expenseItem->updateAmountDue($row['expenseID']);
		}
	}
	
	//recurring expenseOthers
	$sth = $dbh->prepare(
		'SELECT expenseID, name, unitPrice, quantity, recurringID, dayOfMonth
		FROM expenseOthers
		WHERE recurringID IS NOT NULL AND startDate <= :today AND endDate >= :today');
	$sth->execute([':today' => $today]);
	while ($row = $sth->fetch()) {
		$day = date('j', $today);
		if ($day == $row['dayOfMonth']) {
			$sth2 = $dbh->prepare(
				'INSERT INTO expenseOthers (expenseID, name, date, unitPrice, quantity, parentRecurringID)
				VALUES(:expenseID, :name, :date, :unitPrice, :quantity, :parentRecurringID)');
			$sth2->execute([':expenseID' => $row['expenseID'], ':name' => $row['name'], ':date' => $today, ':unitPrice' => $row['unitPrice'], ':quantity' => $row['quantity'], ':parentRecurringID' => $row['recurringID']]);
			
			$expenseItem->updateAmountDue($row['expenseID']);
		}
	}
	
	$orderItem = Factory::createItem('order');
	
	//recurring orders_products
	$sth = $dbh->prepare(
		'SELECT orderProductID, orderID, productID, unitPrice, quantity, recurringID, dayOfMonth
		FROM orders_products
		WHERE recurringID IS NOT NULL AND startDate <= :today AND endDate >= :today');
	$sth->execute([':today' => $today]);
	while ($row = $sth->fetch()) {
		$day = date('j', $today);
		if ($day == $row['dayOfMonth']) {
			$sth2 = $dbh->prepare(
				'INSERT INTO orders_products (orderID, productID, date, unitPrice, quantity, parentRecurringID)
				VALUES(:orderID, :productID, :date, :unitPrice, :quantity, :parentRecurringID)');
			$sth2->execute([':orderID' => $row['orderID'], ':productID' => $row['productID'], ':date' => $today, ':unitPrice' => $row['unitPrice'], ':quantity' => $row['quantity'], ':parentRecurringID' => $row['recurringID']]);
			$id = $dbh->lastInsertId();
			
			//check for discounts on the item
			$sth2 = $dbh->prepare(
				'SELECT discountID, discountType, discountAmount
				FROM orders_discounts
				WHERE appliesToType = "P" AND appliesToID = :appliesToID');
			$sth2->execute([':appliesToID' => $row['orderProductID']]);
			while ($row2 = $sth2->fetch()) {
				$sth3 = $dbh->prepare(
					'INSERT INTO orders_discounts (orderID, discountID, appliesToType, appliesToID, discountType, discountAmount)
					VALUES(:orderID, :discountID, "P", :appliesToID, :discountType, :discountAmount)');
				$sth3->execute([':orderID' => $row['orderID'], ':discountID' => $row2['discountID'], ':appliesToID' => $id, ':discountType' => $row2['discountType'], ':discountAmount' => $row2['discountAmount']]);
			}
			
			$orderItem->updateAmountDue($row['orderID']);
		}
	}
	
	//recurring orders_services
	$sth = $dbh->prepare(
		'SELECT orderServiceID, orderID, serviceID, unitPrice, quantity, recurringID, dayOfMonth
		FROM orders_services
		WHERE recurringID IS NOT NULL AND startDate <= :today AND endDate >= :today');
	$sth->execute([':today' => $today]);
	while ($row = $sth->fetch()) {
		$day = date('j', $today);
		if ($day == $row['dayOfMonth']) {
			$sth2 = $dbh->prepare(
				'INSERT INTO orders_services (orderID, serviceID, date, unitPrice, quantity, parentRecurringID)
				VALUES(:orderID, :serviceID, :date, :unitPrice, :quantity, :parentRecurringID)');
			$sth2->execute([':orderID' => $row['orderID'], ':serviceID' => $row['serviceID'], ':date' => $today, ':unitPrice' => $row['unitPrice'], ':quantity' => $row['quantity'], ':parentRecurringID' => $row['recurringID']]);
			$id = $dbh->lastInsertId();
			
			//check for discounts on the item
			$sth2 = $dbh->prepare(
				'SELECT discountID, discountType, discountAmount
				FROM orders_discounts
				WHERE appliesToType = "S" AND appliesToID = :appliesToID');
			$sth2->execute([':appliesToID' => $row['orderServiceID']]);
			while ($row2 = $sth2->fetch()) {
				$sth3 = $dbh->prepare(
					'INSERT INTO orders_discounts (orderID, discountID, appliesToType, appliesToID, discountType, discountAmount)
					VALUES(:orderID, :discountID, "S", :appliesToID, :discountType, :discountAmount)');
				$sth3->execute([':orderID' => $row['orderID'], ':discountID' => $row2['discountID'], ':appliesToID' => $id, ':discountType' => $row2['discountType'], ':discountAmount' => $row2['discountAmount']]);
			}
			
			$orderItem->updateAmountDue($row['orderID']);
		}
	}
?>