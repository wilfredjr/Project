<?php
	require_once("support/config.php");
	 if(!isLoggedIn())
	 {
	 	toLogin();
	 	die();
	 }
	if(!empty($_POST))
	{
		if(empty($_FILES['file']['name'])){
			Alert("No file selected.","danger");
			redirect("frm_project_management");
			die();
		}
		//Validate form inputs
		$inputs=$_POST;

		$required_fieds=array(
	//		"leave_id"=>"Select Type of Leave. <br/>",
			"proj_name"=>"Enter Project Name. <br/>",
			"description"=>"Enter Description. <br/>",
			"team_lead_ba"=>"Select Team Leader (BA). <br/>",
			"team_lead_dev"=>"Select Team Leader (Dev). <br/>"
			);
		$errors="";

		foreach ($required_fieds as $key => $value)
		{
			if(empty($inputs[$key]))
			{
				$errors.=$value;
			}
		}

		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
				redirect("frm_project_management.php");
			die;
		}
		else
		{
            $phase1 = new DateTime();
	        $t = $phase1->getTimestamp();
			$addDay = 86400;
			$t=$t-$addDay;
			  do{
	                $try=date('Y-m-d', ($t+$addDay));
	                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
	                $nextDay = date('w', ($t+$addDay));
	                $t = $t+$addDay;}
	                while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
	                $date_start=date('Y-m-d',$t);
					$inputs['requestor_id'] = $_SESSION[WEBAPP]['user']['employee_id'];
					$params1=array(
					"employee_id"=>$inputs['requestor_id'],
					"name"=>$inputs['proj_name'],
					"des"=>$inputs['description'],
					"date_start"=>$date_start,
					"team_lead_ba"=>$inputs['team_lead_ba'],
					"team_lead_dev"=>$inputs['team_lead_dev'],
					"stats"=>'1'
					);
					$con->myQuery("INSERT INTO
								project_application(
									name,
									des,
									date_start,
									employee_id,
									team_lead_ba,
									team_lead_dev,
									request_status_id,
									date_filed
								) VALUES(
									:name,
									:des,
									:date_start,
									:employee_id,
									:team_lead_ba,
									:team_lead_dev,
									:stats,
									CURDATE()
								)",$params1);
					$app_id=$con->lastInsertId();
					try {
				     $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
					$con->beginTransaction();
					$inputs1['file_name']=$_FILES['file']['name'];
					$con->myQuery("INSERT INTO project_files(file_name,date_modified,employee_id,project_application_id) VALUES(:file_name,NOW(),'$employee_id','$app_id')",$inputs1);
					$file_id=$con->lastInsertId();
					$filename=$file_id.getFileExtension($_FILES['file']['name']);
					move_uploaded_file($_FILES['file']['tmp_name'],"proj_files/".$filename);
					$con->myQuery("UPDATE project_files SET file_location=? WHERE id=?",array($filename,$file_id));

					insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Uploaded ({$inputs['file_name']}) to project files.");


					$con->commit();
					} catch (Exception $e) {
					  $con->rollBack();
			//		  echo "Failed: " . $e->getMessage();
					  Alert("Upload failed. Please try again.","danger");
					  redirect("frm_project_management");
					  die;
					}
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
				
				Alert("Request has been sent","success");
				redirect("project_management.php");
		}
	}else
	{
		redirect('index.php');
		die();
	}
?>
