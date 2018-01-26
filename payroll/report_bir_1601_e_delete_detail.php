<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(empty($_GET['Eid']) && empty($_GET['id']))
	{
		redirect('index.php');
		die;
	}else
	{	
		$con->myQuery("UPDATE bir_1601_e_details SET is_deleted=1 WHERE id=?",array($_GET['Eid']));
		Alert("Successfully Deleted.","success");
		redirect("report_bir_1601_e_view.php?id=".$_GET['id']);
		die();
	}
?>