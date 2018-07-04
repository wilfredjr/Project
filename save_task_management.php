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
			"phase_id"=>"Select Project Phase. <br/>",
			"employees_id"=>"Select Employee/s. <br/>",
			"date_start"=>"Enter Start Date. <br/>",
			"date_end"=>"Enter End Date. <br/>",
			"worked_done"=>"Enter Work to be done. <br/>"
			);
		$errors="";

		foreach ($required_fieds as $key => $value)
		{
			if(empty($inputs[$key]))
			{
				$errors.=$value;
			}
		}
			$phase_now=$inputs['phase_id'];
			$phase_stat=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['id'],$inputs['phase_id']))->fetch(PDO::FETCH_ASSOC);
			 $sent=$con->myQuery("SELECT id FROM project_phase_request WHERE project_id=? AND (request_status_id=1 OR request_status_id=3) AND ((project_phase_id='$phase_now')AND(type='comp'))",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
			 if(!empty($sent)){
			 	$errors.="Cannot submit task/s. Phase already subject for completion. <br>";
			 }
			$date_now=new datetime();
			$date_now=$date_now->format("Y-m-d");
			$date_now=strtotime($date_now);
#CHECK DATES

	#START DATE LESS THAN END DATE
		if((strtotime($inputs['date_start'])==0)OR(strtotime($inputs['date_end'])==0)) {
			$errors.='Check date format. <br>';
		}else{
		$startdate = strtotime($inputs['date_start']);
		$enddate = strtotime($inputs['date_end']);

		if($phase_stat['status_id']=='1'){
				if($startdate<$date_now){
					$errors .= 'Please check start date. <br/>';
				}
			}

		if ($enddate < $startdate)
		{
			$errors .= 'Please check end date. <br/>';
		}

		$s_date=new datetime($inputs['date_start']);
		$e_date=new datetime($inputs['date_end']);
		$e_date = $e_date->modify( '+1 day' );

		$interval = new DateInterval('P1D');
		$daterange = new DatePeriod($s_date, $interval ,$e_date);
		$woweekends=0;
		$woweekends1=0;
		$true=0;
		$true1=0;
	#NO REPITITION IN THE DATABASE
			// if (is_array($inputs['employees_id'])) {
		// Multiple Employee Selected

			foreach($daterange as $date1)
		{
			$weekday=$date1->format("w");
			if($weekday != 0 && $weekday != 6)
			{
		        $dates=$date1->format("Y-m-d");
		        $check_date=$con->myQuery("SELECT date_start, date_end FROM project_phase_dates WHERE ('$dates' BETWEEN date_start AND date_end) AND project_id=? AND project_phase_id=?",array($inputs['id'],$inputs['phase_id']))->fetch(PDO::FETCH_ASSOC);
		        	if(empty($check_date))
		        	{	
		        		$true1=1;
		        		$errors.=" <b><li>".$dates."</li></b>";
		        	}
		       	//echo $dates.$echo."<br/>";
		        $woweekends1++;
		    }
		}
	}

	// 	foreach ($inputs['employees_id'] as $employee_id) {
	// 	$validate_date=$con->myQuery("SELECT ptd.id, ptd.project_task_id, pt.employee_id, pt.request_status_id, ptd.date AS date_task FROM project_task_dates ptd INNER JOIN project_task pt ON pt.id=ptd.project_task_id WHERE pt.employee_id=? AND (pt.request_status_id!=4  OR pt.request_status_id!=5) AND pt.project_id=? and pt.project_phase_id=?",array($employee_id,$inputs['id'],$inputs['phase_id']));

	// 	$echo="";

	// 	foreach($daterange as $date)
	// 	{
	// 		$weekday=$date->format("w");
	// 		if($weekday != 0 && $weekday != 6)
	// 		{
	// 	        $dates=$date->format("Y-m-d");
	// 	        while($row = $validate_date->fetch(PDO::FETCH_ASSOC))
	// 	        {
	// 	        	if($row['date_task']==$dates)
	// 	        	{	$true=1;
	// 	        		$employee_name=$con->myQuery("SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as name FROM employees e WHERE e.id=?",array($employee_id))->fetch(PDO::FETCH_ASSOC);
	// 	        		$errors.=" <b><li>".$employee_name['name']."</li></b>";
	// 	        		break;
	// 	        	}
	// 	        }
	// 	       	//echo $dates.$echo."<br/>";
	// 	        $woweekends++;
	// 	    }
	// 	}
	// }

		// $check_des=$con->myQuery("SELECT designation_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['id'],$inputs['phase_id']))->fetch(PDO::FETCH_ASSOC);
		// $emp_des=$con->myQuery("SELECT designation_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['id'],$inputs['phase_id']))->fetch(PDO::FETCH_ASSOC);
		// if()
		if($errors!="")
		{
			if($true==1){
				Alert("You have the following errors: <br/> Selected date/s already exist for: <br>".$errors,"danger");
			}elseif($true1==1){
				Alert("You have the following errors: <br/> Selected date/s cannot be applied for the selected phase: <br>".$errors,"danger");
			}else{
			Alert("You have the following errors: <br/>".$errors,"danger");}
			if(empty($inputs['id']))
			{
				redirect("index.php");
			}
			else
			{
				redirect("frm_task_management.php?id=".urlencode($inputs['id']));
			}
			die;
		}
		else
		{

					foreach ($inputs['employees_id'] as $employee_id) {

			// $inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
			$inputs['request_status_id']=1;
			$s=$inputs['request_status_id'];
			$st=$s;
			//IF id exists update ELSE insert
				//Insert
				$date_s1=new DateTime($inputs['date_start']);
				$date_s=$date_s1->format('Y-m-d');
				$date_e1=new DateTime($inputs['date_end']);
				$date_e=$date_e1->format('Y-m-d');

					$inputs['requestor_id'] = $_SESSION[WEBAPP]['user']['employee_id'];
					if($inputs['manager_id']==$inputs['requestor_id']){
						$step_id=3;
					}else{
						$step_id=2;
					}
					$params=array(
					"employee"=>$employee_id,
					"project_id"=>$inputs['id'],
					"phase_id"=>$inputs['phase_id'],
					"date_start"=>$date_s,
					"date_end"=>$date_e,
					"manager"=>$inputs['manager_id'],
					"w"=>$inputs['worked_done'],
					"stats"=>$st,
					"step_id"=>$step_id,
					"admin"=>$inputs['admin_id'],
					"requestor_id" => $inputs['requestor_id']
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
									step_id
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
									:step_id
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
		}
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
				
				Alert("Save successful","success");
				redirect("task_management_project.php?id=".urlencode($inputs['id']));
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
