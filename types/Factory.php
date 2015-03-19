<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Factory {
		public static function createItem($type) {
			global $TYPES;
			$class = $TYPES[$type]['formalName'];
			if (!class_exists($class)) {
				throw new Exception('Missing class '.$class);
			}
			return new $class;
		}
	}
?>