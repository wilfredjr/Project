<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }

     if(!AllowUser(array(1,2))){
         redirect("index.php");
     }


		if(!empty($_POST)){
		//Validate form inputs
		$inputs=$_POST;
		$inputs=array_map('trim', $inputs);
		
		$ba_test=$inputs['tester'];
		$dev_control=$inputs['developer'];
		if(!empty($ba_test)){
			$employee_id=$ba_test;
			$designation="2";
		}elseif(!empty($dev_control)){
			$employee_id=$dev_control;
			$designation="1";
		}

		$errors="";
		$validate=$con->myQuery("SELECT id FROM  project_bug_employee WHERE designation_id=? AND bug_list_id=? AND (request_status_id=1 OR request_status_id=3)",array($designation,$inputs['id']))->fetch(PDO::FETCH_ASSOC);

		if(!empty($validate)){
			$errors.="A request has already been sent. Please check Bug Employee Request. <br>";
		}

		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id'])){
				redirect("index.php");
			}
			else{
				redirect("bugs_view.php?id=".urlencode($inputs['id']));
			}
			die;
		}
		else{
			//IF id exists update ELSE insert				
			//Update
				$employee=$_SESSION[WEBAPP]['user']['employee_id'];
				$date = new DateTime(); 
	            $date_approved=date_format($date, 'Y-m-d');
				$current=$con->myQuery("SELECT * FROM  project_bug_list WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

				 $params=array(
                                            'proj_id'=>$current['project_id'],
                                            'emplo_id'=>$employee_id,
                                            'bug_name'=>$current['name'],
                                            'bug_desc'=>$current['description'],
                                            'date_applied'=>$date_approved,
                                            'stat'=>"1",
                                            'requested_by'=>$employee,
                                            'manager'=>$current['manager_id'],
                                            'admin'=>$current['admin_id'],
                                            'designation'=>$designation,
                                            'step_id'=>"2",
                                            'bug_rate_id'=>$current['bug_rate_id'],
                                            'bug_list_id'=>$current['id']
                                            );

                                        $con->myQuery("INSERT INTO project_bug_employee (project_id, bug_name, bug_desc, employee_id, requested_by, designation_id, request_status_id, date_filed, manager_id, admin_id, step_id, bug_rate_id, bug_list_id) VALUES (:proj_id, :bug_name, :bug_desc, :emplo_id, :requested_by, :designation, :stat, :date_applied, :manager, :admin, :step_id, :bug_rate_id, :bug_list_id)",$params);

			Alert("Reqeuest has been sent","success");
			redirect("bugs_view.php?id=".urlencode($inputs['id']));
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>