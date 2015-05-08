<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class GenericItem {
		public function printItemBody($id) {
			return '';
		}
		
		public function getName($type, $id) {
			global $dbh;
			global $TYPES;
			
			$sth = $dbh->prepare(
				'SELECT name
				FROM '.$TYPES[$type]['pluralName'].'
				WHERE '.$TYPES[$type]['idName'].' = :id');
			$sth->execute([':id' => $id]);
			$row = $sth->fetch();
			
			return $row['name'];
		}
		
		public function parseValue($type, $field, $value) {
			return $value;
		}
		
		public function generateTypeOptions($type) {
			global $dbh;
			global $TYPES;
			$return = [];
			
			$sth = $dbh->prepare(
				'SELECT '.$TYPES[$type]['idName'].', name
				FROM '.$TYPES[$type]['pluralName'].'
				WHERE active = 1
				ORDER BY name');
			$sth->execute();
			while ($row = $sth->fetch()) {
				$return[] = [$row[$TYPES[$type]['idName']], $row['name']];
			}
			
			return $return;
		}
		
		public function customAjax($id, $data) {
			return $return = ['status' => 'success'];
		}
		
		public function printPopups() {
			return '';
		}
	}
?>