<?php
	//Database
	$SETTINGS['dbServer'] = 'localhost';
	$SETTINGS['dbName'] = 'erp';
	$SETTINGS['dbUser'] = 'root';
	$SETTINGS['dbPassword'] = 'dbPasswordHere'; //TODO: take this out when posting code
	
	//Company information
	$SETTINGS['companyName'] = 'Audemium';
	
	//Default timezone (see http://php.net/manual/en/timezones.php for options)
	$SETTINGS['timeZone'] = 'America/New_York';
	
	//Default date/time formats (see http://php.net/manual/en/function.date.php for options)
	$SETTINGS['dateTimeFormat'] = 'd-M-Y g:i A';
	$SETTINGS['dateFormat'] = 'd-M-Y';
	$SETTINGS['timeFormat'] = 'g:i A';
	
	//Default date/time formats in Javascript (see http://trentrichardson.com/examples/timepicker/ for options)
	$SETTINGS['dateFormatJS'] = 'dd-M-yy';
	$SETTINGS['timeFormatJS'] = 'h:mm TT';
	
	//Default columns for list views
	$SETTINGS['columns']['employee'] = ['name', 'positionID', 'locationID', 'payType', 'payAmount'];
	$SETTINGS['columns']['order'] = ['orderID', 'customerID', 'employeeID'];
	$SETTINGS['columns']['location'] = ['name', 'address', 'city', 'state', 'zip'];
	$SETTINGS['columns']['position'] = ['name'];
	$SETTINGS['columns']['product'] = ['name', 'defaultPrice'];
	$SETTINGS['columns']['service'] = ['name', 'defaultPrice'];
	$SETTINGS['columns']['customer'] = ['name'];
	$SETTINGS['columns']['discount'] = ['name', 'discountType', 'discountAmount'];
	$SETTINGS['columns']['expense'] = ['expenseID', 'supplierID', 'employeeID'];
	$SETTINGS['columns']['supplier'] = ['name'];
?>