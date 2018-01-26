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

	
	if(!empty($inputs['s_code'])){
		$available_code=$con->myQuery("SELECT sss_code FROM gd_sss WHERE sss_code=? AND is_deleted =0",array($inputs['s_code']))->fetch(PDO::FETCH_ASSOC);
		if(empty($available_code)){

			$con->myQuery("INSERT INTO gd_sss(sss_code,sss_from_comp,sss_to_comp,sss_ee,sss_er,sss_ec) VALUES (:s_code,:r_comp_from,:r_comp_to,:ee_share,:er_share,:e_comp)",$inputs);

			Alert("SSS code registered","success");
			redirect("../payroll/view_sss.php");
			die();


		}else{
			$errors.="SSS code already exists";
			Alert("".$errors,"danger");
			redirect("../payroll/view_sss.php");
			die();
		}


	}else if(!empty($inputs['s_code1'])){
		
		$con->myQuery("UPDATE gd_sss SET sss_from_comp=:r_comp_from1 ,sss_to_comp=:r_comp_to1 ,sss_ee=:ee_share1,sss_er=:er_share1,sss_ec=:e_comp1 WHERE sss_code=:s_code1 ",$inputs);

			Alert("SSS code updated","success");
			redirect("../payroll/view_sss.php");
			die();
		}

	else{
		$errors.="No SSS code submitted";
		Alert($errors,"danger");
		redirect("../payroll/view_sss.php");
		die();
	}

	
	

redirect("../payroll/view_sss.php");
	
}


?>