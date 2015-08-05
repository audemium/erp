<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	//version
	$VERSION = '0.5';
	
	//global autoloader
	function customAutoloader($class) {
		include 'types/'.$class.'.php';
	}
	spl_autoload_register('customAutoloader');
	
	//load settings, helper functions and type definitions
	require('settings.php');
	require('helpers.php');
	require('types/types.php');
	
	//connect to db
	$dbh = new PDO(
		'mysql:host='.$SETTINGS['dbServer'].';dbname='.$SETTINGS['dbName'],
		$SETTINGS['dbUser'],
		$SETTINGS['dbPassword'],
		[
			PDO::ATTR_PERSISTENT => true
			//default for emulate prepares is true, which allows things like using the same parameter multiple times
		]
	);
	
	//change cookies to HttpOnly and secure (if we're using HTTPS)
	session_set_cookie_params(0, '/', $_SERVER['SERVER_NAME'], isset($_SERVER['HTTPS']), true);
	//set sessions to use the db functions
	session_set_save_handler('dbSessionOpen', 'dbSessionClose', 'dbSessionRead', 'dbSessionWrite', 'dbSessionDestroy', 'dbSessionGc');
	//register_shutdown is required, otherwise $dbh is destroyed before the session is closed
	session_register_shutdown();
	session_start();
	
	//set up time zones
	//can't do if/elseif/else because it's possible someone could set an invalid time zone and the function could fail
	date_default_timezone_set('UTC');
	//if a default is set in settings.php, use that
	if (isset($SETTINGS['timeZone']) && $SETTINGS['timeZone'] != '') {
		date_default_timezone_set($SETTINGS['timeZone']);
	}
	//if user sets a time zone, use that
	if (isset($_SESSION['timeZone']) && $_SESSION['timeZone'] != '') {
		date_default_timezone_set($_SESSION['timeZone']);
	}
	
	//check if the user is logged in, if not then redirect
	$file = basename($_SERVER['SCRIPT_NAME']);
	$exceptions = ['login.php', 'ajax.php', 'module.php'];
	if (empty($_SESSION['loggedIn']) && !in_array($file, $exceptions)) {
		$_SESSION['loginDestination'] = $_SERVER['REQUEST_URI'];
		header('Location: login.php');
		die();
	}
?>