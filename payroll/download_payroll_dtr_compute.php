<?php
	require_once("../support/config.php");
    require_once("../support/PHPExcel.php"); 
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}

    $objPHPExcel = new PHPExcel();
    
    $objPHPExcel->getProperties()->setCreator("SGTSI PAYROLL SYSTEM")
                                 ->setTitle("PAYROLL REPORTS - DTR COMPUTATION");

    $objPHPExcel->setActiveSheetIndex(0);    
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    $nCols = 9;

    foreach (range(0, $nCols) as $col) 
    {
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);                
    }

    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'SPARK GLOBAL TECH SOLUTIONS, INC');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:P1');
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'PAYROLL REPORTS - DTR COMPUTATION');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:P2');
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );


    $data=$con->myQuery("SELECT 
                            p.id,
                            p.payroll_code,
                            p.date_gen,
                            p.date_from,
                            p.date_to,
                            p.pay_group_id,                    
                            pg.name AS payroll_group,
                            p.date_process
                        FROM payroll p
                        INNER JOIN payroll_groups pg ON pg.payroll_group_id=p.pay_group_id  
                        WHERE p.id=?",array($_POST['p_id']))->fetch(PDO::FETCH_ASSOC);
    
    $objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Payroll Code:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B4', htmlspecialchars($data['payroll_code']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Payroll Group:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B5', htmlspecialchars($data['payroll_group']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'Cut-off Date:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B6', htmlspecialchars($data['date_from'].' to '.$data['date_to']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A7', 'Date Generated:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B7', htmlspecialchars($data['date_gen']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A8', 'Date Processed:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B8', htmlspecialchars($data['date_process']));
    

    $row=9;
    $row++;
    $row++;
    $start_row=$row;

    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'CODE');
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'EMPLOYEE NAME');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'TIME-IN');
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, 'TIME-OUT');
    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, 'DAILY RATE');
    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, 'HOURLY RATE');
    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, 'NIGHT RATE');
    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, 'LATE');
    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, 'OT');
    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, 'OT SPECIAL');
    $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, 'OT LEGAL');
    $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, 'SPECIAL');
    $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, 'LEGAL');
    $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, 'RD');
    $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, 'RD SPECIAL');
    $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, 'RD LEGAL');

    $table_header=$objPHPExcel->getActiveSheet()->getStyle("A".$row.":P".$row);
    $table_header->getFont()->setBold(true);
    $table_header->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
    
    $get_details=$con->myQuery("SELECT
                                    dc.id,
                                    dc.employee_id,
                                    e.code AS employee_code,
                                    CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                                    dc.time_in,
                                    dc.time_out,
                                    dc.daily_rate,
                                    dc.hourly_rate,
                                    dc.night_rate,
                                    dc.late,
                                    dc.overtime AS ot,
                                    dc.overtime_special_holiday AS ot_special,
                                    dc.overtime_legal_holiday AS ot_legal,
                                    dc.special_holiday AS special,
                                    dc.legal_holiday AS legal,
                                    dc.rest_day AS rd,
                                    dc.rest_day_special_holiday AS rd_special,
                                    dc.rest_day_legal_holiday AS rd_legal
                                FROM dtr_compute dc
                                INNER JOIN employees e ON e.id=dc.employee_id
                                WHERE dc.payroll_id=? ORDER BY dc.employee_id",array($_POST['p_id']));

    while($get_data=$get_details->fetch(PDO::FETCH_ASSOC))
    {
        $row++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $get_data['employee_code']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $get_data['employee_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $get_data['time_in']);
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $get_data['time_out']);
        
        $objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $get_data['daily_rate']);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $get_data['hourly_rate']);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $get_data['night_rate']);

        $objPHPExcel->getActiveSheet()->getStyle('H')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $get_data['late']);
        $objPHPExcel->getActiveSheet()->getStyle('I')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $get_data['ot']);
        $objPHPExcel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $get_data['ot_special']);
        $objPHPExcel->getActiveSheet()->getStyle('K')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $get_data['ot_legal']);
        $objPHPExcel->getActiveSheet()->getStyle('L')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $get_data['special']);
        $objPHPExcel->getActiveSheet()->getStyle('M')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $get_data['legal']);
        $objPHPExcel->getActiveSheet()->getStyle('N')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $get_data['rd']);
        $objPHPExcel->getActiveSheet()->getStyle('O')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $get_data['rd_special']);
        $objPHPExcel->getActiveSheet()->getStyle('P')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $get_data['rd_legal']);
    }

    $styleArray = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':P'.$row)->applyFromArray($styleArray);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Payroll Report - DTR Computation.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>