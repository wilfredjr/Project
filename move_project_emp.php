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
                $current=$con->myQuery("SELECT id,first_approver_date,second_approver_date,third_approver_date,first_approver_id,second_approver_id,third_approver_id,modification_type,project_id,requested_employee_id,employee_id,manager_id,designation_id,step_id,admin_id FROM  project_requests WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
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
                            if(($current['first_approver_id']==$current_employee) AND ($current['first_approver_date']=='0000-00-00'))
                            {
                                if(empty($current['second_approver_id'])){
                                     $con->myQuery("UPDATE project_requests SET first_approver_date=CURDATE(), status_id=2 WHERE id=?",array($inputs['id'] ));
                                        if(($current['modification_type'])=='1'){
                                            $con->myQuery("INSERT INTO projects_employees(project_id,employee_id,designation_id) VALUES(:project_id,:requested_employee_id,:designation_id)",array("project_id"=>$current['project_id'],"requested_employee_id"=>$current['requested_employee_id'],"designation_id"=>$current['designation_id']));

                                            $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by) VALUES (:project_id,:employee_id,:start_date,:added_by_id)",array("project_id"=>$current['project_id'],"employee_id"=>$current['requested_employee_id'],"start_date"=>$date_removed,"added_by_id"=>$current_employee));
                                        }
                                        else
                                        {
                                            $con->myQuery("DELETE FROM projects_employees WHERE project_id=:project_id and employee_id=:requested_employee_id",array("project_id"=>$current['project_id'],"requested_employee_id"=>$current['requested_employee_id']));
                                            
                                            $get_start_date=$con->myQuery("SELECT id, employee_id, project_id, start_date FROM project_employee_history WHERE employee_id=".$current['requested_employee_id']. " AND project_id=".$current['project_id']);

                                            while($rows =$get_start_date->fetch(PDO::FETCH_ASSOC)):
                                                if (empty($rows['removed_by'])) {

                                                    $project_history_id = $rows['id'];
                                                }
                                            
                                            endwhile;
                                            if (!empty($project_history_id)) {

                    
                                                $con->myQuery("UPDATE project_employee_history SET end_date='$date_removed', removed_by='$current_employee' WHERE id=".$project_history_id);
                                            }
                                        }
                                        // die;
                                }
                                else{
                                    $con->myQuery("UPDATE project_requests SET first_approver_date=CURDATE() WHERE id=?",array($inputs['id'] ));
                                }
                            }
                            elseif(($current['second_approver_id']==$current_employee) AND ($current['second_approver_date']=='0000-00-00'))
                            {
                                if(empty($current['third_approver_id'])){
                                     $con->myQuery("UPDATE project_requests SET second_approver_date=CURDATE(),status_id=2 WHERE id=?",array($inputs['id'] ));
                                       if($current['modification_type']=='1'){
                                            $con->myQuery("INSERT INTO projects_employees(project_id,employee_id) VALUES(:project_id,:requested_employee_id)",array("project_id"=>$current['project_id'],"requested_employee_id"=>$current['requested_employee_id']));
                                            $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by) VALUES (:project_id,:employee_id,:start_date,:added_by_id)",array("project_id"=>$current['project_id'],"employee_id"=>$current['requested_employee_id'],"start_date"=>$date_removed,"added_by_id"=>$current_employee));    
                                        }
                                        else
                                        {
                                            $con->myQuery("DELETE FROM projects_employees WHERE project_id=:project_id and employee_id=:requested_employee_id",array("project_id"=>$current['project_id'],"requested_employee_id"=>$current['requested_employee_id']));
                                            $get_start_date=$con->myQuery("SELECT id, employee_id, project_id, start_date FROM project_employee_history WHERE employee_id=".$current['requested_employee_id']. " AND project_id=".$current['project_id']);

                                            while($rows =$get_start_date->fetch(PDO::FETCH_ASSOC)):
                                                if (empty($rows['removed_by'])) {

                                                    $project_history_id = $rows['id'];
                                                }
                                            
                                            endwhile;
                                            if (!empty($project_history_id)) {

                    
                                                $con->myQuery("UPDATE project_employee_history SET end_date='$date_removed', removed_by='$current_employee' WHERE id=".$project_history_id);
                                            }
                                        }
                                }
                                else{
                                    $con->myQuery("UPDATE project_requests SET second_approver_date=CURDATE() WHERE id=?",array($inputs['id'] ));
                                }
                            }
                             elseif(($current['third_approver_id']==$current_employee) AND ($current['third_approver_date']=='0000-00-00'))
                            {
                                     $con->myQuery("UPDATE project_requests SET third_approver_date=CURDATE(),status_id=2 WHERE id=?",array($inputs['id'] ));
                                       if($current['modification_type']=='1'){
                                            $con->myQuery("INSERT INTO projects_employees(project_id,employee_id) VALUES(:project_id,:requested_employee_id)",array("project_id"=>$current['project_id'],"requested_employee_id"=>$current['requested_employee_id']));
                                            $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by) VALUES (:project_id,:employee_id,:start_date,:added_by_id)",array("project_id"=>$current['project_id'],"employee_id"=>$current['requested_employee_id'],"start_date"=>$date_removed,"added_by_id"=>$current_employee));
                                        }
                                        else
                                        {
                                            $con->myQuery("DELETE FROM projects_employees WHERE project_id=:project_id AND employee_id=:requested_employee_id",array("project_id"=>$current['project_id'],"requested_employee_id"=>$current['requested_employee_id']));
                                            $get_start_date=$con->myQuery("SELECT id, employee_id, project_id, start_date FROM project_employee_history WHERE employee_id=".$current['requested_employee_id']. " AND project_id=".$current['project_id']);

                                            while($rows =$get_start_date->fetch(PDO::FETCH_ASSOC)):
                                                if (empty($rows['removed_by'])) {

                                                    $project_history_id = $rows['id'];
                                                }
                                            
                                            endwhile;
                                            if (!empty($project_history_id)) {

                    
                                                $con->myQuery("UPDATE project_employee_history SET end_date='$date_removed', removed_by='$current_employee' WHERE id=".$project_history_id);
                                            }
                                        }
                            }

                            $employees=getEmpDetails($current['employee_id']);
                        $employee_requested=getEmpDetails($current['requested_employee_id']);
                        $email_settings=getEmailSettings();
                         $current1=$con->myQuery("SELECT id,employee_id,first_approver_date,second_approver_date,third_approver_date,first_approver_id,second_approver_id,third_approver_id,modification_type,project_id,requested_employee_id FROM  project_requests WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                         $manager=getEmpDetails($current['manager_id']);
                        //var_dump($supervisor);
                            if($current1['modification_type']=='0'){
                                $mod_type="Remove";
                            }
                            elseif($current1['modification_type']=='1'){
                                $mod_type="Add";
                            }
                            if(!empty($current1['second_approver_id']) AND ($current1['second_approver_date']=='0000-00-00')){
                                $status_now="For Approval (Second Approver)";
                                $next_step=$current1['second_approver_id'];
                            }
                            elseif(!empty($current1['third_approver_id']) AND ($current1['third_approver_date']=='0000-00-00')){
                                $status_now="For Approval (Third Approver)";
                                $next_step=$current1['third_approver_id'];
                            }
                            else{$status_now="Approved";
                                $next_step="";}
                        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Approved {$employees['first_name']} {$employees['last_name']}'s {$mod_type} employee request. Employee Name: {$employee_requested['first_name']} {$employee_requested['last_name']}. Status: {$status_now}");
                        if (empty($next_step)) {
                            /*
                            Notify only the sender
                             */
                            if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                $header="{$mod_type} Project Employee Request has been Approved";
                                $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);

                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email'],$manager['private_email'],$manager['work_email'],$employee_requested['private_email'],$employee_requested['work_email']),"{$mod_type} Employee Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                // AddAddress(array($manager['private_email'],$manager['work_email'],$employee_requested['private_email'],$employee_requested['work_email'])));

                            }
                        } else {
                            /*
                            Email next set of approvers
                             */
                            $approver=getEmpDetails($next_step);
                            $header="New {$mod_type} Project Employee Request For Your Approval";
                            /*
                            Modify message to be more generic and allow to be sent to multiple people.
                             */
                            $message="Good day,<br/> You have a new {$mod_type} employee request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
                            $message=email_template($header,$message);
                            /*
                            Email Recepients
                             */
                            PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($approver['private_email'],$approver['work_email']),"{$mod_type} Project Employee Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                            /*
                            Notify request has been approved
                             */
                            if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                $header="{$mod_type} Project Employee Request has been Approved";
                                $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);

                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email'],$manager['private_email'],$manager['work_email'],$employee_requested['private_email'],$employee_requested['work_email']),"{$mod_type} Employee Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                // AddCC(array($manager['private_email'],$manager['work_email'],$employee_requested['private_email'],$employee_requested['work_email'])));
                            }
                        }
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
