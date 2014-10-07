<?php
	//Database
	$SETTINGS['dbServer'] = 'localhost';
	$SETTINGS['dbName'] = 'erp';
	$SETTINGS['dbUser'] = 'root';
	$SETTINGS['dbPassword'] = 'dbPasswordHere';
	
	//Default timezone (see http://php.net/manual/en/timezones.php for options)
	$SETTINGS['timeZone'] = 'America/New_York';
	
	//Default time format (see http://php.net/manual/en/function.date.php for options)
	$SETTINGS['dateTimeFormat'] = 'd-M-Y H:i';
	$SETTINGS['dateFormat'] = 'd-M-Y';
	$SETTINGS['timeFormat'] = 'H:i';
	
	//Default columns
	$SETTINGS['columns']['employee'] = ['name', 'positionID', 'locationID', 'payType', 'payAmount'];
	$SETTINGS['columns']['order'] = ['orderID', 'customerID', 'employeeID'];
	$SETTINGS['columns']['location'] = ['name', 'address', 'city', 'state', 'zip'];
	$SETTINGS['columns']['position'] = ['name'];
	$SETTINGS['columns']['product'] = ['name', 'defaultPrice'];
	$SETTINGS['columns']['service'] = ['name', 'defaultPrice'];
	$SETTINGS['columns']['customer'] = ['name'];
	$SETTINGS['columns']['discount'] = ['name', 'discountType', 'discountAmount'];
?>