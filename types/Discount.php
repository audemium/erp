<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Discount extends GenericItem {
		public function parseValue($type, $field, $value) {
			switch ($field) {
				case 'discountType':
					$parsed = ($value == 'C') ? 'Cash' : 'Percentage';
					break;
				case 'discountAmount':
					//TODO: sometimes this will be a currency and sometimes it's a percentage, no idea how i'll do that
					$parsed = formatCurrency($value);
					break;
				default:
					$parsed = $value;
			}
			
			return $parsed;
		}
	}
?>