<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	require('../settings.php');
	require('database.php');
	
	if (php_sapi_name() == 'cli') {
		$break = PHP_EOL;
	} else {
		$break = '<br>';
	}
	
	//connect to db
	$dbh = new PDO(
		'mysql:host='.$SETTINGS['dbServer'].';dbname='.$SETTINGS['dbName'],
		$SETTINGS['dbUser'],
		$SETTINGS['dbPassword'],
		[
			PDO::ATTR_PERSISTENT => false
			//default for emulate prepares is true, which allows things like using the same parameter multiple times
		]
	);
	
	//execute queries
	$maxVersion = $VERSION;
	foreach ($sql as $testVersion => $queryArr) {
		if (version_compare($testVersion, $VERSION) === 1) {
			$maxVersion = $testVersion;
			foreach ($queryArr as $query) {
				$sth = $dbh->prepare($query);
				$sth->execute();
				echo $query.$break;
			}
		}
	}
	
	//update version in settings.php
	if (version_compare($maxVersion, $VERSION) === 1) {
		$settingsStr = file_get_contents('../settings.php');
		$newStr = str_replace($VERSION, $maxVersion, $settingsStr);
		$fileStatus = file_put_contents('../settings.php', $newStr);
	}
	
	echo 'Old Version: '.$VERSION.$break;
	echo 'New Version: '.$maxVersion.$break;
	
	if (version_compare($maxVersion, $VERSION) === 1) {
		$fileStr = ($fileStatus === false) ? 'However, we were not able to update the version in your settings file. Change the version in settings.php to '.$maxVersion : 'Your settings file was also changed to have the correct version.';
		echo 'The database has been updated. '.$fileStr;
	}
	elseif (version_compare($maxVersion, $VERSION) === 0) {
		echo 'It seems things are already up to date.';
	}
	else {
		echo 'Something went wrong. Your settings file might not have the right version in it.';
	}
?>