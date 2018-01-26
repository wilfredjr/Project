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
			if ($manage['is_manager']=='1') {
				if($inputs['type']=='comp'){#manager comp
	                	$def_check=$con->myQuery("SELECT * FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND (in_deficit='1' OR status_id='4')",array($inputs['proj_id'],$inputs['phase_id']))->fetch(PDO::FETCH_ASSOC);
	                	$def_check_start=$con->myQuery("SELECT * FROM project_deficit WHERE project_id=? AND project_phase_id=? AND done_days='0' AND done_hours='0'",array($inputs['proj_id'],$inputs['phase_id']))->fetch(PDO::FETCH_ASSOC);
	                	if(empty($def_check)){
	                    $con->myQuery("UPDATE project_phase_dates SET status_id='2',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($date_applied,$inputs['proj_id'],$inputs['phase_id']));
	                         $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
						        if($stat_check['status_id']=='3'){
						        	$con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase));
						        	  $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
						        }
	                    	if($inputs['phase_id']=='8'){
			                    $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($inputs['proj_id']));
	                    	}else{
	                    		$con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$inputs['proj_id']));
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
						        $con->myQuery("UPDATE project_phase_dates SET status_id='2',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($def_check['date_end'],$inputs['proj_id'],$inputs['phase_id']));
						        $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
						        if($stat_check['status_id']=='3'){
						        	$con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase));
						        	$stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
						        }
						        if($inputs['phase_id']=='8'){
					                    $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($inputs['proj_id']));
			                    	}else{
			                    		$con->myQuery("UPDATE projects SET project_status_id=?,cur_phase=? WHERE id=?",array($stat_check['status_id'],$next_phase,$inputs['proj_id']));
			                    	}
						        $con->myQuery("INSERT INTO project_deficit (project_id,project_phase_id,date_start,date_end,in_hours,in_days,done_days,done_hours) VALUES(?,?,'$date_end_next','$date_applied','$hours','$days','$days','$hours')",array($inputs['proj_id'],$inputs['phase_id']));
		                	}
		                	elseif(($def_check['status_id']=='4')&&($def_check['in_deficit']=='1')){
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
							    $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
							    $con->myQuery("UPDATE project_phase_dates SET status_id='2',in_deficit='0' WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$inputs['phase_id']));
						        if($stat_check['status_id']=='3'){
						        	$con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase));
						        	 $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
						        }
						        if($inputs['phase_id']=='8'){
					                    $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($inputs['proj_id']));
			                    	}else{
			                    		$con->myQuery("UPDATE projects SET project_status_id=?,cur_phase=? WHERE id=?",array($stat_check['status_id'],$next_phase,$inputs['proj_id']));
			                    	}
						        $con->myQuery("UPDATE project_deficit SET done_days='$days',done_hours='$hours',date_end='$date_applied' WHERE done_days='0' AND done_hours='0' AND project_id=? AND project_phase_id=?",array($inputs['proj_id'],$inputs['phase_id']));
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
								    $con->myQuery("UPDATE project_phase_dates SET status_id='2',in_deficit='0',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($def_check['date_end'],$inputs['proj_id'],$inputs['phase_id']));
								    $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
							        if($stat_check['status_id']=='3'){
							        	$con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase));
							        	$stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
							        }
					                    if($inputs['phase_id']=='8'){
						                    $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($inputs['proj_id']));
				                    	}else{
				                    		$con->myQuery("UPDATE projects SET project_status_id=?,cur_phase=? WHERE id=?",array($stat_check['status_id'],$next_phase,$inputs['proj_id']));
				                    	}
							        $con->myQuery("UPDATE project_deficit SET done_days='$days',done_hours='$hours',date_start=?,date_end='$date_applied' WHERE done_days='0' AND done_hours='0' AND project_id=? AND project_phase_id=?",array($date_end_next,$inputs['proj_id'],$inputs['phase_id']));
		                		}else{
		                			 if($inputs['phase_id']=='8'){
						                    $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($inputs['proj_id']));
				                    	}else{
				                    		$con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$inputs['proj_id']));
				                    	}
		                			 $con->myQuery("UPDATE project_phase_dates SET status_id='2',in_deficit='0',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($date_applied,$inputs['proj_id'],$inputs['phase_id']));
		                			  $con->myQuery("UPDATE project_deficit SET date_end='$date_applied' WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$inputs['phase_id']));
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
							$prev_date_end=$con->myQuery("SELECT temp_date_end,date_end FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$prev_phase))->fetch(PDO::FETCH_ASSOC);
							$prev_temp_date_end=(new DateTime($prev_date_end['temp_date_end']))->getTimestamp();;
							$prev_date_end=(new DateTime($prev_date_end['date_end']))->getTimestamp();;
						if(($prev_temp_date_end<=$prev_date_end)){
	                    $con->myQuery("UPDATE project_phase_dates SET status_id='1',in_deficit='1' WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$prev_phase));
	                    $con->myQuery("UPDATE projects SET cur_phase=? WHERE id=?",array($prev_phase,$inputs['proj_id']));
					  }else{
					  	$con->myQuery("UPDATE project_phase_dates SET status_id='4',in_deficit='1' WHERE project_id=? AND project_phase_id=?",array($inputs['proj_id'],$prev_phase));
	                    $con->myQuery("UPDATE projects SET project_status_id='4',cur_phase=? WHERE id=?",array($prev_phase,$inputs['proj_id']));
					  	$con->myQuery("INSERT INTO project_deficit (project_id,project_phase_id,date_start,in_hours,in_days) VALUES(?,?,?,'$hours','$days')",array($inputs['proj_id'],$prev_phase,$date_applied));
					  }
	            }
                $con->beginTransaction();
				if($inputs['type']=='comp'){
				$param1=array(
				"project_id"=>$inputs['proj_id'],
				"phase_id"=>$inputs['phase_id'],
				'start_date'=>$date_applied,
				'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
				"manager_id"=>$inputs['manager_id'],
				"status_id"=>'2',
				"type"=>'comp',
				"designation"=>$inputs['des_id'],
				"hours"=>'',
				"reason"=>'');
				}else{
					$param1=array(
					"project_id"=>$inputs['proj_id'],
					"phase_id"=>$inputs['phase_id']-1,
					'start_date'=>$date_applied,
					'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
					"manager_id"=>$inputs['manager_id'],
					"status_id"=>'2',
					"type"=>'rev',
					"designation"=>($inputs['des_id']),
					"hours"=>$inputs['hours'],
					"reason"=>$inputs['reason']);
				}
	
				$con->myQuery("INSERT INTO project_phase_request (project_id,project_phase_id,employee_id,request_status_id,manager_id,date_filed,designation_id,type,hours,comment,date_approved) VALUES (:project_id,:phase_id,:employee_id,:status_id,:manager_id,:start_date,:designation,:type,:hours,:reason,:start_date)",$param1);
				$con->commit();
				Alert("Project phase has been successfully updated.","success");
			}else{ #Team Leader Start
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
				"reason"=>'');
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
					"hours"=>$inputs['hours'],
					"reason"=>$inputs['reason']);
				}
				$con->myQuery("INSERT INTO project_phase_request (project_id,project_phase_id,employee_id,request_status_id,manager_id,date_filed,designation_id,type,hours,comment) VALUES (:project_id,:phase_id,:employee_id,:status_id,:manager_id,:start_date,:designation,:type,:hours,:reason)",$param1);
				$con->commit();
				if($inputs['type']=='comp'){
					$validate1=$con->myQuery("SELECT * FROM project_phase_request WHERE project_id=? AND project_phase_id=? AND (request_status_id='1' OR request_status_id='3') AND type='rev'",array($inputs['proj_id'],$last_phase))->fetchAll(PDO::FETCH_ASSOC);
					if(!empty($validate1)){
						$con->myQuery("UPDATE project_phase_request SET request_status_id='5', date_cancelled=? WHERE project_id=? AND project_phase_id=? AND type='rev'",array($date_applied,$inputs['proj_id'],$last_phase));
					} 
				}else{
					$validate1=$con->myQuery("SELECT * FROM project_phase_request WHERE project_id=? AND project_phase_id=? AND (request_status_id='1' OR request_status_id='3') AND type='comp'",array($inputs['proj_id'],$inputs['phase_id']))->fetchAll(PDO::FETCH_ASSOC);
					// var_dump($validate1);
					// die;
					if(!empty($validate1)){
						$con->myQuery("UPDATE project_phase_request SET request_status_id='5', date_cancelled=? WHERE project_id=? AND project_phase_id=? AND type='comp'",array($date_applied,$inputs['proj_id'],$inputs['phase_id']));
					}
				}
				Alert("Request has been sent.","success");
			}
			redirect("my_projects_view.php?id=".urlencode($inputs['proj_id'])."&tab=1");
	}
}
?>