<?php
    require_once("support/config.php");
     if(!isLoggedIn()){
        toLogin();
        die();
     }


        if(!empty($_POST)){
        //Validate form inputs
        $inputs=$_POST;
        
        $overtime=$con->myQuery("SELECT * FROM employees_ot WHERE id=:ot_id AND request_status_id=2 AND id NOT IN (SELECT employees_ot_id FROM employees_ot_adjustments WHERE employees_ot_id=:ot_id AND (request_status_id=2 OR request_status_id=1)) LIMIT 1",array("ot_id"=>$inputs['ot_id']))->fetch(PDO::FETCH_ASSOC);
        if (empty($overtime)) {
            Alert("Invalid record selected.");
            redirect("overtime.php?tab=3");
            die;
        }
        // echo "<pre>";
        // print_r($inputs);
        // echo "</pre>";
        // var_dump($overtime);
        // die;
        // if(empty($inputs['id'])){
        //  Modal("Invalid Record Selected");
        //  redirect("time_management.php");
        // }

        $required_fieds=array(
            "adj_time_in"=>"Enter Time in. <br/>",
            "adj_time_out"=>"Enter Time out. <br/>"
            );
        
        $errors="";

        $startdate = strtotime($inputs['adj_time_in']);
        $enddate = strtotime($inputs['adj_time_out']);

        foreach ($required_fieds as $key => $value) {
            if(empty($inputs[$key])){
                $errors.=$value;
            }else{
                #CUSTOM VALIDATION
            }
        }
        $approval_flow=getApprovalFlow($_SESSION[WEBAPP]['user']['department_id']);
        if (empty($approval_flow)) {
            $errors.=" No approval flow selected. Please contact your Administrator. <br/>";
        }
        if($errors!="")
        {
            Alert("You have the following errors: <br/>".$errors,"danger");
            redirect("frm_overtime_adjustment_request.php?id=".$overtime['id']);
            die;
        }
        else{
            // echo "<pre>";
            // print_r($inputs);
            // echo "</pre>";
            // die;
            $inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            $inputs['approval_step_id']=$approval_flow[0]['id'];
            
            $status=1;
            try {
                $inputs['ot_date']=date_format(date_create($overtime['ot_date']), 'Y-m-d');
                $inputs['orig_time_in']=$overtime['time_from'];
                $inputs['orig_time_out']=$overtime['time_to'];
                $inputs['adj_time_in']=date_format(date_create($inputs['adj_time_in']), 'H:i:s');
                $inputs['adj_time_out']=date_format(date_create($inputs['adj_time_out']), 'H:i:s');
                $inputs['orig_no_hours']=$overtime['no_hours'];

                $s_time=$inputs['adj_time_in'];
                $e_time=$inputs['adj_time_out'];

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

                $inputs['adj_no_hours']=floatval($ot_total_time);
                $con->beginTransaction();
                
                unset($inputs['hour']);
                unset($inputs['minute']);
                unset($inputs['meridian']);
            
                //var_dump($inputs);
                //die();

                $con->myQuery("INSERT INTO employees_ot_adjustments(
                    employees_id,
                    ot_date,
                    orig_time_in,
                    orig_time_out,
                    orig_no_hours,
                    adj_time_in,
                    adj_time_out,
                    adj_no_hours,
                    approval_step_id,
                    request_status_id,
                    date_filed,
                    employees_ot_id
                    ) VALUES(
                    :employees_id,
                    :ot_date,
                    :orig_time_in,
                    :orig_time_out,
                    :orig_no_hours,
                    :adj_time_in,
                    :adj_time_out,
                    :adj_no_hours,
                    :approval_step_id,
                    {$status},
                    NOW(),
                    :ot_id
                    )",$inputs);
                
                    $page="overtime.php";
                    // var_dump($inputs);
                    // die;
                    $audit_message="Add {$inputs['adj_time_in']}-{$inputs['adj_time_out']}";

                    $query="INSERT INTO request_steps(approval_step_id, request_id, request_type, step_number) VALUES ";
                    $request_id = $con->lastInsertId();
                    $values=array();
                    foreach ($approval_flow as $key => $step) {
                        $value="";
                        $value.="(";
                        $value.="{$step['id']},{$request_id},'ot_adjustment',{$step['step_number']}";
                        $value.=")";
                        $values[]=$value;
                    }
                    $query.=implode(",", $values);
                    $con->myQuery($query);

                    $con->commit();
            } catch (Exception $e) {
                $con->rollBack();
                // die;
                Alert("Save Failed.","danger");
                redirect($page);
            }
                
                // die('ins');
                $employees=getEmpDetails($inputs['employees_id']);
                
                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"{$employees['first_name']} {$employees['last_name']} filed an overtime adjustment request. {$audit_message}");

                $email_settings=getEmailSettings();
                /*
                Get Employees from approval_step_employees based on the first step
                 */
                $approvers=getEmployeesFromSteps($inputs['approval_step_id']);
                /*
                Modify message to be more generic and allow to be sent to multiple people.
                 */
                $header="New Overtime Adjustment Request For Your Approval";
                $message="Good day,<br/> You have a new overtime adjustment request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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
                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Overtime Adjustment Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                //var_dump($supervisor);
            // die;
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