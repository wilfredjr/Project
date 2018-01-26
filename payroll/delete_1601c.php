<?php
	require_once("../support/config.php");
	
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if(!empty($_POST))
	{
		$con->myQuery("UPDATE sixteen_zero_one_c SET is_deleted=1 WHERE id=?",array($_POST['id']));
		Alert("Successfully Deleted!","success");
		redirect("view_1601c.php");
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>