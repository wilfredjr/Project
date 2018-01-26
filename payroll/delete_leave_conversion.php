<?php
	require_once("../support/config.php");
	
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if(!empty($_POST))
	{
		$con->myQuery("UPDATE leave_conversion SET is_deleted=1 WHERE id=?",array($_POST['id']));
		Alert("Successfully Deleted!","success");
		redirect("leave_conversion.php");
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>