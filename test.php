<?php
switch (variable) {
	case 'allowance':

				$audit_details=$con->myQuery("SELECT employee_name,food_allowance,transpo_allowance,request_reason,date_applied FROM vw_employees_allowances WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
				$current=$con->myQuery("SELECT status,supervisor_id,final_approver_id,employees_id FROM  employees_allowances WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
				$audit_message="Date applied for ({$audit_details["date_applied"]}), Food allowance (".number_format($audit_details['food_allowance'],2)."). Transportation Allowance (".number_format($audit_details['transpo_allowance'], 2)."). With a reason of ({$audit_details["request_reason"]})";

				switch ($current['status']) {
					case 'Supervisor Approval':
						switch ($inputs['action']) {
							case 'approve':
									$con->myQuery("UPDATE employees_allowances SET status ='Final Approver Approval',reason='',supervisor_date_action=NOW() WHERE id=?",array($inputs['id']));

									$supervisor=getEmpDetails($current['supervisor_id']);
									$final_approver=getEmpDetails($current['final_approver_id']);
									$employees=getEmpDetails($current['employees_id']);
									$email_settings=getEmailSettings();
									//var_dump($supervisor);
									insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Approved {$employees['first_name']} {$employees['last_name']}'s allowance request. {$audit_message}");

									if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
										$header="Allowance Request Approved by Supervisor";
										$message="Hi {$employees['first_name']},<br/> Your request has been approved by your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
										$message=email_template($header,$message);
										
										emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($employees['private_email'],$employees['work_email'])),"Allowance Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);

										if(!empty($final_approver['private_email']) || !empty($final_approver['work_email'])){

										$header="New Allowance Request For Your Approval";
										$message="Hi {$final_approver['first_name']},<br/> You have a new allowance request from {$employees['first_name']} {$employees['last_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
										$message=email_template($header,$message);
										
										emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($final_approver['private_email'],$final_approver['work_email'])),"Allowance Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
										}
									}
								break;
							case 'reject':
								$required_fieds=array(
								"reason"=>"Enter Reason for rejection. <br/>"
								);
								if(validate($required_fieds)){
									$con->myQuery("UPDATE employees_allowances SET status ='Rejected (Supervisor)',reason=?,supervisor_date_action=NOW() WHERE id=?",array($inputs['reason'],$inputs['id']));

									$supervisor=getEmpDetails($current['supervisor_id']);
									$employees=getEmpDetails($current['employees_id']);
									$email_settings=getEmailSettings();

									insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Rejected {$employees['first_name']} {$employees['last_name']}'s allowance request. The reason given is '{$inputs['reason']}'. {$audit_message}");
									//var_dump($supervisor);
									if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
										$header="Allowance Request Rejected by Supervisor";
										$message="Hi {$employees['first_name']},<br/> Your request has been rejected by your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Spark Global Tech Systems Inc HRIS.";
										$message=email_template($header,$message);
										// var_dump($email_settings);
										 //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
										emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($employees['private_email'],$employees['work_email'])),"Allowance Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
									}
								}
								break;
						}
						break;
					case 'Final Approver Approval':
						switch ($inputs['action']) {
							case 'approve':
									try {
										$con->beginTransaction();
											$con->myQuery("UPDATE employees_allowances SET status ='Approved',reason='',final_approver_date_action=NOW() WHERE id=?",array($inputs['id']));
											//die;
										// die;
										$con->commit();
										
									} catch (Exception $e) {
										$con->rollback();
										Alert("Save Failed.","danger");
										redirect("allowance_approval.php");
										die;
									}
									// var_dump($current);
									$supervisor=getEmpDetails($current['supervisor_id']);
									$employees=getEmpDetails($current['employees_id']);
									$final_approver=getEmpDetails($current['final_approver_id']);
									$email_settings=getEmailSettings();

									insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s attendance adjustment request. {$audit_message}");
									//var_dump($supervisor);
									if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
										$header="Allowance Request has been Approved";
										$message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
										$message=email_template($header,$message);

										emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($employees['private_email'],$employees['work_email'])),"Allowance Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
									}
									
								break;
							case 'reject':
								$required_fieds=array(
								"reason"=>"Enter Reason for rejection. <br/>"
								);
								if(validate($required_fieds)){
									$con->myQuery("UPDATE employees_allowances SET status ='Rejected (Final Approver)',reason=?,final_approver_date_action=NOW() WHERE id=?",array($inputs['reason'],$inputs['id']));

									$supervisor=getEmpDetails($current['supervisor_id']);
									$employees=getEmpDetails($current['employees_id']);
									$final_approver=getEmpDetails($current['final_approver_id']);
									$email_settings=getEmailSettings();
									
									insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Rejected {$employees['first_name']} {$employees['last_name']}'s allowance request. The reason given is '{$inputs['reason']}'. {$audit_message}");

									if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
										$header="Allowance Request Rejected by Final Approver";
										$message="Hi {$employees['first_name']},<br/> Your request has been rejected by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. The reason given is '{$inputs['reason']}'.  For more details please login to the Spark Global Tech Systems Inc HRIS.";
										$message=email_template($header,$message);

										emailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",implode(",",array($employees['private_email'],$employees['work_email'])),"Allowance Request (Rejected)",$message,$email_settings['host'],$email_settings['port']);
									}
								}
								break;
						}
						break;
				}
		break;
}