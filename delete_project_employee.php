<?php
	require_once("support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	
// die;
	$inputs=$_POST;
		// var_dump($_POST);
		// die;
	if(empty($inputs['id']))
	{
		redirect('index.php');
		die;
	}elseif(empty($inputs['tab']))
	{
		redirect('my_projects.php');
		die;
	}else
	{	
		if($inputs['tab'] == "1") {
			$project_id=$inputs['id'];
			$proj=$_POST['project_name'];
			
			$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
			$manage=AccessForProject($project_id, $employee_id);

			$inputs = $_POST;
			// var_dump($manage);
			// die;
			$date = new DateTime();

			$date_removed=date_format($date, 'Y-m-d');

			// var_dump($inputs);
			// die;
				$errors="";
				$validate_employee_project_request=$con->myQuery("SELECT id, requested_employee_id, project_id, is_deleted, status_id FROM project_requests WHERE is_deleted=0 AND status_id = 1 AND project_id=".$inputs['id']);
				while($rows = $validate_employee_project_request->fetch(PDO::FETCH_ASSOC)):
					if ($rows['requested_employee_id'] == $inputs['employee_id']) {
						$errors.="<li>Removal of Employee already requested.</li>";
					}

				endwhile;

				$validate_task=$con->myQuery("SELECT id FROM project_task_list WHERE employee_id=? AND status_id != 2 AND project_id=?",array($inputs['employee_id'],$inputs['id']))->fetch(PDO::FETCH_ASSOC);
				if(!empty($validate_task)){
					$errors.="<li>Task/s are still assigned to the employee.</li>";
				}
				if($errors!=""){

				Alert("You have the following error/s: <br/>".$errors,"danger");
					if(empty($inputs['id'])){
						redirect("my_projects.php");
					}
					else{
						redirect("my_projects_view.php?id=".urlencode($inputs['id'])."&tab=2");
					}
				die;
				} 
				$admin=$con->myQuery("SELECT employee_id FROM projects WHERE id=".$inputs['id'])->fetch(PDO::FETCH_ASSOC);
				$con->beginTransaction();	
				$date = new DateTime();

				$date_applied=date_format($date, 'Y-m-d');
				if($manage['is_manager']=='1'){
					$step_id=='3';
				}elseif(($manage['is_team_lead_ba']=='1')OR($manage['is_team_lead_dev']=='1')){
					$step_id='2';
				}
				$params=array(
					'proj_id'=>$inputs['id'],
					'requested_employee_id'=>$inputs['employee_id'],
					'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
					'status_id'=>'1',
					'date_filed'=>$date_applied,
					'mod_type'=>"0",
					'manager'=>$inputs['manager_id'],
					'admin'=>$admin['employee_id'],
					'step_id'=>$step_id,
					'designation'=>$inputs['designation']

					);

					$con->myQuery("INSERT INTO project_requests (project_id,employee_id,manager_id,status_id,modification_type,date_filed,requested_employee_id,designation_id,admin_id,step_id) VALUES (:proj_id,:employee_id,:manager,:status_id,:mod_type,:date_filed,:requested_employee_id,:designation,:admin,:step_id)",$params);

					$con->commit();	
					// $emp_name=getEmpDetails($inputs['employee_id']);

					// $proj_details=$con->myQuery("SELECT id,name,first_approver_id,second_approver_id,third_approver_id from projects WHERE is_deleted=0 AND id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

					// $proj = $proj_details['name'];

					// insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['last_name']} request to remove ".$emp_name['first_name']." ".$emp_name['middle_name']." ".$emp_name['last_name']." from project \"$proj\".");
					// //die;
					// $email_settings=getEmailSettings();
	               
	               
	    //             $header="Project Management";
	    //             /*
	    //             Modify message to be more generic and allow to be sent to multiple people.

	    //              */
	               
	    //             $message="Good day,<br>You have a new employee request. <br> {$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['middle_name']} {$_SESSION[WEBAPP]['user']['last_name']} wants to remove {$emp_name['first_name']} {$emp_name['last_name']} from project \"$proj\".<br> For more details please login to the Secret 6 HRIS.";
	    //             $message=email_template($header,$message);
	    //             //die;
	    //             $fapprover=getEmpDetails($proj_details['first_approver_id']);
	    //             // var_dump($fapprover);
	                 
	               
	    //         		/*
			  //           Email Recepients 
			  //            */
	    //             PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($fapprover['private_email'],$fapprover['work_email']), "Secret 6 Project Management",$message,$email_settings['host'],$email_settings['port']);

					Alert("Request Succesful","success");

					redirect("my_projects_view.php?id=".$inputs['id']."&tab=2");
		}
		elseif($inputs['tab'] == "2") {


			$con->myQuery("UPDATE project_requests SET is_deleted=1 WHERE project_id=".($inputs['id'])." AND requested_employee_id=".($inputs['emp_id']));
			//$con->myQuery("UPDATE employees_shift_details SET is_deleted=1 WHERE employee_shift_master_id=?",array($inputs['id']));
			Alert("Delete Successful.","success");



			redirect("my_projects_view.php?id=".$inputs['id']."&tab=3");

			die();
		}else{
			$con->myQuery("UPDATE project_requests SET is_deleted=1 WHERE project_id=".($inputs['id'])." AND requested_employee_id=".($inputs['emp_id']));
			//$con->myQuery("UPDATE employees_shift_details SET is_deleted=1 WHERE employee_shift_master_id=?",array($inputs['id']));
			Alert("Delete Successful.","success");



			redirect("project_employee_request.php");

			die();
		}
	}
?>