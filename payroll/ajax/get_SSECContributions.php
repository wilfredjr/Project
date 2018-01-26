<?php
require_once("../../support/config.php"); 
$exploded_str =explode("-",$_GET['month_year']);
$data=$con->myQuery("SELECT
	sum(payroll_govde.govde_eeshare + payroll_govde.govde_ershare) as 'ss_contribution',
	sum(gov_ec) as 'ec_contribution'
	FROM
	payroll_govde
	inner join
	payroll
	ON payroll_govde.payroll_code = payroll.payroll_code
	WHERE (YEAR(STR_TO_DATE(payroll.date_to,'%Y-%m-%d'))= ? and MONTH(STR_TO_DATE(payroll.date_to,'%Y-%m-%d')) = ?) and payroll.is_deleted = 0
	",array($exploded_str[0],$exploded_str[1]))->fetchall(PDO::FETCH_ASSOC);
if(!empty($data)){
	$data[0]['ss_contribution'] = number_format($data[0]['ss_contribution'],2,'.',',');
	$data[0]['ec_contribution'] = number_format($data[0]['ec_contribution'],2,'.',',');
}

echo json_encode($data);
?>