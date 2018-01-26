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
		$inputs=array_map("trim",$inputs);
		$errors="";
		if (empty($inputs['name'])){
			$errors.="Enter Employment Status Name. <br/>";
		}


		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id'])){
				redirect("frm_employment_status.php");
			}
			else{
				redirect("frm_employment_status.php?id=".urlencode($inputs['id']));
			}
			die;
		}
		else{
			//IF id exists update ELSE insert
			if(empty($inputs['id'])){
				//Insert
				unset($inputs['id']);
				
				$con->myQuery("INSERT INTO employment_status(name, is_regular) VALUES(:name,:is_regular)",$inputs);
			}
			else{
				//Update
				
				$con->myQuery("UPDATE employment_status SET name=:name,is_regular = :is_regular WHERE id=:id",$inputs);
			}
			// die;
			Alert("Save succesful","success");
			redirect("employment_status.php");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>