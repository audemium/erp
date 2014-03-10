<?php
	require_once('init.php');
	header('Content-Type: application/json');
	
	if ($_POST['action'] == 'login') {
		$return = array('status' => 'fail');
		
		$sth = $dbh->prepare('SELECT employeeID, password FROM employees WHERE username = :username');
		$sth->execute(array(':username' => $_POST['username']));
		$result = $sth->fetchAll();
		if (count($result) == 1) {
			if (password_verify($_POST['password'], $result[0]['password'])) {
				//user is verified
				session_regenerate_id(true);
				$_SESSION['loggedIn'] = true;
				$_SESSION['employeeID'] = $result[0]['employeeID'];
				unset($return);
				$redirect = (isset($_SESSION['loginDestination']) && $_SESSION['loginDestination'] != '/login.php') ? $_SESSION['loginDestination'] : '/index.php';
				$return = array('status' => 'success', 'redirect' => $redirect);
				unset($_SESSION['loginDestination']);
			}
		}
		
		echo json_encode($return);
	}
?>