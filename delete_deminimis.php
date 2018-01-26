<?php
	require_once("support/config.php");
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
		$con->myQuery("UPDATE de_minimis_benefits SET is_deleted=1 WHERE id=?",array($_GET['id']));
		Alert("Delete Successful.","success");
		redirect("deminimis.php");
		die();
	}
?>