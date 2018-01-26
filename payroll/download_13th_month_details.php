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
                                 ->setTitle("13th MONTH BREAKDOWN REPORT");

    $objPHPExcel->setActiveSheetIndex(0);    
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    $nCols = 9;

    foreach (range(0, $nCols) as $col) 
    {
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);                
    }

    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'SPARK GLOBAL TECH SOLUTIONS, INC');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $objPHPExcel->getActiveSheet()->SetCellValue('A2', '13th MONTH BREAKDOWN REPORT');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:D2');
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );


    $data=$con->myQuery("SELECT 
                            md.id,
                            md.13th_month_id,
                            m.transaction_number,
                            m.payroll_group_id,
                            m.date_start,
                            m.date_end,
                            m.date_processed,
                            pg.name AS payroll_group,
                            md.employee_id,
                            e.code AS employee_code,
                            CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                            md.amount
                        FROM 13th_month_details md
                        INNER JOIN 13th_month m ON m.id=md.13th_month_id
                        INNER JOIN payroll_groups pg ON pg.payroll_group_id=m.payroll_group_id  
                        INNER JOIN employees e ON e.id=md.employee_id
                        WHERE md.id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    
    $objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Employee Code:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B4', htmlspecialchars($data['employee_code']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Employee Name:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B5', htmlspecialchars($data['employee_name']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A6', '13th Month Pay:');
    $objPHPExcel->getActiveSheet()->getStyle('B6')->getNumberFormat()->setFormatCode('#,##0.00');
    $objPHPExcel->getActiveSheet()->SetCellValue('B6', htmlspecialchars($data['amount']));

    $objPHPExcel->getActiveSheet()->SetCellValue('A8', 'Transaction Number:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B8', htmlspecialchars($data['transaction_number']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A9', 'Payroll Group:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B9', htmlspecialchars($data['payroll_group']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A10', 'Cut-off Date:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B10', htmlspecialchars($data['date_start']." to ".$data['date_end']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A11', 'Date Processed:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B11', htmlspecialchars($data['date_processed']));
    

#GET PAYROLL DETAILS
    $row=11;
    $row++;
    $row++;
    $start_row=$row;

    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'PAYROLL CODE');
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'PAYROLL DATE');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'CUT-OFF DATE');
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, 'AMOUNT');

    $table_header=$objPHPExcel->getActiveSheet()->getStyle("A".$row.":D".$row);
    $table_header->getFont()->setBold(true);
    $table_header->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
    
    $get_13th_month_details=$con->myQuery("SELECT 13th_month_id,employee_id FROM 13th_month_details WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    $get_13th_month_payroll_details=$con->myQuery("SELECT id,payroll_details_id FROM 13th_month_payroll_details WHERE 13th_month_id=?",array($get_13th_month_details['13th_month_id']));

    while($get_data1=$get_13th_month_payroll_details->fetch(PDO::FETCH_ASSOC))
    {
        $get_payroll_details=$con->myQuery("SELECT payroll_code,employee_id,13th_month FROM payroll_details WHERE id=? AND employee_id=?",array($get_data1['payroll_details_id'],$get_13th_month_details['employee_id']));
        while($get_data2=$get_payroll_details->fetch(PDO::FETCH_ASSOC))
        {            
            $row++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $get_data2['payroll_code']);
            $objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $get_data2['13th_month']);

            $get_payroll=$con->myQuery("SELECT date_gen,date_from,date_to FROM payroll WHERE payroll_code=?",array($get_data2['payroll_code']))->fetch(PDO::FETCH_ASSOC);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $get_payroll['date_gen']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, htmlspecialchars($get_payroll['date_from'])." to ".$get_payroll['date_to']);
        }
    }

    $styleArray = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':D'.$row)->applyFromArray($styleArray);



#GET ADJUSTMENTS
    $row++;
    $row++;
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'ADJUSTMENTS');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B'.$row.':D'.$row);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row)->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $row2=$row;
    $row2++;
    $start_row2=$row2;

    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row2, 'TYPE');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row2, 'REMARKS');
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row2, 'AMOUNT');

    $table_header=$objPHPExcel->getActiveSheet()->getStyle("B".$row2.":D".$row2);
    $table_header->getFont()->setBold(true);
    $table_header->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
    
    $get_adjustments=$con->myQuery("SELECT adjustment_type,amount,remarks FROM 13th_month_adjust WHERE 13th_month_details_id=?",array($_GET['id']));

    while($get_data3=$get_adjustments->fetch(PDO::FETCH_ASSOC))
    {
        $row2++;
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row2, $get_data3['adjustment_type']==1?'ADD':'MINUS');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row2, $get_data3['remarks']);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row2, $get_data3['amount']);
    }


    $styleArray2 = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('B'.$start_row2.':D'.$row2)->applyFromArray($styleArray2);



    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="13th Month Breakdown Report.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>