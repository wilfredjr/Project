<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}



	if(empty($_POST))
	{
		redirect('index.php');
		die;
	}else
	{	
		$inputs = $_POST;
		$errors = "";

		if (empty($inputs['input_detail_id'])) 
		{
			# ADD NEW

			$details = $con->myQuery("SELECT * FROM bir_1601_e_details WHERE id=?",array($inputs['input_1601e_details']))->fetch(PDO::FETCH_ASSOC);

			$params = array(
					"bir_1604_e_master_id" 		=> $inputs['input_id'],
					"bir_1601_e_details_id" 	=> $details['id'],
					"tin_tax_payer" 			=> $inputs['input_tin_tax_payer'],
					"name_payees" 				=> $inputs['input_name_payees'],
					"atc" 						=> $details['atc_code'],
					"nature_of_income_payment" 	=> $details['nature_of_business'],
					"tax_base"					=> $details['tax_base'],
					"tax_rate" 					=> $details['tax_rate'],
					"tax_withheld" 				=> $details['tax_withheld']
				);

			$con->myQuery("INSERT INTO bir_1604_e_schedule_4(bir_1604_e_master_id,bir_1601_e_details_id,tin_tax_payer,name_payees,atc,nature_of_income_payment,tax_base,tax_rate,tax_withheld)
										VALUES(:bir_1604_e_master_id,:bir_1601_e_details_id,:tin_tax_payer,:name_payees,:atc,:nature_of_income_payment,:tax_base,:tax_rate,:tax_withheld)",$params);
		
		}else
		{
			# EDIT

			$params = array(
					"id" 			=> $inputs['input_detail_id'],
					"tin_tax_payer" => $inputs['input_tin_tax_payer'],
					"name_payees" 	=> $inputs['input_name_payees']
				);

			$con->myQuery("UPDATE bir_1604_e_schedule_4 SET tin_tax_payer=:tin_tax_payer, name_payees=:name_payees WHERE id=:id",$params);
		}
		
		Alert("Successfully Saved.","success");
		redirect("report_bir_1604_e_view_2.php?id=".$inputs['input_id']);
		die();
	}
?>