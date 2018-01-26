<?php
	require_once("support/config.php");
	if(!isLoggedIn())
	{
	 	toLogin();
	 	die();
	}
	if(!empty($_POST))
	{
		$inputs=$_POST;
		// var_dump($inputs);
		// die;

		$required_fieds=array
		(
			"remarks"=>"Enter Remarks. <br/>",
			"project_id"=> "Select Project. <br/>"
		);
		$errors="";

		foreach ($required_fieds as $key => $value)
		{
			if(empty($inputs[$key]))
			{
				$errors.=$value;
			}
		}


	#COMPUTE NUMBER OF HOURS

		$start_dt=date_format(date_create($inputs['date_start']), 'Y-m-d H:i:s');
		$end_dt=date_format(date_create($inputs['date_end']), 'Y-m-d H:i:s');
		$project_id1=$inputs['project_id'];
		// var_dump($start_dt,$end_dt);
		// die;
	// $check_start=$con->myQuery("SELECT start_datetime FROM employees_offset_request WHERE project_id={$project_id1} AND (start_datetime BETWEEN '{$start_dt}' AND '{$end_dt}')")->fetchAll(PDO::FETCH_ASSOC);
	// $check_end=$con->myQuery("SELECT end_datetime FROM employees_offset_request WHERE project_id={$project_id1} AND (end_datetime BETWEEN '{$start_dt}' AND '{$end_dt}')")->fetchAll(PDO::FETCH_ASSOC);
		// var_dump($check_start,$check_end);
		// die;
	$check_dates=$con->myQUery("SELECT start_datetime,end_datetime FROM employees_offset_request WHERE  project_id=2 AND (start_datetime <= '{$end_dt}') AND (end_datetime>='{$start_dt}')" )->fetchAll(PDO::FETCH_ASSOC);

		if (!empty($check_dates)) 
	{
		Alert("Selected date already exists", "danger");
		redirect("frm_offset.php");
		die;
	}

	// if (!empty($check_start) || !empty($check_end)) 
	// {
	// 	Alert("Selected date already exists", "danger");
	// 	redirect("frm_offset.php");
	// 	die;
	// }

		$min 	 = (strtotime($end_dt) - strtotime($start_dt)) / 60;
		$zero    = new DateTime('@0');
		$offset  = new DateTime('@' . $min * 60);
		$diff    = $zero->diff($offset);
		$hours=0;
		// var_dump($diff);
		if (!empty(floatval($diff->y))) {
			$hours+=(intval($diff->y)*365)/24;
		}
		if (!empty(floatval($diff->m))) {
			$hours+=(intval($diff->m)*30)/24;
		}

		if (!empty(floatval($diff->d))) {
			$hours+=(intval($diff->d)*24);
		}
		if (!empty(floatval($diff->h))) {
			$hours+=floatval($diff->format('%H'));
		}
		
		$total_time = $hours.":".$diff->format('%I');
		
		function decimalHours($time)
		{
			// var_dump($time);
		    $hms = explode(":", $time);
		    return ($hms[0] + ($hms[1]/60));
		}
		$decimalHours = decimalHours($total_time);
		
		$total_hours=number_format($decimalHours,'2','.','');
		$inputs['no_hours']=$total_hours;


	#FOR AVAILMENT - IF EXCEED NUMBER OF OFFSET HOURS
		if (empty($inputs['employee_id'])) {
			$inputs['employee_id'] = $_SESSION[WEBAPP]['user']['employee_id'];
			$approval_flow=getApprovalFlow($_SESSION[WEBAPP]['user']['department_id']);
		} else {
			$approval_flow=getApprovalFlow(getEmployeeDepartment($inputs['employee_id']));
		}
		if($inputs['request_type']==2)
		{
			$offset_count=$con->myQuery("SELECT id,offset_count FROM employees_offset WHERE employees_id=?",array($inputs['employee_id']))->fetch(PDO::FETCH_ASSOC);
			if(empty($offset_count))
			{
				$errors.="You have no offset hour/s available. <br/>";
			}else
			{
				if($total_hours > $offset_count['offset_count'])
				{
					$errors.="You exceed number of offset hours avilable. <br/>";
				}
			}
		}
		
		if (empty($approval_flow)) {
			$errors.=" No approval flow selected. Please contact your Administrator. <br/>";
		}
		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect("frm_offset.php");
			die;
		}else
		{

	#INSERT INTO EMPLOYEES_OFFSET_REQUESTS
			
			// $inputs['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
			$inputs['date_start']=date_format(date_create($inputs['date_start']), 'Y-m-d H:i:s');
			$inputs['date_end']=date_format(date_create($inputs['date_end']), 'Y-m-d H:i:s');
			$inputs['approval_step_id']=$approval_flow[0]['id'];
			$inputs['request_status_id']=1;
			if ($_SESSION[WEBAPP]['user']['employee_id'] == $inputs['employee_id']) {
				unset($inputs['requestor_id']);
				var_dump($inputs);
				$con->myQuery("INSERT INTO employees_offset_request(
										employees_id,
										request_type_id,
										approval_step_id,
										start_datetime,
										end_datetime,
										no_hours,
										request_status_id,
										date_filed,
										remarks,
										project_id
									) VALUES(
										:employee_id,
										:request_type,
										:approval_step_id,
										:date_start,
										:date_end,
										:no_hours,
										:request_status_id,
										CURDATE(),
										:remarks,
										:project_id
									)",$inputs);
			} else {
				$inputs['requestor_id'] = $_SESSION[WEBAPP]['user']['employee_id'];
				// var_dump($inputs);
				$con->myQuery("INSERT INTO employees_offset_request(
										employees_id,
										request_type_id,
										approval_step_id,
										start_datetime,
										end_datetime,
										no_hours,
										request_status_id,
										date_filed,
										remarks,
										project_id,
										requestor_id
									) VALUES(
										:employee_id,
										:request_type,
										:approval_step_id,
										:date_start,
										:date_end,
										:no_hours,
										:request_status_id,
										CURDATE(),
										:remarks,
										:project_id,
										:requestor_id
									)",$inputs);
			}
			// die;

									$query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
									$request_id = $con->lastInsertId();
									$values=array();
									foreach ($approval_flow as $key => $step) {
										$value="";
										$value.="(";
										$value.="{$step['id']},{$request_id},'offset',{$step['step_number']}";
										$value.=")";
										$values[]=$value;
									}
									$query.=implode(",", $values);
									$con->myQuery($query);
	#EMAIL SENDING AND AUDIT TRAIL

			// $supervisor=getEmpDetails($inputs['supervisor_id']);
			// $employees=getEmpDetails($inputs['employee_id']);

			// insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed an overtime request. From {$s_time} To {$e_time} for {$inputs['no_hours']} Hours. Worked to be done:{$inputs['worked_done']}");
			// $email_settings=getEmailSettings();
			// //var_dump($supervisor);
			// if(!empty($supervisor) && !empty($email_settings)){
			// 	$header="New Overtime Request For Your Approval";
			// 	$message="Hi {$supervisor['first_name']},<br/> You have a new overtime request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
			// 	$message=email_template($header,$message);
			// 	// var_dump($email_settings);
			// 	 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
			// 	emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($supervisor['private_email'],$supervisor['work_email'])),"Overtime Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
			// }else
			// {
			// 	$final_approver=getEmpDetails($inputs['final_approver_id']);
			// 	if(!empty($final_approver['private_email']) || !empty($final_approver['work_email'])){

			// 		$header="New Overtime Request For Your Approval";
			// 		$message="Hi {$final_approver['first_name']},<br/> You have a new overtime request from {$employees['first_name']} {$employees['last_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
			// 		$message=email_template($header,$message);
			// 		// var_dump($email_settings);
			// 		 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
			// 		emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($final_approver['private_email'],$final_approver['work_email'])),"Overtime Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
			// 	}
			// }
								

			$employees=getEmpDetails($inputs['employee_id']);

insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed an offset request.");

$email_settings=getEmailSettings();

/*
Get Employees from approval_step_employees based on the first step
 */
$approvers=getEmployeesFromSteps($inputs['approval_step_id']);
$header="New Offset Request For Your Approval";
/*
Modify message to be more generic and allow to be sent to multiple people.
 */
$requestor_msg="";
if (!empty($inputs['requestor_id'])) {
	$requestor_msg="Filed by {$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']}. ";
}

$message="Good day,<br/> You have a new offset request from {$employees['last_name']}, {$employees['first_name']}. {$requestor_msg}For more details please login to the Secret 6 HRIS.";
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
PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Offset Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
// die;
			Alert("Save Succesful","success");
			redirect("offset.php");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>
