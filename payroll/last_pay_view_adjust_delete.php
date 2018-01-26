<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(empty($_GET['lp_id']))
	{
		redirect('index.php');
		die;
	}else
	{	
		$con->myQuery("UPDATE last_pay_adjustments SET is_deleted=1 WHERE id=?",array($_GET['lp_id']));
		Alert("Successfully Deleted.","success");
		redirect("last_pay_view.php?id=".$_GET['id']);
		die();
	}
?>