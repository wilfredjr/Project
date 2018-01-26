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

	
	if(!empty($inputs['h_code'])){
		$available_code=$con->myQuery("SELECT hdmf_code FROM gd_hdmf WHERE hdmf_code=? AND is_deleted =0",array($inputs['h_code']))->fetch(PDO::FETCH_ASSOC);
		if(empty($available_code)){

			$con->myQuery("INSERT INTO gd_hdmf(hdmf_code,hdmf_from_comp,hdmf_to_comp,hdmf_cont_option,hdmf_ee,hdmf_er) VALUES (:h_code,:r_comp_from,:r_comp_to,:option,:ee_share,:er_share)",$inputs);

			Alert("Housing code registered","success");
			redirect("../payroll/view_housing.php");
			die();


		}else{
			$errors.="Housing code already exists";
			Alert("".$errors,"danger");
			redirect("../payroll/view_housing.php");
			die();
		}


	}else if(!empty($inputs['h_code1'])){
		
		$con->myQuery("UPDATE gd_hdmf SET hdmf_from_comp=:r_comp_from1 ,hdmf_to_comp=:r_comp_to1 ,hdmf_ee=:ee_share1,hdmf_er=:er_share1, hdmf_cont_option=:option1 WHERE hdmf_code=:h_code1 ",$inputs);

			Alert("Housing code updated","success");
			redirect("../payroll/view_housing.php");
			die();
		}

	else{
		$errors.="No Housing code submitted";
		Alert($errors,"danger");
		redirect("../payroll/view_housing.php");
		die();
	}

	
	

redirect("../payroll/view_hdmf.php");
	
}


?>