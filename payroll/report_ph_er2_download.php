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
    // $objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A1');
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $nCols = 7; //set the number of columns

    // foreach (range(0, $nCols) as $col) 
    // {
    //     $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(false);                
    // }

    $inputFileName = 'files/ER-2.xlsx';

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);



    // $objDrawing = new PHPExcel_Worksheet_Drawing();
    // $objDrawing->setName('logo');
    // $objDrawing->setPath('images/philhealth.jpg');
    // $objDrawing->setHeight(70);
    // $objDrawing->setOffsetY(2);
    // $objDrawing->setOffsetX(20);
    // $objDrawing->setCoordinates('A2');
    // $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

    // // $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman');

    // $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'PLEASE READ INSTRUCTION AT THE BACK BEFORE ACCOMPLISHING THIS FORM');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:G1');
    // $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(
    //     PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    // );

    // //TOP
    // $objPHPExcel->getActiveSheet()->SetCellValue('B3', 'PHILHEALTH');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B3:B4');
    // $objPHPExcel->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);
    // $objPHPExcel->getActiveSheet()->getStyle('B3')->getFont()->setSize(17);
    // $objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(
    //     PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    // );
    // $objPHPExcel->getActiveSheet()->SetCellValue('B5', 'REPORT OF EMPLOYEE-MEMBERS');
    // $objPHPExcel->getActiveSheet()->getStyle('B5')->getFont()->setBold(true);
    // $objPHPExcel->getActiveSheet()->getStyle('B5')->getFont()->setName('Arial');
    // $objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->setHorizontal(
    //     PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    // );
    // $objPHPExcel->getActiveSheet()->SetCellValue('D3', '(CHECK APPLICABLE BOX)');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D3:F3');
    // $objPHPExcel->getActiveSheet()->getStyle('D3')->getAlignment()->setHorizontal(
    //     PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    // );

    // $objDrawing = new PHPExcel_Worksheet_Drawing();
    // $objDrawing->setName('logo');
    // $objDrawing->setPath('images/checkbox.jpg');
    // $objDrawing->setHeight(15);
    // $objDrawing->setOffsetX(140);
    // $objDrawing->setCoordinates('C4');
    // $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

    // $objPHPExcel->getActiveSheet()->SetCellValue('D4', 'INITIAL LIST (Attach to Philhealth Form Er1)');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D4:F4');
    // $objPHPExcel->getActiveSheet()->getStyle('D4')->getAlignment()->setHorizontal(
    //     PHPExcel_Style_Alignment::HORIZONTAL_LEFT
    // );

    // $objDrawing = new PHPExcel_Worksheet_Drawing();
    // $objDrawing->setName('logo');
    // $objDrawing->setPath('images/checkbox.jpg');
    // $objDrawing->setHeight(15);
    // $objDrawing->setOffsetX(140);
    // $objDrawing->setCoordinates('C5');
    // $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

    // $objPHPExcel->getActiveSheet()->SetCellValue('D5', 'SUBSEQUENT LIST');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D5:F5');
    // $objPHPExcel->getActiveSheet()->getStyle('D5')->getAlignment()->setHorizontal(
    //     PHPExcel_Style_Alignment::HORIZONTAL_LEFT
    // );
    // $objPHPExcel->getActiveSheet()->SetCellValue('G3', 'Er2');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G3:G5');
    // $objPHPExcel->getActiveSheet()->getStyle('G3')->getFont()->setBold(true);
    // $objPHPExcel->getActiveSheet()->getStyle('G3')->getFont()->setSize(36);
    // $objPHPExcel->getActiveSheet()->getStyle('G3')->getFont()->setName('Bookman Old Style');
    // $objPHPExcel->getActiveSheet()->getStyle('G3')->getAlignment()->setHorizontal(
    //     PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    // );

    // //EMPLOYER DETAILS
    // $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'NAME OF EMPLOYER/FIRM:');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:E6');

    // $objPHPExcel->getActiveSheet()->SetCellValue('F6', 'EMPLOYER NO.:');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('F6:G6');

    // $objPHPExcel->getActiveSheet()->SetCellValue('A7', 'ADDRESS:');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A7:C7');

    // $objPHPExcel->getActiveSheet()->SetCellValue('D7', 'E-MAIL ADDRESS:');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D7:G7');

    //TABLE

    $row=8;

    $row++;

    $start_row=$row;

    // $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
    // $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setWrapText(true);
    // $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'PHILHEALTH SSS/GSIS NUMBER');
    // $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    // $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'NAME OF EMPLOYEE');
    // $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    // $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'POSITION');
    // $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    // $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, 'SALARY');
    // $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
    // $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setWrapText(true);
    // $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, 'DATE OF EMPLOY-MENT');
    // $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
    // $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setWrapText(true);
    // $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, '(DO NOT FILL) EFF. DATE OF COVERAGE');
    // $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
    // $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setWrapText(true);
    // $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, 'PREVIOUS EMPLOYER (IF ANY)');

    // $table_header=$objPHPExcel->getActiveSheet()->getStyle("A".$row.":G".$row);
    // $table_header->getFont()->setBold(true);
    // $table_header->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);   
    
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
                e.philhealth as 'ph_no',
                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as 'employee_name',
                jt.description as 'jt_desc',
                e.basic_salary as 'salary',
                e.joined_date as 'join_date',
                eh.company as 'p_company'
                FROM employees e
                INNER JOIN job_title jt ON e.job_title_id = jt.id AND jt.is_deleted = 0 
                LEFT JOIN employees_employment_history eh ON e.id = eh.employee_id AND eh.is_deleted = 0
                WHERE e.is_deleted = 0 AND e.is_terminated = 0  ";

    // echo "{$query} {$where}";
    // die();

    $data_query=$con->myQuery("{$query} {$where}",$inputs);     

    while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) 
    {
        $row++;
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row)->getNumberFormat()->setFormatCode('00-000000000-0');
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['ph_no']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['employee_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['jt_desc']);
        $objPHPExcel->getActiveSheet()->getStyle('D'.$row)->getNumberFormat()->setFormatCode('##,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['salary']);
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['join_date']);
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $data['p_company']);
        $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);
    }

    $PHTable = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':G'.$row)->applyFromArray($PHTable);

    // $row++;
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells("A".($row+1).":G".($row+1));   
    $objPHPExcel->getActiveSheet()->SetCellValue("A".($row+1), '-- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- --');
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getFont()->setItalic(true);
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    // $EmployerDet = array(
    //     'font' => array(
    //         'bold' => true,
    //     ),
    //     'borders' => array(
    //         'allborders' => array(
    //             'style' => PHPExcel_Style_Border::BORDER_THIN,
    //         ),
    //     ),
    //     'alignment' => array(
    //         'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    //     ),
    // );
    // $objPHPExcel->getActiveSheet()->getStyle('A6:G7')->applyFromArray($EmployerDet);

    // $PHdetails = array(
    //     'borders' => array(
    //         'outline' => array(
    //             'style' => PHPExcel_Style_Border::BORDER_THIN
    //         )
    //     )
    // );
    // $objPHPExcel->getActiveSheet()->getStyle('A2:G5')->applyFromArray($PHdetails);

    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells("A".($row+1).":B".($row+2));   
    // $objPHPExcel->getActiveSheet()->SetCellValue("A".($row+1), 'TOTAL NO. LISTED ABOVE:');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C'.($row+3).":D".($row+3));   
    // $objPHPExcel->getActiveSheet()->SetCellValue('C'.($row+3), 'PAGE  ___  OF  ___  SHEET');
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('E'.($row+2).":G".($row+2));   
    // $objPHPExcel->getActiveSheet()->SetCellValue('E'.($row+2), '_____________________________________________');  
    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('E'.($row+3).":G".($row+3));
    // $objPHPExcel->getActiveSheet()->SetCellValue('E'.($row+3), 'SIGNATURE OVER PRINTED NAME');      

    // $total = array(
    //     'font' => array(
    //         'bold' => true,
    //     ),
    //     'borders' => array(
    //         'outline' => array(
    //             'style' => PHPExcel_Style_Border::BORDER_THIN,
    //         ),
    //     ),
    //     'alignment' => array(
    //         'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    //     ),
    //     'alignment' => array(
    //         'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    //     ),
    // );

    // $PageSign = array(
    //     'font' => array(
    //         'bold' => true,
    //     ),
    //     'borders' => array(
    //         'outline' => array(
    //             'style' => PHPExcel_Style_Border::BORDER_THIN,
    //         ),
    //     ),
    //     'alignment' => array(
    //         'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    //     ),
    // );

    // $objPHPExcel->getActiveSheet()->getStyle("A".($row+1).":B".($row+3))->applyFromArray($total);
    // $objPHPExcel->getActiveSheet()->getStyle("C".($row+1).":D".($row+3))->applyFromArray($PageSign); 
    // $objPHPExcel->getActiveSheet()->getStyle("E".($row+1).":G".($row+3))->applyFromArray($PageSign); 

    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.($row+4).':G'.($row+4));
    // $objPHPExcel->getActiveSheet()->getStyle("A".($row+4).":G".($row+4))->getFont()->setBold(true);
    // $objPHPExcel->getActiveSheet()->SetCellValue("A".($row+4), "TO BE ACCOMPLISHED IN DUPLICATE");
    // $objPHPExcel->getActiveSheet()->getStyle('A'.($row+4))->getAlignment()->setHorizontal(
    //     PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    // );

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Philhealth Er2.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>