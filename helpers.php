<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	function formatCurrency($amount) {
		return '$'.number_format($amount, 2);
	}
	
	function formatDateTime($unixTS) {
		return date('d-M-Y H:i', $unixTS);
	}
	
	function getName($type, $id) {
		global $dbh;
		global $TYPES;
		
		if ($type == 'employee') {
			$sth = $dbh->prepare(
				'SELECT firstName, lastName
				FROM employees
				WHERE employeeID = :id'
			);
		}
		else {
			$sth = $dbh->prepare(
				'SELECT name
				FROM '.$TYPES[$type]['pluralName'].'
				WHERE '.$TYPES[$type]['idName'].' = :id'
			);
		}
		$sth->execute(array(':id' => $id));
		$row = $sth->fetch();
		
		return ($type == 'employee') ? $row['firstName'].' '.$row['lastName'] : $row['name'];
	}
?>