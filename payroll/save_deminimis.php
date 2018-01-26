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
			
		$inputs=array_map('trim', $inputs);
	
		if(empty($inputs['id']))
		{
			//Insert
			unset($inputs['id']);
			$con->myQuery("INSERT INTO de_minimis_benefits(dmb_code,dmb_desc,dmb_amount) VALUES(:dmb_code,:dmb_desc,:dmb_amount)",$inputs);

			Alert("Save succesful","success");
		}else
		{				
			//Update
			$con->myQuery("UPDATE de_minimis_benefits SET dmb_code=:dmb_code,dmb_desc=:dmb_desc,dmb_amount=:dmb_amount WHERE id=:id",$inputs);
			Alert("Update succesful","success");
			//cinsertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']," Modified {$inputs['first_name']} {$inputs['last_name']} details.");
		}
		redirect("deminimis.php");
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>