<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }


     // var_dump($_FILES['evidence']);
     // var_dump($_POST);
     // die;
		if(!empty($_POST)){
		//Validate form inputs
		$inputs=$_POST;
		// echo "<pre>";
		// print_r($inputs);
		// echo "</pre>";
		// die;

		

		$required_fieds=array(
			"ob_date"=>"Enter Date of OB. <br/>",
			"time_start"=>"Enter Time Start. <br/>",
			"time_end"=>"Enter Time End. <br/>",
			"destination"=>"Enter Destination. <br/>",
			"purpose"=>"Enter Purpose. <br/>"
			);
		$errors="";

		foreach ($required_fieds as $key => $value) {
			if(empty($inputs[$key])){
				$errors.=$value;
			}else{
				#CUSTOM VALIDATION
			}
		}
		$tab=6;
		$approval_flow=getApprovalFlow($_SESSION[WEBAPP]['user']['department_id']);
		if (empty($approval_flow)) {
			$errors.=" No approval flow selected. Please contact your Administrator. <br/>";
		}
		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect("frm_ob_request.php");
			die;
		}
		elseif(empty($_FILES['evidence'])){
			$errors.="Please select file for evidence.<br/>";
		}
		else{
			if(in_array(getFileExtension($_FILES['evidence']['name']), array(".jpg",".jpeg",".gif",".png",".bmp"))==false){
				$errors.="Invalid file type. (Please upload only files with the following extension (.jpg,.jpeg,.gif,.png,.bmp).)<br/>";
			}
			elseif(!empty($_FILES['evidence']['error']))
			{
				switch ($_FILES['evidence']['error']) 
				{
					case 1:
						$errors.="Exceeded upload size.<br/>";
						break;
					case 2:
						$errors.="Exceeded upload size.<br/>";
						break;
					case 3:
						$errors.="Upload did not complete.<br/>";
						break;
					case 4:
						$errors.="No file uploaded.<br/>";
						break;
				}
			}
		}

		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect("frm_ob_request.php");
			die();
			
			die;
		}
		else{
			// echo "<pre>";
			// print_r($inputs);
			// echo "</pre>";
			// die;

			
			//IF id exists update ELSE insert
			$inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
			$inputs['approval_step_id']=$approval_flow[0]['id'];
			$inputs['request_status_id']=1;
			// echo "<pre>";
			// print_r($inputs);
			// echo "</pre>";
			// die;

			if(empty($inputs['id'])){
				//Insert
				unset($inputs['id']);
				$inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
				
			
				$inputs['ob_date']=date_format(date_create($inputs['ob_date']), 'Y-m-d H:i:s');

				$inputs['time_start']=date_format(date_create($inputs['time_start']), 'H:i:s');
				$inputs['time_end']=date_format(date_create($inputs['time_end']), 'H:i:s');
				
				unset($inputs['hour']);
				unset($inputs['minute']);
				unset($inputs['meridian']);
				
				
				$con->myQuery("INSERT INTO employees_ob(
					employees_id,
					approval_step_id,
					ob_date,
					time_from,
					time_to,
					purpose,
					request_status_id,
					date_filed,
					destination
					
					
					
					) VALUES(
					:employees_id,
					:approval_step_id,
					:ob_date,
					:time_start,
					:time_end,
					:purpose,
					
					:request_status_id,
					NOW(),
					:destination
					
					)",$inputs);

				$file_id=$con->lastInsertId();
				$query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
				$request_id = $con->lastInsertId();
				$values=array();

				foreach ($approval_flow as $key => $step) {
					$value="";
					$value.="(";
					$value.="{$step['id']},{$request_id},'official_business',{$step['step_number']}";
					$value.=")";
					$values[]=$value;
				}
				
					

				$query.=implode(",", $values);

				$con->myQuery($query);
				//$con->commit();
			
			

			
			$filename=$file_id.getFileExtension($_FILES['evidence']['name']);
			move_uploaded_file($_FILES['evidence']['tmp_name'],"ob_evidence/".$filename);
			$con->myQuery("UPDATE employees_ob SET evidence=? WHERE id=?",array($filename,$file_id));


			// print_r($file_id);
			// die;
				$employees=getEmpDetails($inputs['employees_id']);

				$audit_message="Destination: {$inputs['destination']}. Purpose: {$inputs['purpose']} during ".date("Y-m-d",strtotime($inputs['time_start']))." - ".date("Y-m-d",strtotime($inputs['time_end']));

				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed an official business request. {$audit_message}");

				$email_settings=getEmailSettings();

				//die;
				//var_dump($supervisor);
				// if(!empty($supervisor) && !empty($email_settings)){
				// 	$header="New Official Business Request For Your Approval";
				// 	$message="Hi {$supervisor['first_name']},<br/> You have a new official business request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
				// 	$message=email_template($header,$message);
				// 	// var_dump($email_settings);
				// 	 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
				// 	emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($supervisor['private_email'],$supervisor['work_email'])),"Official Business Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
				// }
				// else{
				// 	$final_approver=getEmpDetails($inputs['final_approver_id']);
				// 	if(!empty($final_approver['private_email']) || !empty($final_approver['work_email'])){

				// 	$header="New Official Business Request For Your Approval";
				// 	$message="Hi {$final_approver['first_name']},<br/> You have a new official business request from {$employees['first_name']} {$employees['last_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
				// 	$message=email_template($header,$message);
				// 	// var_dump($email_settings);
				// 	 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
				// 	emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($final_approver['private_email'],$final_approver['work_email'])),"Official Business Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
				// 	}
				// }
				$approvers=getEmployeesFromSteps($inputs['approval_step_id']);
                $header="New Official Business Request For Your Approval";
                /*
                Modify message to be more generic and allow to be sent to multiple people.
                 */
                $message="Good day,<br/> You have a new Official Business request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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
                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Official Business Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);

                Alert("Save succesful", "success");
                redirect("shift_request.php");

			}
			
			
			Alert("Save succesful","success");
			redirect("ob_request.php");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>