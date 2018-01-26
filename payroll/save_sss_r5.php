<?php
require_once '../support/config.php';

if(!isLoggedIn())
{
	toLogin();
	die();
}
//     if(!hasAccess(3)){
//   redirect("index.php");
// }
// 	if(!AllowUser(array(1,2,5))){
// 		redirect("index.php");
// 	}
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// die;

if(!empty($_POST)){
		//Validate form inputs
	$inputs=$_POST;
	// echo "<pre>";
	// print_r($inputs);
	// echo "</pre>";
	// die();
	$errors="";
	// redirect("frm_sss_r5.php");
	// Alert("Save failed. Please try again.","danger");
	// die;

	

	if(empty($inputs['ref_no']))
	{
		try {
			// var_dump($inputs['ref_no']);
			// die;
			$con->beginTransaction();
			$inputs=$_POST;
			//check underpayment
			if(empty($inputs['underpayment'])){
				$inputs['underpayment']=0;
				$inputs['remarks']= "";
			}
			//date conversion
			$inputs['month_year'] = $inputs['month_year'] . '-01';
			//$inputs['ss_contribution']=str_replace(',','',$inputs['ss_contribution']);
			//$inputs['ec_contribution']=str_replace(',','',$inputs['ec_contribution']);
			$inputs['SS']=number_format($inputs['SS'],2,".",'');
			$inputs['EC']=number_format($inputs['EC'],2,".",'');

			$params = array(
				'for_date_of' 			=> $inputs['month_year'],
				'ss_contribution' 		=> $inputs['ss_contribution'],
				'ec_contribution' 		=> $inputs['ec_contribution'],
				'amt_ss_contribution' 	=> $inputs['SS'],
				'amt_ec_contribution' 	=> $inputs['EC'],
				'w_underpayment' 		=> $inputs['underpayment'],
				'remarks' 				=> $inputs['remarks'],
				);
	// 		echo "<pre>";
	// print_r($params);
	// echo "</pre>";
	// die();

			$con->myQuery("INSERT INTO sss_r5_main(for_date_of,ss_contribution,ec_contribution,amt_ss_contribution,amt_ec_contribution,w_underpayment,remarks) VALUES (:for_date_of,:ss_contribution,:ec_contribution,:amt_ss_contribution,:amt_ec_contribution,:w_underpayment,:remarks)", $params);
			$ref_no = $con->lastInsertId();




			$con->commit();
			Alert("R5 files saved successfully","success");
			redirect("frm_sss_r5.php?ref_no=".urlencode($ref_no));


		} catch (Exception $e) {
			$con->rollBack();
			Alert("Save failed. Please try again.","danger");
			redirect("frm_sss_r5.php");
		}
		
	}
	else{
// var_dump($_POST);
// die;
		try 
		{
			$con->beginTransaction();
			$inputs=$_POST;
			//check underpayment
			if(empty($inputs['underpayment'])){
				$inputs['underpayment']=0;
				$inputs['remarks']= "";
			}
			//date conversi
			$inputs['month_year'] = $inputs['month_year'] . '-01';
			// $inputs['ss_contribution']=str_replace(',','',$inputs['ss_contribution']);
			// $inputs['ec_contribution']=str_replace(',','',$inputs['ec_contribution']);
			$inputs['SS']=number_format($inputs['SS'],2,".",'');
			$inputs['EC']=number_format($inputs['EC'],2,".",'');

			$params = array(
				'ref_no'				=> $inputs['ref_no'],
				'for_date_of' 			=> $inputs['month_year'],
				'ss_contribution' 		=> $inputs['ss_contribution'],
				'ec_contribution' 		=> $inputs['ec_contribution'],
				'amt_ss_contribution' 	=> $inputs['SS'],
				'amt_ec_contribution' 	=> $inputs['EC'],
				'w_underpayment' 		=> $inputs['underpayment'],
				'remarks' 				=> $inputs['remarks'],
				);

	// 	echo "<pre>";
	// print_r($params);
	// echo "</pre>";
	// die();


			$con->myQuery("UPDATE sss_r5_main SET for_date_of=:for_date_of,ss_contribution=:ss_contribution,ec_contribution=:ec_contribution,amt_ss_contribution=:amt_ss_contribution,amt_ec_contribution=:amt_ec_contribution,w_underpayment=:w_underpayment,remarks=:remarks WHERE ref_no=:ref_no",$params);

			$con->commit();

			Alert("R5 files saved successfully","success");
			redirect("frm_sss_r5.php?ref_no=".$inputs['ref_no']);


		} catch (Exception $e) {
			$con->rollBack();
			Alert("Save failed. Please try again.","danger");
			redirect("frm_sss_r5.php");
		}
	}

	//redirect("view_company_rates.php");

	die();

	
}else{
	redirect('index.php');	
}

?>