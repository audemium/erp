<?php
	require_once('init.php');
	header('Content-Type: application/json');
	
	if ($_POST['action'] == 'login') {
		$return = array('status' => 'fail');
		
		$sth = $dbh->prepare('SELECT employeeID, password, timeZone FROM employees WHERE username = :username');
		$sth->execute(array(':username' => $_POST['username']));
		$result = $sth->fetchAll();
		if (count($result) == 1) {
			if (password_verify($_POST['password'], $result[0]['password'])) {
				//user is verified
				session_regenerate_id(true);
				$_SESSION['loggedIn'] = true;
				$_SESSION['employeeID'] = $result[0]['employeeID'];
				$_SESSION['timeZone'] = $result[0]['timeZone'];
				$redirect = (isset($_SESSION['loginDestination']) && $_SESSION['loginDestination'] != '/login.php') ? $_SESSION['loginDestination'] : '/index.php';
				unset($return);
				$return = array('status' => 'success', 'redirect' => $redirect);
				unset($_SESSION['loginDestination']);
			}
		}
		
		echo json_encode($return);
	}
	
	if ($_POST['action'] == 'search') {
		$return = array('status' => 'fail');
		
		//TODO: get search results
		unset($return);
		$results = array(
			array('name' => 'Nicholas Anderson', 'type' => 'Employeee', 'image' => 'user_blue_32.png', 'url' => 'employees.php?id=1'),
			array('name' => 'Website Development', 'type' => 'Service', 'image' => 'basket_32.png', 'url' => 'services.php?id=1'),
			array('name' => 'Owner', 'type' => 'Position', 'image' => 'user_business_32.png', 'url' => 'positions.php?id=1')
		);
		$return = array('status' => 'success', 'results' => $results);
		
		echo json_encode($return);
	}
?>