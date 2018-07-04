<?php
require_once 'support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}
    $usertype=$con->myQuery("SELECT user_type_id FROM users WHERE employee_id=:employee_id",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
    if ($usertype!=5) {
        redirect("index.php");
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
            if(empty($_FILES['file']['name'])){
            Alert("No file selected.","danger");
            redirect("frm_bug_management.php?id=".urlencode($inputs['project_id']));
            die();
        }
            $required_fieds=array(
    //      "leave_id"=>"Select Type of Leave. <br/>",
            "bug_name"=>"Enter Project Name. <br/>",
            "bug_rate"=>"Select Bug Rating. <br/>",
            "desc"=>"Enter Description. <br/>",
            );
        $errors="";

        foreach ($required_fieds as $key => $value)
        {
            if(empty($inputs[$key]))
            {
                $errors.=$value;
            }
        }

	// echo $errors;
	// die;
	if($errors!=""){

			Alert("You have the following error/s: <br/>".$errors,"danger");
			if(empty($inputs['project_id'])){
				redirect("bug_management.php");
			}
			else{
				redirect("frm_bug_management.php?id=".urlencode($inputs['project_id']));
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
	if(!empty($inputs['project_id'])){
        $current=$con->myQuery("SELECT * FROM projects WHERE id=?",array($inputs['project_id']))->fetch(PDO::FETCH_ASSOC);
        $current1=$con->myQuery("SELECT days FROM project_bug_rate WHERE id=?",array($inputs['bug_rate']))->fetch(PDO::FETCH_ASSOC);
		$con->beginTransaction();	

		//unset($inputs['shifting_id']);
		$project_id = $inputs['project_id'];
		$manager = $inputs['manager_id'];
        $name=$inputs['bug_name'];
        $bug_rate=$inputs['bug_rate'];
        $team_lead_ba=$current['team_lead_ba'];
        $team_lead_dev=$current['team_lead_dev'];
		$date = new DateTime();
		$des = $inputs['desc'];
		$date_start=$date->format("Y-m-d");
		$date_applied=date_format($date, 'Y-m-d');
        $bug_days=$current1['days']-1;
// phase1 date
		$phase1 = new DateTime( $date_start );
    $t = $phase1->getTimestamp();
    $t=$t-86400;
        $addDay = 86400;
        do{
        $try=date('Y-m-d', ($t+$addDay));
                $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
        $nextDay = date('w', ($t+$addDay));
        $t = $t+$addDay;}
        while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
          $phase1_start=date('Y-m-d',$t);
      if($bug_days!=0){
            for($i=0; $i<$bug_days; $i++){
            $try=date('Y-m-d', ($t+$addDay));
                    $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
            $nextDay = date('w', ($t+$addDay));
            if($nextDay == 0 || $nextDay == 6 || !empty($holiday)) {
                $i--;
            }
            $t = $t+$addDay;
            }
        $phase1_date=date('Y-m-d',$t);
    }else{
        $phase1_date=$phase1_start;
    }
    // var_dump($date_start);
    // die;
    $phase1=date('Y-m-d',$t);
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
    $phase2=date('Y-m-d',$t);
    // var_dump($phase2_start,$phase2);
    // die;
     $phase2_date=date('Y-m-d',$t);
//phase 2 end

		$params=array(
			'project_id'=>$project_id,
			'employee_id'=>$_SESSION[WEBAPP]['user']['employee_id'],
            'name'=>$name,
            'bug_rate_id'=>$bug_rate,
			'date_start'=>$date_start,
            'date_end'=>$phase2,
			'description'=>$des,
			'date_applied'=>$date_applied,
			'proj_sta'=>"1",
			'manager'=>$manager,
			'team_lead_ba'=>$team_lead_ba,
			'team_lead_dev'=>$team_lead_dev,
			'cur_phase'=>"1",
			);

		$con->myQuery("INSERT INTO project_bug_list (employee_id,project_id,name,bug_rate_id,description,project_status_id,date_filed,date_start,date_end,manager_id,team_lead_ba, team_lead_dev,bug_phase_id) VALUES (:employee_id,:project_id,:name,:bug_rate_id,:description,:proj_sta,:date_start,:date_start,:date_end,:manager,:team_lead_ba,:team_lead_dev,:cur_phase)",$params);
		$bug_id = $con->lastInsertId();
		// var_dump($inputs['emp_id']);
		// die;
			$con->myQuery("INSERT INTO project_bug_phase_dates (project_id,bug_list_id,bug_phase_id,date_end,project_status_id,date_start) VALUES ('$project_id','$bug_id','1','$phase1_date','1','$phase1_start')");
			$con->myQuery("INSERT INTO project_bug_phase_dates (project_id,bug_list_id,bug_phase_id,date_end,project_status_id,date_start) VALUES ('$project_id','$bug_id','2','$phase2_date','3','$phase2_start')");	
		// die;
		$con->commit();	

                 try {  
                         $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
                        $con->beginTransaction();
                        $inputs1['file_name']=$_FILES['file']['name'];
                        $con->myQuery("INSERT INTO bug_files(file_name,date_modified,employee_id,project_id,bug_list_id) VALUES(:file_name,NOW(),'$employee_id','$project_id','$bug_id')",$inputs1);
                        $file_id=$con->lastInsertId();

                        $filename=$file_id.getFileExtension($_FILES['file']['name']);
                        move_uploaded_file($_FILES['file']['tmp_name'],"bug_files/".$filename);
                        $con->myQuery("UPDATE bug_files SET file_location=? WHERE id=?",array($filename,$file_id));
                        $con->commit();           
                        } catch (Exception $e) {
                      $con->rollBack();
            //        echo "Failed: " . $e->getMessage();
                      Alert("Upload failed. Please try again.","danger");
                      redirect($page);
                      die;
                    }
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

		Alert("Bug Successfully Created","success");

		redirect("bug_management_project.php?id=".urlencode($inputs['project_id']));

	} 
}


?>