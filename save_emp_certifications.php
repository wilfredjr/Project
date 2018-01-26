<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }

     if(!AllowUser(array(1,4))){
         redirect("index.php");
     }

		if(!empty($_POST)){
		//Validate form inputs
		$inputs=$_POST;
		// echo "<pre>";
		// print_r($inputs);
		// echo "</pre>";
		// die;

		if(empty($inputs['employee_id'])){
			Modal("Invalid Record Selected");
			redirect("employees.php");
		}

		$required_fieds=array(
			"certification_id"=>"Select Certification. <br/>",
			"institute"=>"Enter Institute. <br/>",
			"date_given"=>"Select Date Given. <br/>"
			);
		$errors="";

		foreach ($required_fieds as $key => $value) {
			if(empty($inputs[$key])){
				$errors.=$value;
			}else{
				#CUSTOM VALIDATION
			}
		}
		$tab=6;

		try {
			  $test=new DateTime($inputs['date_given']);
		} catch (Exception $e) {
			  $errors.="Invalid Date Format";
		}

		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id'])){
				redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}");
			}
			else{
				redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}&ee_id={$inputs['id']}");
			}
			die;
		}
		else{
			// echo "<pre>";
			// print_r($inputs);
			// echo "</pre>";
			// die;

			//IF id exists update ELSE insert
			$inputs['date_given']=date_format(date_create($inputs['date_given']),'Y-m-d');
			if(empty($inputs['id'])){
				//Insert
				unset($inputs['id']);

				$con->myQuery("INSERT INTO employees_certifications(
					employee_id,
					certification_id,
					institute,
					date_given,
					remarks
					) VALUES(
					:employee_id,
					:certification_id,
					:institute,
					:date_given,
					:remarks
					)",$inputs);

				$skill_name=$con->myQuery("SELECT name FROM certifications WHERE id=?",array($inputs['certification_id']))->fetchColumn();
				$emp=getEmpDetails($inputs['employee_id']);
				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Added ({$skill_name}) Certification to ({$emp['last_name']}, {$emp['first_name']} {$emp['middle_name']}).");
			}
			else{
				//Update

				$con->myQuery("UPDATE employees_certifications SET
					employee_id=:employee_id,
					certification_id=:certification_id,
					institute=:institute,
					date_given=:date_given,
					remarks=:remarks
					WHERE id=:id
					",$inputs);

				$skill_name=$con->myQuery("SELECT name FROM certifications WHERE id=?",array($inputs['certification_id']))->fetchColumn();
				$emp=getEmpDetails($inputs['employee_id']);
				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Modified ({$skill_name}) Certification of ({$emp['last_name']}, {$emp['first_name']} {$emp['middle_name']}).");
			}

			Alert("Save succesful","success");
			redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>
