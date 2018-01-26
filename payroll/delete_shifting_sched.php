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
		$con->myQuery("UPDATE employees_shift_master SET is_deleted=1 WHERE id=?",array($_GET['id']));
		$con->myQuery("UPDATE employees_shift_details SET is_deleted=1 WHERE employee_shift_master_id=?",array($_GET['id']));
		Alert("Delete Successful.","success");
		redirect("../payroll/view_shifting_sched.php");
		die();
	}
?>