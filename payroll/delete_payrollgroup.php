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
		$con->myQuery("UPDATE payroll_groups SET is_deleted=1 WHERE payroll_group_id=?",array($_GET['id']));
		Alert("Delete Successful.","success");
		redirect("view_payrollgroups.php");
		die();
	}
?>