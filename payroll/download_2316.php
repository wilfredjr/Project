<?php
require_once("../support/config.php");
require_once("../support/PHPExcel.php"); 
if(!isLoggedIn())
{
    toLogin(); 
    die();
}

$objPHPExcel = new PHPExcel();
$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(12);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.20);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.20);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.20);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.20);

     // Set properties
$objPHPExcel->getProperties()->setCreator("SECRET 6")
->setTitle("BIR 2316");

$objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Sheet1');

$inputFileName = 'files/2316.xlsx';

$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objPHPExcel = $objReader->load($inputFileName);

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
    company_profile ")->fetch(PDO::FETCH_ASSOC);

if(!empty($_GET['year'])){
    $year = $_GET['year'];
}else{
    $year = '';
}

if(!empty($_GET['employee'])){
    $employee_id = $_GET['employee'];
}else{
    $employee_id = '';
}

if(!empty($_GET['tci'])){
    $taxable_compensation_income_prev = $_GET['tci']; 
}else{
    $taxable_compensation_income_prev = 0;
}

if(!empty($_GET['atw'])){
    $amount_tax_withheld_prev = $_GET['atw']; 
}else{
    $amount_tax_withheld_prev = 0;
}

$data=$con->myQuery("SELECT
    CONCAT(employees.last_name,' ',employees.first_name,' ',employees.middle_name) as emp_name,
    employees.address1,
    employees.address2,
    employees.birthday,
    employees.tin,
    tax_status.code as 'tax_status',
    SUM(payroll_details.government_deduction) as government_deduction,
    SUM(employees.basic_salary) as basic_salary,
    SUM(payroll_details.withholding_tax) as tax_withheld
    FROM
    payroll_details
    INNER JOIN employees ON payroll_details.employee_id = employees.id
    INNER JOIN tax_status ON tax_status.id = employees.tax_status_id
    WHERE employees.id= ?
    GROUP BY employees.id",array($employee_id))->fetch(PDO::FETCH_ASSOC);



$tin1 = substr($company_profile['tin'], 0,3);
$tin2 = substr($company_profile['tin'], 3,3);
$tin3 = substr($company_profile['tin'], 6,3);
$tin4 = '0000';

$emp_tin1 = substr($data['tin'], 0,3);
$emp_tin2 = substr($data['tin'], 3,3);
$emp_tin3 = substr($data['tin'], 6,3);
$emp_tin4 = '0000';

$address = $data['address1'] .' '. $data['address2'];

$date = date_create($data['birthday']);
$date_of_birth = date_format($date,'m/d/Y');

$code = $data['tax_status'];

$tax_exemption_details=$con->myQuery("SELECT
    tax_code,
    exemption
    FROM tax_exemptions
    WHERE tax_code = ?",array($code))->fetch(PDO::FETCH_ASSOC);

$objPHPExcel->getActiveSheet()->SetCellValue('F8',$year);
$objPHPExcel->getActiveSheet()->SetCellValue('F13',$emp_tin1);
$objPHPExcel->getActiveSheet()->SetCellValue('J13',$emp_tin2);
$objPHPExcel->getActiveSheet()->SetCellValue('N13',$emp_tin3);
$objPHPExcel->getActiveSheet()->SetCellValue('R13',$emp_tin4);
$objPHPExcel->getActiveSheet()->SetCellValue('B17',$data['emp_name']);
$objPHPExcel->getActiveSheet()->SetCellValue('R17',$company_profile['rdo_code']);
$objPHPExcel->getActiveSheet()->SetCellValue('B21',$address);
$objPHPExcel->getActiveSheet()->SetCellValue('B32',$date_of_birth);
$objPHPExcel->getActiveSheet()->SetCellValue('F57',$tin1);
$objPHPExcel->getActiveSheet()->SetCellValue('J57',$tin2);
$objPHPExcel->getActiveSheet()->SetCellValue('N57',$tin3);
$objPHPExcel->getActiveSheet()->SetCellValue('R57',$tin4);
$objPHPExcel->getActiveSheet()->SetCellValue('B62',$company_profile['company_name']);
$objPHPExcel->getActiveSheet()->SetCellValue('B66',$company_profile['address']);
$objPHPExcel->getActiveSheet()->SetCellValue('R66',$company_profile['zip_code']);
$objPHPExcel->getActiveSheet()->SetCellValue('AG42',number_format($data['government_deduction'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('AG51',number_format($data['government_deduction'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('AG58',number_format($data['basic_salary'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('AG120',number_format($data['basic_salary'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('L87',number_format($data['government_deduction'] + $data['basic_salary'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('L90',number_format($data['government_deduction'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('L93',number_format($data['basic_salary'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('L96',number_format($taxable_compensation_income_prev,2));
$objPHPExcel->getActiveSheet()->SetCellValue('L99',number_format($taxable_compensation_income_prev + $data['basic_salary'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('L102',number_format($tax_exemption_details['exemption'],2));

if($data['basic_salary'] > $tax_exemption_details['exemption']){
    $objPHPExcel->getActiveSheet()->SetCellValue('L108', $data['basic_salary'] - $tax_exemption_details['exemption'] - $taxable_compensation_income_prev,2);
    $net_taxable_comp_income = $data['basic_salary'] - $tax_exemption_details['exemption'] -$taxable_compensation_income_prev;

}else{
    $objPHPExcel->getActiveSheet()->SetCellValue('L108', $tax_exemption_details['exemption'] - $data['basic_salary'] - $taxable_compensation_income_prev,2);
    $net_taxable_comp_income = $tax_exemption_details['exemption'] - $data['basic_salary'] - $taxable_compensation_income_prev;

}

$objPHPExcel->getActiveSheet()->SetCellValue('L114',number_format($data['tax_withheld'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('L117',number_format($amount_tax_withheld_prev,2));
$objPHPExcel->getActiveSheet()->SetCellValue('L120',number_format($data['tax_withheld'] + $amount_tax_withheld_prev,2));

$tax_due_details=$con->myQuery("SELECT
    amount,
    rate,
    of_excess_over
    FROM
    tax_due
    WHERE over <= ? AND but_not_over >= ? ",array($net_taxable_comp_income,$net_taxable_comp_income))->fetch(PDO::FETCH_ASSOC);

$tax_due_amount  = $tax_due_details['amount']; 
$tax_due_rate    = $tax_due_details['rate'];
$tax_excess_over = $tax_due_details['of_excess_over']; 

$tax_due1 = ($net_taxable_comp_income - $tax_excess_over) * $tax_due_rate;
$tax_due2 = $tax_due1 + $tax_due_amount;

$objPHPExcel->getActiveSheet()->SetCellValue('L111',number_format($tax_due2,2));
$objPHPExcel->getActiveSheet()->SetCellValue('L102',number_format($tax_exemption_details['exemption'],2));

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="2316.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
die;
?>