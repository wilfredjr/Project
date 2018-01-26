<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}

    if(!AllowUser(array(1))){
        redirect("index.php");
    }


		if(!empty($_POST)){
		//Validate form inputs
		$inputs=$_POST;
		$inputs=array_map('trim', $inputs);
		$errors="";
		if (empty($inputs['name'])){
			$errors.="Enter Leave Type. <br/>";
		}
		
		if (!empty($inputs['is_pay'])) 
		{
			$inputs['is_pay']=1;
		}else
		{
			$inputs['is_pay']=0;
		}
		
		if (!empty($inputs['is_convertable'])) 
		{
			$inputs['is_convertable']=1;
		}else
		{
			$inputs['is_convertable']=0;
		}

		// var_dump($inputs);
		// die();

		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id']))
			{
				redirect("frm_leave_type.php");
			}else
			{
				redirect("frm_leave_type.php?id=".urlencode($inputs['id']));
			}
			die;
		}else
		{
			if(empty($inputs['id'])){
				//Insert
				unset($inputs['id']);
				$con->myQuery("INSERT INTO 
						leaves(name,is_pay,is_convertable) 
						VALUES(:name,:is_pay,:is_convertable)",$inputs);
			}else
			{
				//Update
				$con->myQuery("UPDATE leaves 
				 	SET name=:name,
				 		is_pay=:is_pay,
				 		is_convertable=:is_convertable
				 	WHERE id=:id",$inputs);
			}
			Alert("Save succesful","success");
			redirect("leave_type.php");
		}
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>