<?php
	//Database
	$SETTINGS['dbServer'] = 'localhost';
	$SETTINGS['dbName'] = 'erp';
	$SETTINGS['dbUser'] = 'root';
	$SETTINGS['dbPassword'] = 'dbPasswordHere';
	
	//Default timezone
	$SETTINGS['timeZone'] = 'America/New_York';
	
	//Default columns
	$SETTINGS['columns']['employee'] = ['name', 'positionID', 'locationID', 'payType', 'payAmount'];
	$SETTINGS['columns']['order'] = ['orderID', 'customerID', 'employeeID'];
	$SETTINGS['columns']['location'] = ['name', 'address', 'city', 'state', 'zip'];
	$SETTINGS['columns']['position'] = ['name'];
	$SETTINGS['columns']['product'] = ['name', 'defaultPrice'];
	$SETTINGS['columns']['service'] = ['name', 'defaultPrice'];
	$SETTINGS['columns']['customer'] = ['name'];
	$SETTINGS['columns']['discount'] = ['name', 'type', 'amount'];
?>