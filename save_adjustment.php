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
		// print_r($inputs);
		// echo "</pre>";
		// die;
		// if(empty($inputs['id'])){
		// 	Modal("Invalid Record Selected");
		// 	redirect("time_management.php");
		// }

		$required_fieds=array(
			"adj_in_time"=>"Enter Time in. <br/>",
			"adj_out_time"=>"Enter Time out. <br/>",
			"reason"=>"Enter Reason"
			);

		$errors="";

		$startdate = strtotime($inputs['adj_in_time']);
		$enddate = strtotime($inputs['adj_out_time']);

		foreach ($required_fieds as $key => $value) {
			if(empty($inputs[$key])){
				$errors.=$value;
			}else{
				#CUSTOM VALIDATION
			}
		}
		/*
		Get the approval flow using the department id of the logged in user.
		 */
		$approval_flow=getApprovalFlow($_SESSION[WEBAPP]['user']['department_id']);
		if (empty($approval_flow)) {
			$errors.=" No approval flow selected. Please contact your Administrator. <br/>";
		}
		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect("frm_adjustment_request.php");
			die;
		}
		else {
			/*
			Modify Query to use the new process.
			remove final_approver_id
			modify
				supervisor_id > approval_step_id,
				status > request_status_id
			 */
			$inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
			$inputs['approval_step_id']=$approval_flow[0]['id'];
			$inputs['request_status_id']=1;

			try {
				$inputs['adj_date']=date_format(date_create($inputs['adj_date']), 'Y-m-d');
				$inputs['adj_in_time']=date_format(date_create($inputs['adj_in_time']), $inputs['adj_date'].' H:i:s');
				$inputs['adj_out_time']=date_format(date_create($inputs['adj_out_time']), $inputs['adj_date'].' H:i:s');

				$con->beginTransaction();
				if(empty($inputs['id'])){
				unset($inputs['id']);
				unset($inputs['orig_in_time']);
				unset($inputs['orig_out_time']);
				unset($inputs['hour']);
				unset($inputs['minute']);
				unset($inputs['meridian']);



				$con->myQuery("INSERT INTO employees_adjustments(
					employees_id,
					adj_date,
					adj_in_time,
					adj_out_time,
					approval_step_id,
					adjustment_reason,
					request_status_id,
					date_filed,
					attendance_id
					) VALUES(
					:employees_id,
					:adj_date,
					:adj_in_time,
					:adj_out_time,
					:approval_step_id,
					:reason,
					:request_status_id,
					NOW(),
					0
					)",$inputs);
					$page="adjustment_request.php";

					$audit_message="Add {$inputs['adj_in_time']}-{$inputs['adj_out_time']}";
				}
				else{
					$current=$con->myQuery("SELECT in_time,out_time FROM attendance WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
					$inputs['orig_in_time']=$current['in_time'];
					$inputs['orig_out_time']=$current['out_time'];
					$inputs['adj_date']=date_format(date_create($current['in_time']), 'Y-m-d');
					$inputs['adj_in_time']=date_format(date_create($inputs['adj_in_time']), $inputs['adj_date'].' H:i:s');
					$inputs['adj_out_time']=date_format(date_create($inputs['adj_out_time']), $inputs['adj_date'].' H:i:s');

					unset($inputs['hour']);
					unset($inputs['minute']);
					unset($inputs['meridian']);
					$con->myQuery("INSERT INTO employees_adjustments(
						employees_id,
						adj_date,
						adj_in_time,
						adj_out_time,
						approval_step_id,
						adjustment_reason,
						request_status_id,
						date_filed,
						attendance_id,
						orig_in_time,
						orig_out_time
						) VALUES(
						:employees_id,
						:adj_date,
						:adj_in_time,
						:adj_out_time,
						:approval_step_id,
						:reason,
						:request_status_id,
						NOW(),
						:id,
						:orig_in_time,
						:orig_out_time
						)",$inputs);
						$page="time_management.php";
						$audit_message="From {$inputs['orig_in_time']}-{$inputs['orig_out_time']} to {$inputs['adj_in_time']}-{$inputs['adj_out_time']}";
				}


				$query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
				$request_id = $con->lastInsertId();
				$values=array();
				foreach ($approval_flow as $key => $step) {
					$value="";
					$value.="(";
					$value.="{$step['id']},{$request_id},'adjustment',{$step['step_number']}";
					$value.=")";
					$values[]=$value;

				}
				$query.=implode(",", $values);
				$con->myQuery($query);
				$con->commit();
			} catch (Exception $e) {
				$con->rollBack();
				Alert("Save Failed.","danger");
				redirect($page);
				die;
			}
			// die;

            $employees=getEmpDetails($inputs['employees_id']);

			insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed an attendance adjustment request. {$audit_message}");

			$email_settings=getEmailSettings();

			/*
			Get Employees from approval_step_employees based on the first step
			 */
			$approvers=getEmployeesFromSteps($inputs['approval_step_id']);
			/*
			Modify message to be more generic and allow to be sent to multiple people.
			 */
			$header="New Attendance Adjustment Request For Your Approval";
			$message="Good day,<br/> You have a new attendance adjustment request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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
			PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Attendance Adjustment Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);


			Alert("Save succesful","success");
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
