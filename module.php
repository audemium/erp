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
		//technically these are just the last 10 transactions, regardless of how recent they are
		//I could change it to be transactions within the last month, but eh
		$return['title'] = 'Recent Transactions';
		$return['content'] = '<ul>';
		$data = array();
		
		$sth = $dbh->prepare(
			'SELECT CONCAT("E", paymentID) AS paymentID, expenses.expenseID, expensePayments.date, paymentAmount, supplierID
			FROM expensePayments, expenses
			WHERE expensePayments.expenseID = expenses.expenseID
			ORDER BY date DESC LIMIT 10');
		$sth->execute();
		while ($row = $sth->fetch()) {
			$sortThis[$row['paymentID']] = $row['date'];
			$data[$row['paymentID']] = [$row['expenseID'], $row['date'], $row['paymentAmount'], $row['supplierID']];
		}
		
		$sth = $dbh->prepare(
			'SELECT CONCAT("O", paymentID) AS paymentID, orders.orderID, orderPayments.date, paymentAmount, customerID
			FROM orderPayments, orders
			WHERE orderPayments.orderID = orders.orderID
			ORDER BY date DESC LIMIT 10');
		$sth->execute();
		while ($row = $sth->fetch()) {
			$sortThis[$row['paymentID']] = $row['date'];
			$data[$row['paymentID']] = [$row['orderID'], $row['date'], $row['paymentAmount'], $row['customerID']];
		}
		
		//TODO: eventually should include payments to employees
		
		if (count($data) > 0) {
			arsort($sortThis);
			$i = 1;
			foreach ($sortThis as $key => $value) {
				$type = substr($key, 0, 1);
				$id = substr($key, 1);
				if ($type == 'E') {
					$return['content'] .= '<li><a href="item.php?type=expense&id='.$data[$key][0].'">'.formatDate($data[$key][1]).'</a>: Paid '.formatCurrency($data[$key][2]).' to '.getLinkedName('supplier', $data[$key][3]).'.</li>';
				}
				else {
					$return['content'] .= '<li><a href="item.php?type=order&id='.$data[$key][0].'">'.formatDate($data[$key][1]).'</a>: Received '.formatCurrency($data[$key][2]).' from '.getLinkedName('customer', $data[$key][3]).'.</li>';
				}
				if ($i == 10) {
					break;
				}
				$i++;
			}
		}
		else {
			$return['content'] = 'No recent transactions.';
		}
		
		$return['content'] .= '</ul>';
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
	
	/* Net Income */
	if ($_POST['module'] == 'netIncome') {
		$return['title'] = 'Net Income';
		$return['content'] = 'WIP';
		
		$unix = mktime(0, 0, 0, 1, 1, date('Y'));
		if ($SETTINGS['accounting'] == 'cash') {
			//income
			$sth = $dbh->prepare(
				'SELECT SUM(paymentAmount) AS total
				FROM orderPayments
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$income = $sth->fetch();
			
			//expenses
			$sth = $dbh->prepare(
				'SELECT SUM(paymentAmount) AS total
				FROM expensePayments
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$expenses = $sth->fetch();
			
			$net = $income['total'] - $expenses['total'];
			$color = ($net > 0) ? '#8bb50f' : '#d41111';
			$spacer = ($net > 0) ? '&nbsp;' : '';
			$return['content'] = '<span style="color:'.$color.'; font-size:2.7em; font-weight:bold; display:inline-block; margin:7% 0 0 7%;">'.$spacer.formatCurrency($net).'</span>';
			$return['content'] .= '<span style="margin-left:50px;">this year</span>';
		}
		else {
			//income
			$sth = $dbh->prepare(
				'SELECT SUM(lineAmount) AS total
				FROM orders_products
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$op = $sth->fetch();
			
			$sth = $dbh->prepare(
				'SELECT SUM(lineAmount) AS total
				FROM orders_services
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$os = $sth->fetch();
			
			//expenses
			$sth = $dbh->prepare(
				'SELECT SUM(lineAmount) AS total
				FROM expenses_products
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$ep = $sth->fetch();
			
			$sth = $dbh->prepare(
				'SELECT SUM(lineAmount) AS total
				FROM expenseOthers
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$eo = $sth->fetch();
			
			$net = ($op['total'] + $os['total']) - ($ep['total'] + $eo['total']);
			$color = ($net > 0) ? '#8bb50f' : '#d41111';
			$spacer = ($net > 0) ? '&nbsp;' : '';
			$return['content'] = '<span style="color:'.$color.'; font-size:2.7em; font-weight:bold; display:inline-block; margin:7% 0 0 7%;">'.$spacer.formatCurrency($net).'</span>';
			$return['content'] .= '<span style="margin-left:50px;">this year</span>';
		}
		
		echo json_encode($return);
	}
	
	/* Income */
	if ($_POST['module'] == 'income') {
		$return['title'] = 'Income';
		
		$unix = mktime(0, 0, 0, 1, 1, date('Y'));
		if ($SETTINGS['accounting'] == 'cash') {
			//for cash basis, just add the payments we took in
			$sth = $dbh->prepare(
				'SELECT SUM(paymentAmount) AS total
				FROM orderPayments
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$row = $sth->fetch();
			
			$return['content'] = '<span style="color:#8bb50f; font-size:2.7em; font-weight:bold; display:inline-block; margin:7% 0 0 7%;">&nbsp;'.formatCurrency($row['total']).'</span>';
			$return['content'] .= '<span style="margin-left:50px;">this year</span>';
		}
		else {
			//for accrual basis, add up the lineAmount column
			$sth = $dbh->prepare(
				'SELECT SUM(lineAmount) AS total
				FROM orders_products
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$products = $sth->fetch();
			
			$sth = $dbh->prepare(
				'SELECT SUM(lineAmount) AS total
				FROM orders_services
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$services = $sth->fetch();
			
			$total = $products['total'] + $services['total'];
			$return['content'] = '<span style="color:#8bb50f; font-size:2.7em; font-weight:bold; display:inline-block; margin:7% 0 0 7%;">&nbsp;'.formatCurrency($total).'</span>';
			$return['content'] .= '<span style="margin-left:50px;">this year</span>';
		}
		
		echo json_encode($return);
	}
	
	/* Expenses */
	if ($_POST['module'] == 'expenses') {
		$return['title'] = 'Expenses';
		
		$unix = mktime(0, 0, 0, 1, 1, date('Y'));
		if ($SETTINGS['accounting'] == 'cash') {
			//for cash basis, just add the payments we made
			$sth = $dbh->prepare(
				'SELECT SUM(paymentAmount) AS total
				FROM expensePayments
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$row = $sth->fetch();
			
			$return['content'] = '<span style="color:#d41111; font-size:2.7em; font-weight:bold; display:inline-block; margin:7% 0 0 7%;">&nbsp;'.formatCurrency($row['total']).'</span>';
			$return['content'] .= '<span style="margin-left:50px;">this year</span>';
		}
		else {
			//for accrual basis, add up the lineAmount column
			$sth = $dbh->prepare(
				'SELECT SUM(lineAmount) AS total
				FROM expenses_products
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$products = $sth->fetch();
			
			$sth = $dbh->prepare(
				'SELECT SUM(lineAmount) AS total
				FROM expenseOthers
				WHERE date > :unix');
			$sth->execute([':unix' => $unix]);
			$others = $sth->fetch();
			
			$total = $products['total'] + $others['total'];
			$return['content'] = '<span style="color:#d41111; font-size:2.7em; font-weight:bold; display:inline-block; margin:7% 0 0 7%;">&nbsp;'.formatCurrency($total).'</span>';
			$return['content'] .= '<span style="margin-left:50px;">this year</span>';
		}
		
		echo json_encode($return);
	}
?>