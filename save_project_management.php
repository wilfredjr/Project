<?php
require_once 'support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}
if (empty($_SESSION[WEBAPP]['user']['access_project_management'])) {
	redirect("index.php");
	die;
}

if(!empty($_POST)){
		//Validate form inputs

	$inputs=$_POST;
	$errors="";
	$error_count=0;

	//  if ($inputs['fapprover'] == $inputs['sapprover']){
	//  	$duplicate_id = $inputs['fapprover'];
	//  	$error_count = $error_count +1;
	//  }

	//  if ($inputs['fapprover'] == $inputs['tapprover']) {
	//  	$duplicate_id = $inputs['fapprover'];
	//  	$error_count = $error_count +1;

	//  }
	//  if (!empty($inputs['sapprover']) && !empty($inputs['tapprover'])) {
	// 	 if ($inputs['sapprover'] == $inputs['tapprover']) {
	// 	 	$duplicate_id = $inputs['sapprover'];
	// 	 	$error_count = $error_count +1;
	// 	}
	// }
	if ($error_count > 0) {
		$employees=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name,' (',code,')') as employee_name FROM employees WHERE id=? LIMIT 1",array($duplicate_id))->fetch(PDO::FETCH_ASSOC);
		$errors.="<li>Repetition of Employee Approver Name: ".$employees['employee_name']."</li>";
	}

	// echo $errors;
	// die;
	if($errors!=""){

			Alert("You have the following error/s: <br/>".$errors,"danger");
			if(empty($inputs['project_id'])){
				redirect("project_management.php");
			}
			else{
				redirect("frm_project_management.php?id=".urlencode($inputs['project_id']));
			}
			die;
	}

	// echo '<pre>';
	// print_r($inputs);
	// echo '</pre>';
	// die;
	// echo "<pre>";
	// 		print_r($_SESSION[WEBAPP]['user']['employee_id']);
	// 		echo "</pre>";
	// 		die;


	unset($inputs['example_length']);
	unset($inputs['select_all']);
	if(empty($inputs['project_id'])){
		$con->beginTransaction();	

		//unset($inputs['shifting_id']);

		$proj = $inputs['proj_name'];
		$manager = $inputs['manager'];
		$fapprover = $inputs['manager'];
		$sapprover = $inputs['sapprover'];
		$tapprover = $inputs['tapprover'];
		$date = new DateTime();
		$date_f=new DateTime($inputs['date_start']);
		$des = $inputs['description'];
		$date_start=$date_f->format("Y-m-d");
		$man_hours = $inputs['man_hours'];
		$date_applied=date_format($date, 'Y-m-d');
		$date_applied;
// phase1 date
		$phase1 = new DateTime( $date_start );
    $t = $phase1->getTimestamp();
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
// phase3 date
    $daystoadd=($inputs['man_hours']/8);
    if (is_float($daystoadd)){
        $fordate=floor($daystoadd)+1;
    }else{
    	$fordate=$daystoadd;
    }
        $phase2 = new DateTime( $phase2 );
    $t = $phase2->getTimestamp();
        $addDay = 86400;
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

		$params=array(
			'proj_name'=>$proj,
			'emplo_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
			'date_start'=>$date_start,
			'description'=>$des,
			'date_applied'=>$date_applied,
			'proj_sta'=>"1",
			'manager'=>$manager,
			'fapprover'=>$fapprover,
			'sapprover'=>$sapprover,
			'tapprover'=>$tapprover,
			'is_del'=>"0",
			'end_date'=>$phase8,
			'man_hours'=>$inputs['man_hours'],
			'cur_phase'=>"1",
            'team_lead_ba'=>$inputs['team_lead_ba'],
            'team_lead_dev'=>$inputs['team_lead_dev']
			);

		$con->myQuery("INSERT INTO projects (name,description,employee_id,start_date,project_status_id,is_deleted,date_filed,manager_id,first_approver_id,second_approver_id,third_approver_id,end_date,man_hours,cur_phase,team_lead_ba,team_lead_dev) VALUES (:proj_name,:description,:emplo_id,:date_start,:proj_sta,:is_del,:date_applied,:manager,:fapprover,:sapprover,:tapprover,:end_date,:man_hours,:cur_phase,:team_lead_ba,:team_lead_dev)",$params);

		$project_id = $con->lastInsertId();
		// var_dump($inputs['emp_id']);
		// die;
		$array_count = count($inputs['emp_id']);
		$ctr=0;
		$ctr2=0;
		$ctr3=0;
		$added_by=$_SESSION[WEBAPP]['user']['employee_id'];
		for ($i=0; $i < $array_count; $i++) { 
			if ($inputs['manager'] == $inputs['emp_id'][$i]) {
					$ctr=1;
					$manager = 1;

			} else {

					$manager = 0;
			}
			if ($inputs['team_lead_ba'] == $inputs['emp_id'][$i]) {
					$ctr2=1;
					$team_lead_ba = 1;

			} else {

					$team_lead_ba = 0;
			}
			if ($inputs['team_lead_dev'] == $inputs['emp_id'][$i]) {
					$ctr3=1;
					$team_lead_dev = 1;

			} else {

					$team_lead_dev = 0;
			}
			$param=array(
				"project_id"=>$project_id,
				"employee_id"=>$inputs['emp_id'][$i],
				"manager"=>$manager,
				"team_lead_ba"=>$team_lead_ba,
				"team_lead_dev"=>$team_lead_dev
				);
			$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager,is_team_lead_ba,is_team_lead_dev) VALUES (:project_id,:employee_id,:manager,:team_lead_ba,:team_lead_dev)",$param);

			$param1=array(
				"project_id"=>$project_id,
				"employee_id"=>$inputs['emp_id'][$i],
				"manager"=>$manager,
				"team_lead_ba"=>$team_lead_ba,
				"team_lead_dev"=>$team_lead_dev,
				"date_start"=>$date_applied,
				'added_by'=>$_SESSION[WEBAPP]['user']['employee_id'],
				);

			$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead_ba,is_team_lead_dev) VALUES (:project_id,:employee_id,:date_start,:added_by,:manager,:team_lead_ba,:team_lead_dev)",$param1);

			
		}
		if ($inputs['manager'] == $inputs['team_lead_ba'] AND $inputs['manager'] == $inputs['team_lead_dev']) {
				// 			echo "7";
				// die;
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager,is_team_lead_ba,is_team_lead_dev,designation_id) VALUES ('$project_id','$manager_id','1','1','1','3')");
				$ctr=1;
				$ctr2=1;
				$ctr3=1;
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead_ba,is_team_lead_dev) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1','1','1')");
			}
			elseif($inputs['manager'] == $inputs['team_lead_ba']){
				// echo "6";
				// die;
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager,is_team_lead_ba,designation_id) VALUES ('$project_id','$manager_id','1','1','2')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_dev,designation_id) VALUES ('$project_id','$team_lead_dev','1','1')");
				// $ctr=1;
				// $ctr2=1;
				// $ctr3=1;
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead_ba) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1','1')");
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_dev) VALUES ('$project_id','$team_lead_dev','$date_applied','$added_by','1')");
			}
			elseif($inputs['manager'] == $inputs['team_lead_dev']){
				// echo "5";
				// die;
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager,is_team_lead_dev,designation_id) VALUES ('$project_id','$manager_id','1','1','1')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_ba,designation_id) VALUES ('$project_id','$team_lead_ba','1','2')");
				// $ctr=1;
				// $ctr2=1;
				// $ctr3=1;
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead_dev) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1','1')");
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_ba) VALUES ('$project_id','$team_lead_ba','$date_applied','$added_by','1')");
			}
			elseif($inputs['team_lead_ba'] == $inputs['team_lead_dev']){
				// echo "4";
				// die;
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_ba,is_team_lead_dev,designation_id) VALUES ('$project_id','$team_lead_ba','1','1','3')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager) VALUES ('$project_id','$manager_id','1')");
				// $ctr=1;
				// $ctr2=1;
				// $ctr3=1;
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_ba,is_team_lead_dev) VALUES ('$project_id','$team_lead_ba','$date_applied','$added_by','1','1')");
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1')");
			}
			else{
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_ba,designation_id) VALUES ('$project_id','$team_lead_ba','1','2')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_dev,designation_id) VALUES ('$project_id','$team_lead_dev','1','1')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager) VALUES ('$project_id','$manager_id','1')");
				// $ctr=1;
				// $ctr2=1;
				// $ctr3=1;
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_ba) VALUES ('$project_id','$team_lead_ba','$date_applied','$added_by','1')");
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_dev) VALUES ('$project_id','$team_lead_dev','$date_applied','$added_by','1')");
				$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1')");
			}
			$phase1_des1=$phase1_des['designation_id'];
			$phase2_des1=$phase2_des['designation_id'];
			$phase3_des1=$phase3_des['designation_id'];
			$phase4_des1=$phase4_des['designation_id'];
			$phase5_des1=$phase5_des['designation_id'];
			$phase6_des1=$phase6_des['designation_id'];
			$phase7_des1=$phase7_des['designation_id'];
			$phase8_des1=$phase8_des['designation_id'];
			$con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','1','$phase1_date','1','$phase1_des1','$date_start')");
			$con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','2','$phase2_date','3','$phase2_des1','$phase2_start')");	
			$con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','3','$phase3_date','3','$phase3_des1','$phase3_start')");	
			$con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','4','$phase4_date','3','$phase4_des1','$phase4_start')");	
			$con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','5','$phase5_date','3','$phase5_des1','$phase5_start')");	
			$con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','6','$phase6_date','3','$phase6_des1','$phase6_start')");	
			$con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','7','$phase7_date','3','$phase7_des1','$phase7_start')");	
			$con->myQuery("INSERT INTO project_phase_dates (project_id,project_phase_id,date_end,status_id,designation_id,date_start) VALUES ('$project_id','8','$phase8_date','3','$phase8_des1','$phase8_start')");	

		// die;
		$con->commit();	

		// var_dump($inputs);
		// die;




  //       insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['last_name']} created a new project. Project Name: {$proj}");

  //       $email_settings=getEmailSettings();

  //       $array_count = count($inputs['emp_id']);

		// $project_id = $con->lastInsertId();
		// $ctr=0;

		// $manager_name=getEmpDetails($inputs['manager']);
		// $header="Project Management";

		// for ($i=0; $i < $array_count; $i++) { 
			
			
		// 	 $email=getEmpDetails($inputs['emp_id'][$i]);
			 

		// 	 // echo $manager_name['last_name'];
		// 	 // die;
			 
  //               /*
  //               Modify message to be more generic and allow to be sent to multiple people.
  //                */

  //           $members ="";
  //           for ($j=0; $j < $array_count; $j++) { 
  //       		$member_name=getEmpDetails($inputs['emp_id'][$j]);
  //       		$members = $members."<tr><td></td><td>".$member_name['last_name'].", ".$member_name['first_name']." ".$member_name['middle_name']."</td></tr>";
  //       	}
  //       	// echo "<table>".$members."<table>";
  //       	// die;
  //       	if (!empty($inputs['team_leader'])) {
		// 		$team_leader_info = getEmpDetails($inputs['team_leader']);
		// 		$team_leader_name = $team_leader_info['last_name'].", ".$team_leader_info['first_name']." ".$team_leader_info['middle_name'];
		// 	} else {
		// 		$team_leader_name = "-";
		// 	}
  //       	$message="Good day,<br/> You have been selected for this project \"$proj\". 
  //       	<table class='table table-bordered table-condensed table-hover'><tr>
  //       	<td valign='top'>Manager: </td>
  //       	<td>".$manager_name['last_name'].", ".$manager_name['first_name']." ".$manager_name['middle_name']."</td>
  //       	</tr>
  //       	<tr>
		// 	<td valign='top'>Team Lead: </td>
		// 	<td>".$team_leader_name."</td>
		// 	</tr><tr>
  //       	<td valign='top'>Member/s: </td>
  //       	<td>
  //       	".$members."
  //       	</td></tr></table>
  //       	<br>
  //       	For more details please login to the Secret 6 HRIS.";
        	
  //       	$message=email_template($header,$message);
                
  //           // die;
                
  //           /*
  //           Email Recepients 
  //            */
  //       	PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($email['private_email'],$email['work_email']), "Secret 6 New Project",$message,$email_settings['host'],$email_settings['port']);
       
				
			
			
		// }
  //     	if (!empty($manager_name)) {
  //     		PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($manager_name['private_email'],$manager_name['work_email']), "Secret 6 New Project",$message,$email_settings['host'],$email_settings['port']);

  //     	}
  //     	if (!empty($team_leader_info)) {
  //     		PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($team_leader_info['private_email'],$team_leader_info['work_email']), "Secret 6 New Project",$message,$email_settings['host'],$email_settings['port']);

  //     	}

		Alert("Project successfully created","success");

		redirect("project_management.php");

	} else{

			// var_dump($inputs);
			// die;
			$project_id = $inputs['project_id'];	

			$current_employee=$_SESSION[WEBAPP]['user']['employee_id'];

			$date = new DateTime();
		
			$date_applied=date_format($date, 'Y-m-d');

			$reset_emp=$con->myQuery("SELECT * FROM project_employee_history WHERE project_id=".$project_id ." AND (removed_by is null or removed_by = '0')");
			while($rows3 = $reset_emp->fetch(PDO::FETCH_ASSOC)):
					$con->myQuery("UPDATE project_employee_history SET is_manager='0', is_team_lead_ba='0', is_team_lead_dev='0' WHERE id=".$rows3['id']);
			endwhile;

			$validate_changes=$con->myQuery("SELECT is_manager, is_team_lead_ba,is_team_lead_dev, employee_id, is_deleted FROM projects_employees WHERE is_deleted=0 AND project_id=".$project_id);
			// $validate_employee_project_request=$con->myQuery("SELECT id, requested_employee_id, project_id, is_deleted, status_id FROM project_requests WHERE is_deleted=0 AND project_id=".$inputs['id']);
			// if ($inputs['manager'] != ($validate_changes['employee_id'] && $validate_changes['is_manager']=1)) {
			// 	echo "GG";
			// }
			$member_count = count($inputs['emp_id']);
			$member_counter =0;
			$proj=$inputs['proj_name'];
			$members ="";
			$manager_name=getEmpDetails($inputs['manager']);

			$email_settings=getEmailSettings();

	        $array_count = count($inputs['emp_id']);

		
			// $header="Secret 6 Project";
			// for ($k=0; $k < $array_count; $k++) { 
			// 	$member_name=getEmpDetails($inputs['emp_id'][$k]);
			// 	$members = $members."<tr><td></td><td>".$member_name['last_name'].", ".$member_name['first_name']." ".$member_name['middle_name']."</td></tr>";
			// }
			// if (!empty($inputs['team_leader'])) {
			// 	$team_leader_info = getEmpDetails($inputs['team_leader']);
			// 	$team_leader_name = $team_leader_info['last_name'].", ".$team_leader_info['first_name']." ".$team_leader_info['middle_name'];
			// } else {
			// 	$team_leader_name = "-";
			// }
			// $message="Good day,<br/> You have been added for this project \"$proj\". 
			// <table class='table table-bordered table-condensed table-hover'><tr>
			// <td valign='top'>Manager: </td>
			// <td>".$manager_name['last_name'].", ".$manager_name['first_name']." ".$manager_name['middle_name']."</td>
			// </tr>
			// <tr>
			// <td valign='top'>Team Lead: </td>
			// <td>".$team_leader_name."</td>
			// </tr><tr>
			// <td valign='top'>Member/s: </td>
			// <td>
			// ".$members."
			// </td></tr></table>
			// <br>
			// For more details please login to the Secret 6 HRIS.";
						        	
			// $message=email_template($header,$message);

			
			// while($rows = $validate_changes->fetch(PDO::FETCH_ASSOC)):

			// 	//manager email
				

			// 	if ($rows['is_manager'] == 1 && $rows['is_deleted'] == 0) {
					
			// 		if ($inputs['manager'] != $rows['employee_id']) {
			// 			$manager_email=getEmpDetails($inputs['manager']);

			// 			insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['last_name']} set ".$manager_email['first_name']." ".$manager_email['middle_name']." ".$manager_email['last_name']." as project manager to project \"$proj\".");

			// 			PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($manager_email['private_email'],$manager_email['work_email']), "Secret 6 Project Management (You have been set as manager)",$message,$email_settings['host'],$email_settings['port']);
			// 		}
			// 	}

			// 	//team lead email

			// 	if(!empty($inputs['team_leader'])) {
			// 		if ($rows['is_team_lead'] == 1 && $rows['is_deleted'] == 0) {
						
			// 			if ($inputs['team_leader'] != $rows['employee_id']) {
							
			// 				$team_lead_email=getEmpDetails($inputs['team_leader']);

			// 				insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['last_name']} set ".$team_lead_email['first_name']." ".$team_lead_email['middle_name']." ".$team_lead_email['last_name']." as project leader to project \"$proj\".");

			// 				PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($team_lead_email['private_email'],$team_lead_email['work_email']), "Secret 6 Project Management (You have been set as team leader)",$message,$email_settings['host'],$email_settings['port']);
							
			// 			} else {
			// 				$team_lead_change = "false";
			// 			}
			// 		}
			// 	}

			// 	//project member remove

				

	
			// 	for ($a=0; $a < $member_count; $a++) {
			// 		if ($rows['is_deleted'] == 0) {
			// 			if ($inputs['emp_id'][$a] == $rows['employee_id']) {
			// 				$member_counter = $member_counter +1;

			// 			}
			// 		}

			// 	}
			// 	if ($member_counter < 1) {
			// 		//echo $rows['employee_id'];
					
			// 		$emp_name=getEmpDetails($rows['employee_id']);

			// 		$get_start_date=$con->myQuery("SELECT id, employee_id, project_id, start_date FROM project_employee_history WHERE employee_id=".$rows['employee_id'] . " AND project_id=".$project_id);
			// 		while($rows1 =$get_start_date->fetch(PDO::FETCH_ASSOC)):
			// 			if (empty($rows1['removed_by'])) {

			// 				$project_history_id = $rows1['id'];
			// 			}
					
			// 		endwhile;

			// 		if (!empty($project_history_id)) {

					
			// 		$con->myQuery("UPDATE project_employee_history SET end_date='$date_applied', removed_by='$current_employee' WHERE id=".$project_history_id);
			// 		}


			// 		insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['last_name']} remove ".$emp_name['first_name']." ".$emp_name['middle_name']." ".$emp_name['last_name']." from project \"$proj\".");

			// 		$message_remove="Good day,<br/> You have been removed from project \"$proj\" by {$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['middle_name']} {$_SESSION[WEBAPP]['user']['last_name']}. For more details please login to the Secret 6 HRIS.";
	  //              	$message_remove=email_template($header,$message_remove);

	  //              	PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($emp_name['private_email'],$emp_name['work_email']), "Secret 6 Project Management",	$message_remove,$email_settings['host'],$email_settings['port']);
			// 		}
			// 		$member_counter =0;
			// endwhile;
		
			// if(!empty($inputs['team_leader']) && empty($team_lead_email) && empty($team_lead_change)) {

			// 	$team_lead_email=getEmpDetails($inputs['team_leader']);
				
			// 	insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['last_name']} set ".$team_lead_email['first_name']." ".$team_lead_email['middle_name']." ".$team_lead_email['last_name']." as project leader to project \"$proj\".");

			// 	PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($team_lead_email['private_email'],$team_lead_email['work_email']), "Secret 6 Project Management",$message,$email_settings['host'],$email_settings['port']);
			// 	// echo "GG";
			// }
			
			for ($c=0; $c < $member_count; $c++) {
					if ($inputs['emp_id'][$c] == $inputs['manager']) {
						$mana_emp = "false";
					}
					if (!empty($inputs['team_lead_ba'])) {
						if ($inputs['emp_id'][$c] == $inputs['team_lead_ba']) {
							$tl_emp = "false";

						}

					}
					if (!empty($inputs['team_lead_dev'])) {
						if ($inputs['emp_id'][$c] == $inputs['team_lead_dev']) {
							$tl_emp1 = "false";

						}

					}

			}
			// if(empty($mana_emp) && empty($tl_emp)) {
			// 	$mana_emp_saved = "true";
			// 	$tl_emp_saved = "true";
			// 	$mana_emp_id = $inputs['manager'];

			// 	$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead) VALUES ('$project_id','$mana_emp_id','$date_applied','$current_employee','1','1')");
			// }
			// else

			if (empty($mana_emp) ) {
					$mana_emp_saved = "true";
					$mana_emp_id = $inputs['manager'];

					$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$mana_emp_id','$date_applied','$current_employee','1')");
			}
			elseif (empty($tl_emp) ) {
					$tl_emp_saved = "true";
					$tl_emp_id = $inputs['team_lead_ba'];

					$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_ba) VALUES ('$project_id','$tl_emp_id','$date_applied','$current_employee','1')");
			}
			elseif (empty($tl_emp1) ) {
					$tl_emp_saved = "true";
					$tl_emp_id = $inputs['team_lead_dev'];

					$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_dev) VALUES ('$project_id','$tl_emp_id','$date_applied','$current_employee','1')");
			}
			// echo $mana_emp;
			// echo $tl_emp;
			// die;
			

			for ($b=0; $b < $member_count; $b++) {

				$emp_id_validate = $inputs['emp_id'][$b];
				$tl_id_validate_ba =$inputs['team_lead_ba'];
				$tl_id_validate_dev =$inputs['team_lead_dev'];
				$mana_id_validate =$inputs['manager'];
				
				$validate_added_history=$con->myQuery("SELECT * FROM project_employee_history WHERE project_id=".$project_id ." AND (removed_by is null or removed_by = '0')");

				while($rows2 = $validate_added_history->fetch(PDO::FETCH_ASSOC)):
					if ($rows2['employee_id'] == $emp_id_validate) {
						$new_employee = "false";
						$emp_id_his = $rows2['id'];
					} 
					// if ($rows2['employee_id'] == $tl_id_validate) {
					// 	$new_employee = "false";
					// 	$tl_id_his = $rows2['id'];
					// } 
				endwhile;

				if (!empty($new_employee)) {
					

					if ($inputs['manager'] == $emp_id_validate) {

						
						$manager_ctr="false";


						$con->myQuery("UPDATE project_employee_history SET is_manager='1' WHERE id=".$emp_id_his);

					}
					if ($inputs['team_lead_ba'] == $emp_id_validate) {
					
						$team_lead_ctr = "false";;

						


						$con->myQuery("UPDATE project_employee_history SET is_team_lead_ba='1' WHERE id=".$emp_id_his);
						

						// $con->commit;
						 //die;

					} if ($inputs['team_lead_dev'] == $emp_id_validate) {
					
						$team_lead_ctr1 = "false";;

						


						$con->myQuery("UPDATE project_employee_history SET is_team_lead_dev='1' WHERE id=".$emp_id_his);
						

						// $con->commit;
						 //die;

					} 

					

					if ($inputs['manager'] == $emp_id_validate && $inputs['team_lead_ba'] == $emp_id_validate && $inputs['team_lead_dev'] == $emp_id_validate) {

						$con->myQuery("UPDATE project_employee_history SET is_team_lead_ba='1', is_team_lead_dev='1', is_manager='1'  WHERE id=".$emp_id_his);
					}



				} elseif(empty($new_employee)) {


					if ($inputs['manager'] == $emp_id_validate && empty($mana_emp_saved) && $inputs['team_lead_ba'] == $emp_id_validate && $inputs['team_lead_dev'] == $emp_id_validate && empty($tl_emp_saved)) {

						$team_lead_ctr = "false";
						$manager_ctr="false";

						$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead_ba,is_team_lead_dev) VALUES ('$project_id','$emp_id_validate','$date_applied','$current_employee','1','1','1')");
					}
					elseif ($inputs['manager'] == $emp_id_validate && empty($mana_emp_saved)) {

						
						$manager_ctr="false";

						$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$emp_id_validate','$date_applied','$current_employee','1')");

					}// elseif (!empty($inputs['manager']) && !empty($new_manager) && empty($mana_emp_saved)) {
					// 		$manager_ctr="false";
					// 		$mana_id = $inputs['manager'];

					// 		$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$mana_id','$date_applied','$current_employee','1')");
					// }
						
					elseif ($inputs['team_lead_ba'] == $emp_id_validate && empty($tl_emp_saved)) {

							$team_lead_ctr = "false";

							$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_ba) VALUES ('$project_id','$emp_id_validate','$date_applied','$current_employee','1')");
					} //elseif (!empty($inputs['team_leader']) && $inputs['team_leader'] != $inputs['emp_id'][$b] && !empty($new_team_lead)  && empty($mana_tl_saved)) {
					// 		$team_lead_ctr = "false";
					// 		$tl_id = $inputs['team_leader'];

					// 		$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead) VALUES ('$project_id','$tl_id','$date_applied','$current_employee','1')");
					// }

					elseif ($inputs['team_lead_dev'] == $emp_id_validate && empty($tl_emp_saved)) {

							$team_lead_ctr1 = "false";

							$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_dev) VALUES ('$project_id','$emp_id_validate','$date_applied','$current_employee','1')");
					}

					if (empty($manager_ctr) && empty($team_lead_ctr) && empty($team_lead_ctr1)) {
							// echo "test".$empo."<br>";
							// die;
								$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by) VALUES ('$project_id','$emp_id_validate','$date_applied','$current_employee')");
					}



				}


				//die;
					// while($rows2 = $validate_added_history->fetch(PDO::FETCH_ASSOC)):
						
					// 	if (empty(strtotime($rows2['end_date']))) {

					// 		if ($inputs['emp_id'][$b] == $rows2['employee_id']) {
								

					// 			$new_history = "false";

								
					// 		} elseif($inputs['manager'] == $rows2['employee_id']) {

								

					// 			$new_manager = "false";
					// 			$con->myQuery("UPDATE project_employee_history SET is_manager='1' WHERE id=".$rows2['id']);

					// 		} elseif($inputs['team_leader'] == $rows2['employee_id']) {

							
					// 			$new_team_lead = "false";
					// 			$con->myQuery("UPDATE project_employee_history SET is_team_lead='1' WHERE id=".$rows2['id']);

					// 		}
								
							
					// 	}
							
					// 	//echo $new_history.$inputs['emp_id'][$b];
					// endwhile;

					//echo $new_history . $inputs['manager']."<br>";	

					// if (empty($new_history)) {

					// 	 echo "test".$inputs['emp_id'][$b]."<br>";
					// 	// die;
					// 	echo "<br>";
					// 	echo $empo = $inputs['emp_id'][$b];
					// 	if ($inputs['manager'] == $inputs['emp_id'][$b]) {

						
					// 	$manager_ctr="false";

					// 	$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$empo','$date_applied','$current_employee','1')");

					// 	} elseif (!empty($inputs['manager']) && !empty($new_manager) && empty($mana_emp_saved)) {
					// 		$manager_ctr="false";
					// 		$mana_id = $inputs['manager'];

					// 		$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$mana_id','$date_applied','$current_employee','1')");
					// 	}
						
					// 	if ($inputs['team_leader'] == $inputs['emp_id'][$b] && empty($mana_tl_saved)) {

					// 		$team_lead_ctr = "false";

					// 		$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead) VALUES ('$project_id','$empo','$date_applied','$current_employee','1')");

					// 	} elseif (!empty($inputs['team_leader']) && $inputs['team_leader'] != $inputs['emp_id'][$b] && !empty($new_team_lead)  && empty($mana_tl_saved)) {
					// 		$team_lead_ctr = "false";
					// 		$tl_id = $inputs['team_leader'];

					// 		$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead) VALUES ('$project_id','$tl_id','$date_applied','$current_employee','1')");
					// 	}

					// 	if (empty($manager_ctr) && empty($team_lead_ctr)) {
					// 		// echo "test".$empo."<br>";
					// 		// die;
					// 			$con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by) VALUES ('$project_id','$empo','$date_applied','$current_employee')");
					// 	}


					// }

					$new_history="";
					$new_manager = "";
					$manager_ctr = "";
					$team_lead_ctr = "";
					$new_team_lead = "";
					

			$email = getEmpDetails($inputs['emp_id'][$b]);
			$emp_request_id = $inputs['emp_id'][$b];

			
			
			$validate_added_employee=$con->myQuery("SELECT is_manager, is_team_lead_ba, is_team_lead_dev, employee_id, is_deleted FROM projects_employees WHERE is_deleted=0 AND project_id=".$project_id);
					while($row = $validate_added_employee->fetch(PDO::FETCH_ASSOC)):
						
						
						if ($inputs['emp_id'][$b] == $row['employee_id']) {
							$new_employee = "false";
							$inputs['emp_id'][$b];

						}
					endwhile;
			
					
					if (empty($new_employee)) {
						
						//echo "GG";
								
								
								insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "{$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['last_name']} add ".$email['first_name']." ".$email['middle_name']." ".$email['last_name']." to project \"$proj\".");

						            /*
						            Email Recepients to new employee
						             */
						        	PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($email['private_email'],$email['work_email']), "Secret 6 Project Management",$message,$email_settings['host'],$email_settings['port']);
							
						       
										
									
					}
					$new_employee = "";
				

			}
			
			//var_dump($validate_changes);
			 //die;
			$con->beginTransaction();	
			

			
			

			$data=$con->myQuery("SELECT project_id FROM projects_employees WHERE is_deleted=0");

			
			if (!empty($data)) {
				while($row = $data->fetch(PDO::FETCH_ASSOC)):
					if ($row['project_id'] == $project_id) {
						$con->myQuery("DELETE FROM projects_employees WHERE project_id=$project_id");
						// $con->commit();	
						// echo "test";
						// die;
					}
				endwhile;
			}

			$status_id=$con->myQuery("SELECT id FROM project_status  WHERE status_name=? LIMIT 1",array($inputs['status']))->fetch(PDO::FETCH_ASSOC);
			
			
			$proj = $inputs['proj_name'];
			$project_status_id = $status_id['id'];
			$manager = $inputs['manager'];
			$fapprover = $inputs['manager'];
			$sapprover = $inputs['sapprover'];
			$tapprover = $inputs['tapprover'];
			$date = new DateTime();
			$date_f=new DateTime($inputs['date_start']);
			$des = $inputs['description'];
            $man_hours = $inputs['man_hours'];
			$date_start=$date_f->format("Y-m-d");
            $team_lead_ba=$inputs['team_lead_ba'];
            $team_lead_dev=$inputs['team_lead_dev'];

			$date_now=date_format($date, 'Y-m-d');

			if ($status_id['id']  == 2) {

				$date_end = $date_now;
			} else {

				$date_end ='';
			}
// phase1 date
		$phase1 = new DateTime( $date_start );
    $t = $phase1->getTimestamp();
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
// phase3 date
    $daystoadd=($inputs['man_hours']/8);
    if (is_float($daystoadd)){
        $fordate=floor($daystoadd)+1;
    }else{
    	$fordate=$daystoadd;
    }
        $phase2 = new DateTime( $phase2 );
    $t = $phase2->getTimestamp();
        $addDay = 86400;
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

	// 			echo '<pre>';
	// print_r($inputs);
	// echo '</pre>';
	// die;
			

			
			$con->myQuery("UPDATE projects SET name='$proj',description='$des',start_date='$date_start',end_date='$date_end',project_status_id='$project_status_id',is_deleted='0',manager_id='$manager',first_approver_id='$fapprover',second_approver_id='$sapprover',third_approver_id='$tapprover',end_date='$phase8',man_hours='$man_hours', team_lead_ba='$team_lead_ba', team_lead_dev='$team_lead_dev' WHERE id=$project_id");
			



			
			$array_count = count($inputs['emp_id']);

	
		
			$ctr=0;
			$ctr2=0;
			$ctr3=0;
			for ($i=0; $i < $array_count; $i++) { 
				if ($inputs['manager'] == $inputs['emp_id'][$i]) {
						$ctr=1;
						$manager = 1;

				} else {

						$manager = 0;
				}
				if ($inputs['team_lead_ba'] == $inputs['emp_id'][$i]) {
						$ctr2=1;
						$team_lead_ba = 1;

				} else {

						$team_lead_ba = 0;
				}
				if ($inputs['team_lead_dev'] == $inputs['emp_id'][$i]) {
						$ctr2=1;
						$team_lead_dev = 1;

				} else {

						$team_lead_dev = 0;
				}
				$param=array(
					"project_id"=>$project_id,
					"employee_id"=>$inputs['emp_id'][$i],
					"manager"=>$manager,
					"team_lead_ba"=>$team_lead_ba,
					"team_lead_dev"=>$team_lead_dev
					);
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager,is_team_lead_ba,is_team_lead_dev) VALUES (:project_id,:employee_id,:manager,:team_lead_ba,:team_lead_dev)",$param);

				// $param1=array(
				// "project_id"=>$project_id,
				// "employee_id"=>$inputs['emp_id'][$i],
				// "manager"=>$manager,
				// "team_lead"=>$team_lead,
				// "date_start"=>$date_now,
				// 'added_by'=>$_SESSION[WEBAPP]['user']['employee_id'],
				// );

				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead) VALUES (:project_id,:employee_id,:date_start,:added_by,:manager,:team_lead)",$param1);

				
			}
			if ($inputs['manager'] == $inputs['team_lead_ba'] AND $inputs['manager'] == $inputs['team_lead_dev']) {
				// 			echo "7";
				// die;
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager,is_team_lead_ba,is_team_lead_dev,designation_id) VALUES ('$project_id','$manager_id','1','1','1','3')");
				$ctr=1;
				$ctr2=1;
				$ctr3=1;
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead_ba,is_team_lead_dev) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1','1','1')");
			}
			elseif($inputs['manager'] == $inputs['team_lead_ba']){
				// echo "6";
				// die;
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager,is_team_lead_ba,designation_id) VALUES ('$project_id','$manager_id','1','1','2')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_dev,designation_id) VALUES ('$project_id','$team_lead_dev','1','1')");
				// $ctr=1;
				// $ctr2=1;
				// $ctr3=1;
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead_ba) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1','1')");
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_dev) VALUES ('$project_id','$team_lead_dev','$date_applied','$added_by','1')");
			}
			elseif($inputs['manager'] == $inputs['team_lead_dev']){
				// echo "5";
				// die;
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager,is_team_lead_dev,designation_id) VALUES ('$project_id','$manager_id','1','1','1')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_ba,designation_id) VALUES ('$project_id','$team_lead_ba','1','2')");
				// $ctr=1;
				// $ctr2=1;
				// $ctr3=1;
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager,is_team_lead_dev) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1','1')");
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_ba) VALUES ('$project_id','$team_lead_ba','$date_applied','$added_by','1')");
			}
			elseif($inputs['team_lead_ba'] == $inputs['team_lead_dev']){
				// echo "4";
				// die;
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_ba,is_team_lead_dev,designation_id) VALUES ('$project_id','$team_lead_ba','1','1','3')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager) VALUES ('$project_id','$manager_id','1')");
				// $ctr=1;
				// $ctr2=1;
				// $ctr3=1;
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_ba,is_team_lead_dev) VALUES ('$project_id','$team_lead_ba','$date_applied','$added_by','1','1')");
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1')");
			}
			else{
				$manager_id = $inputs['manager'];
				$team_lead_ba = $inputs['team_lead_ba'];
				$team_lead_dev = $inputs['team_lead_dev'];
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_ba,designation_id) VALUES ('$project_id','$team_lead_ba','1','2')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_team_lead_dev,designation_id) VALUES ('$project_id','$team_lead_dev','1','1')");
				$con->myQuery("INSERT INTO projects_employees (project_id,employee_id,is_manager) VALUES ('$project_id','$manager_id','1')");
				// $ctr=1;
				// $ctr2=1;
				// $ctr3=1;
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_ba) VALUES ('$project_id','$team_lead_ba','$date_applied','$added_by','1')");
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_team_lead_dev) VALUES ('$project_id','$team_lead_dev','$date_applied','$added_by','1')");
				// $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,is_manager) VALUES ('$project_id','$manager_id','$date_applied','$added_by','1')");
			}
			$con->myQuery("UPDATE project_phase_dates SET `date_end`='$phase1_date', `date_start`='$date_start', `designation_id`=? WHERE project_id='$project_id' AND project_phase_id=1",array($phase1_des['designation_id']));
			$con->myQuery("UPDATE project_phase_dates SET `date_end`='$phase2_date', `date_start`='$phase2_start', `designation_id`=? WHERE project_id='$project_id' AND project_phase_id=2",array($phase2_des['designation_id']));
			$con->myQuery("UPDATE project_phase_dates SET `date_end`='$phase3_date', `date_start`='$phase3_start', `designation_id`=? WHERE project_id='$project_id' AND project_phase_id=3",array($phase3_des['designation_id']));
			$con->myQuery("UPDATE project_phase_dates SET `date_end`='$phase4_date', `date_start`='$phase4_start', `designation_id`=? WHERE project_id='$project_id' AND project_phase_id=4",array($phase4_des['designation_id']));
			$con->myQuery("UPDATE project_phase_dates SET `date_end`='$phase5_date', `date_start`='$phase5_start', `designation_id`=? WHERE project_id='$project_id' AND project_phase_id=5",array($phase5_des['designation_id']));
			$con->myQuery("UPDATE project_phase_dates SET `date_end`='$phase6_date', `date_start`='$phase6_start', `designation_id`=? WHERE project_id='$project_id' AND project_phase_id=6",array($phase6_des['designation_id']));
			$con->myQuery("UPDATE project_phase_dates SET `date_end`='$phase7_date', `date_start`='$phase7_start', `designation_id`=? WHERE project_id='$project_id' AND project_phase_id=7",array($phase7_des['designation_id']));
			$con->myQuery("UPDATE project_phase_dates SET `date_end`='$phase8_date', `date_start`='$phase8_start', `designation_id`=? WHERE project_id='$project_id' AND project_phase_id=8",array($phase8_des['designation_id']));
			$con->commit();	
			// die;
			Alert("Project successfully updated","success");

			redirect("frm_project_management.php?id=".$project_id);
			die;

	}	
}


?>