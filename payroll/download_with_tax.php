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
$objPHPExcel->getProperties()->setCreator("SECRET 6")
->setTitle("With Holding Tax");

$objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Sheet1');

    $nCols = 4; //set the number of columns


    $inputFileName = 'files/withholding_tax.xlsx';

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);


    $row=5;

    $row++;

    $start_row=$row;   
    
    $inputs=array();
    $where="";
    $filter_sql=""; 

   if(!empty($_GET['pay_code']))
    {
        $p_code= $_GET['pay_code'];
       
        $filter_sql.=" p.payroll_id = ".$p_code;
    }




    // if(!empty($_GET['d_start']) && !empty($_GET['d_end']))
    // {   
    //     $date_start_sql=":date_start";
    //     $date_end_sql=":date_end";
    //     $date_start= date_create($_GET['d_start']);
    //     $date_end= date_create($_GET['d_end']);
    //     $inputs['date_start']=date_format($date_start,'Y-m-d');
    //     $inputs['date_end']=date_format($date_end,'Y-m-d');


    //$filter_sql.=" e.joined_date BETWEEN ".$date_start_sql."  AND " .$date_end_sql ;
    // }

    $where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " WHERE ".$filter_sql:"";

    $query=" SELECT e.code,
    p.tax_compensation AS emp_tax_comp,
    tax_status.code as 'tax_status',
    CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as name, 
    p.withholding_tax AS emp_wtax
    FROM payroll_details p 
    INNER JOIN employees e ON p.employee_id=e.id 
    INNER JOIN tax_status ON p.tax_compensation = tax_status.id ";


    // echo "{$query} {$where}";
    // die();

    $data_query=$con->myQuery("{$query} {$where}",$inputs);     
      $total=0;
    while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) 
    {
        $row++;
        
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['code']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['tax_status']);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['name']);
        $objPHPExcel->getActiveSheet()->getStyle('D'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['emp_wtax']);
        $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);

        $total += $data['emp_wtax'];

    }

    

    $PHTable = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
            )
        // ,
        // 'alignment' => array(
        //     'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        //     ),
        );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':D'.$row)->applyFromArray($PHTable);

    // $row++;
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells("A".($row+1).":D".($row+1));   
    $objPHPExcel->getActiveSheet()->SetCellValue("A".($row+1), '-- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- --');
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getFont()->setItalic(true);
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        );


    $objPHPExcel->getActiveSheet()->getStyle("C".($row+3))->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle("C".($row+3))->getAlignment()->setHorizontal(  PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->getStyle("D".($row+3))->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->SetCellValue("C".($row+3), 'TOTAL'); 
    $objPHPExcel->getActiveSheet()->getStyle('D'. ($row+3))->getNumberFormat()->setFormatCode('##,##0.00');
    $objPHPExcel->getActiveSheet()->SetCellValue("D".($row+3), $total); 


    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="withholding_tax.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
    ?>