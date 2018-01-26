<?php
    require_once("support/config.php");
if (!isLoggedIn()) {
         toLogin();
         die();
}

if (!AllowUser(array(1,4))) {
     redirect("index.php");
}

if (!empty($_POST)) {
            //Validate form inputs
        $inputs=$_POST;
            // die;
    /*
    Unset extra rice
     */
    unset($inputs['hour']);
    unset($inputs['minute']);

    if (empty($inputs['employee_id'])) {
        Modal("Invalid Record Selected");
        redirect("frm_employee.php");
    } else {
        $emp=getEmpDetails($inputs['employee_id']);
        if (empty($emp)) {
            Modal("Invalid Record Selected");
            redirect("frm_employee.php");
        }
    }

    $required_fieds=array(
    "start_date"=>"<li>Enter start date. </li>",
    "time_in"=>"<li>Select Time in. </li>",
    "time_out"=>"<li>Select Time out.</li>",
    // "beginning_in"=>"<li>Select Beginning in. </li>",
    // "beginning_out"=>"<li>Select Beginning out.</li>",
    // "ending_in"=>"<li>Select Ending in. </li>",
    // "ending_out"=>"<li>Select Ending out.</li>",
    "working_days"=>"<li>Select working days.</li>",
    "late_start"=>"<li>Select Late Start.</li>"
    );
    $errors="";

    foreach ($required_fieds as $key => $value) {
        if (empty($inputs[$key])) {
            $errors.=$value;
        } else {
            #CUSTOM VALIDATION
            if (in_array($key, array(
                "time_in", "time_out", "beginning_in", "beginning_out", "ending_in", "ending_out", "late_start"))) {
                /*
                Validate if valid time
                 */
                $d=DateTime::createFromFormat('h:i A', $inputs[$key]);

                if (empty($d)) {
                    switch ($key) {
                        case 'time_in':
                            $errors.="<li>Invalid Time in.</li>";
                            break;
                        case 'time_out':
                            $errors.="<li>Invalid Time out.</li>";
                            break;
                        case 'beginning_in':
                            $errors.="<li>Invalid Beginning in.</li>";
                            break;
                        case 'beginning_out':
                            $errors.="<li>Invalid Beginning out.</li>";
                            break;
                        case 'ending_in':
                            $errors.="<li>Invalid Ending in.</li>";
                            break;
                        case 'ending_out':
                            $errors.="<li>Invalid Ending out.</li>";
                            break;
                        case 'late_start':
                            $errors.="<li>Invalid Late Start.</li>";
                            break;
                    }
                } else {
                    $inputs[$key]=$d->format("H:i:s");
                }
            } elseif ($key=='start_date') {
              try {
                $start_date=new DateTime($inputs['start_date']);
              } catch (Exception $e) {
              }

                if (empty($start_date)) {
                    $errors.="<li>Invalid Start date.</li>";
                } else {
                    $inputs[$key]=$start_date->format("Y-m-d");
                }
            } elseif ($key=="grace_minutes") {
                $inputs[$key]=intval($inputs[$key]);
            }
        }
    }
    /*
    Validate if start date is less than the current active shift's start date
     */
    $cur_active=$con->myQuery("SELECT start_date FROM employees_default_shifts WHERE employee_id=? AND ISNULL(end_date)", array($emp['id']))->fetchColumn();
    if (!empty($cur_active)) {
        $current_start_date=new DateTime($cur_active);
        if ($start_date<$current_start_date) {
            $errors.="<li>Start date cannot be less than currently active default shift.</li>";
        }
    }
    $tab=13;



    if ($errors!="") {
        Alert("You have the following errors: <br/><ul>".$errors."</ul>", "danger");
        if (empty($inputs['id'])) {
            redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}");
        } else {
            redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}&ee_id={$inputs['id']}");
        }
        die;
    } else {
            //IF id exists update ELSE insert
            $inputs['working_days']=implode(",", $inputs['working_days']);

            $new_end_date=$start_date;
            $new_end_date->modify("-1 day");
        unset($inputs['meridian']);
        
        /*
        Get active default shift and set the end date the the entered start date - 1 Day
         */
        $con->myQuery("UPDATE employees_default_shifts SET end_date=? WHERE employee_id=? AND ISNULL(end_date)", array($new_end_date->format("Y-m-d"), $emp['id']));
        /*
        Insert New Default shift
         */
        // $con->myQuery("INSERT INTO employees_default_shifts(employee_id, time_in, time_out, beginning_in, beginning_out, ending_in, ending_out, start_date, break_one_start, break_one_end, break_two_start, break_two_end, break_three_start, break_three_end, working_days, late_start, grace_minutes) VALUES(:employee_id, :time_in, :time_out, :beginning_in, :beginning_out, :ending_in, :ending_out, :start_date, :break_one_start, :break_one_end, :break_two_start, :break_two_end, :break_three_start, :break_three_end, :working_days, :late_start, :grace_minutes)", $inputs);
        $con->myQuery("INSERT INTO employees_default_shifts(employee_id, time_in, time_out, start_date, working_days, late_start, grace_minutes) VALUES(:employee_id, :time_in, :time_out, :start_date, :working_days, :late_start, :grace_minutes)", $inputs);
        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "Change default shift of {$emp['last_name']}, {$emp['first_name']}.");
                Alert("Save succesful", "success");
                redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}");
    }
            die;
} else {
            redirect('index.php');
            die();
}
    redirect('index.php');
