<?php
require_once 'support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}

if(!empty($_POST)){
	$inputs=$_POST;

	$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
	$project_id=$inputs['proj_id'];
	$manage=AccessForProject($project_id, $employee_id);
	$errors="";
	$inputs=array_map('trim', $inputs);
	if(($manage['is_team_lead_ba']=='1')||($manage['is_team_lead_dev']=='1')){
		if($inputs['type']=='rev'){
			$phase=$inputs['phase_id']-1;
		}else{
			$phase=$inputs['phase_id'];
		}
	$validate=$con->myQuery("SELECT * FROM project_phase_request WHERE project_id=? AND project_phase_id=? AND (request_status_id='1' OR request_status_id='3') AND type=?",array($inputs['proj_id'],$phase,$inputs['type']))->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($validate)){
		$errors.="<li>Request has already been sent.</li>";
		}
	}
	if($inputs['type']=='comp'){
	$validate1=$con->myQuery("SELECT * FROM project_phase_request WHERE project_id=? AND project_phase_id=? AND (request_status_id='1' OR request_status_id='3') AND type='rev'",array($inputs['proj_id'],$last_phase))->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($validate1)){
		$errors.="<li>Phase Reversion has been submitted. Please cancel the request to proceed.</li>";
	} 
	$validate_task=$con->myQuery("SELECT id FROM project_task_list WHERE project_id=? AND project_phase_id=? AND status_id!=2",array($inputs['proj_id'],$inputs['phase_id']))->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($validate_task)){
		$errors.="<li>Active employee task/s. </li>";
	}
	}else{
	$validate1=$con->myQuery("SELECT * FROM project_phase_request WHERE project_id=? AND project_phase_id=? AND (request_status_id='1' OR request_status_id='3') AND type='comp'",array($inputs['proj_id'],$inputs['phase_id']))->fetchAll(PDO::FETCH_ASSOC);
	// var_dump($validate1);
	// die;
	if(!empty($validate1)){
		$errors.="<li>Phase Completion has been submitted. Please cancel the request to proceed.</li>";
	}
}
	// var_dump($errors);
	// die;
if($errors!=""){
				Alert("You have the following errors: <br/>".$errors,"danger");
				if(empty($inputs['proj_id'])){
					redirect("my_projects.php");
				}
				else{
					redirect("my_projects_view.php?id=".urlencode($inputs['proj_id'])."&tab=1");
				}
				die;
		} else {
		        	$date = new DateTime();
					$date_now=$date->getTimestamp();
					$date_now=$date_now-86400;
					$addDay = 86400;
					do{
					$try=date('Y-m-d', ($date_now+$addDay));
					        $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
					$nextDay = date('w', ($date_now+$addDay));
					$date_now = $date_now+$addDay;}
					while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
					$date_applied=date('Y-m-d',$date_now);
					$hours=$inputs['hours'];
					$next_phase=$inputs['phase_id']+1;
					$prev_phase=$inputs['phase_id']-1;
				 #Team Leader Start
				$last_phase=$inputs['phase_id']-1;
				 $con->beginTransaction();
				if($inputs['type']=='comp'){
				$param1=array(
				"project_id"=>$inputs['proj_id'],
				"phase_id"=>$inputs['phase_id'],
				'start_date'=>$date_applied,
				'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
				"manager_id"=>$inputs['manager_id'],
				"status_id"=>'1',
				"type"=>'comp',
				"designation"=>$inputs['des_id'],
				"hours"=>'',
				"step"=>'2',
				"admin_id"=>$inputs['admin_id'],
				"reason"=>$inputs['reason']
				);
				}else{
					$param1=array(
					"project_id"=>$inputs['proj_id'],
					"phase_id"=>$last_phase,
					'start_date'=>$date_applied,
					'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
					"manager_id"=>$inputs['manager_id'],
					"status_id"=>'1',
					"type"=>'rev',
					"designation"=>$inputs['des_id'],
					"hours"=>'',
					"step"=>'2',
					"admin_id"=>$inputs['admin_id'],
					"reason"=>$inputs['reason']);
				}
				$con->myQuery("INSERT INTO project_phase_request (project_id,project_phase_id,employee_id,request_status_id,manager_id,date_filed,designation_id,type,hours,comment,step_id,admin_id) VALUES (:project_id,:phase_id,:employee_id,:status_id,:manager_id,:start_date,:designation,:type,:hours,:reason,:step,:admin_id)",$param1);
				$request_id=$con->lastInsertId();
				$con->commit();

				try {
				     $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
					$con->beginTransaction();
					$inputs1['file_name']=$_FILES['file']['name'];
					$project_id=$inputs['proj_id'];
					$project_phase_id=$inputs['phase_id'];
					$con->myQuery("INSERT INTO project_files(file_name,date_modified,employee_id,project_id,project_phase_id,phase_request_id) VALUES(:file_name,NOW(),'$employee_id','$project_id','$project_phase_id','$request_id')",$inputs1);
					$file_id=$con->lastInsertId();

					$filename=$file_id.getFileExtension($_FILES['file']['name']);
					move_uploaded_file($_FILES['file']['tmp_name'],"proj_files/".$filename);
					$con->myQuery("UPDATE project_files SET file_location=? WHERE id=?",array($filename,$file_id));					
					
					insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Uploaded ({$inputs['file_name']}) to project files.");


					$con->commit();
					} catch (Exception $e) {
					  $con->rollBack();
			//		  echo "Failed: " . $e->getMessage();
					  Alert("Upload failed. Please try again.","danger");
				  	  redirect("my_projects_view.php?id=".urlencode($inputs['proj_id'])."&tab=1");
					  die;
					}
				Alert("Request has been sent.","success");
			redirect("my_projects_view.php?id=".urlencode($inputs['proj_id'])."&tab=1");
	}
}
?>