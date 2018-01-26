<?php

require_once("../support/config.php");

if(!isLoggedIn()){
	toLogin();
	die();
}


if(!empty($_POST)){




	$inputs=$_POST;
	
	$inputs=array_map('trim', $inputs);

	$available=$con->myQuery("SELECT id FROM company_profile")->fetch(PDO::FETCH_ASSOC);

	$inputs['id']=$available['id'];

		// var_dump($inputs);
		// die;

	if(empty($available)){
			unset($inputs['id']);
			$con->myQuery("INSERT INTO company_profile(name,address,email,contact_no,website,foundation_day,fax_no) VALUES (:n,:ad,:em,:cn,:wb,:fd,:fn)",$inputs);

			Alert("Company settings saved","success");
			redirect("company_settings.php");
			die();

	}else{
			$con->myQuery("UPDATE company_profile SET name=:n,address=:ad,email=:em,contact_no=:cn,website=:wb,foundation_day=:fd,fax_no=:fn WHERE id=:id",$inputs);

			Alert("Company settings saved","success");
			redirect("company_settings.php");
			die();

	}

		



}else{

redirect("company_settings.php");
			die();

}

?>