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
$objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);
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
$objPHPExcel->getProperties()->setCreator("SPARK GLOBAL TECH SOLUTIONS, INC")
->setTitle("SSS r5 Form");

//$objPHPExcel->setActiveSheetIndex(1);
    // Rename sheet
// $objPHPExcel->getActiveSheet()->setTitle('Payors');

    $nCols = 13; //set the number of columns

    $inputFileName = 'files/R-5.xlsx';

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);

    //Company Info
        $objPHPExcel->setActiveSheetIndex(0);
    $company_details = $con->myQuery("SELECT *  FROM company_details")->fetch(PDO::FETCH_ASSOC); 
// var_dump($company_details); 
//     die;

    //TABLE

    $row=0;

    $start_row=$row;
    $ref_no = $_GET['ref_no'];

    //$exploded_str =explode("-",$_GET['rep_month_year']);

    $where="WHERE is_deleted = 0 and ref_no = ". $_GET['ref_no'];
    $filter_sql=""; 


    //$where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " AND ".$filter_sql:"";


    $query=" SELECT * 
    FROM
    sss_r5_main";

    // echo "{$query} {$where}";
    // die();

    // $count=$con->myQuery("SELECT COUNT(e.id) FROM employees e {$query} {$where}",$inputs);

    // echo $count;
    // die;

    $data_query=$con->myQuery("{$query} {$where}")->fetch(PDO::FETCH_ASSOC);    
    // echo ("{$query} {$where}");
    // die;
    // var_dump($data_query);
    // die;

    //company 
    $objPHPExcel->getActiveSheet()->getStyle('A15')->getNumberFormat()->setFormatCode('00-0000000-0');
    $objPHPExcel->getActiveSheet()->SetCellValue('A15', $company_details['sss_no']);
    $objPHPExcel->getActiveSheet()->getStyle('I15')->getNumberFormat()->setFormatCode('00-0000000-0');
    $objPHPExcel->getActiveSheet()->SetCellValue('I15', $company_details['sss_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A17', $company_details['company_name']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A20', $company_details['address']);
    $objPHPExcel->getActiveSheet()->getStyle('K22')->getNumberFormat()->setFormatCode('0000000000');
    $objPHPExcel->getActiveSheet()->SetCellValue('K22', $company_details['tin']);
    $objPHPExcel->getActiveSheet()->SetCellValue('J22', $company_details['zip_code']);
    $objPHPExcel->getActiveSheet()->getStyle('B24')->getNumberFormat()->setFormatCode('0000000');
    $objPHPExcel->getActiveSheet()->SetCellValue('B24', $company_details['phone_no']);
    $objPHPExcel->getActiveSheet()->getStyle('F24')->getNumberFormat()->setFormatCode('00000000000');
    $objPHPExcel->getActiveSheet()->SetCellValue('F24', $company_details['mobile_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('J24', $company_details['email_address']);
    $objPHPExcel->getActiveSheet()->SetCellValue('L24', $company_details['website']);


    //$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B1:G1');
 

    $exploded_str =explode("-",$data_query['for_date_of']);
    $themonth = $exploded_str[1];
    $start_month_row = 27;
    $row= $start_month_row+$themonth;
    // var_dump($start_month_row+$themonth);
    // die;
    //$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:L1');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $exploded_str[0]);
        $objPHPExcel->getActiveSheet()->getStyle('D'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data_query['amt_ss_contribution']);
        $objPHPExcel->getActiveSheet()->getStyle('H'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data_query['amt_ec_contribution']);
        $objPHPExcel->getActiveSheet()->getStyle('K'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data_query['amt_ss_contribution'] + $data_query['amt_ec_contribution']);
    // $objPHPExcel->getActiveSheet()->getStyle('B1')->getNumberFormat()->setFormatCode('0000000000');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B1:G1');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B2:G2');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B3:G3');
    // $objPHPExcel->getActiveSheet()->SetCellValue('B1', $company_details['tin']);
    // $objPHPExcel->getActiveSheet()->SetCellValue('B2', $company_details['company_name']);
    // $objPHPExcel->getActiveSheet()->SetCellValue('B3', $company_details['address']);

    // while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) 
    // {
    //     $row++;
    //     $totalEeShare +=$data['eeshare'];
    //     $totalErShare +=$data['ershare'];
    //     $objPHPExcel->getActiveSheet()->getStyle('A'.$row)->getNumberFormat()->setFormatCode('0000000000');
    //     // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$row.':B'.$row);
    //     $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['pagibig']);
    //     $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['lastname']);
    //     $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['firstname']);
    //     $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['middlename']);
    //     $objPHPExcel->getActiveSheet()->getStyle('E'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
    //     $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['eeshare']);
    //     $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
    //     $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $data['ershare']);
    //     if($data['is_terminated']== 1){
    //         $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, 'Terminated');
    //     }else{
    //         $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, 'Active');
    //     }
    //     // $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $data['termination_date']);
    //     // $objPHPExcel->getActiveSheet()->getStyle('J'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
    //     // $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['basic_salary']);
    //     // $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data['description']);
    //     // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L'.$row.':N'.$row);
    //     $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);
    // }
     //$row =$row+2;
    // $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);
    // $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'Total Remittance');
    // $objPHPExcel->getActiveSheet()->getStyle('E'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
    // $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $totalEeShare);
    // $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
    // $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $totalErShare);
    // $AlignLTable = array(
    //     'alignment' => array(
    //         'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    //         ),
    //     );

    // $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':N'.$row)->applyFromArray($AlignLTable);

    // $AlignLTable = array(
    //     'alignment' => array(
    //         'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    //         ),
    //     );

    // $objPHPExcel->getActiveSheet()->getStyle('C'.$start_row.':F'.$row)->applyFromArray($AlignLTable);

    // $row++;
    // $row =$row+2;
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells("A".($row+1).":N".($row+1));   
    // $objPHPExcel->getActiveSheet()->SetCellValue("A".($row+1), '-- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- --');
    // $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getFont()->setItalic(true);
    // $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getAlignment()->setHorizontal(
    //     PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    //     );
    $objPHPExcel->setActiveSheetIndex(1);

    // $inputFileName = 'files/R-5.xlsx';

    // $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    // $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    // $objPHPExcel = $objReader->load($inputFileName);
    $objPHPExcel->getActiveSheet()->getStyle('A15')->getNumberFormat()->setFormatCode('00-0000000-0');
    $objPHPExcel->getActiveSheet()->SetCellValue('A15', $company_details['sss_no']);
    $objPHPExcel->getActiveSheet()->getStyle('I15')->getNumberFormat()->setFormatCode('00-0000000-0');
    $objPHPExcel->getActiveSheet()->SetCellValue('I15', $company_details['sss_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A17', $company_details['company_name']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A20', $company_details['address']);
    $objPHPExcel->getActiveSheet()->getStyle('K22')->getNumberFormat()->setFormatCode('0000000000');
    $objPHPExcel->getActiveSheet()->SetCellValue('K22', $company_details['tin']);
    $objPHPExcel->getActiveSheet()->SetCellValue('J22', $company_details['zip_code']);
    $objPHPExcel->getActiveSheet()->getStyle('B24')->getNumberFormat()->setFormatCode('0000000');
    $objPHPExcel->getActiveSheet()->SetCellValue('B24', $company_details['phone_no']);
    $objPHPExcel->getActiveSheet()->getStyle('F24')->getNumberFormat()->setFormatCode('00000000000');
    $objPHPExcel->getActiveSheet()->SetCellValue('F24', $company_details['mobile_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('J24', $company_details['email_address']);
    $objPHPExcel->getActiveSheet()->SetCellValue('L24', $company_details['website']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A1', $ref_no);

    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $exploded_str[0]);
        $objPHPExcel->getActiveSheet()->getStyle('D'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data_query['amt_ss_contribution']);
        $objPHPExcel->getActiveSheet()->getStyle('H'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data_query['amt_ec_contribution']);
        $objPHPExcel->getActiveSheet()->getStyle('K'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data_query['amt_ss_contribution'] + $data_query['amt_ec_contribution']);


 $objPHPExcel->setActiveSheetIndex(2);

    // $inputFileName = 'files/R-5.xlsx';

    // $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    // $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    // $objPHPExcel = $objReader->load($inputFileName);
    $objPHPExcel->getActiveSheet()->getStyle('A15')->getNumberFormat()->setFormatCode('00-0000000-0');
    $objPHPExcel->getActiveSheet()->SetCellValue('A15', $company_details['sss_no']);
    $objPHPExcel->getActiveSheet()->getStyle('I15')->getNumberFormat()->setFormatCode('00-0000000-0');
    $objPHPExcel->getActiveSheet()->SetCellValue('I15', $company_details['sss_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A17', $company_details['company_name']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A20', $company_details['address']);
    $objPHPExcel->getActiveSheet()->getStyle('K22')->getNumberFormat()->setFormatCode('0000000000');
    $objPHPExcel->getActiveSheet()->SetCellValue('K22', $company_details['tin']);
    $objPHPExcel->getActiveSheet()->SetCellValue('J22', $company_details['zip_code']);
    $objPHPExcel->getActiveSheet()->getStyle('B24')->getNumberFormat()->setFormatCode('0000000');
    $objPHPExcel->getActiveSheet()->SetCellValue('B24', $company_details['phone_no']);
    $objPHPExcel->getActiveSheet()->getStyle('F24')->getNumberFormat()->setFormatCode('00000000000');
    $objPHPExcel->getActiveSheet()->SetCellValue('F24', $company_details['mobile_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('J24', $company_details['email_address']);
    $objPHPExcel->getActiveSheet()->SetCellValue('L24', $company_details['website']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A1', $ref_no);

    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $exploded_str[0]);
        $objPHPExcel->getActiveSheet()->getStyle('D'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data_query['amt_ss_contribution']);
        $objPHPExcel->getActiveSheet()->getStyle('H'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data_query['amt_ec_contribution']);
        $objPHPExcel->getActiveSheet()->getStyle('K'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data_query['amt_ss_contribution'] + $data_query['amt_ec_contribution']);
$objPHPExcel->setActiveSheetIndex(0);

    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="SSS_r5.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
    ?>