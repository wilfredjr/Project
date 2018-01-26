<?php
require_once("support/config.php");
 if(!isLoggedIn()){
 	toLogin();
 	die();
 }

// var_dump($_POST);
// die;

if(empty($_POST['type'])){
	Modal("Invalid Record Selected");
	redirect("index.php");
	die;
}
else{
	if(!in_array($_POST['type'],array('pre_overtime','overtime','official_business','adjustment','leave','shift','offset',"allowance","ot_adjustment","task_management_approval","task_completion_submit","task_completion_approval","project_application_approval","project_development_approval"))){
		Modal("Invalid Record Selected");
		redirect("index.php");
		die;
	}
}
$startTimeStamp="";
$endTimeStamp="";


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
$approver_id=$_SESSION[WEBAPP]['user']['employee_id'];
$inputs=$_POST;
$required_fieds=array();
$page='index.php';
switch ($inputs['type']) {
	case 'offset':
		$page="offset_approval.php";
		break;
	case 'pre_overtime':
		$page="overtime_approval.php";
		break;
	case 'overtime':
		$page="overtime_approval.php";
		break;
	case 'leave':
		$page="leave_approval.php";
		break;
	case 'adjustment':
		$page="adjustments_approval.php";
		break;
	case 'official_business':
		$table="employees_ob";
		$page="ob_approval.php";
		break;
	case 'shift':
		$table="employees_change_shift";
		$page="shift_approval.php";
		break;
	case 'allowance':
		$table="employees_allowances";
		$page="allowance_approval.php";
		break;
	case 'ot_adjustment':
		$page="ot_adjustments_approval.php";
		break;
    case 'task_management_approval':
        $page="task_management_approval.php";
        break;
    case 'task_completion_submit':
        $page="my_tasks.php";
        break;
    case 'task_completion_approval':
        $page="task_completion_approval.php";
        break;
    case 'project_application_approval':
        $page="project_application_approval.php";
        break;
    case 'project_development_approval':
        $page="project_development_approval.php";
        break;
	default:
		redirect("index.php");
		break;

}

if(empty($_POST['id'])){
	Modal("Invalid Record Selected");
	redirect($page);
	die;
}
else{
	try {
		switch ($inputs['type']) {
			case 'ot_adjustment':

                $audit_details=$con->myQuery("SELECT employee_name,ot_date,orig_time_in,orig_time_out,adj_time_in,adj_time_out FROM vw_employees_ot_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                $current=$con->myQuery("SELECT id,ot_date,adj_time_in,adj_time_out,employees_ot_id,adj_no_hours,employees_id,request_status_id,approval_step_id FROM  employees_ot_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";

                switch ($inputs['action']) {
                    case 'approve':
                            /*
                            Get Next step if exists if empty set status to approved 2
                             */

                            $next_step=getNextStep($current['approval_step_id'], $current['id'], 'ot_adjustment');
                            if (empty($next_step)) {
                                $status=2;
                                try {
                                        $con->beginTransaction();
                                        $con->myQuery("UPDATE employees_ot_adjustments SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                        $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                        $con->myQuery("UPDATE employees_ot SET time_from=:adj_time_in,time_to=:adj_time_out,no_hours=:adj_no_hours WHERE id=:employees_ot_id",array("adj_time_in"=>$current['adj_time_in'],"adj_time_out"=>$current['adj_time_out'],"employees_ot_id"=>$current['employees_ot_id'],"adj_no_hours"=>$current['adj_no_hours']));
                                        // die;
                                        $con->commit();

                                    } catch (Exception $e) {
                                        $con->rollback();
                                        Alert("Save Failed.","danger");
                                        redirect("ot_adjustments_approval.php");
                                        die;
                                    }
                            } else {
                                $con->myQuery("UPDATE employees_ot_adjustments SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                 $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                            }

                            $employees=getEmpDetails($current['employees_id']);
                            $email_settings=getEmailSettings();
                            //var_dump($supervisor);
                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Approved {$employees['first_name']} {$employees['last_name']}'s adjustment request. {$audit_message}");
                            if (empty($next_step)) {
                                /*
                                Notify only the sender
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $header="Overtime Adjustment Request has been Approved";
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            } else {
                                /*
                                Email next set of approvers
                                 */
                                $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                $header="New Overtime Adjustment Request For Your Approval";
                                /*
                                Modify message to be more generic and allow to be sent to multiple people.
                                 */
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
                                /*
                                Notify request has been approved
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $header="Overtime Adjustment Request has been Approved";
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            }
                            Alert("Overtime Adjustment Request approved succesfully.","success");
                        break;
                    case 'reject':
                        $required_fieds=array(
                        "reason"=>"Enter Reason for rejection. <br/>"
                        );
                        if(validate($required_fieds)){
                            $con->myQuery("UPDATE employees_ot_adjustments SET request_status_id = 4,reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                             $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                            $supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                            $employees=getEmpDetails($current['employees_id']);
                            $email_settings=getEmailSettings();

                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Rejected {$employees['first_name']} {$employees['last_name']}'s overtime adjustment request. The reason given is '{$inputs['reason']}'. {$audit_message}");
                            //var_dump($supervisor);
                            if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                $header="Overtime Adjustment Request Rejected";
                                $message="Hi {$employees['first_name']},<br/> Your request has been rejected by , {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);
                                // var_dump($email_settings);
                                 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Claim Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
                            }
                        }
                        break;
                }

                // switch ($current['status']) {
                //     case 'Supervisor Approval':
                //         switch ($inputs['action']) {
                //             case 'approve':
                //                     $con->myQuery("UPDATE employees_ot_adjustments SET status ='Final Approver Approval',reason='',supervisor_date_action=NOW() WHERE id=?",array($inputs['id']));

                //                     $supervisor=getEmpDetails($current['supervisor_id']);
                //                     $final_approver=getEmpDetails($current['final_approver_id']);
                //                     $employees=getEmpDetails($current['employees_id']);
                //                     $email_settings=getEmailSettings();
                //                     //var_dump($supervisor);
                //                     insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Approved {$employees['first_name']} {$employees['last_name']}'s Overtime attendance adjustment request. {$audit_message}");

                //                     if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                //                         $header="Overtime Attendance Adjustment Request Approved by Supervisor";
                //                         $message="Hi {$employees['first_name']},<br/> Your request has been approved by your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}. For more details please login to the Secret 6 HRIS.";
                //                         $message=email_template($header,$message);

                //                         emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($employees['private_email'],$employees['work_email'])),"Overtime Attendance Adjustment Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);

                //                         if(!empty($final_approver['private_email']) || !empty($final_approver['work_email'])){

                //                         $header="New Overtime Attendance Adjustment Request For Your Approval";
                //                         $message="Hi {$final_approver['first_name']},<br/> You have a new Overtime attendance adjustment request from {$employees['first_name']} {$employees['last_name']}. For more details please login to the Secret 6 HRIS.";
                //                         $message=email_template($header,$message);

                //                         emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($final_approver['private_email'],$final_approver['work_email'])),"Overtime Attendance Adjustment Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                //                         }
                //                     }
                //                 break;
                //             case 'reject':
                //             $required_fieds=array(
                //                 "reason"=>"Enter Reason for rejection. <br/>"
                //                 );
                //                 if(validate($required_fieds)){
                //                     $con->myQuery("UPDATE employees_ot_adjustments SET status ='Rejected (Supervisor)',reason=?,supervisor_date_action=NOW() WHERE id=?",array($inputs['reason'],$inputs['id']));

                //                     $supervisor=getEmpDetails($current['supervisor_id']);
                //                     $employees=getEmpDetails($current['employees_id']);
                //                     $email_settings=getEmailSettings();

                //                     insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Rejected {$employees['first_name']} {$employees['last_name']}'s Overtime attendance adjustment request. The reason given is '{$inputs['reason']}'. {$audit_message}");
                //                     //var_dump($supervisor);
                //                     if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
                //                         $header="Overtime Attendance Adjustment Request Rejected by Supervisor";
                //                         $message="Hi {$employees['first_name']},<br/> Your request has been rejected by your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
                //                         $message=email_template($header,$message);
                //                         // var_dump($email_settings);
                //                          //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                //                         emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($employees['private_email'],$employees['work_email'])),"Overtime Attendance Adjustment Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
                //                     }
                //                 }
                //                 break;
                //         }
                //         break;
                //     case 'Final Approver Approval':
                //         switch ($inputs['action']) {
                //             case 'approve':
                //                     try {
                //                         $con->beginTransaction();
                //                             $con->myQuery("UPDATE employees_ot_adjustments SET status ='Approved',reason='',final_approver_date_action=NOW() WHERE id=?",array($inputs['id']));
                //                             $current=$con->myQuery("SELECT ot_date,adj_time_in,adj_time_out,employees_ot_id,adj_no_hours,supervisor_id,employees_id,final_approver_id FROM employees_ot_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                //                                 $con->myQuery("UPDATE employees_ot SET time_from=:adj_time_in,time_to=:adj_time_out,no_hours=:adj_no_hours WHERE id=:employees_ot_id",array("adj_time_in"=>$current['adj_time_in'],"adj_time_out"=>$current['adj_time_out'],"employees_ot_id"=>$current['employees_ot_id'],"adj_no_hours"=>$current['adj_no_hours']));

                //                             //die;
                //                             // var_dump($current);
                //                             //
                //                         // die('ere');
                //                         $con->commit();

                //                     } catch (Exception $e) {
                //                         $con->rollback();
                //                         Alert("Save Failed.","danger");
                //                         redirect("adjustments_approval.php");
                //                         die;
                //                     }
                //                     // var_dump($current);
                //                     $supervisor=getEmpDetails($current['supervisor_id']);
                //                     $employees=getEmpDetails($current['employees_id']);
                //                     $final_approver=getEmpDetails($current['final_approver_id']);
                //                     $email_settings=getEmailSettings();

                //                     insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s Overtime attendance adjustment request. {$audit_message}");
                //                     //var_dump($supervisor);
                //                     if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                //                         $header="Overtime Attendance Adjustment Request has been Approved";
                //                         $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                //                         $message=email_template($header,$message);

                //                         emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($employees['private_email'],$employees['work_email'])),"Overtime Attendance Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                //                     }

                //                 break;
                //             case 'reject':
                //             $required_fieds=array(
                //                 "reason"=>"Enter Reason for rejection. <br/>"
                //                 );
                //                 if(validate($required_fieds)){
                //                     $con->myQuery("UPDATE employees_ot_adjustments SET status ='Rejected (Final Approver)',reason=?,final_approver_date_action=NOW() WHERE id=?",array($inputs['reason'],$inputs['id']));

                //                     $supervisor=getEmpDetails($current['supervisor_id']);
                //                     $employees=getEmpDetails($current['employees_id']);
                //                     $final_approver=getEmpDetails($current['final_approver_id']);
                //                     $email_settings=getEmailSettings();

                //                     insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Rejected {$employees['first_name']} {$employees['last_name']}'s Overtime attendance adjustment request. The reason given is '{$inputs['reason']}'. {$audit_message}");

                //                     if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
                //                         $header="Overtime Attendance Adjustment Request Rejected by Final Approver";
                //                         $message="Hi {$employees['first_name']},<br/> Your request has been rejected by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. The reason given is '{$inputs['reason']}'.  For more details please login to the Secret 6 HRIS.";
                //                         $message=email_template($header,$message);

                //                         emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($employees['private_email'],$employees['work_email'])),"Overtime Attendance Adjustment Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
                //                     }
                //                 }
                //                 break;
                //         }
                //         break;
                // }
                break;
	           #OFFSET
			case 'offset':
				$audit_details=$con->myQuery("SELECT employees_name,request_type,start_datetime,end_datetime,step_name,status FROM vw_employees_offset WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
				$current=$con->myQuery("SELECT id,request_status_id,approval_step_id,employees_id,no_hours FROM employees_offset_request WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
						switch ($inputs['action'])
						{
					       #APPROVED
							case 'approve':
              /*
              Get Next step if exists if empty set status to approved 2
               */
               $employees=getEmpDetails($current['employees_id']);
              $next_step=getNextStep($current['approval_step_id'], $current['id'], 'offset');
              if (empty($next_step)) {
                  $status=2;
                  try {
								$con->myQuery("UPDATE employees_offset_request SET request_status_id ={$status} WHERE id=?",array($inputs['id']));
                                  $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
								Alert("Approved Offset!","success");

              } catch (Exception $e) {
                  $con->rollback();
                  Alert("Save Failed.","danger");
                  redirect("offset_approval.php");
                  die;
              }
      }
      else {
          $con->myQuery("UPDATE employees_offset_request SET approval_step_id=? WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
            $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
      }

								$employees=getEmpDetails($current['employees_id']);
								$email_settings=getEmailSettings();

								insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"({$audit_details['step_name']}) Approved {$employees['first_name']} {$employees['last_name']}'s offset ({$audit_details['request_type']}) request. Status of Request: {$audit_details['status']}");
								if (empty($next_step)) {
									$header="Offset Request Approved!";
                  $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
									$message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['first_name']} {$final_approver['last_name']}. For more details please login to the Secret 6 HRIS.";
									$message=email_template($header,$message);

									PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Offset Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);

                }
             else {
               /*
               Email next set of approvers
                */
               $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
               $header="New Offset Request For Your Approval";
               /*
               Modify message to be more generic and allow to be sent to multiple people.
                */
               $message="Good day,<br/> You have a new offset request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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
               PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Offset Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
               /*
               Notify request has been approved
                */
               if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                   $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                   $header="Offset Request has been Approved";
                   $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                   $message=email_template($header,$message);

                   PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Offset Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
               }
									}
                  		Alert("Offset Request Succesfully Approved","success");

								break;

							case 'reject':
              $required_fieds=array(
                  "reason"=>"Enter Reason for rejection. <br/>"
                  );
                            #REJECTED

								$con->myQuery("UPDATE employees_offset_request SET request_status_id =4,reject_reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                                  $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

								$supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
									$employees=getEmpDetails($current['employees_id']);
									$email_settings=getEmailSettings();
									//var_dump($supervisor);

									insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"({$audit_details['step_name']}) Rejected {$employees['first_name']} {$employees['last_name']}'s offset ({$audit_details['request_type']}) request. The reason given is '{$inputs['reason']}'");

									if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
										$header="Offset Request Rejected by Supervisor";
										$message="Hi {$employees['first_name']},<br/> Your request has been rejected by {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
										$message=email_template($header,$message);

										PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Offset Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
									}
                  Alert("Offset Request Has Been Rejected","success");
								break;
					#CANCELLED
							case 'cancel':
								echo confirm("Are you sure to cancel?");
								break;
						}

				break;
			case 'overtime':
				$audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
        $current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  employees_ot WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                switch ($inputs['action']) {
                    case 'approve':
                            /*
                            Get Next step if exists if empty set status to approved 2
                             */
                            $next_step=getNextStep($current['approval_step_id'], $current['id'], 'overtime');
                            if (empty($next_step)) {
                                $status=2;
                                try {
                                        $con->beginTransaction();
                                        $con->myQuery("UPDATE employees_ot SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                        // die;
                                         $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                        $con->commit();

                                    } catch (Exception $e) {
                                        $con->rollback();
                                        Alert("Save Failed.","danger");
                                        redirect("adjustments_approval.php");
                                        die;
                                    }
                            } else {
                                $con->myQuery("UPDATE employees_ot SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                 $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                            }

                            $employees=getEmpDetails($current['employees_id']);
                            $email_settings=getEmailSettings();
                            //var_dump($supervisor);
                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Approved {$employees['first_name']} {$employees['last_name']}'s overtime claim request. {$audit_message}");
                            if (empty($next_step)) {
                                /*
                                Notify only the sender
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $header="Overtime Claim Request has been Approved";
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Claim Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            } else {
                                /*
                                Email next set of approvers
                                 */
                                $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                $header="New Overtime Claim Request For Your Approval";
                                /*
                                Modify message to be more generic and allow to be sent to multiple people.
                                 */
                                $message="Good day,<br/> You have a new overtime claim request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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
                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Overtime Claim Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                /*
                                Notify request has been approved
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $header="Overtime Claim Request has been Approved";
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Claim Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            }
                            Alert("Overtime Claim Request approved succesfully.","success");
                        break;
                    case 'reject':
                        $required_fieds=array(
                        "reason"=>"Enter Reason for rejection. <br/>"
                        );
                        if(validate($required_fieds)){
                            $con->myQuery("UPDATE employees_ot SET request_status_id = 4,reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                            $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                            $supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                            $employees=getEmpDetails($current['employees_id']);
                            $email_settings=getEmailSettings();

                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Rejected {$employees['first_name']} {$employees['last_name']}'s overtime claim request. The reason given is '{$inputs['reason']}'. {$audit_message}");
                            //var_dump($supervisor);
                            if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                $header="Overtime Claim Request Rejected";
                                $message="Hi {$employees['first_name']},<br/> Your request has been rejected by , {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);
                                // var_dump($email_settings);
                                 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Claim Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
                            }
                        }
                        break;
                }
				break;
			case 'leave':
                #LEAVE
				$audit_details=$con->myQuery("SELECT employee_name,leave_type,date_start,date_end,reason,status,step_name,request_status_id FROM vw_employees_leave WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
				$current=$con->myQuery("SELECT id,request_status_id,approval_step_id,employee_id,comment FROM employees_leaves WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
				if(empty($audit_details['leave_type']))
				{
					$audit_details['leave_type']="Leave Without Pay";
				}
				// die;
						switch ($inputs['action'])
						{
							case 'approve':
                                  /*
                                  Get Next step if exists if empty set status to approved 2
                                   */
                                    $employees=getEmpDetails($current['employee_id']);
                                  $next_step=getNextStep($current['approval_step_id'], $current['id'], 'leave');
                                     if (empty($next_step)) {
                                        $status=2;
                                        $con->beginTransaction();
                                        try {
                                                $con->myQuery("UPDATE employees_leaves SET request_status_id = {$status} WHERE id=?",array($inputs['id']));
                                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                               $hd="";
                                               if(!empty($current['comment'] || $current['comment'] !== ""))
                                               {
                                                   $hd=$current['comment'];
                                               }
                                               $employee_leave=$con->myQuery("SELECT id,employee_id,balance_per_year FROM employees_available_leaves WHERE leave_id=? AND employee_id=? AND is_cancelled=0 AND is_deleted=0 ",array($inputs['leave_id'],$inputs['emp_id']))->fetch(PDO::FETCH_ASSOC);  

                                               if(!empty($employee_leave))
                                               {
                                                   #WITH PAY
                                                   $leave_balance=$employee_leave['balance_per_year'];
                                                   if($hd !== "")
                                                   {
                                                       $less=0.5;
                                                       $leave_deduct=$leave_balance-$less;
                                                       $remark='L-HD-'.$hd;
                                                   }else
                                                   {
                                                       $remark='L';
                                                       $leave_balance=$employee_leave['balance_per_year'];
                                                       $leave_deduct=0;
                                                       // var_dump($inputs['date_start'], $inputs['date_end']);
                                                       $datetime1 = new DateTime($inputs['date_start']. " 00:00:00");
                                                       $datetime2 = new DateTime($inputs['date_end']. " 00:00:00");
                                                       $woweekends = 0;
                                                       $datetime2->modify("+1 day");
                                                       if($datetime1==$datetime2)
                                                       {
                                                           $woweekends=1;
                                                       }else
                                                       {
                                                           $interval = $datetime1->diff($datetime2);
                                                           $datetime2->modify("-1 day");
                                                           var_dump($interval);
                                                           for($i=0; $i<=$interval->d; $i++){
                                                               $modif = $datetime1->modify('+1 day');
                                                               $weekday = $datetime1->format('w');

                                                               if($weekday != 0 && $weekday != 1)
                                                               { # 0=Sunday and 6=Saturday
                                                                   $woweekends+=1;  
                                                               }
                                                           }
                                                       }
                                                       $leave_deduct=$leave_balance-$woweekends;
                                                   }
                                                   
                                                   if($leave_deduct>=0) 
                                                   {
                                                       $con->myQuery("UPDATE employees_available_leaves SET balance_per_year=? WHERE leave_id=? AND employee_id=? AND is_cancelled=0",array($leave_deduct,$inputs['leave_id'],$inputs['emp_id']));
                                                   }else
                                                   {
                                                       #WITHOUT PAY
                                                       $remark='A';
                                                   }
                
                                               }
                                               else
                                               {
                                                   #WITHOUT PAY
                                                   $remark='A';
                                               }
                                               $con->commit();
                        						Alert("Approved Leave!","success");
                                      } catch (Exception $e) {
                                              $con->rollback();
                                              Alert("Save Failed.","danger");
                                              redirect("leave_approval.php");
                                              die;
                                          }
                                      }
                                      else{
                                        $con->myQuery("UPDATE employees_leaves SET approval_step_id=? WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                        $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                      }

      								$employees=getEmpDetails($current['employee_id']);
      								$email_settings=getEmailSettings();

      								insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"({$audit_details['step_name']}) Approved {$employees['first_name']} {$employees['last_name']}'s ({$audit_details['leave_type']}) request. Status of Request: Approved");
      								if (empty($next_step)) {
      									$header="Leave Request Approved!";
                                         $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
      									$message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['first_name']} {$final_approver['last_name']}. For more details please login to the Secret 6 HRIS.";
      									$message=email_template($header,$message);

      									PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Leave Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);

                                    } else {
                                     /*
                                     Email next set of approvers
                                      */
                                     $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                     $header="New Leave Request For Your Approval";
                                     /*
                                     Modify message to be more generic and allow to be sent to multiple people.
                                      */
                                     $message="Good day,<br/> You have a new leave request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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
                                     PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Leave Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                     /*
                                     Notify request has been approved
                                      */
                                     if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                         $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                         $header="Leave Request has been Approved";
                                         $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                         $message=email_template($header,$message);

                                         PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Leave Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                     }
                      									}
                                        		Alert("Leave Request Succesfully Approved","success");

      								break;
      							case 'reject':
                                    $required_fieds=array(
                                        "reason"=>"Enter Reason for rejection. <br/>"
                                        );
                                                  #REJECTED

      								$con->myQuery("UPDATE employees_leaves SET request_status_id =4,reject_reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

      								$supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
      									$employees=getEmpDetails($current['employee_id']);
      									$email_settings=getEmailSettings();
      									//var_dump($supervisor);

      									insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"({$audit_details['step_name']}) Rejected {$employees['first_name']} {$employees['last_name']}'s ({$audit_details['leave_type']}) request. The reason given is '{$inputs['reason']}'");

      									if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
      										$header="Leave Request Rejected by Supervisor";
      										$message="Hi {$employees['first_name']},<br/> Your request has been rejected by {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
      										$message=email_template($header,$message);

      										PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Leave Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
      									}
                                        Alert("Leave Request Has Been Rejected","success");
                      								break;
                      					#CANCELLED
                      							case 'cancel':
                      								echo confirm("Are you sure to cancel?");
                      								break;
                      						}

                      				break;
			case 'adjustment':

				$audit_details=$con->myQuery("SELECT employee_name,adjustment_reason,adj_date,orig_in_time,orig_out_time,adj_in_time,adj_out_time FROM vw_employees_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
				$current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  employees_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
				if($audit_details['orig_in_time']=="00:00:00"){
					$audit_message="Add {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']}";
				}
				else{

					$audit_message="From {$audit_details['orig_in_time']}-{$audit_details['orig_out_time']} to {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']}. Adjustment Reason:{$audit_details['adjustment_reason']}";
				}
                switch ($inputs['action']) {
                    case 'approve':
                            /*
                            Get Next step if exists if empty set status to approved 2
                             */
                            $next_step=getNextStep($current['approval_step_id'], $current['id'], 'adjustment');
                            if (empty($next_step)) {
                                $status=2;
                                try {
                                        $con->beginTransaction();
                                        $con->myQuery("UPDATE employees_adjustments SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                        $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                        $current=$con->myQuery("SELECT adj_date,adj_in_time,adj_out_time,attendance_id,employees_id,adjustment_reason FROM employees_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                                        if($audit_details['orig_in_time']=="00:00:00" || $audit_details['orig_in_time']==NULL){
                                            $con->myQuery("INSERT INTO attendance (in_time,out_time,employees_id,note) VALUES(:adj_in_time,:adj_out_time,:employees_id,:note)",array("adj_in_time"=>$current['adj_in_time'],"adj_out_time"=>$current['adj_out_time'],"employees_id"=>$current['employees_id'],"note"=>$current['adjustment_reason']));
                                        }
                                        else{
                                            $con->myQuery("UPDATE attendance SET in_time=:adj_in_time,out_time=:adj_out_time,note=:note WHERE id=:attendance_id",array("adj_in_time"=>$current['adj_in_time'],"adj_out_time"=>$current['adj_out_time'],"attendance_id"=>$current['attendance_id'],"note"=>$current['adjustment_reason']));
                                        }

                                        $get_payroll_group_id=$con->myQuery("SELECT payroll_group_id FROM employees WHERE is_deleted =0 AND id=?",array($current['employees_id']))->fetch(PDO::FETCH_ASSOC);

                                        $date = new DateTime();
                                        $date_created=date_format($date, 'Y-m-d');

                                        $adj_out_time           = new DateTime($current['adj_out_time']);
                                        $adj_in_time            = new DateTime($current['adj_in_time']);
                                        $no_of_work_hours       = $adj_out_time->diff($adj_in_time);
                                        $no_of_work_hours       = $no_of_work_hours->h;

                                        $days_per_month = get_salary_settings($get_payroll_group_id['payroll_group_id'])['days_per_month'];
                                        $basic_salary   = get_basic_salary($current['employees_id'])['basic_salary'];
                                        $dailyrate      = ($basic_salary / $days_per_month);
                                        $hourlyrate     = ($dailyrate / 8);
                                        $pa_amount      = ($hourlyrate * $no_of_work_hours);

                                        $param=array(
                                            'emp_id'        =>$current['employees_id'],
                                            'date_created'  =>$date_created,
                                            'date_occur'    =>$current['adj_date'],
                                            'amount'        =>number_format($pa_amount,2),
                                            'reason'        =>'Attendance Adjusment',
                                            'status'        =>'0',
                                            'a_type'        =>'1'
                                        );

                                        $con->myQuery("INSERT INTO payroll_adjustments (employee_id,date_created,date_occur,amount,reason,status,adjustment_type) VALUES (:emp_id,:date_created,:date_occur,:amount,:reason,:status,:a_type)",$param);

                                            //die;
                                        // die;
                                        $con->commit();

                                    } catch (Exception $e) {
                                        $con->rollback();
                                        Alert("Save Failed.","danger");
                                        redirect("adjustments_approval.php");
                                        die;
                                    }
                            } else {
                                $con->myQuery("UPDATE employees_adjustments SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                            }

                            $employees=getEmpDetails($current['employees_id']);
                            $email_settings=getEmailSettings();
                            //var_dump($supervisor);
                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Approved {$employees['first_name']} {$employees['last_name']}'s attendance adjustment request. {$audit_message}");
                            if (empty($next_step)) {
                                /*
                                Notify only the sender
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $header="Attendance Adjustment Request has been Approved";
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Attendance Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            } else {
                                /*
                                Email next set of approvers
                                 */
                                $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                $header="New Attendance Adjustment Request For Your Approval";
                                /*
                                Modify message to be more generic and allow to be sent to multiple people.
                                 */
                                $message="Good day,<br/> You have a new attendance adjustment request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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
                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Attendance Adjustment Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                /*
                                Notify request has been approved
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $header="Attendance Adjustment Request has been Approved";
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Attendance Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            }
                        break;
                    case 'reject':
                    $required_fieds=array(
                        "reason"=>"Enter Reason for rejection. <br/>"
                        );
                        if(validate($required_fieds)){
                            $con->myQuery("UPDATE employees_adjustments SET request_status_id = 4,reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                            $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                            $supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                            $employees=getEmpDetails($current['employees_id']);
                            $email_settings=getEmailSettings();

                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Rejected {$employees['first_name']} {$employees['last_name']}'s attendance adjustment request. The reason given is '{$inputs['reason']}'. {$audit_message}");
                            //var_dump($supervisor);
                            if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
                                $header="Attendance Adjustment Request Rejected";
                                $message="Hi {$employees['first_name']},<br/> Your request has been rejected by , {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);
                                // var_dump($email_settings);
                                 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Attendance Adjustment Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
                            }
                        }
                    break;
                    }

				break;
			case 'official_business':
				$audit_details=$con->myQuery("SELECT employee_name,destination,purpose,ob_date,time_from,time_to FROM vw_employees_ob WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

				$audit_message="Destination: {$audit_details['destination']}. Purpose: {$audit_details['purpose']} during ".date("Y-m-d",strtotime($audit_details['time_from']))." - ".date("Y-m-d",strtotime($audit_details['time_to']));

				$current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);



						switch ($inputs['action']) {



							case 'approve':
                                //  var_dump($current);
                                // die;
                                $next_step=getNextStep($current['approval_step_id'], $current['id'], 'official_business');

                                //die;
                                if (empty($next_step)) {

                                    $status=2;
                                    $con->myQuery("UPDATE {$table} SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                    $employees=getEmpDetails($current['employees_id']);
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $email_settings=getEmailSettings();
                                    //var_dump($supervisor);

                                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s official business request. {$audit_message}");

                                    // if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
                                    //     $header="Official Business Request has been Approved";
                                    //     $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    //     $message=email_template($header,$message);

                                    //     PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Official Business Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    // }
                                    // var_dump($current);
                                    // die;

                               }
                                else {





                                    // var_dump($next_step);
                                    // die;
									$con->myQuery("UPDATE {$table} SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

									$employees=getEmpDetails($current['employees_id']);
									$email_settings=getEmailSettings();

									insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Approved {$employees['first_name']} {$employees['last_name']}'s official business request. {$audit_message}");
                                }




                                if (empty($next_step)) {
                                    /*
                                    Notify only the sender
                                     */
                                    // var_dump($next_step);
                                    // die;

                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                        $header="Official Business Request has been Approved";
                                        $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Attendance Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    }
                                } else {

                                    /*
                                    Email next set of approvers
                                     */

                                    $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                    $header="New Official Business Request For Your Approval";
                                    /*
                                    Modify message to be more generic and allow to be sent to multiple people.
                                     */
                                    $message="Good day,<br/> You have a new offical business request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
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

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Official Business Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                    /*
                                    Notify request has been approved
                                     */

                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                        $supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                        $header="Official Business Request has been Approved";
                                        
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by {$supervisor['first_name']} {$supervisor['last_name']}. For more details please login to the Secret 6 HRIS.";

                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Official Business Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    }

                                }

							break;

				            case 'reject':
    							$required_fieds=array(
    								"reason"=>"Enter Reason for rejection. <br/>"
    								);
    								if(validate($required_fieds)){
    									$con->myQuery("UPDATE {$table} SET request_status_id = 4,reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                                        $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
    									$supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                        $employees=getEmpDetails($current['employees_id']);
                                        $email_settings=getEmailSettings();

    									insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Rejected {$employees['first_name']} {$employees['last_name']}'s official business request. The reason given is '{$inputs['reason']}'. {$audit_message}");
    									//var_dump($supervisor);
    									if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
    										$header="Official Business Request Rejected by Supervisor";
    										$message="Hi {$employees['first_name']},<br/> Your request has been rejected by {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
    										$message=email_template($header,$message);
    										// var_dump($email_settings);
    										 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
    										PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Official Business Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
    									}
    								}
							break;
                        //die;

						}

			break;


			case 'shift':
				$audit_details=$con->myQuery("SELECT employee_name,orig_in_time,orig_out_time,adj_in_time,adj_out_time,date_from,date_to FROM vw_employees_change_shift WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

				$audit_message="From {$audit_details['orig_in_time']}-{$audit_details['orig_out_time']} to {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']} during ".date("Y-m-d",strtotime($audit_details['date_from']))." - ".date("Y-m-d",strtotime($audit_details['date_to']));



				$current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);



						switch ($inputs['action']) {
                            case 'approve':
                                //  var_dump($current);
                                // die;
                                $next_step=getNextStep($current['approval_step_id'], $current['id'], 'shift');

                                //die;
                                if (empty($next_step)) {

                                    $status=2;
                                    $con->myQuery("UPDATE {$table} SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                    $employees=getEmpDetails($current['employees_id']);
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $email_settings=getEmailSettings();
                                    //var_dump($supervisor);

                                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s change shift request. {$audit_message}");


                                    // if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){


                                    //     $header="Change Shift Request has been Approved";
                                    //     $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    //     $message=email_template($header,$message);

                                    //     PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Change Shift Request (Approved)",$message,$email_settings['host'],$email_settings['port']);


                                    // }
                                    // var_dump($current);
                                    // die;

                               }
                                else {





                                    // var_dump($next_step);
                                    // die;
                                    $con->myQuery("UPDATE {$table} SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                    $employees=getEmpDetails($current['employees_id']);
                                    $email_settings=getEmailSettings();

                                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Approved {$employees['first_name']} {$employees['last_name']}'s change shift request. {$audit_message}");
                                }




                                if (empty($next_step)) {
                                    /*
                                    Notify only the sender
                                     */
                                    // var_dump($next_step);
                                    // die;
                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){

                                        $header="Change Shift Request has been Approved";
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Change Shift Request (Approved)",$message,$email_settings['host'],$email_settings['port']);

                                    }
                                } else {
                                    /*
                                    Email next set of approvers
                                     */
                                    $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                    $header="New Change Shift Request For Your Approval";
                                    $message="Hi {$final_approver['first_name']},<br/> You have a new change shift request from {$employees['first_name']} {$employees['last_name']}. For more details please login to the Secret 6 HRIS.";
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
                                    /*
                                    Notify request has been approved
                                     */
                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                        $supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                        $header="Change Shift Request has been Approved";
                                        
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by {$supervisor['first_name']} {$supervisor['last_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Change Shift Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    }
                                }

                            break;

							case 'reject':
							$required_fieds=array(
								"reason"=>"Enter Reason for rejection. <br/>"
								);
								if(validate($required_fieds)){
									$con->myQuery("UPDATE {$table} SET request_status_id = 4,reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
									$supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $employees=getEmpDetails($current['employees_id']);
                                    $email_settings=getEmailSettings();
									//var_dump($supervisor);
									insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Rejected {$employees['first_name']} {$employees['last_name']}'s change shift request. The reason given is '{$inputs['reason']}'. {$audit_message}");

									if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
										$header="Change Shift Request Rejected by Supervisor";
										$message="Hi {$employees['first_name']},<br/> Your request has been rejected by {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
										$message=email_template($header,$message);
										// var_dump($email_settings);
										 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
										PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Change Shift Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
									}
								}
								break;

						}




				break;

			case 'allowance':

				$audit_details=$con->myQuery("SELECT employee_name,food_allowance,transpo_allowance,request_reason,date_applied FROM vw_employees_allowances WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
				$current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  employees_allowances WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
				$audit_message="Date applied for ({$audit_details["date_applied"]}), Food allowance (".number_format($audit_details['food_allowance'],2)."). Transportation Allowance (".number_format($audit_details['transpo_allowance'], 2)."). With a reason of ({$audit_details["request_reason"]})";
                switch ($inputs['action']) {
                    case 'approve':
                            /*
                            Get Next step if exists if empty set status to approved 2
                             */
                            $next_step=getNextStep($current['approval_step_id'], $current['id'], 'allowance');

                            if (empty($next_step)) {
                                $status=2;
                                try {
                                        $con->beginTransaction();
                                        $con->myQuery("UPDATE employees_allowances SET request_status_id =2,reason='' WHERE id=?",array($inputs['id']));
                                        $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                        $con->commit();

                                    } catch (Exception $e) {
                                        $con->rollback();
                                        Alert("Save Failed.","danger");
                                        redirect("allowance_approval.php");
                                        die;
                                    }
                            } else {
                                $con->myQuery("UPDATE employees_allowances SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                            }

                            $employees=getEmpDetails($current['employees_id']);
                            $email_settings=getEmailSettings();
                            //var_dump($supervisor);

                            if (empty($next_step)) {
                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s allowance request. {$audit_message}");
                                /*
                                Notify only the sender
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $header="Allowance Request has been Approved";
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Attendance Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            } else {
                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Approved {$employees['first_name']} {$employees['last_name']}'s allowance request. {$audit_message}");
                                /*
                                Email next set of approvers
                                 */
                                $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
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
                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Attendance Adjustment Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                /*
                                Notify request has been approved
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $header="Allowance Request has been Approved";
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Allowance Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            }
                        break;
                    case 'reject':
                    $required_fieds=array(
                        "reason"=>"Enter Reason for rejection. <br/>"
                        );
                        if(validate($required_fieds)){
                            $con->myQuery("UPDATE employees_allowances SET request_status_id = 4,reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                            $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                            $supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                            $employees=getEmpDetails($current['employees_id']);
                            $email_settings=getEmailSettings();

                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Rejected {$employees['first_name']} {$employees['last_name']}'s allowance request. The reason given is '{$inputs['reason']}'. {$audit_message}");
                            //var_dump($supervisor);
                            if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
                                $header="Allowance Request Rejected";
                                $message="Hi {$employees['first_name']},<br/> Your request has been rejected by , {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);
                                // var_dump($email_settings);
                                 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Allowance Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
                            }
                        }
                        break;
                }
				break;

                case 'task_management_approval':

                $current=$con->myQuery("SELECT * FROM  project_task WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $next_phase=$current['project_phase_id']+1;
                    $prev_phase=$current['project_phase_id'];
                $date = (new DateTime())->getTimestamp();
                $date_now=date('Y-m-d',$date);
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                 switch ($inputs['action']) {
                    case 'approve':

                    $phase_stat=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$current['project_phase_id']))->fetch(PDO::FETCH_ASSOC);
                    $params1=array(
                    "employee"=>$current['employee_id'],
                    "project_id"=>$current['project_id'],
                    "phase_id"=>$current['project_phase_id'],
                    "date_start"=>$current['date_start'],
                    "date_end"=>$current['date_end'],
                    "manager_id"=>$current['manager_id'],
                    "w"=>$current['worked_done'],
                    "stats"=>$phase_stat['status_id']
                    );
                    $con->myQuery("INSERT INTO
                                project_task_list(
                                    employee_id,
                                    project_id,
                                    project_phase_id,
                                    date_start,
                                    date_end,
                                    status_id,
                                    manager_id,
                                    worked_done
                                ) VALUES(
                                    :employee,
                                    :project_id,
                                    :phase_id,
                                    DATE_FORMAT(:date_start,'%Y-%m-%d'),
                                    DATE_FORMAT(:date_end,'%Y-%m-%d'),
                                    :stats,
                                    :manager_id,
                                    :w
                                )",$params1);
                    
                    $con->myQuery("UPDATE project_task SET request_status_id = 2, date_approved=? WHERE id=?",array($date_now,$inputs['id']));
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
                        if(validate($required_fieds)){
                            $con->myQuery("UPDATE project_task SET request_status_id = 4, reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
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
                break;

                #Task Completion Submit
                case 'task_completion_submit':

                $current=$con->myQuery("SELECT * FROM  project_task_list WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $next_phase=$current['project_phase_id']+1;
                    $prev_phase=$current['project_phase_id'];
                $date = (new DateTime())->getTimestamp();
                $date_now=date('Y-m-d',$date);
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                if($current['project_phase_id']==3){
                    $team_lead=$con->myQuery("SELECT employee_id FROM projects_employees WHERE is_team_lead_dev=1 AND project_id=?",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
                }else{
                    $team_lead=$con->myQuery("SELECT employee_id FROM projects_employees WHERE is_team_lead_dev=1 AND project_id=?",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
                }
                if((($current['manager_id']==$team_lead['employee_id'])AND($current['manager_id']==$approver_id))OR($current['manager_id']==$approver_id)){
                    $phase_stat=2;
                    $date_approved=$date_now;
                    $con->myQuery("UPDATE project_task_list SET is_submitted = 1, status_id=2, date_finished=?, work_done=? WHERE id=?",array($date_now,$inputs['work_done'],$inputs['id']));
                }elseif($approver_id==$team_lead['employee_id']){
                    $team_lead="";
                    $phase_stat=1;
                    $date_approved="";
                    $con->myQuery("UPDATE project_task_list SET is_submitted = 1 WHERE id=?",array($inputs['id']));
                }else{
                    $team_lead=$team_lead['employee_id'];
                    $phase_stat=1;
                    $date_approved="";
                    $con->myQuery("UPDATE project_task_list SET is_submitted = 1 WHERE id=?",array($inputs['id']));
                }
                     
                    $params1=array(
                    "employee"=>$current['employee_id'],
                    "project_id"=>$current['project_id'],
                    "phase_id"=>$current['project_phase_id'],
                    "date_start"=>$current['date_start'],
                    "date_end"=>$current['date_end'],
                    "manager_id"=>$current['manager_id'],
                    "w"=>$current['worked_done'],
                    "w1"=>$inputs['work_done'],
                    "stats"=>$phase_stat,
                    "team_lead_id"=>$team_lead,
                    "date_approved"=>$date_approved,
                    "task_list_id"=>$current['id']
                    );
                    $con->myQuery("INSERT INTO
                                project_task_completion(
                                    employee_id,
                                    project_id,
                                    project_phase_id,
                                    date_start,
                                    date_end,
                                    date_filed,
                                    request_status_id,
                                    manager_id,
                                    work_to_do,
                                    worked_done,
                                    team_lead_id,
                                    date_approved,
                                    task_list_id
                                ) VALUES(
                                    :employee,
                                    :project_id,
                                    :phase_id,
                                    DATE_FORMAT(:date_start,'%Y-%m-%d'),
                                    DATE_FORMAT(:date_end,'%Y-%m-%d'),
                                    CURDATE(),
                                    :stats,
                                    :manager_id,
                                    :w,
                                    :w1,
                                    :team_lead_id,
                                    :date_approved,
                                    :task_list_id
                                )",$params1);
                          $task_id=$con->lastInsertId();
                          $con->myQuery("UPDATE project_task_list SET request_id = ? WHERE id=?",array($task_id,$inputs['id']));
                    if(!empty($_FILES['file']['name'])){
                    if($current['manager_id']==$approver_id){
                    try {  
                         $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
                        $con->beginTransaction();
                        $inputs['file_name']=$_FILES['file']['name'];
                        $project_id=$current['project_id'];
                        $project_phase_id=$current['project_phase_id'];
                        $con->myQuery("INSERT INTO project_files(file_name,date_modified,employee_id,project_id,project_phase_id,task_completion_id,is_approved) VALUES(:file_name,NOW(),'$employee_id','$project_id','$project_phase_id','$task_id','1')",$inputs);
                        $file_id=$con->lastInsertId();

                        $filename=$file_id.getFileExtension($_FILES['file']['name']);
                        move_uploaded_file($_FILES['file']['tmp_name'],"proj_files/".$filename);
                        $con->myQuery("UPDATE project_files SET file_location=? WHERE id=?",array($filename,$file_id));
                        $con->commit();                     
                    Alert("Request has been approved.","success");
                        } catch (Exception $e) {
                      $con->rollBack();
            //        echo "Failed: " . $e->getMessage();
                      Alert("Upload failed. Please try again.","danger");
                      redirect($page);
                      die;
                    }
                }else{
                     try {  
                         $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
                        $con->beginTransaction();
                        $inputs1['file_name']=$_FILES['file']['name'];
                        $project_id=$current['project_id'];
                        $project_phase_id=$current['project_phase_id'];
                        $con->myQuery("INSERT INTO project_files(file_name,date_modified,employee_id,project_id,project_phase_id,task_completion_id,is_approved) VALUES(:file_name,NOW(),'$employee_id','$project_id','$project_phase_id','$task_id','0')",$inputs1);
                        $file_id=$con->lastInsertId();

                        $filename=$file_id.getFileExtension($_FILES['file']['name']);
                        move_uploaded_file($_FILES['file']['tmp_name'],"proj_files/".$filename);
                        $con->myQuery("UPDATE project_files SET file_location=? WHERE id=?",array($filename,$file_id));
                        $con->commit();           
                        } catch (Exception $e) {
                      $con->rollBack();
            //        echo "Failed: " . $e->getMessage();
                      Alert("Upload failed. Please try again.","danger");
                      redirect($page);
                      die;
                    }
                    Alert("Request has been sent.","success");
                }
            }
                break;

            case 'task_completion_approval':

                $current=$con->myQuery("SELECT * FROM  project_task_completion WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $next_phase=$current['project_phase_id']+1;
                    $prev_phase=$current['project_phase_id'];
                $date = (new DateTime())->getTimestamp();
                $date_now=date('Y-m-d',$date);
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                switch ($inputs['action']) {
                    case 'approve':

                        $con->myQuery("UPDATE project_task_completion SET request_status_id = 2, date_approved=? WHERE id=?",array($date_now,$inputs['id']));
                        $con->myQuery("UPDATE project_task_list SET status_id = 2, date_finished=?, work_done=? WHERE id=?",array($current['date_filed'],$current['worked_done'],$current['task_list_id']));
                        $con->myQuery("UPDATE project_files SET is_approved = 1 WHERE task_completion_id=?",array($inputs['id']));
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
                            if(validate($required_fieds)){
                                $con->myQuery("UPDATE project_task_completion SET request_status_id = 4, reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
                                $con->myQuery("UPDATE project_files SET is_deleted = 1 WHERE task_completion_id=?",array($inputs['id']));
                                $con->myQuery("UPDATE project_task_list SET is_submitted = 0 WHERE request_id=?",array($inputs['id']));
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
                break;

                case 'project_application_approval':

                $current=$con->myQuery("SELECT * FROM  project_application WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $next_phase=$current['project_phase_id']+1;
                    $prev_phase=$current['project_phase_id'];
                $date = (new DateTime())->getTimestamp();
                $date_now=date('Y-m-d',$date);
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                switch ($inputs['action']) {
                    case 'approve':
                                    $employee=$_SESSION[WEBAPP]['user']['employee_id'];
                                    $con->beginTransaction();  
                                    $date = new DateTime(); 
                                     $date_approved=date_format($date, 'Y-m-d');
                                     $date_start=$current['date_start'];
                                        // phase1 date
                                        $phase1 = new DateTime($current['date_start']);
                                            $t = $phase1->getTimestamp();
                                            $addDay = 86400;
                                            $t=$t-$addDay;
                                            for($i=0; $i<5; $i++){
                                                $addDay = 86400;
                                                $try=date('Y-m-d', ($t+$addDay));
                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                $nextDay = date('w', ($t+$addDay));
                                                if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                                                    $i--;
                                                }
                                                $t = $t+$addDay;
                                            }
                                            // var_dump($date_start);
                                            // die;
                                            $phase1=date('Y-m-d',$t);
                                            $phase1_date=date('Y-m-d',$t);
                                            $phase1_des=$con->myQuery("SELECT designation_id FROM project_phases WHERE id=1")->fetch(PDO::FETCH_ASSOC);
                                        // phase2 date
                                            $phase1 = new DateTime( $phase1 );
                                            $t = $phase1->getTimestamp();
                                            $addDay = 86400;
                                            do{
                                                $try=date('Y-m-d', ($t+$addDay));
                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                $nextDay = date('w', ($t+$addDay));
                                                $t = $t+$addDay;}
                                                while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                                  $phase2_start=date('Y-m-d',$t);
                                            for($i=0; $i<5; $i++){
                                                $addDay = 86400;
                                                $try=date('Y-m-d', ($t+$addDay));
                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                $nextDay = date('w', ($t+$addDay));
                                                if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                                                    $i--;
                                                }
                                                $t = $t+$addDay;
                                            }
                                            $phase2=date('Y-m-d',$t);
                                            // var_dump($phase2_start,$phase2);
                                            // die;
                                             $phase2_date=date('Y-m-d',$t);
                                             $phase2_des=$con->myQuery("SELECT designation_id FROM project_phases WHERE id=2")->fetch(PDO::FETCH_ASSOC);

                                            $params=array(
                                            'proj_name'=>$current['name'],
                                            'emplo_id'=>$employee,
                                            'date_start'=>$current['date_start'],
                                            'description'=>$current['des'],
                                            'date_applied'=>$current['date_filed'],
                                            'proj_sta'=>"1",
                                            'manager'=>$current['employee_id'],
                                            'cur_phase'=>"1",
                                            'team_lead_ba'=>$current['team_lead_ba'],
                                            'team_lead_dev'=>$current['team_lead_dev']
                                            );

                                        $con->myQuery("INSERT INTO projects (name,description,employee_id,start_date,project_status_id,date_filed,manager_id,cur_phase,team_lead_ba, team_lead_dev) VALUES (:proj_name,:description,:emplo_id,:date_start,:proj_sta,:date_applied,:manager,:cur_phase,:team_lead_ba,:team_lead_dev)",$params);
                                        $project_id = $con->lastInsertId();
                                        $added_by=$current['employee_id'];
                                        $date_applied=date_format($date, 'Y-m-d');
                                        $manager_id = $current['employee_id'];
                                        $team_lead_ba = $current['team_lead_ba'];
                                        $team_lead_dev = $current['team_lead_dev'];
                                        $con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_ba,designation_id) VALUES ('$project_id','$team_lead_ba','1','2')");
                                        $con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_dev,designation_id) VALUES ('$project_id','$team_lead_dev','1','1')");
                                        $con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager) VALUES ('$project_id','$manager_id','1')");
                                        $con->myQuery("INSERT INTO projects_employees (project_id,employee_id) VALUES ('$project_id','$employee')");
                                        $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_ba) VALUES ('$project_id','$team_lead_ba','$date_applied','$added_by','1')");
                                        $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_dev) VALUES ('$project_id','$team_lead_dev','$date_applied','$added_by','1')");
                                        $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1')");

                                        $phase1_des1=$phase1_des['designation_id'];
                                        $phase2_des1=$phase2_des['designation_id'];
                                        $con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','1','$phase1_date','1','$phase1_des1','$date_start')");
                                        $con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','2','$phase2_date','3','$phase2_des1','$phase2_start')");
                                        $con->commit(); 

                                    $con->myQuery("UPDATE project_application SET request_status_id = 2, date_approved=? WHERE id=?",array($date_approved,$current['id']));
                                    $con->myQuery("UPDATE project_files SET is_approved = 1, project_id='$project_id', project_phase_id='1' WHERE project_application_id=?",array($current['id']));
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
                            if(validate($required_fieds)){
                                $con->myQuery("UPDATE project_application SET request_status_id = 4, reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
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
                break;

                case 'project_development_approval':

                $current=$con->myQuery("SELECT * FROM  project_development WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $next_phase=$current['project_phase_id']+1;
                    $prev_phase=$current['project_phase_id'];
                $date = (new DateTime())->getTimestamp();
                $date_now=date('Y-m-d',$date);
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                switch ($inputs['action']) {
                    case 'approve':
                                    $employee=$_SESSION[WEBAPP]['user']['employee_id'];
                                    $date = new DateTime(); 
                                     $date_approved=date_format($date, 'Y-m-d');
                                       $project_id=$current['project_id'];
                                       if($current['step_id']=='0'){
                                                    $con->myQuery("UPDATE project_development SET request_status_id = 2, date_approved=? WHERE id=?",array($date_approved,$current['id']));
                                                    $con->myQuery("UPDATE project_files SET is_approved = 1, project_id='$project_id', project_phase_id='1' WHERE project_application_id=?",array($current['id']));
                                            $con->beginTransaction();  
                                                $params=array(
                                                'proj_id'=>$current['project_id'],
                                                'emplo_id'=>$employee,
                                                'date_applied'=>$date_approved,
                                                'stat'=>"1",
                                                'manager'=>$current['manager_id'],
                                                'admin'=>$current['admin_id'],
                                                'hours'=>$inputs['hours'],
                                                'comment'=>$inputs['work_done'],
                                                'step_id'=>'2',
                                                'type'=>'team',
                                                'team'=>$current['team_lead_id'],
                                                'phase_request'=>$current['id']
                                                );

                                                $con->myQuery("INSERT INTO project_development (project_id, employee_id, team_lead_id, manager_id, admin_id, type, request_status_id, hours, date_filed, phase_request_id, step_id, comment) VALUES (:proj_id,:emplo_id,:team,:manager,:admin,:type,:stat,:hours,:date_applied,:phase_request, :step_id,:comment)",$params);
                                                $dev_id=$con->lastInsertId();
                                            $con->commit(); 

                                            try {
                                                 $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
                                                $con->beginTransaction();
                                                $inputs1['file_name']=$_FILES['file']['name'];
                                                $con->myQuery("INSERT INTO project_files(file_name,date_modified,employee_id,project_id,project_phase_id,project_dev_id) VALUES(:file_name,NOW(),'$employee_id','$project_id','3','$dev_id')",$inputs1);
                                                $file_id=$con->lastInsertId();

                                                $filename=$file_id.getFileExtension($_FILES['file']['name']);
                                                move_uploaded_file($_FILES['file']['tmp_name'],"proj_files/".$filename);
                                                $con->myQuery("UPDATE project_files SET file_location=? WHERE id=?",array($filename,$file_id));
                                        
                                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Uploaded ({$inputs['file_name']}) to project files.");

                                                $con->commit();
                                                } catch (Exception $e) {
                                                  $con->rollBack();
                                        //        echo "Failed: " . $e->getMessage();
                                                }
                                       }elseif($current['step_id']=='2'){
                                            $con->myQuery("UPDATE project_development SET step_id='3' WHERE id=?",array($current['id']));
                                       }elseif($current['step_id']=='3'){
                                                    $con->beginTransaction();  
                                                             // phase3 date
                                                            $daystoadd=($current['hours']/8);
                                                            if (is_float($daystoadd)){
                                                                $fordate=floor($daystoadd)+1;
                                                            }else{
                                                                $fordate=$daystoadd;
                                                            }
                                                                $phase2 = new DateTime($date_approved);
                                                            $t = $phase2->getTimestamp();
                                                                $addDay = 86400;
                                                                $t=$t-$addDay;
                                                                do{
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                    $t = $t+$addDay;}
                                                                while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                                                $phase3_start=date('Y-m-d',$t);
                                                            for($i=0; $i<$fordate-1; $i++){
                                                                $addDay = 86400;
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                                                                    $i--;
                                                                }
                                                                $t = $t+$addDay;
                                                            }
                                                            $phase3=date('Y-m-d',$t);
                                                            // var_dump($phase3_start,$phase3);
                                                            // die;
                                                            $phase3_date=date('Y-m-d',$t);
                                                            $phase3_des=$con->myQuery("SELECT designation_id FROM project_phases WHERE id=3")->fetch(PDO::FETCH_ASSOC);
                                                        // phase4 date
                                                           $phase3 = new DateTime( $phase3 );
                                                            $t = $phase3->getTimestamp();
                                                             $addDay = 86400;
                                                             do{
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                   $t = $t+$addDay;}
                                                                while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                                            $phase4_start=date('Y-m-d',$t);
                                                            for($i=0; $i<4; $i++){
                                                                $addDay = 86400;
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                                                                    $i--;
                                                                }
                                                                $t = $t+$addDay;
                                                            }
                                                            $phase4=date('Y-m-d',$t);
                                                            // var_dump($phase4_start,$phase4);
                                                            // die;
                                                             $phase4_date=date('Y-m-d',$t);
                                                             $phase4_des=$con->myQuery("SELECT designation_id FROM project_phases WHERE id=4")->fetch(PDO::FETCH_ASSOC);
                                                        // phase5 date
                                                            $phase4 = new DateTime( $phase4 );
                                                            $t = $phase4->getTimestamp();
                                                                $addDay = 86400;
                                                                do{
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                $t = $t+$addDay;}
                                                                while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                                                $phase5_start=date('Y-m-d',$t);
                                                            for($i=0; $i<4; $i++){
                                                                $addDay = 86400;
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                                                                    $i--;
                                                                }
                                                                $t = $t+$addDay;
                                                            }
                                                            $phase5=date('Y-m-d',$t);
                                                            // var_dump($phase5_start,$phase5);
                                                            // die;
                                                             $phase5_date=date('Y-m-d',$t);
                                                             $phase5_des=$con->myQuery("SELECT designation_id FROM project_phases WHERE id=5")->fetch(PDO::FETCH_ASSOC);
                                                        // phase6 date
                                                            $phase5 = new DateTime( $phase5 );
                                                            $t = $phase5->getTimestamp();
                                                                  $addDay = 86400;
                                                              do{
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                $t = $t+$addDay;}
                                                                while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                                                $phase6_start=date('Y-m-d',$t);
                                                            for($i=0; $i<1; $i++){
                                                                $addDay = 86400;
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                                                                    $i--;
                                                                }
                                                                $t = $t+$addDay;
                                                            }
                                                            $phase6=date('Y-m-d',$t);
                                                            // var_dump($phase6_start,$phase6);
                                                            // die;
                                                             $phase6_date=date('Y-m-d',$t);
                                                             $phase6_des=$con->myQuery("SELECT designation_id FROM project_phases WHERE id=6")->fetch(PDO::FETCH_ASSOC);
                                                        // phase7 date
                                                             $phase6 = new DateTime( $phase6 );
                                                            $t = $phase6->getTimestamp();
                                                                $addDay = 86400;
                                                                do{
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                $t = $t+$addDay;}
                                                                while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                                                $phase7_start=date('Y-m-d',$t);
                                                            for($i=0; $i<1; $i++){
                                                                $addDay = 86400;
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                                                                    $i--;
                                                                }
                                                                $t = $t+$addDay;
                                                            }
                                                            $phase7=date('Y-m-d',$t);
                                                            // var_dump($phase7_start,$phase7);
                                                            // die;
                                                             $phase7_date=date('Y-m-d',$t);
                                                             $phase7_des=$con->myQuery("SELECT designation_id FROM project_phases WHERE id=7")->fetch(PDO::FETCH_ASSOC);
                                                        // phase8 date
                                                            $phase7 = new DateTime( $phase7 );
                                                            $t = $phase7->getTimestamp();
                                                                $addDay = 86400;
                                                                do{
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                $t = $t+$addDay;}
                                                                while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                                                $phase8_start=date('Y-m-d',$t);
                                                            for($i=0; $i<2; $i++){
                                                                $addDay = 86400;
                                                                $try=date('Y-m-d', ($t+$addDay));
                                                                        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                                                $nextDay = date('w', ($t+$addDay));
                                                                if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                                                                    $i--;
                                                                }
                                                                $t = $t+$addDay;
                                                            }
                                                            $phase8=date('Y-m-d',$t);
                                                            // var_dump($phase8_start,$phase8);
                                                            // die;
                                                             $phase8_date=date('Y-m-d',$t);
                                                             $phase8_des=$con->myQuery("SELECT designation_id FROM project_phases WHERE id=8")->fetch(PDO::FETCH_ASSOC);
                                                        // phase8 dete
                                                             $phase3_des1=$phase3_des['designation_id'];
                                                            $phase4_des1=$phase4_des['designation_id'];
                                                            $phase5_des1=$phase5_des['designation_id'];
                                                            $phase6_des1=$phase6_des['designation_id'];
                                                            $phase7_des1=$phase7_des['designation_id'];
                                                            $phase8_des1=$phase8_des['designation_id'];
                                                            $con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','3','$phase3_date','1','$phase3_des1','$phase3_start')");   
                                                            $con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','4','$phase4_date','3','$phase4_des1','$phase4_start')");   
                                                            $con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','5','$phase5_date','3','$phase5_des1','$phase5_start')");   
                                                            $con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','6','$phase6_date','3','$phase6_des1','$phase6_start')");   
                                                            $con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','7','$phase7_date','3','$phase7_des1','$phase7_start')");   
                                                            $con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','8','$phase8_date','3','$phase8_des1','$phase8_start')");

                                                            $con->myQuery("UPDATE project_development SET request_status_id = 2, date_approved=? WHERE id=?",array($date_approved,$current['id']));
                                                            $con->myQuery("UPDATE project_files SET is_approved = 1 WHERE project_dev_id=?",array($current['id']));
                                                            $con->myQuery("UPDATE projects SET end_date = '$phase8_date', man_hours=?,cur_phase='3' WHERE id=?",array($current['hours'],$current['project_id']));

                                            $con->commit();
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
                            if(validate($required_fieds)){
                                $con->myQuery("UPDATE project_application SET request_status_id = 4, reason=? WHERE id=?",array($inputs['reason'],$inputs['id']));
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
