<?php
	require_once("../support/config.php");
    require_once("../support/PHPExcel.php"); 
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}

    // var_dump($_POST);
    // die();

    $objPHPExcel = new PHPExcel();
    
    
    $objPHPExcel->getProperties()->setCreator("SGTSI PAYROLL SYSTEM")
                                 ->setTitle("Leave Conversion Report");

    $objPHPExcel->setActiveSheetIndex(0);    
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    $nCols = 9;

    foreach (range(0, $nCols) as $col) 
    {
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);                
    }

    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'SPARK GLOBAL TECH SOLUTIONS, INC');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:E1');
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'LEAVE CONVERSION REPORT');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:E2');

    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );


    $data=$con->myQuery("SELECT id,transaction_code,leave_conversion.pay_group_id,payroll_groups.name AS payroll_group,date_generated,for_year,date_processed 
                        FROM leave_conversion INNER JOIN payroll_groups ON payroll_groups.payroll_group_id=leave_conversion.pay_group_id  WHERE leave_conversion.id=?",array($_POST['p_id']))->fetch(PDO::FETCH_ASSOC);
    
    $objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Transaction Number:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B4', htmlspecialchars($data['transaction_code']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Payroll Group:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B5', htmlspecialchars($data['payroll_group']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'Year:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B6', htmlspecialchars($data['for_year']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A7', 'Date Generated:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B7', htmlspecialchars($data['date_generated']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A8', 'Date Processed:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B8', htmlspecialchars($data['date_processed']));
    
    $row=8;
    $row++;
    $row++;
    $start_row=$row;

    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'EMPLOYEE CODE');
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'EMPLOYEE FULL NAME');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'LEAVE CREDIT');
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, 'RATE PER DAY');
    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, 'AMOUNT');

    $table_header=$objPHPExcel->getActiveSheet()->getStyle("A".$row.":E".$row);
    $table_header->getFont()->setBold(true);
    $table_header->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
    

    $data_query=$con->myQuery("SELECT
                                lcd.id,
                                lcd.leave_conversion_id,
                                lc.transaction_code,
                                lcd.employee_id,
                                e.code AS employee_code,
                                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                                SUM(lcd.remaining_leave) AS remaining_leave_credit,
                                lcd.rate_per_day,
                                SUM(lcd.amount) AS total_amount
                            FROM leave_conversion_details lcd 
                            INNER JOIN leave_conversion lc  ON lc.id=lcd.leave_conversion_id
                            INNER JOIN employees e      ON e.id=lcd.employee_id
                            WHERE lcd.leave_conversion_id=?
                            GROUP BY lcd.employee_id",array($_POST['p_id']));     

    while ($data = $data_query->fetch(PDO::FETCH_ASSOC)) 
    {
        $row++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['employee_code']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['employee_name']);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['remaining_leave_credit']);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['rate_per_day']);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['total_amount']);
    }

    $styleArray = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':E'.$row)->applyFromArray($styleArray);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Leave Conversion Report.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>