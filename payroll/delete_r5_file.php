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
		$con->myQuery("UPDATE sss_r5_main SET is_deleted=1 WHERE ref_no=?",array($_GET['id']));
		
		Alert("Delete Successful.","success");
		redirect("../payroll/view_r5.php");
		die();
	}
?>