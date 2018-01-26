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
                                 ->setTitle("PAYROLL DETAILS REPORTS");

    $objPHPExcel->setActiveSheetIndex(0);    
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    $nCols = 9;

    foreach (range(0, $nCols) as $col) 
    {
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);                
    }

    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'SPARK GLOBAL TECH SOLUTIONS, INC');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:Q1');
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'PAYROLL DETAILS REPORTS');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:Q2');
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
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'BASIC SALARY');
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, 'ADJSUTMENT (PLUS)');
    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, 'OVERTIME');
    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, 'TAXABLE ALLOWANCE');
    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, 'GROSS PAY');
    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, 'ABSENT');
    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, 'LATE');
    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, 'ADJUSTMENT (MINUS)');
    $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, 'COMPANY DEDUCTIONS');
    $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, 'GOVERNMENT DEDUCTIONS');
    $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, 'WITHOLDING TAX');
    $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, 'TOTAL DEDUCTIONS');
    $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, 'LOAN');
    $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, 'NON-TAXABLE ALLOWANCE');
    $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, '13TH MONTH');
    $objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, 'NET PAY');

    $table_header=$objPHPExcel->getActiveSheet()->getStyle("A".$row.":R".$row);
    $table_header->getFont()->setBold(true);
    $table_header->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
    
    $get_details=$con->myQuery("SELECT
                                    pd.id,
                                    e.code AS employee_code,
                                    CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                                    pd.basic_salary,
                                    pd.payroll_adjustment_plus,
                                    pd.overtime,
                                    pd.receivable,
                                    pd.tax_earning AS gross,
                                    pd.absent,
                                    pd.late,
                                    pd.payroll_adjustment_minus,
                                    pd.company_deduction,
                                    pd.government_deduction,
                                    pd.withholding_tax,
                                    pd.total_deduction,
                                    pd.loan,
                                    pd.de_minimis,
                                    pd.13th_month,
                                    pd.net_pay
                                FROM payroll_details pd
                                INNER JOIN employees e ON e.id=pd.employee_id
                                WHERE pd.payroll_id=? ORDER BY pd.employee_id",array($_POST['p_id']));

    while($get_data=$get_details->fetch(PDO::FETCH_ASSOC))
    {
        $row++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $get_data['employee_code']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $get_data['employee_name']);

        $objPHPExcel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $get_data['basic_salary']);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $get_data['payroll_adjustment_plus']);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $get_data['overtime']);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $get_data['receivable']);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $get_data['gross']);

        $objPHPExcel->getActiveSheet()->getStyle('H')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $get_data['absent']);
        $objPHPExcel->getActiveSheet()->getStyle('I')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $get_data['late']);
        $objPHPExcel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $get_data['payroll_adjustment_minus']);
        $objPHPExcel->getActiveSheet()->getStyle('K')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $get_data['company_deduction']);
        $objPHPExcel->getActiveSheet()->getStyle('L')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $get_data['government_deduction']);
        $objPHPExcel->getActiveSheet()->getStyle('M')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $get_data['withholding_tax']);
        $objPHPExcel->getActiveSheet()->getStyle('N')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $get_data['total_deduction']);

        $objPHPExcel->getActiveSheet()->getStyle('O')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $get_data['loan']);
        $objPHPExcel->getActiveSheet()->getStyle('P')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $get_data['de_minimis']);
        $objPHPExcel->getActiveSheet()->getStyle('Q')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $get_data['13th_month']);
        $objPHPExcel->getActiveSheet()->getStyle('R')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $get_data['net_pay']);
    }

    $styleArray = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':R'.$row)->applyFromArray($styleArray);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Payroll Details Report.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>