<?php
require_once 'support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}
if (empty($_SESSION[WEBAPP]['user']['access_project_management'])) {
	redirect("index.php");
	die;
}

if(!empty($_POST)){
		//Validate form inputs

	$inputs=$_POST;
	$errors="";
	$error_count=0;


	var_dump($inputs);
	die;
		$con->beginTransaction();	

		//unset($inputs['shifting_id']);


			

			
			$con->myQuery("UPDATE projects SET name='$proj',description='$des',start_date='$date_start',end_date='$date_end',project_status_id='$project_status_id',is_deleted='0',manager_id='$manager',first_approver_id='$fapprover',second_approver_id='$sapprover',third_approver_id='$tapprover' WHERE id=$project_id");
			



	
			$con->commit();	
			//die;
			Alert("Project successfully updated","success");

			redirect("frm_project_management.php?id=".$project_id);
			die;

	}	
}


?>