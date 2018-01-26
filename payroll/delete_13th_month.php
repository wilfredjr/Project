<?php
	require_once("../support/config.php");
	
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if(!empty($_POST))
	{
		$con->myQuery("UPDATE 13th_month SET is_deleted=1 WHERE id=?",array($_POST['id']));
		Alert("Successfully Deleted!","success");
		redirect("13th_month.php");
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>