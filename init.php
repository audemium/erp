<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	//global error handler
	function customError($errNum, $errStr, $errFile, $errLine) {
		echo 'Error Code: '.$errNum.'<br>';
		echo 'Error Message: '.$errStr.'<br>';
		echo 'Error Location: '.$errFile.' on line '.$errLine.'<br>';
	}
	set_error_handler('customError');
	
	//global exception handler
	function customException($exception) {
		echo 'Exception Code: '.$exception->getCode().'<br>';
		echo 'Exception Message: '.$exception->getMessage().'<br>';
		echo 'Exception Location: '.$exception->getFile().' on line '.$exception->getLine().'<br>';
	}
	set_exception_handler('customException');
	
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
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		]
	);
	
	//change cookies to HttpOnly
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], true);
	//set sessions to use the db functions, register session shutdown, then start the session
	session_set_save_handler('dbSessionOpen', 'dbSessionClose', 'dbSessionRead', 'dbSessionWrite', 'dbSessionDestroy', 'dbSessionGc');
	session_register_shutdown();
	session_start();
	
	//destroy a session like this
	/*$cookieParams = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
	session_destroy();*/
	
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
	if (empty($_SESSION['loggedIn']) && $_SERVER['SCRIPT_NAME'] != '/login.php' && $_SERVER['SCRIPT_NAME'] != '/ajax.php') {
		$_SESSION['loginDestination'] = $_SERVER['REQUEST_URI'];
		header('Location: login.php');
		die();
	}
?>