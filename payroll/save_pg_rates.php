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

	if($errors!="")
	{
		Alert($errors,"danger");
		if(empty($inputs['payroll_group_rate_id']))
		{
			redirect("frm_payroll_group_rate.php?pg_id=".urlencode($_POST['payroll_group_id']));
		}
		die;
	}

	if(empty($inputs['payroll_group_rate_id']))
	{
		try 
		{
			$con->beginTransaction();
			$inputs=$_POST;
			
			$params = array(
				'payroll_group_id' 		=> $inputs['payroll_group_id'],
				'rd_rate' 				=> $inputs['rd_rate'],
				'sh_rate' 				=> $inputs['sh_rate'],
				'rd_sh_rate' 			=> $inputs['rd_sh_rate'],
				'rh_rate' 				=> $inputs['rh_rate'],
				'rd_rh_rate' 			=> $inputs['rd_rh_rate'],
				'n_rate' 				=> $inputs['n_rate'],
				'o_ot_rate' 			=> $inputs['o_ot_rate'],
				'rd_ot_rate' 			=> $inputs['rd_ot_rate'],
				'sh_ot_rate' 			=> $inputs['sh_ot_rate'],
				'rd_sh_ot_rate' 		=> $inputs['rd_sh_ot_rate'],
				'rh_ot_rate'			=> $inputs['rh_ot_rate'],						
				'rd_rh_ot_rate' 		=> $inputs['rd_rh_ot_rate']
				);

			$pg_id = $inputs['payroll_group_id'];
			
			$con->myQuery("UPDATE payroll_groups SET set_rates=1 WHERE payroll_group_id=?", array($pg_id));

			$con->myQuery("INSERT INTO payroll_group_rates(payroll_group_id,rd_rate,sh_rate,rd_sh_rate,rh_rate,rd_rh_rate,n_rate,o_ot_rate,rd_ot_rate,sh_ot_rate,rd_sh_ot_rate,rh_ot_rate,rd_rh_ot_rate) VALUES (:payroll_group_id,:rd_rate,:sh_rate,:rd_sh_rate,:rh_rate,:rd_rh_rate,:n_rate,:o_ot_rate,:rd_ot_rate,:sh_ot_rate,:rd_sh_ot_rate,:rh_ot_rate,:rd_rh_ot_rate)", $params);	

			$params_ps = array(
				'payroll_group_id' 			=> $inputs['payroll_group_id'],
				'salary_period' 			=> $inputs['salary_period'],
				'government_ded_period' 	=> $inputs['government_ded_period'],
				'tax_ded_period' 			=> $inputs['tax_ded_period'],
				'company_ded_period' 		=> $inputs['company_ded_period'],
				'minimum_wage' 				=> $inputs['minimum_wage'],
				'13th_month_release_date' 	=> date_format(date_create($inputs['13th_month_release_date']),'Y-m-d'),
				'first_cut_off' 			=> $inputs['first_cut_off'],
				'second_cut_off' 			=> $inputs['second_cut_off'],
				'days_per_month'			=> $inputs['days_per_month'],
				'sss_ded'					=> $inputs['sss_ded'],
				'philhealth_ded'			=> $inputs['philhealth_ded'],
				'pagibig_ded'				=> $inputs['pagibig_ded']
				);


			$con->myQuery("INSERT INTO payroll_settings(pay_group_id,salary_settings,government_settings,tax_settings,company_settings,minimum_wage,13th_month_release_date,days_per_month,first_cut_off,second_cut_off,sss_deduction,philhealth_deduction,pagibig_deduction) VALUES (:payroll_group_id,:salary_period,:government_ded_period,:tax_ded_period,:company_ded_period,:minimum_wage,:13th_month_release_date,:days_per_month,:first_cut_off,:second_cut_off,:sss_ded,:philhealth_ded,:pagibig_ded)", $params_ps);
			
			$con->commit();
			Alert("Rates saved successfully","success");
			redirect("frm_payroll_group_rate.php?pg_id=".urlencode($_POST['payroll_group_id']));

		} catch (Exception $e) {
			$con->rollBack();
			Alert("Save failed. Please try again.","danger");
			redirect("frm_payroll_group_rate.php?pg_id=".urlencode($_POST['payroll_group_id']));
		}
	}
	else{
// var_dump($_POST);
// die;
		try {
			$con->beginTransaction();
			$inputs=$_POST;
			
			$params = array(
				'payroll_group_rate_id' => $inputs['payroll_group_rate_id'],
				'rd_rate' 				=> $inputs['rd_rate'],
				'sh_rate' 				=> $inputs['sh_rate'],
				'rd_sh_rate' 			=> $inputs['rd_sh_rate'],
				'rh_rate' 				=> $inputs['rh_rate'],
				'rd_rh_rate' 			=> $inputs['rd_rh_rate'],
				'n_rate' 				=> $inputs['n_rate'],
				'o_ot_rate' 			=> $inputs['o_ot_rate'],
				'rd_ot_rate' 			=> $inputs['rd_ot_rate'],
				'sh_ot_rate' 			=> $inputs['sh_ot_rate'],
				'rd_sh_ot_rate' 		=> $inputs['rd_sh_ot_rate'],
				'rh_ot_rate'			=> $inputs['rh_ot_rate'],						
				'rd_rh_ot_rate' 		=> $inputs['rd_rh_ot_rate']
				);


			$con->myQuery("UPDATE payroll_group_rates SET 
						   rd_rate=:rd_rate,
						   sh_rate=:sh_rate,
						   rd_sh_rate=:rd_sh_rate,
						   rh_rate=:rh_rate,
						   rd_rh_rate=:rd_rh_rate,
						   n_rate=:n_rate,
						   o_ot_rate=:o_ot_rate,
						   rd_ot_rate=:rd_ot_rate,
						   sh_ot_rate=:sh_ot_rate,
						   rd_sh_ot_rate=:rd_sh_ot_rate,
						   rh_ot_rate=:rh_ot_rate,
						   rd_rh_ot_rate=:rd_rh_ot_rate 
						   WHERE payroll_group_rate_id=:payroll_group_rate_id",$params);


			$params_ps = array(
				'payroll_group_id' 			=> $inputs['payroll_group_id'],
				'pg_sal_and_ded_id' 		=> $inputs['pg_sal_and_ded_id'],
				'salary_period' 			=> $inputs['salary_period'],
				'government_ded_period' 	=> $inputs['government_ded_period'],
				'tax_ded_period' 			=> $inputs['tax_ded_period'],
				'company_ded_period' 		=> $inputs['company_ded_period'],
				'minimum_wage' 				=> $inputs['minimum_wage'],
				'13th_month_release_date' 	=> date_format(date_create($inputs['13th_month_release_date']),'Y-m-d'),
				'first_cut_off' 			=> $inputs['first_cut_off'],
				'second_cut_off' 			=> $inputs['second_cut_off'],
				'days_per_month'			=> $inputs['days_per_month'],
				'sss_ded'					=> $inputs['sss_ded'],
				'philhealth_ded'			=> $inputs['philhealth_ded'],
				'pagibig_ded'				=> $inputs['pagibig_ded']
				);
		

			$con->myQuery("UPDATE payroll_settings SET 
						   pay_group_id=:payroll_group_id,
						   salary_settings=:salary_period,
						   government_settings=:government_ded_period,
						   tax_settings=:tax_ded_period,
						   company_settings=:company_ded_period,
						   minimum_wage=:minimum_wage,
						   13th_month_release_date=:13th_month_release_date,
						   days_per_month=:days_per_month,
						   first_cut_off=:first_cut_off,
						   second_cut_off=:second_cut_off,
						   sss_deduction=:sss_ded,
						   philhealth_deduction=:philhealth_ded,
						   pagibig_deduction=:pagibig_ded
						   WHERE id=:pg_sal_and_ded_id",$params_ps);


		

			$con->commit();
			Alert("Rates saved successfully","success");
			redirect("frm_payroll_group_rate.php?pg_id=".urlencode($_POST['payroll_group_id']));
		

		} catch (Exception $e) {
			$con->rollBack();
			Alert("Save failed. Please try again.","danger");
			redirect("frm_payroll_group_rate.php?pg_id=".urlencode($_POST['payroll_group_id']));
		}
	}

	//redirect("view_company_rates.php");

	die();

	
}else{
	redirect('index.php');	
}

?>