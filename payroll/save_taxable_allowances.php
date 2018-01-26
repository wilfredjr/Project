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
			$con->myQuery("INSERT INTO receivable_and_taxable_allowances(rta_code,rta_desc,rta_amount,rta_taxable) VALUES(:rta_code,:rta_desc,:rta_amount,:rta_taxable)",$inputs);

			Alert("Save succesful","success");
		}else
		{				
			//Update
			$con->myQuery("UPDATE receivable_and_taxable_allowances SET rta_code=:rta_code,rta_desc=:rta_desc,rta_amount=:rta_amount,rta_taxable=:rta_taxable WHERE id=:id",$inputs);
			Alert("Update succesful","success");
			//cinsertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']," Modified {$inputs['first_name']} {$inputs['last_name']} details.");
		}
		redirect("taxable_allowances.php");
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>