<?php
	require_once("../support/config.php");
	
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if(!empty($_POST))
	{
	#VALIDATE INPUTS
		$inputs=$_POST;			

		$con->myQuery("UPDATE sixteen_zero_one_c SET is_processed=1, date_processed=CURDATE() WHERE id=?",array($inputs['p_id']));
		
		Alert("Successfully Processed!","success");
		redirect("1601c_view.php?id=".$inputs['p_id']);
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>