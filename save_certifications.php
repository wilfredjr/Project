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

		$errors="";
		if (empty($inputs['name'])){
			$errors.="<li>Enter Certification Name.</li>";
		}
		if (empty($inputs['description'])){
			$errors.="<li>Enter Description. </li>";
		}


		if($errors!=""){

			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id'])){
				redirect("certifications.php");
			}
			else{
				redirect("certifications.php?id=".urlencode($inputs['id']));
			}
			die;
		}
		else{
			//IF id exists update ELSE insert
			if(empty($inputs['id'])){
				//Insert
				unset($inputs['id']);
				
				$con->myQuery("INSERT INTO certifications(name,description) VALUES(:name,:description)",$inputs);
			}
			else{
				//Update
				
				$con->myQuery("UPDATE certifications SET name=:name,description=:description WHERE id=:id",$inputs);
			}

			Alert("Save succesful","success");
			redirect("certifications.php");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>