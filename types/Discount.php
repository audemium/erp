<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Discount extends GenericItem {
		public function parseValue($type, $item) {
			foreach ($item as $field => $value) {
				switch ($field) {
					case 'discountType':
						$parsed[$field] = ($value == 'C') ? 'Cash' : 'Percentage';
						break;
					case 'discountAmount':
						$parsed[$field] = ($item['discountType'] == 'C') ? formatCurrency($value) : ($value + 0).'%';
						break;
					default:
						$parsed[$field] = $value;
				}
				if (isset($parsed[$field]) && ($field != 'discountAmount' || ($field == 'discountAmount' && $item['discountType'] != 'C'))) {
					$parsed[$field] = htmlspecialchars($parsed[$field], ENT_QUOTES | ENT_HTML5, 'UTF-8');
				}
			}
			
			return $parsed;
		}
	}
?>