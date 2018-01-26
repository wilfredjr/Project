<?php
require_once '../support/config.php';

// 	if(!isLoggedIn()){
// 		toLogin();
// 		die();
// 	}
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
	
	$errors="";

	if($errors!=""){

		Alert($errors,"danger");
		if(empty($inputs['payroll_group_rate_id'])){
			redirect("frm_payroll_group_rate.php?pg_id=".urlencode($_POST['payroll_group_id']));
			
		}else{
			redirect("frm_payroll_group_rate.php?pg_id=".urlencode($_POST['payroll_group_id']));
		}
		die;
	}


			
	if(empty($inputs['payroll_group_rate_id'])){

				//Insert
		try {
			$con->beginTransaction();
			$inputs=$_POST;
			unset($inputs['payroll_group_rate_id']);
			unset($inputs['payroll_group_name']);
			unset($inputs['pg_sal_and_ded_id']);
			unset($inputs['salary_period']);
			unset($inputs['government_ded_period']);
			unset($inputs['tax_ded_period']);
			unset($inputs['company_ded_period']);
			unset($inputs['minimum_wage']);
			unset($inputs['13th_month_release_date']);

				//$userid=$_SESSION[WEBAPP]['user']['id'];
				// var_dump($inputs);
				// die;
			$con->myQuery("UPDATE payroll_groups SET set_rates=1 WHERE payroll_group_id=?", array($inputs['payroll_group_id']));

			$con->myQuery("INSERT INTO payroll_group_rates(payroll_group_id,rd_rate,sh_rate,rd_sh_rate,rh_rate,rd_rh_rate,n_rate,o_ot_rate,rd_ot_rate,sh_ot_rate,rd_sh_ot_rate,rh_ot_rate,rd_rh_ot_rate) VALUES (:payroll_group_id,:rd_rate,:sh_rate,:rd_sh_rate,:rh_rate,:rd_rh_rate,:n_rate,:o_ot_rate,:rd_ot_rate,:sh_ot_rate,:rd_sh_ot_rate,:rh_ot_rate,:rd_rh_ot_rate)", $inputs);	

			//save periods
			$inputsForPeriods=$_POST;
			unset($inputsForPeriods['payroll_group_rate_id']);
			unset($inputsForPeriods['payroll_group_name']);
			unset($inputsForPeriods['pg_sal_and_ded_id']);
			unset($inputsForPeriods['rd_rate']);
			unset($inputsForPeriods['sh_rate']);
			unset($inputsForPeriods['rd_sh_rate']);
			unset($inputsForPeriods['rh_rate']);
			unset($inputsForPeriods['rd_rh_rate']);
			unset($inputsForPeriods['n_rate']);
			unset($inputsForPeriods['o_ot_rate']);
			unset($inputsForPeriods['rd_ot_rate']);
			unset($inputsForPeriods['sh_ot_rate']);
			unset($inputsForPeriods['rd_sh_ot_rate']);
			unset($inputsForPeriods['rh_ot_rate']);
			unset($inputsForPeriods['rd_rh_ot_rate']);
			$inputsForPeriods['13th_month_release_date']=date_format(date_create($inputsForPeriods['13th_month_release_date']),'Y-m-d');



		$con->myQuery("INSERT INTO payroll_settings(pay_group_id,salary_settings,government_settings,tax_settigs,company_settings,minimum_wage,13th_month_release_date,days_per_month,first_cut_off,second_cut_off) VALUES (:payroll_group_id,:salary_period,:government_ded_period,:tax_ded_period,:company_ded_period,:minimum_wage,:13th_month_release_date,:days_per_month,:first_cut_off,:second_cut_off)", $inputsForPeriods);


				// var_dump($con);
				// die;				
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
			unset($inputs['payroll_group_id']);
			unset($inputs['payroll_group_name']);
			unset($inputs['pg_sal_and_ded_id']);
			unset($inputs['salary_period']);
			unset($inputs['government_ded_period']);
			unset($inputs['tax_ded_period']);
			unset($inputs['company_ded_period']);
			unset($inputs['minimum_wage']);
			unset($inputs['13th_month_release_date']);

		$con->myQuery("UPDATE payroll_group_rates SET rd_rate=:rd_rate,sh_rate=:sh_rate,rd_sh_rate=:rd_sh_rate,rh_rate=:rh_rate,rd_rh_rate=:rd_rh_rate,n_rate=:n_rate,o_ot_rate=:o_ot_rate,rd_ot_rate=:rd_ot_rate,sh_ot_rate=:sh_ot_rate,rd_sh_ot_rate=:rd_sh_ot_rate,rh_ot_rate=:rh_ot_rate,rd_rh_ot_rate=:rd_rh_ot_rate WHERE payroll_group_rate_id=:payroll_group_rate_id",$inputs);

		$inputsForPeriods=$_POST;
			// var_dump($inputsForPeriods);
			// die;
			unset($inputsForPeriods['payroll_group_name']);
			unset($inputsForPeriods['payroll_group_rate_id']);
			unset($inputsForPeriods['rd_rate']);
			unset($inputsForPeriods['sh_rate']);
			unset($inputsForPeriods['rd_sh_rate']);
			unset($inputsForPeriods['rh_rate']);
			unset($inputsForPeriods['rd_rh_rate']);
			unset($inputsForPeriods['n_rate']);
			unset($inputsForPeriods['o_ot_rate']);
			unset($inputsForPeriods['rd_ot_rate']);
			unset($inputsForPeriods['sh_ot_rate']);
			unset($inputsForPeriods['rd_sh_ot_rate']);
			unset($inputsForPeriods['rh_ot_rate']);
			unset($inputsForPeriods['rd_rh_ot_rate']);
			
			 $inputsForPeriods['13th_month_release_date']=date_format(date_create($inputsForPeriods['13th_month_release_date']),'Y-m-d');
			//$test=new DateTime($inputsForPeriods['13th_month_release_date']);
			// $inputsForPeriods['13th_month_release_date']=$test->format('Y-m-d');
			// var_dump($inputsForPeriods);
			// die;
			$con->myQuery("UPDATE payroll_settings SET pay_group_id=:payroll_group_id,salary_settings=:salary_period,government_settings=:government_ded_period,tax_settings=:tax_ded_period,company_settings=:company_ded_period,minimum_wage=:minimum_wage,13th_month_release_date=:13th_month_release_date,days_per_month=:days_per_month,first_cut_off=:first_cut_off,second_cut_off=:second_cut_off WHERE id=:pg_sal_and_ded_id",$inputsForPeriods);
		$con->commit();
			Alert("Rates saved successfully","success");
			redirect("frm_payroll_group_rate.php?pg_id=".urlencode($_POST['payroll_group_id']));
			// /redirect("view_company_rates.php");

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