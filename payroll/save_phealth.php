<?php
require_once '../support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}


if(!empty($_POST)){
		//Validate form inputs

	$inputs=$_POST;
	$inputs=array_map('trim', $inputs);
	$errors="";

// var_dump($inputs);
// die;

	
	if(!empty($inputs['p_code'])){
		$available_code=$con->myQuery("SELECT ph_code FROM gd_philhealth WHERE ph_code=? AND is_deleted =0",array($inputs['p_code']))->fetch(PDO::FETCH_ASSOC);
		if(empty($available_code)){

			$con->myQuery("INSERT INTO gd_philhealth(ph_code,ph_from_comp,ph_to_comp,ph_ee,ph_er) VALUES (:p_code,:r_comp_from,:r_comp_to,:ee_share,:er_share)",$inputs);

			Alert("PhilHealth code registered","success");
			redirect("../payroll/view_phealth.php");
			die();


		}else{
			$errors.="PhilHealth code already exists";
			Alert("".$errors,"danger");
			redirect("../payroll/view_phealth.php");
			die();
		}


	}else if(!empty($inputs['p_code1'])){
		
		$con->myQuery("UPDATE gd_philhealth SET ph_from_comp=:r_comp_from1 ,ph_to_comp=:r_comp_to1 ,ph_ee=:ee_share1,ph_er=:er_share1 WHERE ph_code=:p_code1 ",$inputs);

			Alert("PhilHealth code updated","success");
			redirect("../payroll/view_phealth.php");
			die();
		}

	else{
		$errors.="No PhilHealth code submitted";
		Alert($errors,"danger");
		redirect("../payroll/view_phealth.php");
		die();
	}

	
	

redirect("../payroll/view_phealth.php");
	
}


?>