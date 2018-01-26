<?php
    require_once("support/config.php");
     if (!isLoggedIn()) {
         toLogin();
         die();
     }

     if (empty($_SESSION[WEBAPP]["user"]["can_apply_for_meal_transpo"])) {
	    redirect("index.php");
	    die;
	  }
        if (!empty($_POST)) {
            //Validate form inputs
        $inputs=$_POST;
        // var_dump($inputs);
        // var_dump($_FILES);
        // die; 
            $required_fieds=array(
            "reason"=>"Enter Reason. <br/>",
            "date_applied"=>"Select Date. <br/>"
            );
            $errors="";

            foreach ($required_fieds as $key => $value) {
                if (empty($inputs[$key])) {
                    $errors.=$value;
                } else {
                    #CUSTOM VALIDATION
                    if ($key=="date_applied") {
                        /*
                            Get Total Work hours for the day
                         */
                        $date_applied=new DateTime($inputs['date_applied']);
                        if ($date_applied) {
                        	$inputs[$key]=$date_applied->format("Y-m-d");
                            $dtr_record=getTimeInAndOut($_SESSION[WEBAPP]["user"]["employee_id"], $inputs["date_applied"]);
                            // var_dump(!empty($dtr_record['in_time']) && !empty($dtr_record['out_time']));
                            // die;
                            if (!empty($dtr_record['in_time']) && !empty($dtr_record['out_time'])) {
                                $hours_worked=getHoursWorked($dtr_record['in_time'], $dtr_record['out_time'], $dtr_record['shift'], true);
                                $ot=getOvertimePerDay($_SESSION[WEBAPP]['user']['employee_id'], $date_applied->format("Y-m-d"));
                                
                                // var_dump($hours_worked, $ot);
                                $total_hours=floatval($hours_worked['hours'])+$ot;
                                if ($total_hours <= 9) {
                                    $errors.="Date selected should have working hours of greater than 9.";
                                } else {
                                	/*
                                	Check if date already exists and if approved or in process
                                	 */
                                }
                        } else {
                                $errors.="No DTR record found";
                            }
                        }
                    }
                }
            }

            if (empty($inputs["food_allowance"]) && empty($inputs['transpo_allowance'])) {
                $errors.="Enter food allowance amount or transportation allowance amount.";
            }
            
            if(empty($_FILES['evidence'])){
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
            /*
            Get the approval flow using the department id of the logged in user.
             */
            $approval_flow=getApprovalFlow($_SESSION[WEBAPP]['user']['department_id']);
            if (empty($approval_flow)) {
                $errors.=" No approval flow selected. Please contact your Administrator. <br/>";
            }
            if ($errors!="") {
                Alert("You have the following errors: <br/>".$errors, "danger");
                redirect("frm_allowance_request.php");
                die;
            } else {
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

                $con->myQuery("INSERT INTO employees_allowances(
				employees_id,
				approval_step_id,
				date_applied,
				food_allowance,
				transpo_allowance,
				request_reason,
				request_status_id,
				date_filed
				) VALUES(
				:employees_id,
				:approval_step_id,
				:date_applied, 
				:food_allowance,
				:transpo_allowance,
				:reason,
				:request_status_id,
				CURDATE()
				)", $inputs);
                $file_id=$con->lastInsertId();
                $filename=$file_id.getFileExtension($_FILES['evidence']['name']);
                move_uploaded_file($_FILES['evidence']['tmp_name'],"allowance_evidence/".$filename);
                $con->myQuery("UPDATE employees_allowances SET evidence=? WHERE id=?",array($filename,$file_id));

                $query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
                $request_id = $file_id;
                $values=array();
                foreach ($approval_flow as $key => $step) {
                    $value="";
                    $value.="(";
                    $value.="{$step['id']},{$request_id},'allowance  ',{$step['step_number']}";
                    $value.=")";
                    $values[]=$value;
                }
                $query.=implode(",", $values);
                $con->myQuery($query);

                $employees=getEmpDetails($inputs['employees_id']);
 
                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$employees['first_name']} {$employees['last_name']} filed an allowance request. For {$inputs['date_applied']} with Food Allowance of ({$inputs['food_allowance']}), Transportaion Allowance of ({$inputs['transpo_allowance']}).");

                $email_settings=getEmailSettings();

                /*
                Get Employees from approval_step_employees based on the first step
                 */
                $approvers=getEmployeesFromSteps($inputs['approval_step_id']);
                $header="New Allowance Request For Your Approval";
                /*
                Modify message to be more generic and allow to be sent to multiple people.
                 */
                $message="Good day,<br/> You have a new allowance request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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
                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Allowance Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                
                Alert("Save succesful", "success");
                redirect("allowance_request.php");
            }
            die;
        } else {
            redirect('index.php');
            die();
        }
    redirect('index.php');
