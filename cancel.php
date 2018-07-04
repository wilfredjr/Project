  <?php
  	require_once("support/config.php");

  	if(empty($_GET['id']))
  	{
		Modal("Invalid Record Selected");
		redirect("index.php");
		die;
	}
	else{
		$id=$_GET['id'];

		switch ($_POST['type']) {
			case 'leave':
				$table="employees_leaves";
				$page="employee_leave_request.php";


				$audit_details=$con->myQuery("SELECT employee_name,leave_type,date_start,date_end,reason FROM vw_employees_leave WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
				if(empty($audit_details['leave_type'])){
						$audit_details['leave_type']="Leave Without Pay";
					}
				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Cancelled leave ({$audit_details['leave_type']}) request. From {$audit_details['date_start']} To {$audit_details['date_end']}. Reason for leave: {$audit_details['reason']}");

				break;
        case 'offset':
  				$table="employees_offset_request";
  				$page="offset.php";


  				$audit_details=$con->myQuery("SELECT employees_name,request_type FROM vw_employees_offset WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);

  				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Cancelled offset ({$audit_details['request_type']}) request.");

  				break;
			case 'overtime':
				$table="employees_ot";
				$page="overtime.php?tab=2";

				$pre_ot_id=$con->myQuery("SELECT ot_pre_id FROM employees_ot WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
				$con->myQuery("UPDATE employees_ot_pre SET if_proceed=0 WHERE id=?",array($pre_ot_id['ot_pre_id']));

				$audit_details=$con->myQuery("SELECT employee_name,ot_date,worked_done,no_hours FROM vw_employees_ot WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);

				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Cancelled overtime request. From {$audit_details['date_from']} To {$audit_details['date_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");
				break;
			case 'pre_overtime':
				$table="employees_ot_pre";
				$page="overtime.php";

				$audit_details=$con->myQuery("SELECT employee_name,ot_date,worked_done,no_hours FROM vw_employees_ot_pre WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);

				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Cancelled overtime request. From {$audit_details['date_from']} To {$audit_details['date_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");
				break;
			case 'shift':
				$table="employees_change_shift";
				$page="shift_request.php";

				$audit_details=$con->myQuery("SELECT employee_name,adjustment_reason,orig_in_time,orig_out_time,adj_in_time,adj_out_time FROM vw_employees_adjustments WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);

				$audit_message="From {$audit_details['orig_in_time']}-{$audit_details['orig_out_time']} to {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']}. Adjustment Reason:{$audit_details['adjustment_reason']}";
				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Cancelled attendance adjustment request. {$audit_message}");
				break;
			case 'ob':
				$table="employees_ob";
				$page="ob_request.php";

				$audit_details=$con->myQuery("SELECT employee_name,destination,purpose,ob_date FROM vw_employees_ob WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);

				$audit_message="Destination: {$audit_details['destination']}. Purpose: {$audit_details['purpose']} during ".date("Y-m-d",strtotime($audit_details['date_from']))." - ".date("Y-m-d",strtotime($audit_details['date_to']));

				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Cancelled official business request. {$audit_message}");
				break;
			case 'adjustment':
				$table="employees_adjustments";
				$page="adjustment_request.php";

				$audit_details=$con->myQuery("SELECT employee_name,adjustment_reason,orig_in_time,orig_out_time,adj_in_time,adj_out_time FROM vw_employees_adjustments WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
				if($audit_details['orig_in_time']=="0000-00-00 00:00:00"){
						$audit_message="Add {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']}";
				}
				else{
					$audit_message="From {$audit_details['orig_in_time']}-{$audit_details['orig_out_time']} to {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']}. Adjustment Reason:{$audit_details['adjustment_reason']}";
				}

				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Cancelled attendance adjustment request. {$audit_message}");

				break;
			case 'allowance':
				$table="employees_allowances";
				$page="allowance_request.php";

				$audit_details=$con->myQuery("SELECT employee_name,food_allowance,transpo_allowance,request_reason,date_applied FROM vw_employees_allowances WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
				$audit_message="Date applied for ({$audit_details["date_applied"]}), Food allowance (".number_format($audit_details['food_allowance'],2)."). Transportation Allowance (".number_format($audit_details['transpo_allowance'], 2)."). With a reason of ({$audit_details["request_reason"]})";
				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Cancelled attendance adjustment request. {$audit_message}");

				break;
			case 'ot_adj':
				$table="employees_ot_adjustments";
				$page="overtime.php?tab=3";

				$audit_details=$con->myQuery("SELECT employee_name,orig_time_in,orig_time_out,adj_time_in,adj_time_out FROM vw_employees_ot_adjustments WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
				if($audit_details['orig_time_in']=="0000-00-00 00:00:00"){
						$audit_message="Add {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}";
				}
				else{
					$audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
				}

				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Cancelled ot attendance adjustment request. {$audit_message}");

				break;
			case 'phase':
				$proj=$_POST['proj_id'];
				$table="project_phase_request";
				$page="my_projects_view.php?id=".$proj."&tab=1";
				break;
			case 'phase1':
				$proj=$_POST['proj_id'];
				$table="project_phase_request";
				$page="project_phase_request.php";
				break;
			case 'assign':
				$proj=$_POST['proj_id'];
				$table="project_task";
				$page="task_management_project.php?id=".$proj;
				break;
			case 'assign1':
				$proj=$_POST['proj_id'];
				$table="project_task";
				$page="task_management_project.php";
				break;
			case 'task_submit':
				$proj=$_POST['proj_id'];
				$table="project_task_list";
				$page="my_tasks.php";
				break;
			case 'bug':
				$bug=$_POST['bug_id'];
				$table="project_bug_request";
				if(empty($bug)){
					$page="bug_phase_request.php";
				}else{
				$page="bugs_view.php?id=".$bug;
				}
				break;
			case 'dev':
				$table="project_development";
				$page="project_development_request.php";
				break;
			case 'bug_app':
				$proj=$_POST['proj_id'];
				$table="project_bug_application";
				$page="bug_management_project.php?id=".$proj;
				break;
			case 'bug_emp':
				$proj=$_POST['proj_id'];
				$table="project_bug_employee";
				$page="bug_employee_request.php";
				break;
			default:
				redirect("index.php");
				break;
		}
		if($_POST['type']=='task_submit'){
			$task=$con->myQuery("SELECT request_id FROM {$table} WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
			$con->myQuery("UPDATE {$table} SET is_submitted =0 WHERE id=?",array($id));
			$con->myQuery("UPDATE project_task_completion SET request_status_id =5,date_cancelled = NOW() WHERE id=?",array($task['request_id']));
			$con->myQuery("UPDATE project_files SET is_deleted =1 WHERE task_completion_id=?",array($task['request_id']));
		}elseif($_POST['type']=='bug'){
			$con->myQuery("UPDATE {$table} SET request_status_id =5,date_cancelled = NOW() WHERE id=?",array($id));
			$con->myQuery("UPDATE bug_files SET is_deleted =1 WHERE bug_request_id=?",array($id));
		}elseif(($_POST['type']=='phase')OR($_POST['type']=='phase1')){
			$con->myQuery("UPDATE {$table} SET request_status_id =5,date_cancelled = NOW() WHERE id=?",array($id));
			$con->myQuery("UPDATE project_files SET is_deleted =1 WHERE phase_request_id=?",array($id));
		}elseif($_POST['type']=='dev'){
			$dev=$con->myQuery("SELECT phase_request_id FROM {$table} WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
			$con->myQuery("UPDATE {$table} SET request_status_id=5,date_cancelled = NOW() WHERE id=?",array($id));
			$con->myQuery("UPDATE project_files SET is_deleted =1 WHERE project_dev_id=?",array($id));
			$con->myQuery("UPDATE {$table} SET request_status_id =1  WHERE id=?",array($dev['phase_request_id']));
		}elseif($_POST['type']=='bug_app'){
			$con->myQuery("UPDATE {$table} SET request_status_id =5,date_cancelled = NOW() WHERE id=?",array($id));
			$con->myQuery("UPDATE bug_files SET is_deleted =1 WHERE bug_list_id=?",array($id));
		}else{
		$con->myQuery("UPDATE {$table} SET request_status_id =5,date_cancelled = NOW() WHERE id=?",array($id));}
		Alert("Cancel Request Successful","success");
		redirect($page);
	}
  ?>
