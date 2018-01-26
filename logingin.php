<?php
	require_once 'support/config.php';
	if(!empty($_POST)){

		$user=$con->myQuery("SELECT users.id,users.user_type_id as 'user_type',e.first_name,e.middle_name,e.last_name,e.department_id,e.image,e.gender,e.id as employee_id,can_apply_for_meal_transpo,view_employee_leave_calendar,allow_overtime,access_project_management,can_manage_projects FROM `users` JOIN employees e ON e.id=users.employee_id LEFT JOIN pay_grade pg ON pg.id=e.pay_grade_id WHERE username=? AND password=? AND users.is_deleted=0 AND e.is_terminated=0 AND e.is_deleted=0 LIMIT 1",array($_POST['username'],encryptIt($_POST['password'])))->fetch(PDO::FETCH_ASSOC);
		if(!empty($_SESSION[WEBAPP]['attempt_no']) && $_SESSION[WEBAPP]['attempt_no']>1){
			Alert("Maximum login attempts achieved. <br/>Your account will be deactivated. </br> Contact your system administrator to retreive your password.","danger");
			UNSET($_SESSION[WEBAPP]['attempt_no']);
			$con->myQuery("UPDATE users SET is_active=0 WHERE username=?",array($_POST['username']));
			redirect("frmlogin.php");
			die;
		}

		if(empty($user)){
			Alert("Invalid Username/Password","danger");

			if(!empty($_SESSION[WEBAPP]['attempt_no'])){
				// setcookie("attempt_no",$_SESSION[WEBAPP]['attempt_no']+1,time()+(3600));
				$_SESSION[WEBAPP]['attempt_no']+=1;
			}
			else{
				$_SESSION[WEBAPP]['attempt_no']=1;
			}
			redirect('frmlogin.php');
		}
		else{
			$_SESSION[WEBAPP]['user']=$user;
			refresh_activity($_SESSION[WEBAPP]['user']['id']);
			redirect("index.php");	
		}

		

		
		die;
	}
	else{
		Modal("Invalid Username/Password");
		redirect('frmlogin.php');
		die();
	}
	redirect('frmlogin.php');
?>