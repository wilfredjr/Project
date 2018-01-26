<?php
	require_once("../support/config.php");
	
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if(!empty($_POST))
	{
		$con->myQuery("UPDATE last_pay SET is_deleted=1 WHERE id=?",array($_POST['id']));
		Alert("Successfully Deleted!","success");
		redirect("../payroll/last_pay.php");
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>