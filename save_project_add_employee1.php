<?php
require_once 'support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}

if(!empty($_POST)){
		//Validate form inputs

	$inputs=$_POST;
	$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
	$project_id=$inputs['id'];
	$manage=AccessForProject($project_id, $employee_id);
	// var_dump($_POST);
	// die;
	$errors="";
	$inputs=array_map('trim', $inputs);
		
		if (empty($inputs['employee_id'])){
			$errors.="<li>No Employee Selected.</li>";
		}
			$validate_employee_project_request=$con->myQuery("SELECT id, requested_employee_id, project_id, is_deleted, status_id FROM project_requests WHERE is_deleted=0 AND status_id = 1 AND project_id=".$inputs['id']);
			while($rows = $validate_employee_project_request->fetch(PDO::FETCH_ASSOC)):
				if ($rows['requested_employee_id'] == $inputs['employee_id']) {
					$errors.="<li>Employee already requested.</li>";
				}

			endwhile;

			$validate_employee_project_employees=$con->myQuery("SELECT id, employee_id, project_id, is_deleted FROM projects_employees WHERE is_deleted=0 AND project_id=".$inputs['id']);
			while($rows1 = $validate_employee_project_employees->fetch(PDO::FETCH_ASSOC)):
				if ($rows1['employee_id'] == $inputs['employee_id']) {
					$errors.="<li>Employee already in the project.</li>";
				}

			endwhile;

			$proj_details=$con->myQuery("SELECT id,name,employee_id,manager_id,team_lead_ba,team_lead_dev,first_approver_id,second_approver_id,third_approver_id from projects WHERE is_deleted=0 AND id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
			
			
			 if(!AllowUser(array(1,2,3)))
	    {
	        redirect("index.php");
	    }
		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id'])){
				redirect("my_projects.php");
			}
			else{
				redirect("my_projects_view.php?id=".urlencode($inputs['id'])."&tab=3");
			}
			die;
		} else {
			// var_dump($inputs);
			// die;

			$con->beginTransaction();	
			$date = new DateTime();

			$date_applied=date_format($date, 'Y-m-d');

			$project_id=$inputs['id'];
			$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
			$emp_utype=$con->myQuery("SELECT utype_id FROM employees WHERE id=?",array($inputs['employee_id']))->fetch(PDO::FETCH_ASSOC);
			$date_applied=date_format($date, 'Y-m-d');

			if ($proj_details['manager_id']==$employee_id) {
				$params=array(
					'proj_id'=>$inputs['id'],
					'requested_employee_id'=>$inputs['employee_id'],
					'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
					'status_id'=>'1',
					'date_filed'=>$date_applied,
					'mod_type'=>"1",
					'manager'=>$inputs['manager_id'],
					'designation'=>$emp_utype['utype_id'],
					'admin'=>$proj_details['employee_id'],
					'step_id'=>'3'
					);

					$con->myQuery("INSERT INTO project_requests (project_id,employee_id,manager_id,status_id,modification_type,date_filed,requested_employee_id,designation_id,admin_id,step_id) VALUES (:proj_id,:employee_id,:manager,:status_id,:mod_type,:date_filed,:requested_employee_id,:designation,:admin,:step_id)",$params);

					$con->commit();	

				// insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['last_name']} added ".$emp_name['first_name']." ".$emp_name['middle_name']." ".$emp_name['last_name']." to project \"$proj\".");
				// //die;
				// $email_settings=getEmailSettings();
               
               
    //             $header="Project Management";
    //             /*
    //             Modify message to be more generic and allow to be sent to multiple people.

    //              */
                
    //             $message="Good day,<br/> You have been added to project \"$proj\" by {$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['middle_name']} {$_SESSION[WEBAPP]['user']['last_name']}. For more details please login to the Secret 6 HRIS.";
    //             $message=email_template($header,$message);
                
                
               
    //         		/*
		  //           Email Recepients 
		  //            */
    //             PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($emp_name['private_email'],$emp_name['work_email']), "Secret 6 Project Management",$message,$email_settings['host'],$email_settings['port']);
				Alert("Request Succesfull","success");

				redirect("my_projects_view.php?id=".$inputs['id']."&tab=3");

			}
			else {
					$params=array(
					'proj_id'=>$inputs['id'],
					'requested_employee_id'=>$inputs['employee_id'],
					'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
					'status_id'=>'1',
					'date_filed'=>$date_applied,
					'mod_type'=>"1",
					'manager'=>$inputs['manager_id'],
					'designation'=>$emp_utype['utype_id'],
					'admin'=>$proj_details['employee_id'],
					'step_id'=>'2'
					);

					$con->myQuery("INSERT INTO project_requests (project_id,employee_id,manager_id,status_id,modification_type,date_filed,requested_employee_id,designation_id,admin_id,step_id) VALUES (:proj_id,:employee_id,:manager,:status_id,:mod_type,:date_filed,:requested_employee_id,:designation,:admin,:step_id)",$params);

					$con->commit();	

					// $emp_name=getEmpDetails($inputs['employee_id']);
					// $proj = $proj_details['name'];

					// insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['last_name']} request to add ".$emp_name['first_name']." ".$emp_name['middle_name']." ".$emp_name['last_name']." to project \"$proj\".");
					// //die;
					// $email_settings=getEmailSettings();
	               
	               
	    //             $header="Project Management";
	    //             /*
	    //             Modify message to be more generic and allow to be sent to multiple people.

	    //              */
	               
	    //             $message="Good day,<br>You have a new employee request. <br> {$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['middle_name']} {$_SESSION[WEBAPP]['user']['last_name']} wants to add {$emp_name['first_name']} {$emp_name['last_name']} to project \"$proj\".<br> For more details please login to the Secret 6 HRIS.";
	    //             $message=email_template($header,$message);
	    //             //die;
	    //             $fapprover=getEmpDetails($proj_details['first_approver_id']);
	    //             // var_dump($fapprover);
	    //             // die;
	               
	    //         		/*
			  //           Email Recepients 
			  //            */
	    //             PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($fapprover['private_email'],$fapprover['work_email']), "Secret 6 Project Management",$message,$email_settings['host'],$email_settings['port']);
					Alert("Request Succesfull","success");

					redirect("my_projects_view.php?id=".$inputs['id']."&tab=3");
			}
		}

}


?>