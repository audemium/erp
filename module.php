<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	require_once('init.php');
	if (empty($_SESSION['loggedIn'])) {
		$_SESSION['loginDestination'] = $_SERVER['HTTP_REFERER'];
		http_response_code(401);
		die();
	}
	header('Content-Type: application/json');
	
	/* Notifications */
	if ($_POST['module'] == 'notifications') {
		$return['title'] = 'Notifications';
		$return['content'] = 'WIP';
		
		echo json_encode($return);
	}
	
	/* Recent Transactions */
	if ($_POST['module'] == 'recentTransactions') {
		$return['title'] = 'Recent Transactions';
		$return['content'] = 'WIP';
		//TODO: expensePayments and orderPayments need a date
		//TODO: eventually should include payments to employees
		
		echo json_encode($return);
	}
	
	/* Unpaid Orders */
	if ($_POST['module'] == 'unpaidOrders') {
		$return['title'] = 'Unpaid Orders';
		
		$return['content'] = '<table class="dataTable stripe row-border" style="width:100%;">
			<thead>
				<tr>
					<th>Order</th>
					<th>Customer</th>
					<th>Amount</th>
				</tr>
			</thead>
			<tbody>';
				//TODO: sort by order creation date?
				$sth = $dbh->prepare(
					'SELECT orderID, customerID, amountDue
					FROM orders
					WHERE amountDue > 0 AND orders.active = 1');
				$sth->execute();
				while ($row = $sth->fetch()) {
					$return['content'] .= '<tr><td><a href="item.php?type=order&id='.$row['orderID'].'">Order #'.$row['orderID'].'</a></td>';
					$return['content'] .= '<td>'.getLinkedName('customer', $row['customerID']).'</td>';
					$return['content'] .= '<td>'.formatCurrency($row['amountDue']).'</td></tr>';
				}
			$return['content'] .= '</tbody>
		</table>';
		
		echo json_encode($return);
	}
	
	/* Unpaid Bills */
	if ($_POST['module'] == 'unpaidBills') {
		$return['title'] = 'Unpaid Bills';
		
		$return['content'] = '<table class="dataTable stripe row-border" style="width:100%;">
			<thead>
				<tr>
					<th>Expense</th>
					<th>Supplier</th>
					<th>Amount</th>
				</tr>
			</thead>
			<tbody>';
				//TODO: sort by expense creation date?
				$sth = $dbh->prepare(
					'SELECT expenseID, supplierID, amountDue
					FROM expenses
					WHERE amountDue > 0 AND expenses.active = 1');
				$sth->execute();
				while ($row = $sth->fetch()) {
					$return['content'] .= '<tr><td><a href="item.php?type=expense&id='.$row['expenseID'].'">Expense #'.$row['expenseID'].'</a></td>';
					$return['content'] .= '<td>'.getLinkedName('supplier', $row['supplierID']).'</td>';
					$return['content'] .= '<td class="textRight">'.formatCurrency($row['amountDue']).'</td></tr>';
				}
			$return['content'] .= '</tbody>
		</table>';
		
		echo json_encode($return);
	}
	
	/* Income */
	if ($_POST['module'] == 'income') {
		$return['title'] = 'Income';
		$return['content'] = 'WIP';
		
		echo json_encode($return);
	}
	
	/* Expenses */
	if ($_POST['module'] == 'expenses') {
		$return['title'] = 'Expenses';
		$return['content'] = 'WIP';
		
		echo json_encode($return);
	}
?>