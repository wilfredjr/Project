<?php
require_once("support/config.php");
 if(!isLoggedIn()){
 	toLogin();
 	die();
 }

function validate($fields)
{
    global $page;
    $inputs=$_POST;
    $errors="";
    foreach ($fields as $key => $value) {
        if(empty($inputs[$key])){
            $errors.=$value;
            //var_dump($inputs[$key]);
        }else{
            #CUSTOM VALIDATION
        }
    }
    if($errors!=""){
        Alert("You have the following errors: <br/>".$errors,"danger");
        redirect($page);
        return false;
        die;
    }
    else{
        return true;
    }


}
$inputs=$_POST;
$required_fieds=array();
$page='index.php';

if(empty($_POST['id'])){
	Modal("Invalid Record Selected");
	redirect($page);
	die;
}
else{
	try {
		  // $audit_details=$con->myQuery("SELECT employee_name,ot_date,orig_time_in,orig_time_out,adj_time_in,adj_time_out FROM vw_employees_ot_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
        $current_employee=$_SESSION[WEBAPP]['user']['employee_id'];
                $current=$con->myQuery("SELECT * FROM  project_phase_request WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $next_phase=$current['project_phase_id']+1;
                    $prev_phase=$current['project_phase_id'];
                $date = (new DateTime())->getTimestamp();
                $date_now=date('Y-m-d',$date);
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                 switch ($inputs['action']) {
                    case 'approve':
                    $page='project_phase_approval.php';
                if($current['step_id']=='2'){
                        $con->myQuery("UPDATE project_phase_request SET step_id = 3 WHERE id=?",array($inputs['id']));
                }elseif($current['step_id']=='3'){
                    if($inputs['req_type']=='comp'){#manager comp
                        $def_check=$con->myQuery("SELECT * FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND (in_deficit='1' OR status_id='4')",array($current['project_id'],$current['project_phase_id']))->fetch(PDO::FETCH_ASSOC);
                        $def_check_start=$con->myQuery("SELECT * FROM project_deficit WHERE project_id=? AND project_phase_id=? AND done_days='0' AND done_hours='0'",array($current['project_id'],$current['project_phase_id']))->fetch(PDO::FETCH_ASSOC);
                        if(empty($def_check)){
                        $con->myQuery("UPDATE project_phase_dates SET status_id='2',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($current['date_filed'],$current['project_id'],$current['project_phase_id']));

                            if($current['project_phase_id']=='2'){
                             $phase_check=$con->myQuery("SELECT id FROM  project_phase_dates WHERE project_id=? AND project_phase_id='3'",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
                             if(empty($phase_check)){
                                $current1=$con->myQuery("SELECT * FROM  projects WHERE id=?",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);

                                     $con->myQuery("INSERT INTO project_development (project_id,employee_id,team_lead_id,manager_id,admin_id,type,request_status_id,date_filed, phase_request_id) VALUES(?,?,?,?,?,'admin','1','$date_now',?)",array($current1['id'],$current1['employee_id'],$current1['team_lead_dev'],$current1['manager_id'],$current1['employee_id'],$current['id']));

                                      $con->myQuery("UPDATE projects SET project_status_id='1' WHERE id=?",array($current['project_id']));
                                     }else{
                                 $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    if(!empty($stat_check)){
                                        $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                         $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    }
                                if($current['project_phase_id']=='8'){
                                    $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                }else{
                                    $con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$current['project_id']));
                                    }
                                }
                            }else{
                                 $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    if(!empty($stat_check)){
                                        $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                         $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    }
                                if($current['project_phase_id']=='8'){
                                    $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                }else{
                                    $con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$current['project_id']));
                                    }
                                }
                        }else{
                            if(($def_check['status_id']=='4') && ($def_check['in_deficit']=='0')){
                                    $date_end1= new DateTime($def_check['date_end']);
                                    $date_end_next=($date_end1->getTimestamp())+$addDay;
                                    do{
                                    $try=date('Y-m-d', ($date_end_next+$addDay));
                                            $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                    $nextDay = date('w', ($date_end_next+$addDay));
                                    $date_end_next = $date_end_next+$addDay;}
                                    while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                    $date_end_next=date('Y-m-d',$date_end_next);
                                    $date_now1= new DateTime($current['date_filed']);
                                    // $date_now1->modify('+1 day');
                                    $interval = $date_now1->diff($date_end1);
                                    $days = $interval->days;
                                    $period = new DatePeriod($date_end1, new DateInterval('P1D'), $date_now1);
                                    foreach($period as $dt) {
                                    $curr = $dt->format('D');
                                    $holiday= $con->myQuery("SELECT holiday_date FROM holidays WHERE holiday_date=?",array($dt->format('Y-m-d')))->fetchAll(PDO::FETCH_ASSOC);
                                    // substract if Saturday or Sunday
                                    if ($curr == 'Sat' || $curr == 'Sun') {
                                        $days--;
                                        }
                                    // (optional) for the updated question
                                    elseif (!empty($holiday)) {
                                        $days--;
                                        }
                                    }
                                    if($days=='0'){$days='1';}
                                    $hours=$days*8;
                                    $con->myQuery("UPDATE project_phase_dates SET status_id='2',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($def_check['date_end'],$current['project_id'],$current['project_phase_id']));
                                    if($current['project_phase_id']=='2'){
                                     $phase_check=$con->myQuery("SELECT id FROM  project_phase_dates WHERE project_id=? AND project_phase_id='3'",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
                                     if(empty($phase_check)){
                                        $current1=$con->myQuery("SELECT * FROM  projects WHERE id=?",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);

                                             $con->myQuery("INSERT INTO project_development (project_id,employee_id,team_lead_id,manager_id,admin_id,type,request_status_id,date_filed, phase_request_id) VALUES(?,?,?,?,?,'admin','1','$date_now',?)",array($current1['id'],$current1['employee_id'],$current1['team_lead_dev'],$current1['manager_id'],$current1['employee_id'],$current['id']));

                                             $con->myQuery("UPDATE projects SET project_status_id='1' WHERE id=?",array($current['project_id']));

                                     }else{
                                    $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    if($stat_check['status_id']=='3'){
                                        $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                         $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    }
                                    if($current['project_phase_id']=='8'){
                                            $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                        }else{
                                            $con->myQuery("UPDATE projects SET project_status_id=?,cur_phase=? WHERE id=?",array($stat_check['status_id'],$next_phase,$current['project_id']));
                                        }
                                    $con->myQuery("INSERT INTO project_deficit (project_id,project_phase_id,date_start,date_end,in_hours,in_days,done_days,done_hours) VALUES(?,?,'$date_end_next',?,'$hours','$days','$days','$hours')",array($current['project_id'],$current['project_phase_id'],$current['date_filed']));
                                    }
                                    }else{
                                        $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                        if($stat_check['status_id']=='3'){
                                            $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                             $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                        }
                                        if($current['project_phase_id']=='8'){
                                                $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                            }else{
                                                $con->myQuery("UPDATE projects SET project_status_id=?,cur_phase=? WHERE id=?",array($stat_check['status_id'],$next_phase,$current['project_id']));
                                            }
                                        $con->myQuery("INSERT INTO project_deficit (project_id,project_phase_id,date_start,date_end,in_hours,in_days,done_days,done_hours) VALUES(?,?,'$date_end_next',?,'$hours','$days','$days','$hours')",array($current['project_id'],$current['project_phase_id'],$current['date_filed']));
                                    }
                                
                                }elseif(($def_check['status_id']=='4')&&($def_check['in_deficit']=='1')){
                                $date_start1= new DateTime($def_check_start['date_start']);
                                $date_now1= new DateTime($current['date_filed']);
                                // $date_now1->modify('+1 day');
                                $interval = $date_now1->diff($date_start1);
                                $days = $interval->days;
                                $period = new DatePeriod($date_start1, new DateInterval('P1D'), $date_now1);
                                foreach($period as $dt) {
                                $curr = $dt->format('D');
                                $holiday= $con->myQuery("SELECT holiday_date FROM holidays WHERE holiday_date=?",array($dt->format('Y-m-d')))->fetchAll(PDO::FETCH_ASSOC);
                                // substract if Saturday or Sunday
                                if ($curr == 'Sat' || $curr == 'Sun') {
                                    $days--;
                                    }
                                // (optional) for the updated question
                                elseif (!empty($holiday)) {
                                    $days--;
                                    }
                                }
                                if($days=='0'){$days='1';}
                                $hours=$days*8;
                                $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                $con->myQuery("UPDATE project_phase_dates SET status_id='2',in_deficit='0' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$current['project_phase_id']));
                                if($stat_check['status_id']=='3'){
                                    $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                    $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                }
                                if($current['project_phase_id']=='8'){
                                        $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                    }else{
                                        $con->myQuery("UPDATE projects SET project_status_id=?,cur_phase=? WHERE id=?",array($stat_check['status_id'],$next_phase,$current['project_id']));
                                    }
                                $con->myQuery("UPDATE project_deficit SET done_days='$days',done_hours='$hours',date_end=? WHERE done_days='0' AND done_hours='0' AND project_id=? AND project_phase_id=?",array($current['date_filed'],$current['project_id'],$current['project_phase_id']));
                            }elseif(($def_check['status_id']=='1')&&($def_check['in_deficit']=='1')){
                                $date_end_check=(new DateTime($def_check['date_end']))->getTimestamp();
                                if($date_end_check<$date_now){
                                    $date_end1= new DateTime($def_check['date_end']);
                                    $date_end_next=($date_end1->getTimestamp())+$addDay;
                                    do{
                                    $try=date('Y-m-d', ($date_end_next+$addDay));
                                            $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                    $nextDay = date('w', ($date_end_next+$addDay));
                                    $date_end_next = $date_end_next+$addDay;}
                                    while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                    $date_end_next=date('Y-m-d',$date_end_next);
                                    $date_now1= new DateTime($date_applied);
                                    // $date_now1->modify('+1 day');
                                    $interval = $date_now1->diff($date_end1);
                                    $days = $interval->days;
                                    $period = new DatePeriod($date_end1, new DateInterval('P1D'), $date_now1);
                                    foreach($period as $dt) {
                                    $curr = $dt->format('D');
                                    $holiday= $con->myQuery("SELECT holiday_date FROM holidays WHERE holiday_date=?",array($dt->format('Y-m-d')))->fetchAll(PDO::FETCH_ASSOC);
                                    // substract if Saturday or Sunday
                                    if ($curr == 'Sat' || $curr == 'Sun') {
                                        $days--;
                                        }
                                    // (optional) for the updated question
                                    elseif (!empty($holiday)) {
                                        $days--;
                                        }
                                    }
                                    if($days=='0'){$days='1';}
                                    $hours=$days*8;
                                    $con->myQuery("UPDATE project_phase_dates SET status_id='2',in_deficit='0',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($def_check['date_end'],$current['project_id'],$current['project_phase_id']));
                                    $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    if($stat_check['status_id']){
                                        $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                        $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    }
                                        if($current['project_phase_id']=='8'){
                                            $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                        }else{
                                            $con->myQuery("UPDATE projects SET project_status_id=?,cur_phase=? WHERE id=?",array($stat_check['status_id'],$next_phase,$current['project_id']));
                                        }
                                    $con->myQuery("UPDATE project_deficit SET done_days='$days',done_hours='$hours',date_start=?,date_end='$date_applied' WHERE done_days='0' AND done_hours='0' AND project_id=? AND project_phase_id=?",array($date_end_next,$current['project_id'],$current['project_phase_id']));
                                }else{
                                     if($current['project_phase_id']=='8'){
                                            $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                        }else{
                                            $con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$current['project_id']));
                                        }
                                     $con->myQuery("UPDATE project_phase_dates SET status_id='2',in_deficit='0',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($current['date_filed'],$current['project_id'],$current['project_phase_id']));
                                      $con->myQuery("UPDATE project_deficit SET date_end=? WHERE project_id=? AND project_phase_id=?",array($current['date_filed'],$inputs['proj_id'],$inputs['phase_id']));
                                }
                            }
                        }
                }else{#rev
                            $hours=$current['hours'];
                            $days=($hours/8);
                            if (is_float($days)){
                                $days=floor($days)+1;
                            }else{
                                $days=$days;
                            }
                            $date_end=$con->myQuery("SELECT temp_date_end,date_end FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$prev_phase))->fetch(PDO::FETCH_ASSOC);
                            $prev_temp_date_end=(new DateTime($date_end['temp_date_end']))->getTimestamp();;
                            $prev_date_end=(new DateTime($date_end['date_end']))->getTimestamp();;
                            $date_now1=(new DateTime($date_now))->getTimestamp();

                        if(($prev_temp_date_end<=$prev_date_end)&&($prev_temp_date_end<$date_now1)){
                        $con->myQuery("UPDATE project_phase_dates SET status_id='1',in_deficit='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$prev_phase));
                        $con->myQuery("UPDATE projects SET cur_phase=? WHERE id=?",array($prev_phase,$current['project_id']));
                      }else{
                        $con->myQuery("UPDATE project_phase_dates SET status_id='4',in_deficit='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$prev_phase));
                        $con->myQuery("UPDATE projects SET project_status_id='4',cur_phase=? WHERE id=?",array($prev_phase,$current['project_id']));
                        $con->myQuery("INSERT INTO project_deficit (project_id,project_phase_id,date_start,in_hours,in_days) VALUES(?,?,?,'$hours','$days')",array($current['project_id'],$prev_phase,$current['date_filed']));
                      }
                    }
                    $con->myQuery("UPDATE project_phase_request SET request_status_id = 2, date_approved=? WHERE id=?",array($date_now,$inputs['id']));
                    $con->myQuery("UPDATE project_files SET is_approved = 1 WHERE phase_request_id=?",array($current['id']));
                }
                    Alert("Request has been approved.","success");
                        break;

                    case 'reject':
                    // $current1=$con->myQuery("SELECT id,employee_id,first_approver_date,second_approver_date,third_approver_date,first_approver_id,second_approver_id,third_approver_id,modification_type,project_id,requested_employee_id FROM  project_requests WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    //     //var_dump($supervisor);
                    //         if($current1['modification_type']=='0'){
                    //             $mod_type="Remove";
                    //         }
                    //         elseif($current1['modification_type']=='1'){
                    //             $mod_type="Add";
                    //         }
                        $required_fieds=array(
                        "reason"=>"Enter Reason for rejection. <br/>"
                        );
                        $page='project_phase_approval.php';
                        if(validate($required_fieds)){
                            $con->myQuery("UPDATE project_phase_request SET request_status_id = 4, reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                            // $supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                            // $employees=getEmpDetails($current['employee_id']);
                            //  $requested_emp=getEmpDetails($current['requested_employee_id']);
                            // $email_settings=getEmailSettings();

                            // insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Rejected {$employees['first_name']} {$employees['last_name']}'s {$mod_type} project employee request. Employee Name: {$requested_emp['first_name']} {$requested_emp['last_name']}. The reason given is '{$inputs['reason']}'. {$audit_message}");
                            // //var_dump($supervisor);
                            // if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                            //     $header="{$mod_type} Project Employee Request Rejected";
                            //     $message="Hi {$employees['first_name']},<br/> Your request has been rejected by , {$supervisor['first_name']} {$supervisor['last_name']}. Employee Name: {$requested_emp['first_name']} {$requested_emp['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
                            //     $message=email_template($header,$message);
                            //     // var_dump($email_settings);
                            //      //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                            //     PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"{$mod_type} Project Employee Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
                           // }
                            Alert("Request has been rejected.","success");
                        }
                        break;
                }
		if($page=="index.php"){
			//var_dump($_POST);
			die();
		}
		redirect($page);
	} catch (Exception $e) {

		die($e);
        redirect("index.php");
	}
}
// die;
if(!empty($page)){
	redirect($page);
}
else{
	die;
 redirect('index.php');
}
?>
