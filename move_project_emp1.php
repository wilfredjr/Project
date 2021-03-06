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
                $current=$con->myQuery("SELECT id,first_approver_date,second_approver_date,third_approver_date,first_approver_id,second_approver_id,third_approver_id,modification_type,project_id,requested_employee_id,employee_id,manager_id,designation_id,step_id,admin_id,date_filed FROM  project_requests WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                $date = new DateTime();

                $date_removed=date_format($date, 'Y-m-d');
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                 switch ($inputs['action']) {
                    case 'approve':
                    $page='project_employee_approval.php';
                    // var_dump($inputs);
                    // die;
                            /*
                            Get Next step if exists if empty set status to approved 2
                             */
                            // $next_step=getNextStep($current['approval_step_id'], $current['id'], 'ot_adjustment');
                          $con->beginTransaction();   
                          if($current['step_id']=='2'){
                           $con->myQuery("UPDATE project_requests SET step_id = 3 WHERE id=?",array($inputs['id']));
                          }elseif($current['step_id']=='3'){
                            if($current['modification_type']=='1'){
                            $param=array(
                            "project_id"=>$current['project_id'],
                            "employee_id"=>$current['requested_employee_id'],
                            'designation'=>$current['designation_id'],
                            'date_assigned'=>$current['date_filed']
                            );

                            $con->myQuery("INSERT INTO projects_employees (project_id,employee_id,designation_id,date_assigned) VALUES (:project_id,:employee_id,:designation,:date_assigned)",$param);
                            $param1=array(
                            "project_id"=>$current['project_id'],
                            "employee_id"=>$current['requested_employee_id'],
                            'start_date'=>$date_removed,
                            'added_by_id'=>$current['employee_id'],
                            'designation'=>$current['designation_id']
                            );
                            $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,designation_id) VALUES (:project_id,:employee_id,:start_date,:added_by_id,:designation)",$param1);
                            $con->myQuery("UPDATE project_requests SET status_id = 2 WHERE id=?",array($current['id']));
                            // if($current['designation_id']=='1'){
                            //      $emp_count=$con->myQuery("SELECT COUNT(id) as id FROM projects_employees WHERE designation_id=1 AND project_id=?",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
                            //      $hours=$con->myQuery("SELECT man_hours FROM  projects WHERE id=?",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
                            //      $dev_start=$con->myQuery("SELECT date_start FROM  project_phase_dates WHERE project_id=? AND project_phase_id=3",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
                            //      if(!empty($dev_start)){
                            //         $dev_start_date=$dev_start['date_start'];
                                    
                            //         $daystoadd=($hours['man_hours']/(8*($emp_count['id'])));
                            //                                 if (is_float($daystoadd)){
                            //                                     $fordate=floor($daystoadd)+1;
                            //                                 }else{
                            //                                     $fordate=$daystoadd;
                            //                                 }
                            //                                     $phase2 = new DateTime($date_start['date_end']);
                            //                                 $t = $phase2->getTimestamp();
                            //                                     $addDay = 86400;
                            //                                     do{
                            //                                     $try=date('Y-m-d', ($t+$addDay));
                            //                                             $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                            //                                     $nextDay = date('w', ($t+$addDay));
                            //                                         $t = $t+$addDay;}
                            //                                     while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                            //                                     $phase3_start=date('Y-m-d',$t);
                            //                                 for($i=0; $i<$fordate-1; $i++){
                            //                                     $addDay = 86400;
                            //                                     $try=date('Y-m-d', ($t+$addDay));
                            //                                             $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                            //                                     $nextDay = date('w', ($t+$addDay));
                            //                                     if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                            //                                         $i--;
                            //                                     }
                            //                                     $t = $t+$addDay;
                            //                                 }
                            //                                 $phase3=date('Y-m-d',$t);

                            //         $con->myQuery("UPDATE project_phase_dates SET temp_date_end = ? WHERE project_id=? AND project_phase_id='2'",array($date_approved,$current['id']));
                            //      }

                            //  }


                        }elseif($current['modification_type']=='0'){
                            $get_start_date=$con->myQuery("SELECT id, employee_id, project_id, start_date FROM project_employee_history WHERE employee_id=".$current['requested_employee_id'] . " AND project_id=".$current['project_id']);
                            while($rows =$get_start_date->fetch(PDO::FETCH_ASSOC)):
                                if (empty($rows['removed_by'])) {

                                    $project_history_id = $rows['id'];
                                }
                            
                            endwhile;
                            
                            $con->myQuery("UPDATE projects_employees SET is_deleted=1 WHERE project_id=".($current['project_id'])." AND employee_id=".($current['requested_employee_id']));

                            if (!empty($project_history_id)) {
                                $con->myQuery("UPDATE project_employee_history SET end_date='$date_removed', removed_by='$current_employee' WHERE id=".$project_history_id);
                            }
                            $con->myQuery("UPDATE project_requests SET status_id = 2 WHERE id=?",array($current['id']));  
                        }
                    }
                    $con->commit(); 
                    $emp=$con->myQuery("SELECT COUNT(pe.id) AS id FROM projects_employees pe JOIN projects p ON p.id=pe.project_id WHERE pe.employee_id=? AND pe.is_deleted=0 AND p.project_status_id!=2",array($current['requested_employee_id']))->fetch(PDO::FETCH_ASSOC);
                    $counted=(8/$emp['id']);
                    if($counted<1){$counted=1;}
                    $con->myQuery("UPDATE projects_employees SET hours=? WHERE employee_id=?",array($counted,$current['requested_employee_id']));
                     Alert("Project Employee Request approved succesfully.","success");
                        break;
                    case 'reject':
                    $current1=$con->myQuery("SELECT id,employee_id,first_approver_date,second_approver_date,third_approver_date,first_approver_id,second_approver_id,third_approver_id,modification_type,project_id,requested_employee_id FROM  project_requests WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                        //var_dump($supervisor);
                            if($current1['modification_type']=='0'){
                                $mod_type="Remove";
                            }
                            elseif($current1['modification_type']=='1'){
                                $mod_type="Add";
                            }
                        $required_fieds=array(
                        "reason"=>"Enter Reason for rejection. <br/>"
                        );
                        $page='project_employee_approval.php';
                        if(validate($required_fieds)){
                            $con->myQuery("UPDATE project_requests SET status_id = 4, reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                            $supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                            $employees=getEmpDetails($current['employee_id']);
                             $requested_emp=getEmpDetails($current['requested_employee_id']);
                            $email_settings=getEmailSettings();

                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Rejected {$employees['first_name']} {$employees['last_name']}'s {$mod_type} project employee request. Employee Name: {$requested_emp['first_name']} {$requested_emp['last_name']}. The reason given is '{$inputs['reason']}'. {$audit_message}");
                            //var_dump($supervisor);
                            if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                $header="{$mod_type} Project Employee Request Rejected";
                                $message="Hi {$employees['first_name']},<br/> Your request has been rejected by , {$supervisor['first_name']} {$supervisor['last_name']}. Employee Name: {$requested_emp['first_name']} {$requested_emp['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);
                                // var_dump($email_settings);
                                 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"{$mod_type} Project Employee Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
                           }
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
