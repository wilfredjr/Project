<?php
	require_once("support/config.php");
	
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
			
		$errors="";
		if (empty($inputs['rta_code'])){
			$errors.="<li>Enter Receivable Allowance Code.</li>";
		}else {

			$validate=$con->myQuery("SELECT rta_code FROM receivable_and_taxable_allowances WHERE is_deleted = 0 AND rta_code='".$inputs['rta_code']."'")->fetchAll(PDO::FETCH_ASSOC);

		
			if (!empty($validate)) {
				$errors.="<li>Code already exist.</li>";
			}
		}
		if (empty($inputs['rta_desc'])){
			$errors.="<li>Enter Receivable Allowance Description.</li>";
		}
		

		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id'])){
				redirect("frm_taxable_allowances.php");
			}
			else{
				redirect("ffrm_taxable_allowances.php?id=".urlencode($inputs['id']));
			}
			die;
		}
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