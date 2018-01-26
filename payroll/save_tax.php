<?php
require_once '../support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}


if(!empty($_POST)){
		//Validate form inputs
	if(!empty($_POST['txp'])){

$_POST['txp']=$_POST['txp']*0.010;


	}

	if(!empty($_POST['txp1'])){

$_POST['txp1']=$_POST['txp1']*0.010;

		
	}
	


	$inputs=$_POST;
	$inputs=array_map('trim', $inputs);
	$errors="";

// var_dump($inputs);
// die;

	
	if(!empty($inputs['t_code'])){
		$available_code=$con->myQuery("SELECT tax_code FROM taxes WHERE tax_code=? AND is_deleted =0",array($inputs['t_code']))->fetch(PDO::FETCH_ASSOC);
		if(empty($available_code)){

			$con->myQuery("INSERT INTO taxes(tax_code,tax_status,tax_operand,tax_amount_comp,tax_ceiling,tax_additional,tax_rate) VALUES (:t_code,:stat,:opr,:amtc,:cling,:adt,:txp)",$inputs);

			Alert("Tax code registered","success");
			redirect("../payroll/view_tax.php");
			die();


		}else{
			$errors.="Tax code already exists";
			Alert("".$errors,"danger");
			redirect("../payroll/view_tax.php");
			die();
		}


	}else if(!empty($inputs['t_code1'])){
		
		$con->myQuery("UPDATE taxes SET tax_status=:stat1 ,tax_operand=:opr1 ,tax_amount_comp=:amtc1,tax_ceiling=:cling1,tax_additional=:adt1,tax_rate=:txp1 WHERE tax_code=:t_code1 ",$inputs);

			Alert("Housing code updated","success");
			redirect("../payroll/view_tax.php");
			die();
		}

	else{
		$errors.="No Housing code submitted";
		Alert($errors,"danger");
		redirect("../payroll/view_tax.php");
		die();
	}

	
	

redirect("../payroll/view_tax.php");
	
}


?>