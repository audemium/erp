<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Customer extends Item {
		public function getName($type, $id) {
			global $dbh;
			
			$sth = $dbh->prepare(
				'SELECT firstName, lastName
				FROM customers
				WHERE customerID = :id');
			$sth->execute([':id' => $id]);
			$row = $sth->fetch();
			
			return $row['firstName'].' '.$row['lastName'];
		}
		
		public function generateTypeOptions($type) {
			global $dbh;
			
			$sth = $dbh->prepare(
				'SELECT customerID, firstName, lastName
				FROM customers
				ORDER BY firstName, lastName');
			$sth->execute();
			while ($row = $sth->fetch()) {
				$return[] = [$row['customerID'], $row['firstName'].' '.$row['lastName']];
			}
			
			return $return;
		}
	}
?>