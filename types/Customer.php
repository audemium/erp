<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class Customer extends GenericItem {
		public function printItemBody($id) {
			global $dbh;
		
			$return = '<section>
				<h2>Orders</h2>
				<div class="sectionData">
					<table class="dataTable stripe row-border"> 
						<thead>
							<tr>
								<th class="dateTimeHeader">Time</th>
								<th>Order</th>
								<th>Employee</th>
							</tr>
						</thead>
						<tbody>';
							$sth = $dbh->prepare(
								'SELECT orderID, employeeID, date
								FROM orders
								WHERE customerID = :customerID AND active = 1');
							$sth->execute([':customerID' => $id]);
							while ($row = $sth->fetch()) {
								$return .= '<tr><td data-sort="'.$row['date'].'">'.formatDateTime($row['date']).'</td>';
								$return .= '<td>'.getLinkedName('order', $row['orderID']).'</td>';
								$return .= '<td>'.getLinkedName('employee', $row['employeeID']).'</td></tr>';
							}
						$return .= '</tbody>
					</table>
				</div>
			</section>';
			
			return $return;
		}
	
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
			$return = [];
			
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