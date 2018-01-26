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

		$errors="";
		if (empty($inputs['default_password']))
		{
			$errors.="Input Default Password. <br/>";
		}


		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect("default_pass.php");
			die;
		}
		else{
			$con->myQuery("UPDATE default_pass SET default_pass=:default_password WHERE id=1",$inputs);

			Alert("Save succesful","success");
			redirect("default_pass.php");
		}
		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>