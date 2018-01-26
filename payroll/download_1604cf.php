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
->setTitle("BIR 1604-CF");

$objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Sheet1');

$inputFileName = 'files/1604CF.xlsx';

$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objPHPExcel = $objReader->load($inputFileName);

$where="";
$filter_sql=""; 

if(!empty($_GET['id']))
{
    $id= $_GET['id'];

    $filter_sql.=" is_processed=1 AND is_deleted = 0 AND SUBSTR(month_year,1,4) = ". $id;
}

$query="SELECT
id,
month_year,
date_processed,
if(adjustment_from_26ofsectiona <> 0,0,tax_required_to_be_withheld) as tax_required_to_be_withheld,
    adjustment_from_26ofsectiona
FROM
sixteen_zero_one_c";

$where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " WHERE ".$filter_sql:"";

$data_query=$con->myQuery("{$query} {$where}");

while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) {
    $month = substr($data['month_year'],5,2);
    switch ($month) {
        case "01":
        $objPHPExcel->getActiveSheet()->SetCellValue('D39', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O39', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O39')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W39')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF39', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF39')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF39', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF39')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W39', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W39')->getNumberFormat()->setFormatCode('##,##0.00');
        }


        case "02":
        $objPHPExcel->getActiveSheet()->SetCellValue('D40', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O40', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O40')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W40', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W40')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF40', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF40')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF40', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF40')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W40', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W40')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "03":
        $objPHPExcel->getActiveSheet()->SetCellValue('D41', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O41', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O41')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W41', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W41')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF41', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF41')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF41', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF41')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W41', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W41')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "04":
        $objPHPExcel->getActiveSheet()->SetCellValue('D42', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O42', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O42')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W42', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W42')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF42', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF42')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF42', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF42')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W42', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W42')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "05":
        $objPHPExcel->getActiveSheet()->SetCellValue('D43', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O43', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O43')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W43', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W43')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF43', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF43')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF43', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF43')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W43', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W43')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "06":
        $objPHPExcel->getActiveSheet()->SetCellValue('D44', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O44', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O44')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W44', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W44')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF44', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF44')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF44', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF44')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W44', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W44')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "07":
        $objPHPExcel->getActiveSheet()->SetCellValue('D45', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O45', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O45')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W45', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W45')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF45', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF45')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF45', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF45')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W45', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W45')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "08":
        $objPHPExcel->getActiveSheet()->SetCellValue('D46', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O46', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O46')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W46', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W46')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF46', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF46')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF46', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF46')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W46', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W46')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "09":
        $objPHPExcel->getActiveSheet()->SetCellValue('D47', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O47', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O47')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W47', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W47')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF47', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF47')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF47', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF47')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W47', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W47')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "10":
        $objPHPExcel->getActiveSheet()->SetCellValue('D48', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O48', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O48')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W48', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W48')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF48', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF48')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF48', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF48')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W48', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W48')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "11":
        $objPHPExcel->getActiveSheet()->SetCellValue('D49', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O49', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O49')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W49', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W49')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF49', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF49')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF49', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF49')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W49', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W49')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
        case "12":
        $objPHPExcel->getActiveSheet()->SetCellValue('D50', $data['date_processed']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O50', $data['tax_required_to_be_withheld']);
        $objPHPExcel->getActiveSheet()->getStyle('O50')->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('W50', $data['adjustment_from_26ofsectiona']);
        $objPHPExcel->getActiveSheet()->getStyle('W50')->getNumberFormat()->setFormatCode('##,##0.00');


        if(!empty($data['adjustment_from_26ofsectiona'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('AF50', '('.$data['adjustment_from_26ofsectiona'].')');
            $objPHPExcel->getActiveSheet()->getStyle('AF50')->getNumberFormat()->setFormatCode('##,##0.00');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('AF50', $data['tax_required_to_be_withheld']);
            $objPHPExcel->getActiveSheet()->getStyle('AF50')->getNumberFormat()->setFormatCode('##,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('W50', 0);
            $objPHPExcel->getActiveSheet()->getStyle('W50')->getNumberFormat()->setFormatCode('##,##0.00');
        }
        break;
    }
}

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


$tin1 = substr($company_profile['tin'], 0,3);
$tin2 = substr($company_profile['tin'], 3,3);
$tin3 = substr($company_profile['tin'], 6,3);
$tin4 = '0000';

$objPHPExcel->getActiveSheet()->SetCellValue('F11',$id);
$objPHPExcel->getActiveSheet()->SetCellValue('C16',$tin1);
$objPHPExcel->getActiveSheet()->SetCellValue('G16',$tin2);
$objPHPExcel->getActiveSheet()->SetCellValue('K16',$tin3);
$objPHPExcel->getActiveSheet()->SetCellValue('O16',$tin4);
$objPHPExcel->getActiveSheet()->SetCellValue('Y16',$company_profile['rdo_code']);
$objPHPExcel->getActiveSheet()->SetCellValue('C20',$company_profile['company_name']);
$objPHPExcel->getActiveSheet()->SetCellValue('C24',$company_profile['address']);
$objPHPExcel->getActiveSheet()->SetCellValue('AF20',$company_profile['contact_no']);
$objPHPExcel->getActiveSheet()->SetCellValue('AF24',$company_profile['zip_code']);


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="1604CF.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
die;
?>