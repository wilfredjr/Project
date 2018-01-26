<?php
	require_once("../support/config.php");
	
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(!empty($_POST))
	{
	#VALIDATE INPUTS
		$inputs=$_POST;			
		$inputs=array_map('trim', $inputs);
		$error="";

		if (empty($inputs['input_nature_of_income_payment'])) 
		{
			$error .= "Enter Nature of Income Payment. </br>";
		}
		if (empty($inputs['input_tax_base'])) 
		{
			$error .= "Enter Tax Base Amount. </br>";
		}


		if ($error!="") 
		{
			Alert("You have the following errors: <br/>".$errors,"danger");
			redirect("report_bir_1601_e_view.php?id=".urlencode($inputs['input_id']));
			die;
		}else
		{
			$reference = $con->myQuery("SELECT id,nature_of_business,tax_rate,atc_code FROM bir_1601_e_reference WHERE id=?",array($inputs['input_atc_details']))->fetch(PDO::FETCH_ASSOC);
			$tax_rate 			= floatval($reference['tax_rate'])/100;
			$total_tax_withheld = floatval($inputs['input_tax_base'])*$tax_rate;
			
			if(empty($inputs['input_detail_id']))
			{
				# INSERT INTO
				$params = array(
						"master_id" 			=> $inputs['input_id'],
						"nature_of_business" 	=> $inputs['input_nature_of_income_payment'],
						"reference_id" 			=> $inputs['input_atc_details'],
						"atc_code" 				=> $reference['atc_code'],
						"tax_base" 				=> $inputs['input_tax_base'],
						"tax_rate" 				=> $reference['tax_rate'],
						"tax_withheld" 			=> $total_tax_withheld
					);
				
				$con->myQuery("INSERT INTO bir_1601_e_details(bir_1601_e_master_id,nature_of_business,reference_id,atc_code,tax_base,tax_rate,tax_withheld) 
													VALUES(:master_id,:nature_of_business,:reference_id,:atc_code,:tax_base,:tax_rate,:tax_withheld)",$params);
				
				Alert("Successfully Saved!","success");
			}else
			{
				#UPDATE
				$params2 = array(
						"id" 					=> $inputs['input_detail_id'],
						"nature_of_business" 	=> $inputs['input_nature_of_income_payment'],
						"reference_id" 			=> $inputs['input_atc_details'],
						"atc_code" 				=> $reference['atc_code'],
						"tax_base" 				=> $inputs['input_tax_base'],
						"tax_rate" 				=> $reference['tax_rate'],
						"tax_withheld" 			=> $total_tax_withheld
					);

				$con->myQuery("UPDATE bir_1601_e_details SET nature_of_business=:nature_of_business, reference_id=:reference_id, atc_code=:atc_code, tax_base=:tax_base, tax_rate=:tax_rate, tax_withheld=:tax_withheld WHERE id=:id",$params2);
		
				Alert("Successfully Updated!","success");
			}			
		}

		redirect("report_bir_1601_e_view.php?id=".$inputs['input_id']);
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>