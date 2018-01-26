<?php

require_once("../support/config.php");

if(!isLoggedIn()){
	toLogin();
	die();
}


if(!empty($_GET)){

	$month_year = $_GET['month_year'];

	$details=$con->myQuery("SELECT
		SUM(pd.tax_earning) AS tax_earning,
		SUM(pd.government_deduction) AS government_deduction,
		SUM(pd.withholding_tax) AS withholding_tax,
		DATE_FORMAT(p.date_to,'%Y-%m') as month_year
		FROM
		payroll_details pd
		INNER JOIN payroll p ON p.id = pd.payroll_id
		INNER JOIN tax_status ts ON pd.tax_compensation = ts.id
		WHERE
		DATE_FORMAT(p.date_to,'%Y-%m') = ?",array($month_year))->fetch(PDO::FETCH_ASSOC);

	$company_profile=$con->myQuery("SELECT
		`name` as company_name,
		address,
		email,
		contact_no,
		website,
		foundation_day,
		fax_no,
		zip_code,
		sss_no,
		philhealth_no,
		tin,
		pagibig_no,
		rdo_code,
		line_of_business
		FROM
		company_profile")->fetch(PDO::FETCH_ASSOC);

	$date=new DateTime($month_year);
	$year=$date->format("Y");
	$my=$year.'-'.'12';

	if($month_year == $my){

		$details_jan_to_nov=$con->myQuery("SELECT 
			SUM(tax_required_to_be_withheld) as tax_required_to_be_withheld
			FROM
			sixteen_zero_one_c
			WHERE 
			month_year >= CONCAT(?,'-01') AND 
			month_year <= CONCAT(?,'-11') AND 
			is_processed=1",array($year,$year))->fetch(PDO::FETCH_ASSOC);

		$first_month=$con->myQuery("SELECT 
			month_year
			FROM
			sixteen_zero_one_c
			WHERE 
			month_year >= CONCAT(?,'-01') AND 
			month_year <= CONCAT(?,'-11') AND 
			is_processed=1
			ORDER BY month_year ASC LIMIT 1",array($year,$year))->fetch(PDO::FETCH_ASSOC);

		$last_month=$con->myQuery("SELECT 
			month_year
			FROM
			sixteen_zero_one_c
			WHERE 
			month_year >= CONCAT(?,'-01') AND 
			month_year <= CONCAT(?,'-11') AND 
			is_processed=1
			ORDER BY month_year DESC LIMIT 1",array($year,$year))->fetch(PDO::FETCH_ASSOC);

		$details_jan_to_dec=$con->myQuery("SELECT 
			SUM(tax_required_to_be_withheld) as tax_required_to_be_withheld
			FROM
			sixteen_zero_one_c
			WHERE 
			month_year >= CONCAT(?,'-01') AND
			month_year <= CONCAT(?,'-12') AND 
			is_processed=1 OR is_processed=0",array($year,$year))->fetch(PDO::FETCH_ASSOC);

		$param = array(
			'month_year' 					=> $month_year,
			'tin'							=> $company_profile['tin'],
			'rdo_code'						=> $company_profile['rdo_code'],
			'total_amount_of_compensation'	=> $details['tax_earning'],
			'other_nontaxable_compensation'	=> $details['government_deduction'],
			'tax_required_to_be_withheld'	=> $details['withholding_tax'],
			);

		$params = array(
			'adjustment_from_26ofsectiona'	=> ($details_jan_to_dec['tax_required_to_be_withheld'] - $details_jan_to_nov['tax_required_to_be_withheld']),
			'previous_month' 				=> $first_month['month_year'] . '-' . $last_month['month_year'],
			'sectiona5'						=> $details_jan_to_nov['tax_required_to_be_withheld'],
			'sectiona6'						=> $details_jan_to_dec['tax_required_to_be_withheld'],
			'total'							=> ($details_jan_to_dec['tax_required_to_be_withheld'] - $details_jan_to_nov['tax_required_to_be_withheld'])
			);

		$con->myQuery("INSERT INTO sixteen_zero_one_c(month_year,tin,rdo_code,total_amount_of_compensation,other_nontaxable_compensation,tax_required_to_be_withheld) VALUES (:month_year,:tin,:rdo_code,:total_amount_of_compensation,:other_nontaxable_compensation,:tax_required_to_be_withheld)",$param);

		$get_last_id=$con->lastInsertId();

		$con->myQuery("UPDATE sixteen_zero_one_c SET adjustment_from_26ofsectiona=:adjustment_from_26ofsectiona ,previous_month=:previous_month ,section_a5_tax_paid=:sectiona5,section_a6_tax_due_for_the_month=:sectiona6,total=:total WHERE id={$get_last_id} ",$params);

		Alert("Temporarily saved","success");
		redirect("1601c_view.php?id=".$get_last_id."&month_year=".$month_year);
		die();		

	}else{
		if(!empty($details['tax_earning'])){
			$param = array(
				'month_year' 					=> $month_year,
				'tin'							=> $company_profile['tin'],
				'rdo_code'						=> $company_profile['rdo_code'],
				'total_amount_of_compensation'	=> $details['tax_earning'],
				'other_nontaxable_compensation'	=> $details['government_deduction'],
				'tax_required_to_be_withheld'	=> $details['withholding_tax']
				);

			$con->myQuery("INSERT INTO sixteen_zero_one_c(month_year,tin,rdo_code,total_amount_of_compensation,other_nontaxable_compensation,tax_required_to_be_withheld) VALUES (:month_year,:tin,:rdo_code,:total_amount_of_compensation,:other_nontaxable_compensation,:tax_required_to_be_withheld)",$param);

			$get_last_id=$con->lastInsertId();

			Alert("Temporarily saved","success");
			redirect("1601c_view.php?id=".$get_last_id."&month_year=".$month_year);
			die();
		}else{
			Alert("No record/s found","danger");
			redirect("view_1601c.php");
			die();
		}

	}
}else{

	redirect("1601c_view.php");
	die();

}

?>