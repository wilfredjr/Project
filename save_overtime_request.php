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
		$required_fieds=array(
			"worked_done"=>"Enter Worked Done. <br/>"
			);
		$errors="";

		foreach ($required_fieds as $key => $value)
		{
			if(empty($inputs[$key]))
			{
				$errors.=$value;
			}else
			{
				#CUSTOM VALIDATION
			}
		}
		$tab=6;
		
				$inputs['ot_date']=SaveDate($inputs['ot_date']);

				if (!empty($inputs['get_id']))
				{
					if($_GET['get_id']==0)
					{
						$ot_date=$con->myQuery("SELECT ot_date FROM employees_ot_pre WHERE id=?",array($inputs['get_id']))->fetch(PDO::FETCH_ASSOC);
						$inputs['ot_date']=date_format(date_create($ot_date['ot_date']), 'Y-m-d');
					}
				}

		
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
				//echo "<br>".date($inputs['no_hours']);
				//die();

		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect("frm_overtime_request.php");
			die;
		}
		else
		{
			if(empty($inputs['get_id']))
			{
				$supervisor=$con->myQuery("SELECT e.supervisor_id FROM employees e WHERE e.id=?",array($_SESSION[WEBAPP]['user']['employee_id']))->fetch(PDO::FETCH_ASSOC);
				$s=$supervisor['supervisor_id'];
		
				if ($s == 0) 
				{
					$st = "Approved";
				}else
				{
					$st = "Supervisor Approval";
				}

#Insert OT PRE APPROVAL
				unset($inputs['get_id']);
				$inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
				$inputs['supervisor_id']=$con->myQuery("SELECT e.supervisor_id FROM employees e WHERE e.id=?",array($inputs['employees_id']))->fetchColumn();
				$inputs['ot_date']=date_format(date_create($inputs['ot_date']), 'Y-m-d');
				$inputs['time_start']=date_format(date_create($inputs['time_start']), 'H:i:s');
				$inputs['time_end']=date_format(date_create($inputs['time_end']), 'H:i:s');
				$inputs['st']=$st;
				unset($inputs['hour']);
				unset($inputs['minute']);
				unset($inputs['meridian']);
				
				//var_dump($inputs);
				//die();

				$con->myQuery("INSERT INTO employees_ot_pre(
					employees_id,
					supervisor_id,
					ot_date,
					time_from,
					time_to,
					no_hours,
					status,
					date_filed,
					worked_done
					) VALUES(
					:employees_id,
					:supervisor_id,
					:ot_date, 
					:time_start,
					:time_end,
					:no_hours,
					:st,
					CURDATE(),
					:worked_done
					)",$inputs); 
				
					$supervisor=getEmpDetails($inputs['supervisor_id']);
					$employees=getEmpDetails($inputs['employees_id']);

					insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed an overtime request. From {$s_time} To {$e_time} for {$inputs['no_hours']} Hours. Worked to be done:{$inputs['worked_done']}");
					$email_settings=getEmailSettings();
					//var_dump($supervisor);
					if(!empty($supervisor) && !empty($email_settings))
					{
						$header="New Overtime Request For Your Approval";
						$message="Hi {$supervisor['first_name']},<br/> You have a new overtime request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
						$message=email_template($header,$message);
						//var_dump($email_settings);
						//emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
						emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($supervisor['private_email'],$supervisor['work_email'])),"Overtime Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
					}

			}else
			{				
				$supervisor=$con->myQuery("SELECT e.supervisor_id FROM employees e WHERE e.id=?",array($_SESSION[WEBAPP]['user']['employee_id']))->fetch(PDO::FETCH_ASSOC);
				$s=$supervisor['supervisor_id'];
		
				if ($s == 0) 
				{
					$st = "Final Approver Approval";
				}else
				{
					$st = "Supervisor Approval";
				}

#Insert OT CLAIM
				$inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
				$inputs['supervisor_id']=$con->myQuery("SELECT e.supervisor_id FROM employees e WHERE e.id=?",array($inputs['employees_id']))->fetchColumn();
				$inputs['final_approver_id']=$con->myQuery("SELECT d.approver_id FROM departments d INNER JOIN employees e ON d.id=e.department_id WHERE e.id=?",array($inputs['employees_id']))->fetchColumn();
				$inputs['time_start']=date_format(date_create($inputs['time_start']), 'H:i:s');
				$inputs['time_end']=date_format(date_create($inputs['time_end']), 'H:i:s');
				$inputs['st']=$st;
				unset($inputs['hour']);
				unset($inputs['minute']);
				unset($inputs['meridian']);
				
				//var_dump($inputs);
				//die();

				$con->myQuery("INSERT INTO employees_ot(
					employees_id,
					supervisor_id,
					ot_approver_id,
					ot_date,
					time_from,
					time_to,
					no_hours,
					status,
					date_filed,
					worked_done,
					ot_pre_id
					) VALUES(
					:employees_id,
					:supervisor_id,
					:final_approver_id,
					:ot_date, 
					:time_start,
					:time_end,
					:no_hours,
					:st,
					CURDATE(),
					:worked_done,
					:get_id
					)",$inputs); 
				$con->myQuery("UPDATE employees_ot_pre SET if_proceed=1 WHERE id=?",array($inputs['get_id']));
				
				$supervisor=getEmpDetails($inputs['supervisor_id']);
				$employees=getEmpDetails($inputs['employees_id']);

				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed an overtime request. From {$s_time} To {$e_time} for {$inputs['no_hours']} Hours. Worked to be done:{$inputs['worked_done']}");
				$email_settings=getEmailSettings();
				//var_dump($supervisor);
				if(!empty($supervisor) && !empty($email_settings)){
					$header="New Overtime Request For Your Approval";
					$message="Hi {$supervisor['first_name']},<br/> You have a new overtime request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
					$message=email_template($header,$message);
					// var_dump($email_settings);
					 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
					emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($supervisor['private_email'],$supervisor['work_email'])),"Overtime Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
				}else
				{
					$final_approver=getEmpDetails($inputs['final_approver_id']);
					if(!empty($final_approver['private_email']) || !empty($final_approver['work_email'])){

						$header="New Overtime Request For Your Approval";
						$message="Hi {$final_approver['first_name']},<br/> You have a new overtime request from {$employees['first_name']} {$employees['last_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
						$message=email_template($header,$message);
						// var_dump($email_settings);
						 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
						emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($final_approver['private_email'],$final_approver['work_email'])),"Overtime Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
					}
				}

				
			}
			
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