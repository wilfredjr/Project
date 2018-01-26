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
		$inputs=array_map('trim', $inputs);
	

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
				$ba_test=$inputs['tester'];
				$dev_control=$inputs['developer'];
				$con->myQuery("UPDATE project_bug_list SET ba_test=?,dev_control=? WHERE id=?",array($ba_test,$dev_control,$inputs['id']));

			Alert("Save succesful","success");
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