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
		
		$inputs=$_POST;
		$inputs=array_map("trim",$inputs);
		$errors="";
		if (empty($inputs['name'])){
			$errors.="Enter Pay Grade Name. <br/>";
		}

		if (empty($inputs['can_apply_for_meal_transpo'])) {
			$inputs['can_apply_for_meal_transpo']=0;
		}
		if (empty($inputs['allow_overtime'])) {
			$inputs['allow_overtime']=0;
		}
		if (empty($inputs['view_employee_leave_calendar'])) {
			$inputs['view_employee_leave_calendar']=0;
		}
		if (empty($inputs['access_project_management'])) {
			$inputs['access_project_management']=0;
		}

		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id'])){
				redirect("frm_pay_grade.php");
			}
			else{
				redirect("frm_pay_grade.php?id=".urlencode($inputs['id']));
			}
			die;
		}
		else{
			//IF id exists update ELSE insert
			if(empty($inputs['id'])){
				//Insert
				unset($inputs['id']);
				
				$con->myQuery("INSERT INTO pay_grade(level, can_apply_for_meal_transpo, allow_overtime, view_employee_leave_calendar,access_project_management) VALUES(:name, :can_apply_for_meal_transpo, :allow_overtime, :view_employee_leave_calendar,:access_project_management)",$inputs);
			}
			else{
				//Update
				
				$con->myQuery("UPDATE pay_grade SET level=:name, can_apply_for_meal_transpo=:can_apply_for_meal_transpo, allow_overtime=:allow_overtime, view_employee_leave_calendar=:view_employee_leave_calendar,access_project_management=:access_project_management WHERE id=:id",$inputs);
			}
			
			Alert("Save succesful","success");
			redirect("pay_grade.php");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>