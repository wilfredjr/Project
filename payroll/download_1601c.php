<?php
require_once("../support/config.php");
require_once("../support/PHPExcel.php"); 
if(!isLoggedIn())
{
    toLogin(); 
    die();
}
    // var_dump($_GET);
    // die();
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
->setTitle("BIR 1601-C");

$objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Sheet1');

$inputFileName = 'files/1601C.xlsx';

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

$data=$con->myQuery("SELECT
    id,
    month_year,
    tin,
    rdo_code,
    total_amount_of_compensation,
    other_nontaxable_compensation,
    tax_required_to_be_withheld,
    adjustment_from_26ofsectiona,
    previous_month,
    section_a5_tax_paid,
    section_a6_tax_due_for_the_month,
    total,
    date_processed,
    is_processed
    FROM
    sixteen_zero_one_c WHERE is_deleted = 0 AND id =?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

$year = substr($data['month_year'], 0,4);
$month = substr($data['month_year'], 5,2);

$tin1 = substr($company_profile['tin'], 0,3);
$tin2 = substr($company_profile['tin'], 3,3);
$tin3 = substr($company_profile['tin'], 6,3);
$tin4 = '0000';

if(!empty($data['previous_month'])){
   $prev_mo1 = substr($data['previous_month'], 0,4);
   $prev_mo2 = substr($data['previous_month'], 5,2);
   $prev_mo3 = $prev_mo2 .'/'.$prev_mo1;

   $prev_mo4 = substr($data['previous_month'], 8,4);
   $prev_mo5 = substr($data['previous_month'], 13,2);
   $prev_mo6 = $prev_mo5 .'/'.$prev_mo4;

   $prev_mo = $prev_mo3.'-'.$prev_mo6;
}else{
    $prev_mo ='';
  
}   

if(!empty($data['total'])){
    $total = number_format($data['total'],2);
}else{
    $total='';
}

if(!empty($data['adjustment_from_26ofsectiona'])){
    $af26 = number_format($data['adjustment_from_26ofsectiona'],2);
}else{
    $af26 = '';
}

if(!empty($data['section_a5_tax_paid'])){
    $sa5 = number_format($data['section_a5_tax_paid'],2);
}else{
    $sa5 = '';
}

if(!empty($data['section_a5_tax_paid'])){
    $sa6 = number_format($data['section_a6_tax_due_for_the_month'],2);
}else{
    $sa6 = '';
}

$objPHPExcel->getActiveSheet()->SetCellValue('H11',$month);
$objPHPExcel->getActiveSheet()->SetCellValue('J11',$year);
$objPHPExcel->getActiveSheet()->SetCellValue('C16',$tin1);
$objPHPExcel->getActiveSheet()->SetCellValue('G16',$tin2);
$objPHPExcel->getActiveSheet()->SetCellValue('K16',$tin3);
$objPHPExcel->getActiveSheet()->SetCellValue('O16',$tin4);
$objPHPExcel->getActiveSheet()->SetCellValue('W16',$company_profile['rdo_code']);
$objPHPExcel->getActiveSheet()->SetCellValue('C20',$company_profile['company_name']);
$objPHPExcel->getActiveSheet()->SetCellValue('C23',$company_profile['address']);
$objPHPExcel->getActiveSheet()->SetCellValue('AH20',$company_profile['contact_no']);
$objPHPExcel->getActiveSheet()->SetCellValue('AJ23',$company_profile['zip_code']);
$objPHPExcel->getActiveSheet()->SetCellValue('Q32',number_format($data['total_amount_of_compensation'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('Q41',number_format($data['other_nontaxable_compensation'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('AC46',number_format($data['tax_required_to_be_withheld'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('AC49',$af26);
$objPHPExcel->getActiveSheet()->SetCellValue('AC69',number_format($data['tax_required_to_be_withheld'],2));
$objPHPExcel->getActiveSheet()->SetCellValue('B76',$prev_mo);
$objPHPExcel->getActiveSheet()->SetCellValue('A86',$sa5);
$objPHPExcel->getActiveSheet()->SetCellValue('J86',$sa6);
$objPHPExcel->getActiveSheet()->SetCellValue('X91',$total);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="1601C.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
die;
?>