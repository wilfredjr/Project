<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}

    if(!AllowUser(array(1,4))){
        redirect("index.php");
    }


	if(!empty($_POST)){
		//Validate form inputs
		$inputs=$_POST;
		if(!empty($inputs['emp_id'])){
		$employee_user=$con->myQuery("SELECT * FROM users WHERE is_deleted=0 and employee_id=?",array($inputs['emp_id']))->fetch(PDO::FETCH_ASSOC);}
		$uname=$con->myQuery("SELECT id,lcase(username) FROM users WHERE is_deleted=0 and username=?",array(strtolower($inputs['username'])));

		$errors="";

		if (empty(trim($inputs['username']))){
			$errors.="Enter Username. <br/>";
		}
		if (empty(trim($inputs['password']))){
			$errors.="Enter Password. <br/>";
		}
		if($employee_user['user_type_id']!=4){
		if (empty($inputs['utype_id'])){
			$errors.="Select User Type. <br/>";
		}
		}
		if (empty(trim($inputs['fname']))){
			$errors.="Enter First Name. <br/>";
		}
		if (empty(trim($inputs['lname']))){
			$errors.="Enter Last Name. <br/>";
		}

		// var_dump($inputs);
		// die;
		// if(empty($inputs['get_id'])){
		// 	if (empty($inputs['emp_id'])){
		// 		$errors.="Select Employee. <br/>";
		// 	}
		// 	if ($employee_user->fetchcolumn() > 0) {
		// 		$errors.="Selected Employee already has an Account. <br />";
		// 	}
		// }

		$uname=$con->myQuery("SELECT id,lcase(username) FROM users WHERE is_deleted=0 and username=?",array(strtolower($inputs['username'])))->fetch(PDO::FETCH_ASSOC);

		if(!empty($uname)){
			if(empty($inputs['get_id'])){
				$errors.="Entered Username is not available.";
			}
			elseif(!empty($inputs['get_id']) && $uname['id']<>$inputs['get_id']){
				$errors.="Entered Username is not available.";
			}
		}

		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id'])){
				redirect("frm_users.php");
			}
			else{
				redirect("frm_users.php?id=".urlencode($inputs['id']));
			}
			die;
		}
		else{
			unset($inputs['get_id']);
			unset($inputs['con_password']);
			//IF id exists update ELSE insert
			$inputs['password']=encryptIt($inputs['password']);
			if($employee_user['user_type_id']=='4'){
				$inputs['utype_id']='4';
			}
			// var_dump($inputs);
			if(empty($inputs['id'])){
				//Insert
				unset($inputs['id']);
				if($inputs['utype_id']=='3'){
					$pay_grade='1';}else{
						$pay_grade='0';
					}
				$con->myQuery("INSERT INTO employees(first_name,middle_name,last_name,pay_grade_id,utype_id) VALUES(?,?,?,'$pay_grade',?)",array($inputs['fname'],$inputs['mname'],$inputs['lname'],$inputs['utype_id']));
				$last_id=$con->lastInsertId();
				$inputs['emp_id']=$last_id;
				$con->myQuery("INSERT INTO users(first_name,middle_name,last_name,employee_id,username,password,user_type_id,password_question,password_answer) VALUES(:fname,:mname,:lname,:emp_id,:username,:password,:utype_id,:pass_q,:pass_a)",$inputs);
			}
			else{
				//Update
				if($inputs['utype_id']=='3'){
					$pay_grade='1';}else{
						$pay_grade='0';
					}
				$con->myQuery("UPDATE employees SET first_name=?,middle_name=?,last_name=?,pay_grade_id='$pay_grade',utype_id=? WHERE id=?", array($inputs['fname'],$inputs['mname'],$inputs['lname'],$inputs['utype_id'],$inputs['emp_id']));
				unset($inputs['emp_id']);
				$con->myQuery("UPDATE users SET first_name=:fname,middle_name=:mname,last_name=:lname,username=:username,password=:password,user_type_id=:utype_id,password_question=:pass_q,password_answer=:pass_a WHERE id=:id",$inputs);
			} 

			// die;
			Alert("Save succesful","success");
			redirect("users.php");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>