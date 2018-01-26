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
                                 ->setTitle("Last Pay Breakdown");

    $objPHPExcel->setActiveSheetIndex(0);    
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    $nCols = 4;

    foreach (range(0, $nCols) as $col) 
    {
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);                
    }

    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'SPARK GLOBAL TECH SOLUTIONS, INC');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'LAST PAY BREAKDOWN');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:D2');

    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );


    $data=$con->myQuery("SELECT 
                            lp.id                                                       AS id,
                            lp.last_pay_code                                            AS last_pay_code,
                            lp.employee_id                                              AS employee_id,
                            CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name)     AS employee_name,
                            e.code                                                      AS employee_code,
                            e.sss_no                                                    AS sss_no,
                            e.tin                                                       AS tin_no,
                            e.philhealth                                                AS philhealth_no,
                            e.pagibig                                                   AS pagibig_no,
                            CONCAT(d.description,' (',d.name,')')                       AS department_name,
                            pg.name                                                     AS payroll_group,
                            CONCAT(ts.description,' (',ts.code,')')                     AS tax_status,
                            lp.date_start                                               AS date_start,
                            lp.date_end                                                 AS date_end,
                            lp.last_salary                                              AS last_salary,
                            lp.13th_month                                               AS 13th_month,
                            lp.total_last_pay                                           AS total_last_pay,
                            lp.date_generated                                           AS date_generated,
                            lp.date_processed                                           AS date_processed
                        FROM last_pay lp
                        INNER JOIN employees e          ON e.id=lp.employee_id
                        INNER JOIN departments d        ON d.id=e.department_id 
                        INNER JOIN payroll_groups pg    ON pg.payroll_group_id=e.payroll_group_id
                        INNER JOIN tax_status ts        ON ts.id=e.tax_status_id
                        WHERE lp.id=?",array($_POST['id']))->fetch(PDO::FETCH_ASSOC);
    
    
    $objPHPExcel->getActiveSheet()->SetCellValue('C4', 'Transaction Number:');
    $objPHPExcel->getActiveSheet()->SetCellValue('D4', $data['last_pay_code']." ");
    $objPHPExcel->getActiveSheet()->SetCellValue('C5', 'Date Generated:');
    $objPHPExcel->getActiveSheet()->SetCellValue('D5', htmlspecialchars($data['date_generated']));
    $objPHPExcel->getActiveSheet()->SetCellValue('C6', 'Date Processed:');
    $objPHPExcel->getActiveSheet()->SetCellValue('D6', htmlspecialchars($data['date_processed']));
    
    $objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Employee Code:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B4', htmlspecialchars($data['employee_code']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Employee Name:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B5', htmlspecialchars($data['employee_name']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'Tax Excemption:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B6', htmlspecialchars($data['tax_status']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A7', 'TIN:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B7', $data['tin_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A8', 'SSS:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B8', $data['sss_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A9', 'PHILHEALTH:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B9', $data['philhealth_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A10', 'PAG-IBIG:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B10', $data['pagibig_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('A11', 'Payroll Group:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B11', htmlspecialchars($data['pagibig_no']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A12', 'Department:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B12', htmlspecialchars($data['department_name']));

    $row=13;
    $row++;
    $row++;
    $start_row=$row;


    $get_adjustments = $con->myQuery("SELECT id,adjustment_type,operation,amount, remarks FROM last_pay_adjustments WHERE last_pay_id=? AND is_deleted=0",array($_POST['id']))->fetchAll(PDO::FETCH_ASSOC);    

// echo "<pre>";
// print_r($get_adjustments);
// echo "</pre>";
// die();
    
    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'BREAKDOWN');
    $objPHPExcel->getActiveSheet()->getStyle('A'.$row)->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'AMOUNT');
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row)->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'REMARKS');
    $objPHPExcel->getActiveSheet()->getStyle('C'.$row)->getFont()->setBold(true);
    for ($i=0; $i < count($get_adjustments); $i++) 
    { 
        $row++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $get_adjustments[$i]['adjustment_type']);

        if ($get_adjustments[$i]['operation'] == "Minus") 
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, "-".$get_adjustments[$i]['amount']);   
        }else
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $get_adjustments[$i]['amount']);
        }
        $objPHPExcel->getActiveSheet()->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#,##0.00');

        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $get_adjustments[$i]['remarks']);
    }

    $row++;
    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'Total');
    $objPHPExcel->getActiveSheet()->getStyle('A'.$row)->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['total_last_pay']);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
    $objPHPExcel->getActiveSheet()->getStyle('B'.$row)->getFont()->setBold(true);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Last Pay Breakdown.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>