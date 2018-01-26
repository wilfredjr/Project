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
		$employee=$_SESSION[WEBAPP]['user']['employee_id'];


		$required_fieds=array(
			"leave_id"=>"Select Type of Leave. <br/>",
			"date_hd"=>"Select Date. <br/>",
			"time_hd"=>"Select Time. <br/>",
			"reason"=>"Specify Reason.",
			"project_id"=>"Select Project"
			);
		$errors="";

		foreach ($required_fieds as $key => $value)
		{
			if(empty($inputs[$key]))
			{
				$errors.=$value;
			}
		}

		$approval_flow=getApprovalFlow($_SESSION[WEBAPP]['user']['department_id']);
		if (empty($approval_flow)) {
			$errors.=" No approval flow selected. Please contact your Administrator. <br/>";
		}


		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id']))
			{
				redirect("frm_half_day.php");
			}else
			{
				redirect("frm_half_day.php?id=".urlencode($inputs['id']));
			}
			die;
		}else
		{
// echo "<pre>";
// print_r($inputs);
// echo "</pre>";
// die();
			//IF id exists update ELSE insert
				//Insert
				if (empty($inputs['employee_id']) || $_SESSION[WEBAPP]['user']['employee_id'] == $inputs['employee_id'] ) {
					$employee=$_SESSION[WEBAPP]['user']['employee_id'];
				} elseif (!empty($inputs['employee_id'])) {
					$employee=$inputs['employee_id'];
				}

				$inputs['approval_step_id']=$approval_flow[0]['id'];
				$inputs['request_status_id']=1;

				$s=$inputs['request_status_id'];
				$st=$s;


				// $inputs['leave_id']=0;
				$inputs['date_start']=$inputs['date_hd'];
				$inputs['date_end']=$inputs['date_hd'];
				if ($inputs['time_hd']==1)
				{
					$comment="AM";
				}
				if ($inputs['time_hd']==2)
				{
					$comment="PM";
				}

				$date_s1=new DateTime($inputs['date_start']);
				$date_s=$date_s1->format('Y-m-d');
				$date_e1=new DateTime($inputs['date_end']);
				$date_e=$date_e1->format('Y-m-d');

				if (empty($inputs['employee_id']) || $_SESSION[WEBAPP]['user']['employee_id'] == $inputs['employee_id']) {
					unset($inputs['requestor_id']);
					$params=array(
							"employee"=>$employee,
							"l_id"=>$inputs['leave_id'],
							"date_start"=>$date_s,
							"date_end"=>$date_e,
							"supervisor"=>$inputs['approval_step_id'],
							"r"=>$inputs['reason'],
							"stats"=>$st,
							"comment"=>$comment,
							"project_id"=>$inputs['project_id']
						);

					$con->myQuery("INSERT INTO
									employees_leaves(
											employee_id,
											leave_id,
											date_start,
											date_end,
											approval_step_id,
										date_filed,
											reason,
										request_status_id,
										comment,
										project_id
									) VALUES(
										:employee,
										:l_id,
										DATE_FORMAT(:date_start,'%Y-%m-%d'),
										DATE_FORMAT(:date_end,'%Y-%m-%d'),
										:supervisor,
										CURDATE(),
										:r,
										:stats,
										:comment,
										:project_id
									)",$params);
				} else {
					$inputs['requestor_id'] = $_SESSION[WEBAPP]['user']['employee_id'];

					$params=array(
							"employee"=>$employee,
							"l_id"=>$inputs['leave_id'],
							"date_start"=>$date_s,
							"date_end"=>$date_e,
							"supervisor"=>$inputs['approval_step_id'],
							"r"=>$inputs['reason'],
							"stats"=>$st,
							"comment"=>$comment,
							"project_id"=>$inputs['project_id'],
							"requestor_id"=>$inputs['requestor_id']
						);

					$con->myQuery("INSERT INTO
									employees_leaves(
											employee_id,
											leave_id,
											date_start,
											date_end,
											approval_step_id,
										date_filed,
											reason,
										request_status_id,
										comment,
										project_id,
										requestor_id
									) VALUES(
										:employee,
										:l_id,
										DATE_FORMAT(:date_start,'%Y-%m-%d'),
										DATE_FORMAT(:date_end,'%Y-%m-%d'),
										:supervisor,
										CURDATE(),
										:r,
										:stats,
										:comment,
										:project_id,
										:requestor_id
									)",$params);
				}
				$query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
				$request_id = $con->lastInsertId();
				$values=array();
				foreach ($approval_flow as $key => $step) {
					$value="";
					$value.="(";
					$value.="{$step['id']},{$request_id},'leave',{$step['step_number']}";
					$value.=")";
					$values[]=$value;
				}
				$query.=implode(",", $values);
				$con->myQuery($query);

		}

			//die;
				$leave_name=$con->myQuery("SELECT name FROM leaves WHERE id=?",array($inputs['leave_id']))->fetchColumn();
				if(empty($leave_name))
				{
					$leave_name="Leave Without Pay";
				}
				$audit_message="From ".date("Y-m-d",strtotime($inputs['date_start'])).". Reason: {$inputs['reason']}";
				$employees=getEmpDetails($employee);
				$email_settings=getEmailSettings();
					$approvers=getEmployeesFromSteps($inputs['approval_step_id']);
					$recepients=array();
					foreach ($approvers as $key => $approver) {
						if (!empty($approver['private_email'])) {
							$recepients[]=$approver['private_email'];
						}
						if (!empty($approver['work_email'])) {
							$recepients[]=$approver['work_email'];
						}
					}
				if (empty($inputs['employee_id']) || $_SESSION[WEBAPP]['user']['employee_id'] == $inputs['employee_id']) {
					insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed a leave ($leave_name) request. {$audit_message}");
				} else {
					insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']} filed a leave ($leave_name) request for {$employees['first_name']} {$employees['last_name']}. {$audit_message}");
				}
				
				$requestor_msg="";
				if (!empty($inputs['requestor_id'])) {
					$requestor_msg="Filed by {$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']}. ";
				}

						$header="New Leave Request For Your Approval";
						$message="Good day,<br/> You have a new leave request from {$employees['last_name']}, {$employees['first_name']}. {$requestor_msg}For more details please login to the Spark Global Tech Systems Inc HRIS.";
						$message=email_template($header,$message);

						/*
						Email Recepients
						 */
						PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Leave Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);

						
				Alert("Save succesful".$employee_id,"success");
				redirect("employee_leave_request.php");
		die;
	}
	else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>
