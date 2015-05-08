<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	require('settings.php');
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
	
	//recurring expenses_products
	$sth = $dbh->prepare(
		'SELECT expenseID, productID, locationID, unitPrice, quantity, recurringID, dayOfMonth 
		FROM expenses_products 
		WHERE recurringID IS NOT NULL AND startDate <= :today AND endDate >= :today');
	$sth->execute([':today' => $today]);
	while ($row = $sth->fetch()) {
		$day = date('j', $today);
		if ($day == $row['dayOfMonth']) {
			$sth = $dbh->prepare(
				'INSERT INTO expenses_products (expenseID, productID, locationID, date, unitPrice, quantity, parentRecurringID)
				VALUES(:expenseID, :productID, :locationID, :date, :unitPrice, :quantity, :parentRecurringID)');
			$sth->execute([':expenseID' => $row['expenseID'], ':productID' => $row['productID'], ':locationID' => $row['locationID'], ':date' => $today, ':unitPrice' => $row['unitPrice'], ':quantity' => $row['quantity'], ':parentRecurringID' => $row['recurringID']]);
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
			$sth = $dbh->prepare(
				'INSERT INTO expenseOthers (expenseID, name, date, unitPrice, quantity, parentRecurringID)
				VALUES(:expenseID, :name, :date, :unitPrice, :quantity, :parentRecurringID)');
			$sth->execute([':expenseID' => $row['expenseID'], ':name' => $row['name'], ':date' => $today, ':unitPrice' => $row['unitPrice'], ':quantity' => $row['quantity'], ':parentRecurringID' => $row['recurringID']]);
		}
	}
?>