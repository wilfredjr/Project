<?php
    require_once("support/config.php");
     if (!isLoggedIn()) {
         toLogin();
         die();
     }



        if (!empty($_POST)) {
            //Validate form inputs
        $inputs=$_POST;
        // echo "<pre>";
        // print_r($inputs);
        // echo "</pre>";
        // die;


            $required_fieds=array(
            "date_from"=>"Enter Date an Time Start. <br/>",
            "date_to"=>"Enter Date an Time End. <br/>",
            "orig_in_time"=>"Enter Original In Time. <br/>",
            "orig_out_time"=>"Enter Original Out Time. <br/>",
            // "adj_in_time"=>"Enter Requested In Time. <br/>",
            // "adj_out_time"=>"Enter Requested Out Time. <br/>",
            "shift_reason"=>"Enter Shift Reason. <br/>",
            "shift"=>"Select a shift<br/>",
            "project_id"=>"Select Project <br/>"
            );
            $errors="";

            foreach ($required_fieds as $key => $value) {
                if (empty($inputs[$key])) {
                    $errors.=$value;
                } else {
                    #CUSTOM VALIDATION
                }
            }

			/*
			Get Shift Details
			 */        
			$shift_details=$con->myQuery("SELECT time_in, time_out, break_one_start, break_one_end, break_two_start, break_two_end, break_three_start, break_three_end, beginning_time_in, beginning_time_out, ending_time_in, ending_time_out FROM shifts WHERE id=?", array($inputs['shift']))->fetch(PDO::FETCH_ASSOC);
			if (empty($shift_details)) {
				$errors.="Invalid shift selected.<br/>";
			}
            $tab=6;
            $approval_flow=getApprovalFlow($_SESSION[WEBAPP]['user']['department_id']);
            if (empty($approval_flow)) {
                $errors.=" No approval flow selected. Please contact your Administrator. <br/>";
            }
            if($errors!="")
            {
                Alert("You have the following errors: <br/>".$errors,"danger");
                redirect("frm_shift_request.php");
                die;
            }
            if ($errors!="") {
                Alert("You have the following errors: <br/>".$errors, "danger");
            
                redirect("frm_shift_request.php");
            
                die;
            } else {
                

            //IF id exists update ELSE insert
            $inputs['approval_step_id']=$approval_flow[0]['id'];
            $inputs['request_status_id']=1;
            $inputs['date_from']=SaveDate($inputs['date_from']);
                $inputs['date_to']=SaveDate($inputs['date_to']);

                
                $inputs['adj_in_time']=$shift_details['time_in'];
                $inputs['adj_out_time']=$shift_details['time_out'];
                $inputs['beginning_in']=$shift_details['beginning_time_in'];
                $inputs['beginning_out']=$shift_details['beginning_time_out'];
                $inputs['ending_in']=$shift_details['ending_time_in'];
                $inputs['ending_out']=$shift_details['ending_time_out'];
                $inputs['break_one_start']=$shift_details['break_one_start'];
                $inputs['break_one_end']=$shift_details['break_one_end'];
                $inputs['break_two_start']=$shift_details['break_two_start'];
                $inputs['break_two_end']=$shift_details['break_two_end'];
                $inputs['break_three_start']=$shift_details['break_three_start'];
                $inputs['break_three_end']=$shift_details['break_three_end'];
                $inputs['working_days']=implode(",", $inputs['working_days']);
                                    $inputs['date_from']=date_format(date_create($inputs['date_from']), 'Y-m-d H:i:s');
                    $inputs['date_to']=date_format(date_create($inputs['date_to']), 'Y-m-d H:i:s');

                    $inputs['orig_in_time']=date_format(date_create($inputs['orig_in_time']), 'H:i:s');
                    $inputs['orig_out_time']=date_format(date_create($inputs['orig_out_time']), 'H:i:s');

                    $inputs['adj_in_time']=date_format(date_create($inputs['adj_in_time']), 'H:i:s');
                    $inputs['adj_out_time']=date_format(date_create($inputs['adj_out_time']), 'H:i:s');

                    
                    unset($inputs['hour']);
                    unset($inputs['minute']);
                    unset($inputs['meridian']);
                    unset($inputs['shift']);

                    
                    //Insert
                // unset($inputs['id']);
                    if (is_array($inputs['employees_id'])) {
                        // Multiple Employee Selected
                        foreach ($inputs['employees_id'] as $employee_id) {
                            $approval_flow=getApprovalFlow(getEmployeeDepartment($employee_id));
                            $inputs['employees_id'] = $employee_id;
                            $inputs['approval_step_id']=$approval_flow[0]['id'];
                            $inputs['requestor_id'] = $_SESSION[WEBAPP]['user']['employee_id'];
                                //$inputs['supervisor_id']=$con->myQuery("SELECT e.supervisor_id FROM employees e WHERE e.id=?", array($inputs['employees_id']))->fetchColumn();
                                //$inputs['final_approver_id']=$con->myQuery("SELECT d.approver_id FROM departments d INNER JOIN employees e ON d.id=e.department_id WHERE e.id=?", array($inputs['employees_id']))->fetchColumn();
                        

                                    // echo "<pre>";
                                    //   print_r($inputs);
                                    //   echo "</pre>";
                                    //   die;
                        
                                    $con->myQuery("INSERT INTO employees_change_shift(
                                    employees_id,
                                    approval_step_id,
                                    request_status_id,
                                    date_from,
                                    date_to,
                                    shift_reason,
                                    
                                    date_filed,
                                    orig_in_time,
                                    orig_out_time,
                                    adj_in_time,
                                    adj_out_time,
                                    beginning_in,
                                    beginning_out,
                                    ending_in,
                                    ending_out,
                                    break_one_start,
                                    break_one_end,
                                    break_two_start,
                                    break_two_end,
                                    break_three_end,
                                    break_three_start,
                                    working_days,
                                    project_id,
                                    requestor_id
                                    ) VALUES(
                                    :employees_id,
                                    :approval_step_id,
                                    :request_status_id,
                                    :date_from,
                                    :date_to,
                                    :shift_reason,
                                    
                                    NOW(),
                                    :orig_in_time,
                                    :orig_out_time,
                                    :adj_in_time,
                                    :adj_out_time,
                                    :beginning_in,
                                    :beginning_out,
                                    :ending_in,
                                    :ending_out,
                                    :break_one_start,
                                    :break_one_end,
                                    :break_two_start,
                                    :break_two_end,
                                    :break_three_end,
                                    :break_three_start,
                                    :working_days,
                                    :project_id,
                                    :requestor_id
                                    )", $inputs);

                            //die;
                            $audit_message="From {$inputs['orig_in_time']}-{$inputs['orig_out_time']} to {$inputs['adj_in_time']}-{$inputs['adj_out_time']} during ".date("Y-m-d", strtotime($inputs['date_from']))." - ".date("Y-m-d", strtotime($inputs['date_to']));

                            $query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
                            $request_id = $con->lastInsertId();
                            $values=array();

                            foreach ($approval_flow as $key => $step) {
                                $value="";
                                $value.="(";
                                $value.="{$step['id']},{$request_id},'shift',{$step['step_number']}";
                                $value.=")";
                                $values[]=$value;
                            }
                        
                        

                            $query.=implode(",", $values);

                            $con->myQuery($query);

            
                            $employees=getEmpDetails($inputs['employees_id']);
                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$employees['first_name']} {$employees['last_name']} filed a shift change request for {$employees['first_name']} {$employees['last_name']}. {$audit_message}");
                            $email_settings=getEmailSettings();
                            //var_dump($supervisor);
                            // if (!empty($supervisor) && !empty($email_settings)) {
                            //     $header="New Change Shift Request For Your Approval";
                            //     $message="Hi {$supervisor['first_name']},<br/> You have a new change shift request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                            //     $message=email_template($header, $message);
                            //     // var_dump($email_settings);
                            //      //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                            //     PHPemailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($supervisor['private_email'],$supervisor['work_email'])), "Change Shift Request (For Approval)", $message, $email_settings['host'], $email_settings['port']);
                            // } else {
                            //     $final_approver=getEmpDetails($inputs['final_approver_id']);
                                
                            //     if (!empty($final_approver['private_email']) || !empty($final_approver['work_email'])) {
                            //         $header="New Change Shift Request For Your Approval";
                            //         $message="Hi {$final_approver['first_name']},<br/> You have a new change shift request from {$employees['first_name']} {$employees['last_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                            //         $message=email_template($header, $message);
                            //     // var_dump($email_settings);
                            //      //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                            //     PHPemailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($final_approver['private_email'],$final_approver['work_email'])), "Change Shift Request (For Approval)", $message, $email_settings['host'], $email_settings['port']);
                            //     }
                            // }
                            
                            $approvers=getEmployeesFromSteps($inputs['approval_step_id']);
                            $header="New Change Shift Request For Your Approval";
                            /*
                            Modify message to be more generic and allow to be sent to multiple people.
                            */
                            $message="Good day,<br/> You have a new Change Shift request from {$employees['last_name']}, {$employees['first_name']}. Filed by {$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']}. For more details please login to the Secret 6 HRIS.";
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
                            PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Change Shift Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                        }
                    }else {
                        $inputs['approval_step_id']=$approval_flow[0]['id'];
                        $con->myQuery("INSERT INTO employees_change_shift(
                        employees_id,
                        approval_step_id,
                        request_status_id,
                        date_from,
                        date_to,
                        shift_reason,
                        date_filed,
                        orig_in_time,
                        orig_out_time,
                        adj_in_time,
                        adj_out_time,
                        beginning_in,
                        beginning_out,
                        ending_in,
                        ending_out,
                        break_one_start,
                        break_one_end,
                        break_two_start,
                        break_two_end,
                        break_three_end,
                        break_three_start,
                        working_days,
                        project_id,
                        requestor_id
                        ) VALUES(
                        :employees_id,
                        :approval_step_id,
                        :request_status_id,
                        :date_from,
                        :date_to,
                        :shift_reason,
                        
                        NOW(),
                        :orig_in_time,
                        :orig_out_time,
                        :adj_in_time,
                        :adj_out_time,
                        :beginning_in,
                        :beginning_out,
                        :ending_in,
                        :ending_out,
                        :break_one_start,
                        :break_one_end,
                        :break_two_start,
                        :break_two_end,
                        :break_three_end,
                        :break_three_start,
                        :working_days,
                        :project_id,
                        :requestor_id
                        )", $inputs);

                        $audit_message="From {$inputs['orig_in_time']}-{$inputs['orig_out_time']} to {$inputs['adj_in_time']}-{$inputs['adj_out_time']} during ".date("Y-m-d", strtotime($inputs['date_from']))." - ".date("Y-m-d", strtotime($inputs['date_to']));

                        $query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
                        $request_id = $con->lastInsertId();
                        $values=array();

                        foreach ($approval_flow as $key => $step) {
                            $value="";
                            $value.="(";
                            $value.="{$step['id']},{$request_id},'shift',{$step['step_number']}";
                            $value.=")";
                            $values[]=$value;
                        }
                    
                        $query.=implode(",", $values);

                        $con->myQuery($query);

                        $employees=getEmpDetails($inputs['employees_id']);
                        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$employees['first_name']} {$employees['last_name']} filed a shift change request. {$audit_message}");
                        $email_settings=getEmailSettings();
             
                
                        $approvers=getEmployeesFromSteps($inputs['approval_step_id']);
                        $header="New Change Shift Request For Your Approval";
                        /*
                        Modify message to be more generic and allow to be sent to multiple people.
                        */
                        $message="Good day,<br/> You have a new Change Shift request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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
                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Change Shift Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                    }
                    
                    Alert("Save succesful", "success");
                    redirect("shift_request.php");

            }
            die;
        } else {
        redirect('index.php');
        die();
    }
    redirect('index.php');
