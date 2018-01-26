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
	 	
		$required_fieds=array(
			"worked_done"=>"Enter Worked Done. <br/>",
			"project_id"=>"Select Project <br/>"
			);
		$errors="";

		foreach ($required_fieds as $key => $value)
		{
			if(empty($inputs[$key]))
			{
				$errors.=$value;
			}
		}
		$tab=6;

		$s_time=$inputs['time_start'];
		$e_time=$inputs['time_end'];

		if ($s_time < $e_time)
		{
			$s_time=$inputs['ot_date'].' '.date_format(date_create($s_time), 'H:i:s');
			$e_time=$inputs['ot_date'].' '.date_format(date_create($e_time), 'H:i:s');
			//echo $s_time."<br>".$e_time;
		}else
		{
			$s_time=$inputs['ot_date'].' '.date_format(date_create($s_time), 'H:i:s');
			$e_time=date('Y-m-d', strtotime($inputs['ot_date'] . ' +1 day')).' '.date_format(date_create($e_time), 'H:i:s');
			//echo $s_time."<br>".$e_time;
		}
		$min =(strtotime($e_time) - strtotime($s_time)) / 60; 
		$zero    = new DateTime('@0');
		$offset  = new DateTime('@' . $min * 60);
		$diff    = $zero->diff($offset);
		$total_time = $diff->format('%H:%I');
		function decimalHours($time)
		{
		    $hms = explode(":", $time);
		    return ($hms[0] + ($hms[1]/60));
		}
		$decimalHours = decimalHours($total_time);

		$ot_total_time=number_format($decimalHours,'2','.','');
		$inputs['no_hours']=$ot_total_time;

		$inputs['ot_date']=date_format(date_create($inputs['ot_date']), 'Y-m-d');		
		$inputs['time_start']=date_format(date_create($inputs['time_start']), 'H:i:s');
		$inputs['time_end']=date_format(date_create($inputs['time_end']), 'H:i:s');
		
		if (empty($inputs['employees_id'])) {
			$inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
		}
		if (is_array($inputs['employees_id'])) {
			foreach ($inputs['employees_id'] as $key => $employee_id) {
				if (empty(getApprovalFlow(getEmployeeDepartment($employee_id)))) {
					$emp_details = getEmpDetails($employee_id);
					$errors.="No Approval flow selected for {$emp_details['first_name']} {$emp_details['last_name']}.<br/>";
				}
				if (!empty(getFiledOvertime($inputs['ot_date'], $inputs['time_start'], $inputs['time_end'], $employee_id))) {
					$emp_details = getEmpDetails($employee_id);
					$errors.= "Existing overtime found for {$emp_details['first_name']} {$emp_details['last_name']}.<br/>";
				}
			}
		} else {
			/*
			Get the approval flow using the department id of the logged in user.
			 */
			$approval_flow=getApprovalFlow($_SESSION[WEBAPP]['user']['department_id']);
			if (empty($approval_flow)) {
				$errors.=" No approval flow selected. Please contact your Administrator. <br/>";
			}
		}
		
		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect("frm_ot_claim.php");
			die;
		}
		else
		{
				// var_dump($inputs);
				// die();
				

#Insert OT CLAIM
				
				$inputs['st']=1;
				unset($inputs['hour']);
				unset($inputs['minute']);
				unset($inputs['meridian']);
				unset($inputs['get_id']);

				

				if (is_array($inputs['employees_id'])) {
					// Multiple Employee Selected
					foreach ($inputs['employees_id'] as $employee_id) {
						$approval_flow=getApprovalFlow(getEmployeeDepartment($employee_id));
						$inputs['employees_id'] = $employee_id;
						$inputs['approval_step_id']=$approval_flow[0]['id'];
						$inputs['requestor_id'] = $_SESSION[WEBAPP]['user']['employee_id'];
						// var_dump($inputs);
						$con->myQuery("INSERT INTO employees_ot(
						employees_id,
						ot_date,
						time_from,
						time_to,
						approval_step_id,
						no_hours,
						request_status_id,
						date_filed,
						worked_done,
						project_id,
						requestor_id
						) VALUES(
						:employees_id,
						:ot_date, 
						:time_start,
						:time_end,
						:approval_step_id,
						:no_hours,
						:st,
						CURDATE(),
						:worked_done,
						:project_id,
						:requestor_id
						)",$inputs); 
						
						$query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
						$request_id = $con->lastInsertId();
						$values=array();
						foreach ($approval_flow as $key => $step) {
							$value="";
							$value.="(";
							$value.="{$step['id']},{$request_id},'overtime',{$step['step_number']}";
							$value.=")";
							$values[]=$value;
						}
						$query.=implode(",", $values);
						$con->myQuery($query);

						$employees=getEmpDetails($inputs['employees_id']);
						// $supervisor=getEmpDetails($inputs['supervisor_id']);

						insertAuditLog($employees['last_name'].", ".$employees['first_name']." ".$employees['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed an overtime request. From {$s_time} To {$e_time} for {$inputs['no_hours']} Hours. Worked to be done:{$inputs['worked_done']}");
						$email_settings=getEmailSettings();
						//var_dump($supervisor);
						/*
						Get Employees from approval_step_employees based on the first step
						 */
						$approvers=getEmployeesFromSteps($inputs['approval_step_id']);
						/*
						Modify message to be more generic and allow to be sent to multiple people.
						 */
						$header="New Overtime Request For Your Approval";
						$message="Good day,<br/> You have a new overtime request from {$employees['last_name']}, {$employees['first_name']}. Filed by {$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']}. For more details please login to the Secret 6 HRIS.";
						$message=email_template($header,$message);
						
						$recepients=array();
						foreach ($approvers as $key => $approver) {
							if (!empty($approver['private_email'])) {
								$recepients[]=$approver['private_email'];
							}
							if (!empty($approver['work_email'])) {
								$recepients[]=$approver['work_email'];
							}
						}
						/*
						Email Recepients 
						 */
						PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Overtime Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
					}
				} else {
					$inputs['approval_step_id']=$approval_flow[0]['id'];
					
					$con->myQuery("INSERT INTO employees_ot(
					employees_id,
					ot_date,
					time_from,
					time_to,
					approval_step_id,
					no_hours,
					request_status_id,
					date_filed,
					worked_done,
					project_id
					) VALUES(
					:employees_id,
					:ot_date, 
					:time_start,
					:time_end,
					:approval_step_id,
					:no_hours,
					:st,
					CURDATE(),
					:worked_done,
					:project_id
					)",$inputs); 
					$query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
					$request_id = $con->lastInsertId();
					$values=array();
					foreach ($approval_flow as $key => $step) {
						$value="";
						$value.="(";
						$value.="{$step['id']},{$request_id},'overtime',{$step['step_number']}";
						$value.=")";
						$values[]=$value;
					}
					$query.=implode(",", $values);
					$con->myQuery($query);

					$employees=getEmpDetails($inputs['employees_id']);
					// $supervisor=getEmpDetails($inputs['supervisor_id']);

					insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed an overtime request. From {$s_time} To {$e_time} for {$inputs['no_hours']} Hours. Worked to be done:{$inputs['worked_done']}");
					$email_settings=getEmailSettings();
					//var_dump($supervisor);
					/*
					Get Employees from approval_step_employees based on the first step
					 */
					$approvers=getEmployeesFromSteps($inputs['approval_step_id']);
					/*
					Modify message to be more generic and allow to be sent to multiple people.
					 */
					$header="New Overtime Request For Your Approval";
					$message="Good day,<br/> You have a new overtime request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
					$message=email_template($header,$message);
					
					$recepients=array();
					foreach ($approvers as $key => $approver) {
						if (!empty($approver['private_email'])) {
							$recepients[]=$approver['private_email'];
						}
						if (!empty($approver['work_email'])) {
							$recepients[]=$approver['work_email'];
						}
					}
					/*
					Email Recepients 
					 */
					PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Overtime Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
				}
				
				// die;
				
			Alert("Save succesful","success");
			redirect("overtime.php");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>