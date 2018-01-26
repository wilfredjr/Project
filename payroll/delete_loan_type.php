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
		$con->myQuery("UPDATE loans SET is_deleted=1 WHERE loan_id=?",array($_GET['id']));
		Alert("Delete Successful.","success");
		redirect("../payroll/view_loan_list.php");
		die();
	}
?>