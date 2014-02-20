<?php
	require_once('connect.php');
	require('checkLogin.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<title>erp</title>
	
	<?php
		require('head.php');
	?>
</head>

<body>
	<div id="container">
		<div id="header">
			<ul>
				<li><a href="#"><img src="images/icons/home_32.png" alt="" /></a></li>
				<li><a href="#"><img src="images/icons/users_32.png" alt="" /></a></li>
				<li><a href="#"><img src="images/icons/money_32.png" alt="" /></a></li>
				<li><a href="#"><img src="images/icons/settings_32.png" alt="" /></a></li>
			</ul>
		</div>
		<div id="content">
			<div id="col1">
				Column 1
			</div>
			<div id="col2">
				Column 2
			</div>
		</div>
		<div id="footer">
			Footer test
		</div>
	</div>
</body>
</html>
