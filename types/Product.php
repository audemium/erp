<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Product extends GenericItem {
		public function printItemBody($id) {
			global $dbh;
			global $TYPES;
		
			$return = '<section>
				<h2>Inventory</h2>
				<div class="sectionData">
					<table class="dataTable stripe row-border"> 
						<thead>
							<tr>
								<th>Location</th>
								<th>Quantity</th>
							</tr>
						</thead>
						<tbody>';
							$sth = $dbh->prepare(
								'SELECT *
								FROM locations_products
								WHERE productID = :productID');
							$sth->execute([':productID' => $id]);
							while ($row = $sth->fetch()) {
								$return .= '<tr><td>'.getLinkedName('location', $row['locationID']).'</td>';
								$return .= '<td>'.$row['quantity'].'</td></tr>';
							}
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			return $return;
		}
		
		public function parseValue($type, $item) {
			foreach ($item as $field => $value) {
				switch ($field) {
					case 'defaultPrice':
						$parsed[$field] = formatCurrency($value);
						break;
					default:
						$parsed[$field] = $value;
				}
				if (isset($parsed[$field]) && $field != 'defaultPrice') {
					$parsed[$field] = htmlspecialchars($parsed[$field], ENT_QUOTES | ENT_HTML5, 'UTF-8');
				}
			}
			
			return $parsed;
		}
	}
?>