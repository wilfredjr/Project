<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }




		if(!empty($_POST)){
		//Validate form inputs
		$inputs=$_POST;
		// echo "<pre>";
		// print_r($_POST);
		// echo "</pre>";
		// die;

		$a = substr($inputs['request_id'], 0, 1);
		if ($a == "o")
		{
			$inputs['request_type'] = "overtime";
			$inputs['request_id'] = ltrim($inputs['request_id'],'o');
		}
		if ($a == "p")
		{
			$inputs['request_type'] = "pre_overtime";
			$inputs['request_id'] = ltrim($inputs['request_id'],'p');
		}

		//echo $inputs['request_type']."</br>".$inputs['request_id'];
		//die();

		if(empty($_POST['request_id']) || empty($_POST['request_type']))
		{
			Modal("Invalid Record Selected");
			redirect("index.php");
			die;
		}

		if(!in_array($_POST['request_type'], array("overtime","pre_overtime","leave","official_business","shift","adjustment","allowance","offset","ot_adjustment","project_approval_emp","project_approval_phase","task_management_approval","task_completion_approval","task_completion_submit","project_application_approval","project_development_approval","bug_application_approval","bug_employee_approval","bug_phase_approval"))){
			Modal("Invalid Record Selected");
			redirect("index.php");
			die;
		}
		$required_fieds=array(
			"reason"=>"Enter Message. <br/>"
			);
		$errors="";
		$page=$inputs['redirect_page'];
		unset($inputs['redirect_page']);
		/*
		Remove supervisor and final_approver columns
		 */
		switch ($inputs['request_type'])
		{
			case 'pre_overtime':
				//$page="overtime_approval.php";
				$table="employees_ot_pre";
				$current=$con->myQuery("SELECT status,employees_id,supervisor_id FROM employees_ot_pre WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				$audit_details=$con->myQuery("SELECT employee_name,ot_date,worked_done,no_hours FROM vw_employees_ot_pre WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				$audit_message="From {$audit_details['ot_date']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}";

				$type="Pre-overtime";
				break;
			case 'overtime':
				//$page="overtime_approval.php";
				$table="employees_ot";
				$current=$con->myQuery("SELECT employees_id,request_status_id,approval_step_id FROM  employees_ot WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				$audit_details=$con->myQuery("SELECT employee_name,ot_date,worked_done,no_hours FROM vw_employees_ot WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				$audit_message="From {$audit_details['ot_date']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}";

				$type="Overtime";
				break;
			case 'leave':
				$table="employees_leaves";
				$current=$con->myQuery("SELECT request_status_id,employee_id as employees_id,approval_step_id FROM  employees_leaves WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				$audit_details=$con->myQuery("SELECT employee_name,leave_type,date_start,date_end,reason FROM vw_employees_leave WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				if(empty($audit_details['leave_type'])){
						$audit_details['leave_type']="Leave Without Pay";
					}
				$audit_message="({$audit_details['leave_type']}) From {$audit_details['date_start']} To {$audit_details['date_end']}. Reason for leave: {$audit_details['reason']}";
				$type="Leave";
				//$page="leave_approval.php";
				break;
			case 'adjustment':
				$table="employees_adjustments";

				$current=$con->myQuery("SELECT employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				$audit_details=$con->myQuery("SELECT employee_name,adjustment_reason,orig_in_time,orig_out_time,adj_in_time,adj_out_time FROM vw_employees_adjustments WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				$audit_message="From {$audit_details['orig_in_time']}-{$audit_details['orig_out_time']} to {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']}. Adjustment Reason:{$audit_details['adjustment_reason']}";
				//$page="adjustments_approval.php";
				$type="Attendance Adjustment";
				break;
			case 'ot_adjustment':
                $table="employees_ot_adjustments";
                $current=$con->myQuery("SELECT employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
                $audit_details=$con->myQuery("SELECT employee_name,orig_time_in,orig_time_out,adj_time_in,adj_time_out FROM vw_employees_ot_adjustments WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
                $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                //$page="adjustments_approval.php";
                $type="Overtime Attendance Adjustment";
                break;
			case 'allowance':
				$table="employees_allowances";
				$current=$con->myQuery("SELECT employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				$audit_details=$con->myQuery("SELECT employee_name,food_allowance,transpo_allowance,request_reason,date_applied FROM vw_employees_allowances WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				$audit_message="Date applied for ({$audit_details["date_applied"]}), Food allowance (".number_format($audit_details['food_allowance'],2)."). Transportation Allowance (".number_format($audit_details['transpo_allowance'], 2)."). With a reason of ({$audit_details["request_reason"]})";
				//$page="adjustments_approval.php";
				$type="Allowance";
				break;
			case 'official_business':
				$table="employees_ob";
				$current=$con->myQuery("SELECT employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				$audit_details=$con->myQuery("SELECT employee_name,destination,purpose,ob_date FROM vw_employees_ob WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				$audit_message="Destination: {$audit_details['destination']}. Purpose: {$audit_details['purpose']} during ".date("Y-m-d",strtotime($audit_details['ob_date']));
				//$page="adjustments_approval.php";
				$type="Official Business";
				break;
			case 'shift':
				$table="employees_change_shift";
				$current=$con->myQuery("SELECT employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);


				$audit_details=$con->myQuery("SELECT employee_name,orig_in_time,orig_out_time,adj_in_time,adj_out_time,date_from,date_to FROM vw_employees_change_shift WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				$audit_message="From {$audit_details['orig_in_time']}-{$audit_details['orig_out_time']} to {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']} during ".date("Y-m-d",strtotime($audit_details['date_from']))." - ".date("Y-m-d",strtotime($audit_details['date_to']));

				//$page="adjustments_approval.php";
				$type="Change Shift";
				break;
			case 'offset':
				$table="employees_offset_request";
				$current=$con->myQuery("SELECT employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				#$audit_details=$con->myQuery("SELECT employees_name FROM vw_employees_offset WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				 #$audit_message="$_SESSION[WEBAPP]['user']['employee_id'] Commented on {$audit_details['employee_name']}'s Offset Request";

				//$page="adjustments_approval.php";
				$type="Offset";
				break;
			case 'project_approval_emp':
				$table="project_requests";
				$current=$con->myQuery("SELECT employee_id as employees_id,status_id as request_status_id,modification_type,manager_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				if($current['modification_type']=='0'){
                                $mod_type="Remove";
                            }
                            elseif($current['modification_type']=='1'){
                                $mod_type="Add";
                            }

				// $audit_details=$con->myQuery("SELECT employee_name,orig_in_time,orig_out_time,adj_in_time,adj_out_time,date_from,date_to FROM vw_employees_change_shift WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				// $audit_message="From {$audit_details['orig_in_time']}-{$audit_details['orig_out_time']} to {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']} during ".date("Y-m-d",strtotime($audit_details['date_from']))." - ".date("Y-m-d",strtotime($audit_details['date_to']));

				//$page="adjustments_approval.php";
				$type="{$mod_type} Project Employee";
				break;
			case 'project_approval_phase':
				$table="project_phase_request";
				$current=$con->myQuery("SELECT employee_id as employees_id,request_status_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				#$audit_details=$con->myQuery("SELECT employees_name FROM vw_employees_offset WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				 #$audit_message="$_SESSION[WEBAPP]['user']['employee_id'] Commented on {$audit_details['employee_name']}'s Offset Request";

				//$page="adjustments_approval.php";
				$type="Project Phase";
				break;
			case 'task_management_approval':
				$table="project_task";
				$current=$con->myQuery("SELECT requestor_id as employees_id,request_status_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				#$audit_details=$con->myQuery("SELECT employees_name FROM vw_employees_offset WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				 #$audit_message="$_SESSION[WEBAPP]['user']['employee_id'] Commented on {$audit_details['employee_name']}'s Offset Request";

				//$page="adjustments_approval.php";
				$type="Task Assignment Approval";
				break;
			case 'task_completion_approval':
				// $table="project_task_completion";
				// if($inputs['request_from']=='submit'){
				// 	$current2=$con->myQuery("SELECT request_id FROM  project_task_list WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				// 	$current=$con->myQuery("SELECT employee_id as employees_id,request_status_id,task_list_id FROM  {$table} WHERE id=?",array($current2['request_id']))->fetch(PDO::FETCH_ASSOC);
				// }else{
				// $current=$con->myQuery("SELECT employee_id as employees_id,request_status_id,task_list_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
				// }
				#$audit_details=$con->myQuery("SELECT employees_name FROM vw_employees_offset WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				 #$audit_message="$_SESSION[WEBAPP]['user']['employee_id'] Commented on {$audit_details['employee_name']}'s Offset Request";

				//$page="adjustments_approval.php";
				$type="Task Completion Approval";
				break;
			case 'project_application_approval':
				$table="project_application";
				$current=$con->myQuery("SELECT employee_id as employees_id,request_status_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				#$audit_details=$con->myQuery("SELECT employees_name FROM vw_employees_offset WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				 #$audit_message="$_SESSION[WEBAPP]['user']['employee_id'] Commented on {$audit_details['employee_name']}'s Offset Request";

				//$page="adjustments_approval.php";
				$type="Project Application Approval";
				break;
			case 'project_development_approval':
				$table="project_development";
				$current=$con->myQuery("SELECT employee_id as employees_id,request_status_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				#$audit_details=$con->myQuery("SELECT employees_name FROM vw_employees_offset WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				 #$audit_message="$_SESSION[WEBAPP]['user']['employee_id'] Commented on {$audit_details['employee_name']}'s Offset Request";

				//$page="adjustments_approval.php";
				$type="Project Application Approval";
				break;
			case 'bug_application_approval':
				$table="project_bug_application";
				$current=$con->myQuery("SELECT employee_id as employees_id,request_status_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				#$audit_details=$con->myQuery("SELECT employees_name FROM vw_employees_offset WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				 #$audit_message="$_SESSION[WEBAPP]['user']['employee_id'] Commented on {$audit_details['employee_name']}'s Offset Request";

				//$page="adjustments_approval.php";
				$type="Bug Application Approval";
				break;
			case 'bug_employee_approval':
				$table="project_bug_employee";
				$current=$con->myQuery("SELECT requested_by as employees_id,request_status_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				#$audit_details=$con->myQuery("SELECT employees_name FROM vw_employees_offset WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				 #$audit_message="$_SESSION[WEBAPP]['user']['employee_id'] Commented on {$audit_details['employee_name']}'s Offset Request";

				//$page="adjustments_approval.php";
				$type="Bug Employee Approval";
				break;

			case 'bug_phase_approval':
				$table="project_bug_request";
				$current=$con->myQuery("SELECT employee_id as employees_id,request_status_id FROM  {$table} WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				#$audit_details=$con->myQuery("SELECT employees_name FROM vw_employees_offset WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);

				 #$audit_message="$_SESSION[WEBAPP]['user']['employee_id'] Commented on {$audit_details['employee_name']}'s Offset Request";

				//$page="adjustments_approval.php";
				$type="Bug Phase Approval";
				break;
		}
		foreach ($required_fieds as $key => $value) {
			if(empty($inputs[$key])){
				$errors.=$value;
			}
		}

		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect($page);
			die;
		}else
		{
			// echo "<pre>";
			// print_r($inputs);
			// echo "</pre>";

			switch ($current['request_status_id'])
					{
						case 1:
							/*
							Query the request
							 */

							$inputs['receiver_id']=$current['employees_id'];
							$status=3;
							$supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
							$employees=getEmpDetails($current['employees_id']);
							$email_settings=getEmailSettings();
							// var_dump($employees);
							// die;
							insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Queried {$employees['first_name']} {$employees['last_name']}'s {$type} request. Query:{$inputs['reason']}. {$audit_message}");

							if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
								$header="{$type} Request Queried";
								$message="Hi {$employees['first_name']},<br/> Your request has been queried by, {$supervisor['first_name']} {$supervisor['last_name']}. The message being '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
								$message=email_template($header,$message);
								// var_dump($email_settings);
								 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
								PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"{$type} Request (Query)",$message,$email_settings['host'],$email_settings['port']);
							}
							break;

						case 3:
							/*
							Query Has been answered;
							 */

							if($type=="{$mod_type} Project Employee"){

								$status=1;
								$current1=$con->myQuery("SELECT id,employee_id,first_approver_date,second_approver_date,third_approver_date,first_approver_id,second_approver_id,third_approver_id,modification_type,project_id,requested_employee_id FROM  project_requests WHERE id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
		                         $manager=getEmpDetails($current['manager_id']);
		                         $employees=getEmpDetails($current['employees_id']);
									$email_settings=getEmailSettings();
	                        	//var_dump($supervisor);
	                            if($current1['modification_type']=='0'){
	                                $mod_type="Remove";
	                            }
	                            elseif($current1['modification_type']=='1'){
	                                $mod_type="Add";
	                            }
	                             if(!empty($current1['first_approver_id'])&& ($current1['first_approver_date']=='0000-00-00')){
	                                $next_step=$current1['first_approver_id'];
	                            }
	                            elseif(!empty($current1['second_approver_id'])&& ($current1['second_approver_date']=='0000-00-00')){
	                                $next_step=$current1['second_approver_id'];
	                            }
	                            elseif(!empty($current1['third_approver_id']) &&($current1['third_approver_date']=='0000-00-00')){
	                                $next_step=$current1['third_approver_id'];
	                            }
	                            	$inputs['receiver_id']	= $next_step;
	                               $approver=getEmpDetails($next_step);
	                               $recepients[]=$approver['private_email'];
	                               $recepients[]=$approver['work_email'];

	                               insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Answered Query for {$type} request. Answer:{$inputs['reason']}.  {$audit_message}");

	                               $header="{$type} Request With Comment For Your Approval";

								$message="Good day,<br/> You have a {$type} request from {$employees['last_name']}, {$employees['first_name']} with a comment of '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
								$message=email_template($header,$message);
								PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($approver['private_email'],$approver['work_email']),"{$type} Request (Answered Query)",$message,$email_settings['host'],$email_settings['port']);
							} else {
								$approvers=getEmployeesFromSteps($current['approval_step_id']);
								/*
								Message all approvers a query has been answered.
								 */

								$inputs['receiver_id']=0;
								$status=1;

								$employees=getEmpDetails($current['employees_id']);
								$email_settings=getEmailSettings();

								insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Answered Query for {$type} request. Answer:{$inputs['reason']}.  {$audit_message}");

								$recepients=array();
								foreach ($approvers as $key => $approver) {
									if (!empty($approver['private_email'])) {
										$recepients[]=$approver['private_email'];
									}
									if (!empty($approver['work_email'])) {
										$recepients[]=$approver['work_email'];
									}
								}
							}
							/*
							Email Recepients
							 */
							$header="{$type} Request With Comment For Your Approval";

							$message="Good day,<br/> You have a {$type} request from {$employees['last_name']}, {$employees['first_name']} with a comment of '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
							$message=email_template($header,$message);
							PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"{$type} Request (Answered Query)",$message,$email_settings['host'],$email_settings['port']);
							break;
					}

			$inputs['sender_id']=$_SESSION[WEBAPP]['user']['employee_id'];
			//die;
			//IF id exists update ELSE insert
			try {
				//echo $table."<br>".$status;
				//die();
				//die("UPDATE {$table} SET status=? WHERE id=?");
				// var_dump($inputs);
				if($inputs['request_type']=='task_completion_approval'){
					if($current['request_status_id']==1){
						$current1=$con->myQuery("SELECT id FROM project_task_list WHERE request_id=?",array($inputs['request_id']))->fetch(PDO::FETCH_ASSOC);
						$inputs1=$inputs;
						$inputs1['request_id']=$current1['id'];
						$con->myQuery("INSERT INTO comments(message,sender_id,receiver_id,request_type,request_id,date_sent) VALUES(:reason,:sender_id,:receiver_id,:request_type,:request_id,NOW())",$inputs1);
					}else{
						$inputs1=$inputs;
						$inputs1['request_id']=$current2['request_id'];
						$con->myQuery("INSERT INTO comments(message,sender_id,receiver_id,request_type,request_id,date_sent) VALUES(:reason,:sender_id,:receiver_id,:request_type,:request_id,NOW())",$inputs1);
					}
				}else{
				$con->myQuery("INSERT INTO comments(message,sender_id,receiver_id,request_type,request_id,date_sent) VALUES(:reason,:sender_id,:receiver_id,:request_type,:request_id,NOW())",$inputs);}
				if($inputs['request_type']=='project_approval_emp'){
					$con->myQuery("UPDATE {$table} SET status_id=? WHERE id=?",array($status,$inputs['request_id']));
				}else{
				$con->myQuery("UPDATE {$table} SET request_status_id=? WHERE id=?",array($status,$inputs['request_id']));}

			} catch (Exception $e) {
				// die;
				Modal("Please try again.");
				redirect("index.php");
				die;
			}
			Alert("Message Sent.","success");
			redirect($page);
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>
