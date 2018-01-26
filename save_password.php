<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}

    //if(!AllowUser(array(2,3))){
    //    redirect("index.php");
    //}

	if(!empty($_POST)){
		//Validate form inputs
		$inputs=$_POST;

		$employee_user=$con->myQuery("SELECT username FROM users WHERE is_deleted=0 and employee_id<>?",array($inputs['emp_id']));
		//$uname=$con->myQuery("SELECT id,lcase(username) FROM users WHERE is_deleted=0 and username=?",array(strtolower($inputs['username'])));

		$errors="";

		if (empty($inputs['username'])){
			$errors.="Enter Username. <br/>";
		}
		if (empty($inputs['password'])){
			$errors.="Enter Password. <br/>";
		}

		while($row = $employee_user->fetch(PDO::FETCH_ASSOC)):
			if ($row['username'] == $inputs['username']) {
				$errors.="Username already exist.";
			}
		endwhile;
		
		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect("frm_change_pass.php");
			die;
		}
		else{
			unset($inputs['con_password']);
			$inputs['password']=encryptIt($inputs['password']);
			//var_dump($inputs);
			//die();
			$con->myQuery("UPDATE users SET username=:username,password=:password WHERE employee_id=:emp_id AND is_deleted=0",$inputs);

			// die;
			Alert("Save succesful","success");
			redirect("frm_change_pass.php");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>