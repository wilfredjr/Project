<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if(empty($_GET['id']))
	{
		redirect('index.php');
		die;
	}else
	{	
		$con->myQuery("UPDATE shifts SET is_deleted=1 WHERE id=?",array($_GET['id']));
		$con->myQuery("UPDATE shift_working_days SET is_deleted=1 WHERE shift_id=?",array($_GET['id']));
		Alert("Delete Successful.","success");
		redirect("../payroll/view_shift.php");
		die();
	}
?>