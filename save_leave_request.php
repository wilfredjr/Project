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
	//		"leave_id"=>"Select Type of Leave. <br/>",
			"date_start"=>"Enter Start Date of Leave. <br/>",
			"date_end"=>"Enter End Date of Leave. <br/>",
			"reason"=>"Enter Reason",
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

#CHECK DATES

	#START DATE LESS THAN END DATE
		$startdate = strtotime($inputs['date_start']);
		$enddate = strtotime($inputs['date_end']);
		if ($enddate < $startdate)
		{
			$errors .= 'Please check dates seleceted. <br/>';
		}
		if (empty($inputs['employee_id']) || $_SESSION[WEBAPP]['user']['employee_id'] == $inputs['employee_id'] ) {
			$employee=$_SESSION[WEBAPP]['user']['employee_id'];
		} elseif (!empty($inputs['employee_id'])) {
			$employee=$inputs['employee_id'];
		}

	#NO REPITITION IN THE DATABASE
		$validate_date=$con->myQuery("SELECT eld.id, eld.employees_leaves_id, el.employee_id, el.request_status_id, eld.date_leave as date_leave FROM employees_leaves_date eld INNER JOIN employees_leaves el ON el.id=eld.employees_leaves_id WHERE el.employee_id=? AND el.request_status_id<>'Cancelled'",array($employee));

		$s_date=new datetime($inputs['date_start']);
		$e_date=new datetime($inputs['date_end']);
		$e_date = $e_date->modify( '+1 day' );

		$interval = new DateInterval('P1D');
		$daterange = new DatePeriod($s_date, $interval ,$e_date);
		$woweekends=0;
		$echo="";

		foreach($daterange as $date)
		{
			$weekday=$date->format("w");
			if($weekday != 0 && $weekday != 6)
			{
		        $dates=$date->format("Y-m-d");
		        while($row = $validate_date->fetch(PDO::FETCH_ASSOC))
		        {
		        	if($row['date_leave']==$dates)
		        	{
		        		$errors="Please check date/s. Selected date/s already exist in the database.";
		        		break;
		        	}
		        }
		       	//echo $dates.$echo."<br/>";
		        $woweekends++;
		    }
		}

	#MAX NUMBER OF LEAVE
		if($inputs['leave_id']<>0)
		{
			$bal_count=$con->myQuery("SELECT id, balance_per_year FROM employees_available_leaves WHERE is_cancelled=0 AND is_deleted=0 AND leave_id=? AND employee_id=? AND ? NOT IN (SELECT id FROM LEAVES WHERE is_pay=0)",array($inputs['leave_id'],$employee,$inputs['leave_id']))->fetch(PDO::FETCH_ASSOC);
			if(!empty($bal_count))
			{
				$begin = new DateTime($inputs['date_start']);
				$end = new DateTime($inputs['date_end']);
				$end = $end->modify( '+1 day' );

				$int = new DateInterval('P1D');
				$drange = new DatePeriod($begin, $int ,$end);
				$count_days=0;

				foreach($drange as $day){
				    $weeks=$day->format("w");
				    if($weeks != 0 && $weeks != 6)
				    {
				    	$count_days++;
				    }
				}
				// var_dump($inputs);
				// var_dump($count_days);
				if($count_days > $bal_count['balance_per_year'])
				{
					$errors="Please check date/s. You exceed number of leave balance.";
				}
			}
		}
		// die;
		if (empty($inputs['employee_id'])) {
			$inputs['employee_id'] = $_SESSION[WEBAPP]['user']['employee_id'];
			$approval_flow=getApprovalFlow($_SESSION[WEBAPP]['user']['department_id']);
		} else {
			$approval_flow=getApprovalFlow(getEmployeeDepartment($inputs['employee_id']));
		}
		if (empty($approval_flow)) {
			$errors.=" No approval flow selected. Please contact your Administrator. <br/>";
		}

		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id']))
			{
				redirect("frm_leave_request.php");
			}
			else
			{
				redirect("frm_leave_request.php?id=".urlencode($inputs['id']));
			}
			die;
		}
		else
		{

			// $inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
			$inputs['approval_step_id']=$approval_flow[0]['id'];
			$inputs['request_status_id']=1;
			$s=$inputs['request_status_id'];
			$st=$s;
			//IF id exists update ELSE insert
			if(empty($inputs['id']))
			{
				//Insert
				$date_s1=new DateTime($inputs['date_start']);
				$date_s=$date_s1->format('Y-m-d');
				$date_e1=new DateTime($inputs['date_end']);
				$date_e=$date_e1->format('Y-m-d');

				unset($inputs['id']);
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
					"project_id"=>$inputs['project_id'],
					"requestor_id" => $inputs['requestor_id']
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
									:project_id,
									:requestor_id
								)",$params);
				}
				
				$employee_leave_id=$con->lastInsertId();

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
							"employee_leave_id"=>$employee_leave_id,
						    "date_leave"=>$dates
						);
				        $con->myQuery("INSERT INTO
				        				employees_leaves_date(
				        					employees_leaves_id,
				        					date_leave)
				        				VALUES(
				        					:employee_leave_id,
				        					:date_leave)",
				        				$emp_l);
				        //echo $dates." <br>";
				        $woweekends++;
				    }
				}
			}

			//die;
// echo "<pre>";
// print_r($inputs);
// echo "</pre>";
// die();
				$leave_name=$con->myQuery("SELECT name FROM leaves WHERE id=?",array($inputs['leave_id']))->fetchColumn();
				if(empty($leave_name))
				{
					$leave_name="Leave Without Pay";
				}
				$audit_message="From ".date("Y-m-d",strtotime($inputs['date_start']))." to ".date("Y-m-d",strtotime($inputs['date_end'])).". Reason: {$inputs['reason']}";
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
				if ($_SESSION[WEBAPP]['user']['employee_id'] == $inputs['employee_id']) {
					insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed a leave ($leave_name) request. {$audit_message}");
				} else {
					insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']} filed a leave ($leave_name) request for {$employees['first_name']} {$employees['last_name']}. {$audit_message}");
				}

				$requestor_msg="";
				if (!empty($inputs['requestor_id'])) {
					$requestor_msg="Filed by {$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']}. ";
				}

				$header="New Leave Request For Your Approval";
				$message="Good day,<br/> You have a new leave request from {$employees['last_name']}, {$employees['first_name']}. {$requestor_msg}For more details please login to the Secret 6 HRIS.";
				$message=email_template($header,$message);

				/*
				Email Recepients
				 */
				PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Leave Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
				
				Alert("Save succesful".$employee_id,"success");
				redirect("employee_leave_request.php");
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
