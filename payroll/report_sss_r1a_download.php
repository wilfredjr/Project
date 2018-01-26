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
                                 ->setTitle("Philhealth Er2 Form");

    $objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

    $nCols = 13; //set the number of columns

    $inputFileName = 'files/R-1A.xlsx';

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);

    //TABLE

    $row=17;

    $start_row=$row;
    
    $inputs=array();
    $where="";
    $filter_sql=""; 

    if(!empty($_GET['d_start']) && !empty($_GET['d_end']))
    {   
        $date_start_sql=":date_start";
        $date_end_sql=":date_end";
        $date_start= date_create($_GET['d_start']);
        $date_end= date_create($_GET['d_end']);
        $inputs['date_start']=date_format($date_start,'Y-m-d');
        $inputs['date_end']=date_format($date_end,'Y-m-d');


        $filter_sql.=" e.joined_date BETWEEN ".$date_start_sql."  AND " .$date_end_sql ;
    }

    $where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " AND ".$filter_sql:"";

    $query=" SELECT 
                e.sss_no,
                e.last_name,
                e.first_name,
                e.middle_name,
                e.birthday,
                e.joined_date,
                e.termination_date,
                e.basic_salary,
                jt.description
                FROM employees e
                INNER JOIN job_title jt ON e.job_title_id = jt.id AND jt.is_deleted = 0 
                WHERE e.is_deleted = 0 AND e.is_terminated = 0 ";

    // echo "{$query} {$where}";
    // die();

    // $count=$con->myQuery("SELECT COUNT(e.id) FROM employees e {$query} {$where}",$inputs);

    // echo $count;
    // die;

    $data_query=$con->myQuery("{$query} {$where}",$inputs);    

    while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) 
    {
        $row++;
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row)->getNumberFormat()->setFormatCode('00-0000000-0');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$row.':B'.$row);
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['sss_no']);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['last_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['first_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['middle_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $data['birthday']);
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data['joined_date']);
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $data['termination_date']);
        $objPHPExcel->getActiveSheet()->getStyle('J'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['basic_salary']);
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data['description']);
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L'.$row.':N'.$row);
        $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);
    }

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
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells("A".($row+1).":N".($row+1));   
    $objPHPExcel->getActiveSheet()->SetCellValue("A".($row+1), '-- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- --');
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getFont()->setItalic(true);
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="SSS R1-A.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>