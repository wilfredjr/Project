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
		if (empty($inputs['comde_code'])){
			$errors.="<li>Enter Company Deduction Code.</li>";
		}else {
			$validate=$con->myQuery("SELECT * FROM company_deductions WHERE is_deleted = 0 AND comde_code='".$inputs['comde_code']."'")->fetchAll(PDO::FETCH_ASSOC);

		
			if (!empty($validate)) {
				$errors.="<li>Code already exist.</li>";
			}
		}
		if (empty($inputs['comde_desc'])){
			$errors.="<li>Enter Company Description.</li>";
		}
		

		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id'])){
				redirect("frm_company_deductions.php");
			}
			else{
				redirect("frm_company_deductions.php?id=".urlencode($inputs['id']));
			}
			die;
		}

		if(empty($inputs['id']))
		{
			//Insert
			unset($inputs['id']);
			$con->myQuery("INSERT INTO company_deductions(comde_code,comde_desc) VALUES(:comde_code,:comde_desc)",$inputs);

			Alert("Save succesful","success");
		}else
		{				
			//Update
			$con->myQuery("UPDATE company_deductions SET comde_code=:comde_code,comde_desc=:comde_desc WHERE id=:id",$inputs);
			Alert("Update succesful","success");
			//cinsertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']," Modified {$inputs['first_name']} {$inputs['last_name']} details.");
		}
		
		redirect("company_deductions.php");
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>