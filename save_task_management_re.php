<?php
	require_once("support/config.php");
	 if(!isLoggedIn())
	 {
	 	toLogin();
	 	die();
	 }
	if(!empty($_POST))
	{
		//Validate form inputs
		$inputs=$_POST;

		$errors="";
			$current=$con->myQuery("SELECT * FROM project_task_list WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
			$admin_id=$con->myQuery("SELECT employee_id FROM projects WHERE id=?",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
			 $sent=$con->myQuery("SELECT id FROM project_task WHERE task_list_id=? AND (request_status_id=1 OR request_status_id=3)",array($current['id']))->fetch(PDO::FETCH_ASSOC);
			 if(!empty($sent)){
			 	$errors.="Task Reassignment has already been submitted. <br>";
			 }
			 if($current['is_submitted']==1){
			 	$errors.="Cannot reassign, task completion has already been submitted. <br>";
			 }
			 if($current['employee_id']==$inputs['employee_id']){
			 	$errors.="Task already assigned to the employee. <br>";
			 }
			
		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id']))
			{
				redirect("index.php");
			}
			else
			{
				redirect("my_projects_view.php?id=".urlencode($inputs['project_id'])."&tab=4");
			}
			die;
		}
		else
		{
			// $inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
			$inputs['request_status_id']=1;
			$s=$inputs['request_status_id'];
			$st=$s;
			//IF id exists update ELSE insert
				//Insert
				$date_s1=new DateTime($current['date_start']);
				$date_s=$date_s1->format('Y-m-d');
				$date_e1=new DateTime($current['date_end']);
				$date_e=$date_e1->format('Y-m-d');

					$inputs['requestor_id'] = $_SESSION[WEBAPP]['user']['employee_id'];
					if($current['manager_id']==$inputs['requestor_id']){
						$step_id=3;
					}else{
						$step_id=2;
					}
					$params=array(
					"employee"=>$inputs['employee_id'],
					"project_id"=>$current['project_id'],
					"phase_id"=>$current['project_phase_id'],
					"date_start"=>$date_s,
					"date_end"=>$date_e,
					"manager"=>$current['manager_id'],
					"w"=>$current['worked_done'],
					"stats"=>$st,
					"step_id"=>$step_id,
					"admin"=>$admin_id['employee_id'],
					"requestor_id" => $inputs['requestor_id'],
					"task_id"=>$current['id']
					);
					$con->myQuery("INSERT INTO
								project_task(
									employee_id,
									project_id,
									project_phase_id,
									date_start,
									date_end,
									date_filed,
									request_status_id,
									requestor_id,
									manager_id,
									worked_done,
									admin_id,
									step_id,
									task_list_id
								) VALUES(
									:employee,
									:project_id,
									:phase_id,
									DATE_FORMAT(:date_start,'%Y-%m-%d'),
									DATE_FORMAT(:date_end,'%Y-%m-%d'),
									CURDATE(),
									:stats,
									:requestor_id,
									:manager,
									:w,
									:admin,
									:step_id,
									:task_id
								)",$params);
					
				$project_task_id=$con->lastInsertId();

				$s_date=new datetime($inputs['date_start']);
				$e_date=new datetime($inputs['date_end']);
	//			$interval=$e_date->diff($s_date);
				$e_date = $e_date->modify( '+1 day' );

				$interval = new DateInterval('P1D');
				$daterange = new DatePeriod($s_date, $interval ,$e_date);
				$woweekends=0;

				foreach($daterange as $date)
				{
					$weekday=$date->format("w");
					if($weekday != 0 && $weekday != 6)
					{ # 0=Sunday and 6=Saturday
				        $dates=$date->format("Y-m-d");
				        $emp_l=array(
							"project_task_id"=>$project_task_id,
						    "date"=>$dates
						);
				        $con->myQuery("INSERT INTO
				        				project_task_dates(
				        					project_task_id,
				        					date)
				        				VALUES(
				        					:project_task_id,
				        					:date)",
				        				$emp_l);
				        //echo $dates." <br>";
				        $woweekends++;
				    }
				}

				// $inputs['requestor_id'] = $_SESSION[WEBAPP]['user']['employee_id'];
				// 	if($inputs['manager_id']==$inputs['requestor_id']){
				// 	$phase_stat=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['id'],$inputs['phase_id']))->fetch(PDO::FETCH_ASSOC);
				// 	$params1=array(
				// 	"employee"=>$employee_id,
				// 	"project_id"=>$inputs['id'],
				// 	"phase_id"=>$inputs['phase_id'],
				// 	"date_start"=>$date_s,
				// 	"date_end"=>$date_e,
				// 	"manager_id"=>$inputs['manager_id'],
				// 	"w"=>$inputs['worked_done'],
				// 	"stats"=>$phase_stat['status_id']
				// 	);
				// 	$con->myQuery("INSERT INTO
				// 				project_task_list(
				// 					employee_id,
				// 					project_id,
				// 					project_phase_id,
				// 					date_start,
				// 					date_end,
				// 					status_id,
				// 					manager_id,
				// 					worked_done
				// 				) VALUES(
				// 					:employee,
				// 					:project_id,
				// 					:phase_id,
				// 					DATE_FORMAT(:date_start,'%Y-%m-%d'),
				// 					DATE_FORMAT(:date_end,'%Y-%m-%d'),
				// 					:stats,
				// 					:manager_id,
				// 					:w
				// 				)",$params1);
				// 	}
			//die;
// echo "<pre>";
// print_r($inputs);
// echo "</pre>";
// die();
				// $leave_name=$con->myQuery("SELECT name FROM leaves WHERE id=?",array($inputs['leave_id']))->fetchColumn();
				// if(empty($leave_name))
				// {
				// 	$leave_name="Leave Without Pay";
				// }
				// $audit_message="From ".date("Y-m-d",strtotime($inputs['date_start']))." to ".date("Y-m-d",strtotime($inputs['date_end'])).". Reason: {$inputs['reason']}";
				// $employees=getEmpDetails($employee);
				// $email_settings=getEmailSettings();
				// $approvers=getEmployeesFromSteps($inputs['approval_step_id']);
				// $recepients=array();
				// foreach ($approvers as $key => $approver) {
				// 	if (!empty($approver['private_email'])) {
				// 		$recepients[]=$approver['private_email'];
				// 	}
				// 	if (!empty($approver['work_email'])) {
				// 		$recepients[]=$approver['work_email'];
				// 	}
				// }
				// if ($_SESSION[WEBAPP]['user']['employee_id'] == $inputs['employee_id']) {
				// 	insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed a leave ($leave_name) request. {$audit_message}");
				// } else {
				// 	insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']} filed a leave ($leave_name) request for {$employees['first_name']} {$employees['last_name']}. {$audit_message}");
				// }

				// $requestor_msg="";
				// if (!empty($inputs['requestor_id'])) {
				// 	$requestor_msg="Filed by {$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']}. ";
				// }

				// $header="New Leave Request For Your Approval";
				// $message="Good day,<br/> You have a new leave request from {$employees['last_name']}, {$employees['first_name']}. {$requestor_msg}For more details please login to the Secret 6 HRIS.";
				// $message=email_template($header,$message);

				// /*
				// Email Recepients
				//  */
				// PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Leave Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
				
				Alert("Request successful","success");
				redirect("my_projects_view.php?id=".urlencode($inputs['project_id'])."&tab=4");
		}
		die;
	}
	else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>
