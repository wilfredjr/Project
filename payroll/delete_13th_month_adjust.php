<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if(empty($_GET['a_id']))
	{
		redirect('index.php');
		die;
	}else
	{	
		$con->myQuery("UPDATE 13th_month_adjust SET is_deleted=1 WHERE id=?",array($_GET['a_id']));
		Alert("Delete Successful.","success");
		redirect("13th_month_adjust.php?id=".$_GET['id']);
		die();
	}
?>