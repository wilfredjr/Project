<?php
	require_once("support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if (empty($_SESSION[WEBAPP]['user']['access_project_management'])) {
		redirect("index.php");
		die;
	}
	if(empty($_GET['id']))
	{
		redirect('index.php');
		die;
	}else
	{	
		$con->myQuery("UPDATE projects SET is_deleted=1 WHERE id=?",array($_GET['id']));
		//$con->myQuery("UPDATE employees_shift_details SET is_deleted=1 WHERE employee_shift_master_id=?",array($_GET['id']));
		Alert("Delete Successful.","success");
		redirect("project_management.php");
		die();
	}
?>