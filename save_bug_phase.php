<?php
require_once 'support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}
            if(empty($_FILES['file']['name'])){
            Alert("No file selected.","danger");
            redirect("bugs_view.php?id=".urlencode($_POST['id']));
            die();
        }
if(!empty($_POST)){
	$inputs=$_POST;
	$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
	$project_id=$inputs['project_id'];
	$errors="";
	$inputs=array_map('trim', $inputs);
			if($inputs['type']=='rev'){
			$phase=$inputs['bug_phase_id'];
		}else{
			$phase=$inputs['bug_phase_id']-1;
		}
	$validate=$con->myQuery("SELECT id FROM project_bug_request WHERE project_id=? AND bug_list_id=? AND bug_phase_id=? AND (request_status_id='1' OR request_status_id='3') AND type=?",array($inputs['project_id'],$inputs['id'],$phase,$inputs['type']))->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($validate)){
		$errors.="<li>Request has already been sent.</li>";
		}

		if($inputs['type']=='comp'){
	$validate1=$con->myQuery("SELECT id FROM project_bug_request WHERE project_id=? AND bug_phase_id=? AND (request_status_id='1' OR request_status_id='3') AND type='rev'",array($inputs['project_id'],$phase))->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($validate1)){
		$errors.="<li>Phase Reversion has been submitted. Please cancel the request to proceed.</li>";
	} 
	}else{
		$validate1=$con->myQuery("SELECT id FROM project_bug_request WHERE project_id=? AND bug_phase_id=? AND (request_status_id='1' OR request_status_id='3') AND type='comp'",array($inputs['project_id'],$phase))->fetchAll(PDO::FETCH_ASSOC);
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
				if(empty($inputs['id'])){
					redirect("bug_management.php");
				}
				else{
					redirect("bugs_view.php?id=".urlencode($inputs['id']));
				}
				die;
		} else {
					$current=$con->myQuery("SELECT * FROM project_bug_list WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
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
					$next_phase=$inputs['bug_phase_id']+1;
					$prev_phase=$inputs['bug_phase_id']-1;
			if ($current['manager_id']==$employee_id) {
				if($inputs['type']=='comp'){#manager comp
	                	$def_check=$con->myQuery("SELECT * FROM project_bug_phase_dates WHERE project_id=? AND bug_list_id=? AND bug_phase_id=? AND (in_deficit='1' OR status_id='4')",array($current['project_id'],$current['id'],$current['bug_phase_id']))->fetch(PDO::FETCH_ASSOC);
	                	$def_check_start=$con->myQuery("SELECT * FROM project_bug_deficit WHERE project_id=? AND bug_list_id=? AND bug_phase_id=? AND done_days='0' AND done_hours='0'",array($current['project_id'],$current['id'],$current['bug_phase_id']))->fetch(PDO::FETCH_ASSOC);
	                	if(empty($def_check)){
	                    $con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='2',temp_date_end=? WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($date_applied,$current['project_id'],$current['id'],$current['bug_phase_id']));
	                         $stat_check=$con->myQuery("SELECT project_status_id FROM project_bug_phase_dates WHERE project_id=? AND bug_list_id AND bug_phase_id=? AND project_status_id='3'",array($current['project_id'],$current['id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
						        if(!empty($stat_check)){
						        	$con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='1' WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase));
						        }
	                    	if($current['bug_phase_id']=='2'){
			                    $con->myQuery("UPDATE project_bug_list SET project_status_id='2',bug_phase_id='2' WHERE id=?",array($current['id']));
	                    	}else{
	                    		$con->myQuery("UPDATE project_bug_list SET project_status_id='1',bug_phase_id=? WHERE id=?",array($next_phase,$current['id']));
	                    	}
		                }else{
		                	if(($def_check['project_status_id']=='4') && ($def_check['in_deficit']=='0')){
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
						        $con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='2',temp_date_end=? WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($def_check['date_end'],$current['project_id'],$current['id'],$current['bug_phase_id']));
						        $stat_check=$con->myQuery("SELECT project_status_id FROM project_bug_phase_dates WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
						        if($stat_check['project_status_id']=='3'){
						        	$con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='1' WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase));
						        	 $stat_check=$con->myQuery("SELECT project_status_id FROM project_bug_phase_dates WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
						        }
						        if($current['bug_phase_id']=='2'){
					                    $con->myQuery("UPDATE project_bug_list SET project_status_id='2',bug_phase_id='2' WHERE id=?",array($current['id']));
			                    	}else{
			                    		$con->myQuery("UPDATE project_bug_list SET project_status_id=?,bug_phase_id=? WHERE id=?",array($stat_check['project_status_id'],$next_phase,$current['id']));
			                    	}
						        $con->myQuery("INSERT INTO project_bug_deficit (project_id,bug_list_id,bug_phase_id,date_start,date_end,done_days,done_hours) VALUES(?,?,?,'$date_end_next','$date_applied','$days','$hours')",array($current['project_id'],$current['id'],$current['bug_phase_id']));
		                	}
		                	elseif(($def_check['project_status_id']=='4')&&($def_check['in_deficit']=='1')){
		                		$date_start1= new DateTime($def_check_start['date_start']);
		                		$date_now1= new DateTime($date_applied);
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
							    $stat_check=$con->myQuery("SELECT project_status_id FROM project_bug_phase_dates WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
							    $con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='2',in_deficit='0' WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$current['bug_phase_id']));
						        if($stat_check['project_status_id']=='3'){
						        	$con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='1' WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase));
						        	$stat_check=$con->myQuery("SELECT project_status_id FROM project_bug_phase_dates WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
						        }
						        if($current['bug_phase_id']=='2'){
					                    $con->myQuery("UPDATE project_bug_list SET project_status_id='2',bug_phas0e_id='2' WHERE id=?",array($current['id']));
			                    	}else{
			                    		$con->myQuery("UPDATE project_bug_list SET project_status_id=?,bug_phase_id=? WHERE id=?",array($stat_check['project_status_id'],$next_phase,$current['id']));
			                    	}
						        $con->myQuery("UPDATE project_bug_deficit SET done_days='$days',done_hours='$hours',date_end='$date_applied' WHERE done_days='0' AND done_hours='0' AND project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$current['bug_phase_id']));
		                	}elseif(($def_check['project_status_id']=='1')&&($def_check['in_deficit']=='1')){
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
								    $con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='2',in_deficit='0',temp_date_end=? WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($def_check['date_end'],$current['project_id'],$current['id'],$current['bug_phase_id']));
								    $stat_check=$con->myQuery("SELECT project_status_id FROM project_bug_phase_dates WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
							        if($stat_check['project_status_id']=='3'){
							        	$con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='1' WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase));
							        	$stat_check=$con->myQuery("SELECT project_status_id FROM project_bug_phase_dates WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
							        }
					                    if($current['bug_phase_id']=='2'){
						                    $con->myQuery("UPDATE project_bug_list SET project_status_id='2',bug_phase_id='2' WHERE id=?",array($current['id']));
				                    	}else{
				                    		$con->myQuery("UPDATE project_bug_list SET project_status_id=?,bug_phase_id=? WHERE id=?",array($stat_check['project_status_id'],$next_phase,$current['id']));
				                    	}
							        $con->myQuery("UPDATE project_bug_deficit SET done_days='$days',done_hours='$hours',date_start=?,date_end='$date_applied' WHERE done_days='0' AND done_hours='0' AND project_id=? AND bug_list_id=? AND bug_phase_id=?",array($date_end_next,$current['project_id'],$current['id'],$current['bug_phase_id']));
		                		}else{
		                			 if($current['bug_phase_id']=='2'){
						                    $con->myQuery("UPDATE project_bug_list SET project_status_id='2',bug_phase_id='2' WHERE id=?",array($current['id']));
				                    	}else{
				                    		$con->myQuery("UPDATE project_bug_list SET project_status_id='1',bug_phase_id=? WHERE id=?",array($next_phase,$current['id']));
				                    	}
		                			 $con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='2',in_deficit='0',temp_date_end=? WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($date_applied,$current['project_id'],$current['id'],$current['bug_phase_id']));
		                			  $con->myQuery("UPDATE project_bug_deficit SET date_end='$date_applied' WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$current['bug_phase_id']));
		                		}
		                	}
		            }
	            }else{#manager rev
			            	$days=($hours/8);
						    if (is_float($days)){
						        $days=floor($days)+1;
						    }else{
						    	$days=$days;
						    }
							$prev_date_end=$con->myQuery("SELECT temp_date_end,date_end FROM project_bug_phase_dates WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$prev_phase))->fetch(PDO::FETCH_ASSOC);
							$prev_temp_date_end=(new DateTime($prev_date_end['temp_date_end']))->getTimestamp();;
							$prev_date_end=(new DateTime($prev_date_end['date_end']))->getTimestamp();;
						if(($prev_temp_date_end<=$prev_date_end)){
	                    $con->myQuery("UPDATE project_bug_phase_dates SET project_status_id='1',in_deficit='1' WHERE project_id=? AND bug_list_id=? AND bug_phase_id=?",array($current['project_id'],$current['id'],$prev_phase));
	                    $con->myQuery("UPDATE projects_bug_list SET bug_phase_id=? WHERE id=?",array($prev_phase,$current['project_id']));
					  }else{
  					  	$con->myQuery("UPDATE project_phase_dates SET status_id='4',in_deficit='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$prev_phase));
	                    $con->myQuery("UPDATE project_bug_list SET project_status_id='4',bug_phase_id=? WHERE id=?",array($prev_phase,$current['project_id']));
					  	$con->myQuery("INSERT INTO project_bug_deficit (project_id,bug_list_id,bug_phase_id,date_start) VALUES(?,?,?,?)",array($current['project_id'],$current['id'],$prev_phase,$date_applied));
					  }
	            }
                $con->beginTransaction();
				if($inputs['type']=='comp'){
				$param1=array(
				"project_id"=>$current['project_id'],
				"bug_list_id"=>$current['id'],
				"bug_phase_id"=>$current['bug_phase_id'],
				'start_date'=>$date_applied,
				'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
				"manager_id"=>$current['manager_id'],
				"status_id"=>'2',
				"type"=>'comp',
				"reason"=>'');
				}else{
					$param1=array(
					"project_id"=>$current['project_id'],
					"bug_list_id"=>$current['id'],
					"bug_phase_id"=>$current['bug_phase_id']-1,
					'start_date'=>$date_applied,
					'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
					"manager_id"=>$current['manager_id'],
					"status_id"=>'2',
					"type"=>'rev',
					"reason"=>$inputs['reason']);
				}
	
				$con->myQuery("INSERT INTO project_bug_request (project_id,bug_list_id,bug_phase_id,employee_id,request_status_id,manager_id,date_filed,type,reason,date_approved) VALUES (:project_id,:bug_list_id,:bug_phase_id,:employee_id,:status_id,:manager_id,:start_date,:type,:reason,:start_date)",$param1);
				$request_id = $con->lastInsertId();
				$con->commit();
				        try {
	                 	 $project_id=$current['project_id'];
	                 	 $bug_id=$current['id'];
                         $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
                        $con->beginTransaction();
                        $inputs1['file_name']=$_FILES['file']['name'];
                        $con->myQuery("INSERT INTO bug_files(file_name,date_modified,employee_id,project_id,bug_list_id,bug_request_id) VALUES(:file_name,NOW(),'$employee_id','$project_id','$bug_id','$request_id')",$inputs1);
                        $file_id=$con->lastInsertId();

                        $filename=$file_id.getFileExtension($_FILES['file']['name']);
                        move_uploaded_file($_FILES['file']['tmp_name'],"bug_files/".$filename);
                        $con->myQuery("UPDATE bug_files SET file_location=? WHERE id=?",array($filename,$file_id));
                        $con->commit();           
                        } catch (Exception $e) {
                      $con->rollBack();
            //        echo "Failed: " . $e->getMessage();
                      Alert("Upload failed. Please try again.","danger");
                      redirect("bugs_view.php?id=".urlencode($inputs['id']));
                      die;
                    }
				Alert("Project phase has been successfully updated.","success");
			}else{ #Team Leader Start
				$last_phase=$inputs['bug_phase_id']-1;
				if($current['bug_phase_id']=='1'){
					$team_lead=$current['team_lead_dev'];
				}elseif($current['bug_phase_id']=='2'){
					$team_lead=$current['team_lead_ba'];
				}

				if($team_lead==$employee_id){
					$step_id='2';
				}else{
					$step_id='1';
				}


				 $con->beginTransaction();
				if($inputs['type']=='comp'){
				$param1=array(
				"project_id"=>$current['project_id'],
				"bug_list_id"=>$current['id'],
				"bug_phase_id"=>$current['bug_phase_id'],
				'start_date'=>$date_applied,
				'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
				"manager_id"=>$current['manager_id'],
				"status_id"=>'1',
				"type"=>'comp',
				"comment"=>$inputs['work_done'],
				'admin_id'=>$current['admin_id'],
				'team_lead'=>$team_lead,
				'step_id'=>$step_id
				);
				}else{
					$param1=array(
					"project_id"=>$current['project_id'],
					"bug_list_id"=>$current['id'],
					"bug_phase_id"=>$last_phase,
					'start_date'=>$date_applied,
					'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
					"manager_id"=>$current['manager_id'],
					"status_id"=>'1',
					"type"=>'rev',
					"comment"=>$inputs['work_done'],
					'admin_id'=>$current['admin_id'],
					'team_lead'=>$team_lead,
					'step_id'=>$step_id
				);
				}
				$con->myQuery("INSERT INTO project_bug_request (project_id,bug_list_id,bug_phase_id,employee_id,request_status_id,manager_id,date_filed,type,comment,admin_id,team_lead_id, step_id) VALUES (:project_id,:bug_list_id,:bug_phase_id,:employee_id,:status_id,:manager_id,:start_date,:type,:comment,:admin_id,:team_lead,:step_id)",$param1);
				$request_id = $con->lastInsertId();
				$con->commit();
					try {
	                 	 $project_id=$current['project_id'];
	                 	 $bug_id=$current['id'];
                         $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
                        $con->beginTransaction();
                        $inputs1['file_name']=$_FILES['file']['name'];
                        $con->myQuery("INSERT INTO bug_files(file_name,date_modified,employee_id,project_id,bug_list_id,bug_request_id) VALUES(:file_name,NOW(),'$employee_id','$project_id','$bug_id','$request_id')",$inputs1);
                        $file_id=$con->lastInsertId();

                        $filename=$file_id.getFileExtension($_FILES['file']['name']);
                        move_uploaded_file($_FILES['file']['tmp_name'],"bug_files/".$filename);
                        $con->myQuery("UPDATE bug_files SET file_location=? WHERE id=?",array($filename,$file_id));
                        $con->commit();           
                        } catch (Exception $e) {
                      $con->rollBack();
            //        echo "Failed: " . $e->getMessage();
                      Alert("Upload failed. Please try again.","danger");
                      redirect("bugs_view.php?id=".urlencode($inputs['id']));
                      die;
                    }
				Alert("Request has been sent.","success");
			}
			redirect("bugs_view.php?id=".urlencode($inputs['id']));
	}
}
?>