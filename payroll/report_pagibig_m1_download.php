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
->setTitle("Pag-ibig M1 Form");

$objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Sheet1');

    $nCols = 13; //set the number of columns

    $inputFileName = 'files/M1.xlsx';

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);

    //Company Info

    $company_details = $con->myQuery("SELECT tin,company_name,address  FROM company_details")->fetch(PDO::FETCH_ASSOC); 
// var_dump($company_details); 
//     die;

    //TABLE

    $row=5;

    $start_row=$row;

    $exploded_str =explode("-",$_GET['rep_month_year']);

    $where="WHERE payroll.is_deleted = 0 AND gov_desc = 'HDMF' and (YEAR(STR_TO_DATE(payroll.date_to,'%Y-%m-%d')) = ". $exploded_str[0] . " and MONTH(STR_TO_DATE(payroll.date_to,'%Y-%m-%d')) =" . $exploded_str[1] . " )";
    $filter_sql=""; 


    //$where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " AND ".$filter_sql:"";


    $query=" SELECT
    employees.pagibig as `pagibig`,
    employees.last_name as `lastname`,
    employees.first_name as `firstname`,
    employees.middle_name as`middlename`,
    payroll_govde.govde_eeshare as `eeshare`,
    payroll_govde.govde_ershare as `ershare`,
    employees.is_terminated as `is_terminated`
    FROM
    employees
    INNER JOIN payroll_govde ON payroll_govde.employee_id = employees.id
    INNER JOIN payroll ON payroll.payroll_code = payroll_govde.payroll_code";

    // echo "{$query} {$where}";
    // die();

    // $count=$con->myQuery("SELECT COUNT(e.id) FROM employees e {$query} {$where}",$inputs);

    // echo $count;
    // die;

    $data_query=$con->myQuery("{$query} {$where}");    
    // echo ("{$query} {$where}");
    // die;
    $totalEeShare=0;
    $totalErShare = 0;
    $objPHPExcel->getActiveSheet()->getStyle('B1')->getNumberFormat()->setFormatCode('0000000000');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B1:G1');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B2:G2');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B3:G3');
    $objPHPExcel->getActiveSheet()->SetCellValue('B1', $company_details['tin']);
    $objPHPExcel->getActiveSheet()->SetCellValue('B2', $company_details['company_name']);
    $objPHPExcel->getActiveSheet()->SetCellValue('B3', $company_details['address']);

    while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) 
    {
        $row++;
        $totalEeShare +=$data['eeshare'];
        $totalErShare +=$data['ershare'];
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row)->getNumberFormat()->setFormatCode('0000000000');
        // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$row.':B'.$row);
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['pagibig']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['lastname']);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['firstname']);
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['middlename']);
        $objPHPExcel->getActiveSheet()->getStyle('E'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['eeshare']);
        $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $data['ershare']);
        if($data['is_terminated']== 1){
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, 'Terminated');
        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, 'Active');
        }
        // $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $data['termination_date']);
        // $objPHPExcel->getActiveSheet()->getStyle('J'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        // $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['basic_salary']);
        // $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data['description']);
        // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L'.$row.':N'.$row);
        $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);
    }
    $row =$row+2;
    $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);
    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'Total Remittance');
    $objPHPExcel->getActiveSheet()->getStyle('E'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $totalEeShare);
    $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $totalErShare);
    $AlignLTable = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':N'.$row)->applyFromArray($AlignLTable);

    $AlignLTable = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
        );

    $objPHPExcel->getActiveSheet()->getStyle('C'.$start_row.':F'.$row)->applyFromArray($AlignLTable);

    // $row++;
    $row =$row+2;
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells("A".($row+1).":N".($row+1));   
    $objPHPExcel->getActiveSheet()->SetCellValue("A".($row+1), '-- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- --');
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getFont()->setItalic(true);
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        );

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Pag-ibig M1.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
    ?>