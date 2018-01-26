<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
	 	toLogin();
	 	die();
	}

	if(!empty($_POST))
	{
		
		$inputs=$_POST;
		$inputs=array_map("trim",$inputs);
		$errors="";


		if (empty($inputs['nature_of_business']))
		{
			$errors .= "Enter Nature of Income Payment. <br/>";
		}
		if (empty($inputs['atc_code'])) 
		{
			$errors .= "Enter ATC Code. <br/>";
		}


		if($errors!="")
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			if(empty($inputs['id']))
			{
				redirect("bir_1601_e_form.php");
			}
			else{
				redirect("bir_1601_e_form.php?id=".urlencode($inputs['id']));
			}
			die;
		}else
		{
			if(empty($inputs['id']))
			{
				unset($inputs['id']);
				$con->myQuery("INSERT INTO bir_1601_e_reference(nature_of_business,tax_rate,atc_type,atc_code) VALUES(:nature_of_business, :tax_rate, :atc_type, :atc_code)",$inputs);
			}else
			{
				$con->myQuery("UPDATE bir_1601_e_reference SET nature_of_business=:nature_of_business, tax_rate=:tax_rate, atc_type=:atc_type, atc_code=:atc_code WHERE id=:id",$inputs);
			}

			Alert("Save succesful","success");
			redirect("bir_1601_e_reference.php");
		}
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>