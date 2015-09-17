<?php
	//These are the default settings for Audemium ERP. Use the installer to generate your own settings.php file.
	
	//Audemium ERP Version
	$VERSION = '0.8.1';

	//Database
	$SETTINGS['dbServer'] = 'localhost';
	$SETTINGS['dbName'] = 'audemium_erp';
	$SETTINGS['dbUser'] = 'erpUser';
	$SETTINGS['dbPassword'] = 'dbPasswordHere';
	
	//Demo mode (disables password reset if true)
	$SETTINGS['demoMode'] = false;
	
	//Company information
	$SETTINGS['companyName'] = 'Audemium';
	
	//Accounting basis, values are cash OR accrual
	$SETTINGS['accounting'] = 'accrual';
	
	//Default timezone (see http://php.net/manual/en/timezones.php for options)
	$SETTINGS['timeZone'] = 'America/New_York';
	
	//Date/time formats (see http://php.net/manual/en/function.date.php for options)
	$SETTINGS['dateTimeFormat'] = 'd-M-Y g:i A';
	$SETTINGS['dateFormat'] = 'd-M-Y';
	$SETTINGS['timeFormat'] = 'g:i A';
	
	//Date/time formats in Javascript (see http://trentrichardson.com/examples/timepicker/ for options)
	$SETTINGS['dateFormatJS'] = 'dd-M-yy';
	$SETTINGS['timeFormatJS'] = 'h:mm TT';
	
	//Currency and number formatting
	$SETTINGS['currencySymbol'] = '$';			//can be a character or html entity
	$SETTINGS['negativeCurrencyFormat'] = 0;	//0 = negative sign ex. -$10.00		1 = parentheses ex. ($10.00)
	$SETTINGS['decimalFormat'] = '.';
	$SETTINGS['thousandsSeparator'] = ',';
	
	//Email
	$SETTINGS['SMTP'] = [
		'host' => '',
		'port' => 587,
		'auth' => true,
		'encryption' => 'tls',
		'username' => '',
		'password' => ''
	];
	
	//Columns for list views
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