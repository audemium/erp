<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Location extends GenericItem {
		public function printItemBody($id) {
			global $dbh;
			global $TYPES;
		
			$return = '<section>
				<h2>Inventory</h2>
				<div class="sectionData">
					<table class="dataTable stripe row-border"> 
						<thead>
							<tr>
								<th class="textLeft">Product</th>
								<th class="textRight">Quantity</th>
							</tr>
						</thead>
						<tbody>';
							$sth = $dbh->prepare(
								'SELECT *
								FROM locations_products
								WHERE locationID = :locationID');
							$sth->execute([':locationID' => $id]);
							while ($row = $sth->fetch()) {
								$return .= '<tr><td>'.getLinkedName('product', $row['productID']).'</td>';
								$return .= '<td class="textRight">'.$row['quantity'].'</td></tr>';
							}
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			return $return;
		}
	}
?>