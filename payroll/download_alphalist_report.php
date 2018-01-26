<?php
	require_once("../support/config.php");
    require_once("../support/PHPExcel.php"); 
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}

    // Variable
    $txtselectedy = (isset($_POST['txtselectedy']) && !empty($_POST['txtselectedy']))? $_POST['txtselectedy'] : '';

    // Single or Married
    $personal_exemption = 250000;

    // 4 maximum , if 21 years old hindi na masasama sa computation
    $additional_exemption = 250000;
    
    // Load All Employees
    $all_employees = $con->myQuery("SELECT 
                                    id,
                                    code,
                                    last_name,
                                    first_name,
                                    middle_name,
                                    basic_salary,
                                    payroll_group_id 
                                    FROM employees 
                                    WHERE is_deleted=0 
                                    AND is_terminated=0");

    //A 
    $_result = array();
    $x=1;
    while($data = $all_employees->fetch(PDO::FETCH_NUM)) {

        // Validation
        $checker_no_previous_employer = $con->myQuery("SELECT COUNT(e.`employee_id`)
                                                       FROM employees_employment_history e
                                                       WHERE e.`employee_id` = ? AND MONTH(e.`date_start`) = 12 AND DATE(e.`date_start`) = 31",array($data[0]))->fetch(PDO::FETCH_NUM);
        // echo "<pre>";
        //     var_dump($data[0]);
        // echo "</pre>";
        // Nested Query
        $_benifits = $con->myQuery("SELECT 
                                    pg.id,
                                    pg.employee_id,
                                    pg.govde_code,
                                    pg.govde_eeshare,
                                    pg.gov_desc 
                                    FROM payroll_govde pg 
                                    JOIN  payroll p 
                                    ON  pg.govde_code = p.payroll_code 
                                    WHERE pg.employee_id = ? 
                                    AND p.is_deleted = 0 
                                    AND p.is_processed = 1",array($data[0]))->fetch(PDO::FETCH_NUM);

        $_13_month_details = $con->myQuery("SELECT 
                                            pd.id,
                                            SUM(pd.13th_month),
                                            SUM(pd.withholding_tax),
                                            SUM(pd.basic_salary),
                                            SUM(pd.de_minimis),
                                            SUM(pd.government_deduction),
                                            SUM(pd.receivable)  
                                            FROM
                                            payroll_details pd 
                                            JOIN payroll p 
                                            ON p.payroll_code = pd.payroll_code
                                            WHERE pd.employee_id = ? 
                                            AND pd.payroll_year = ?
                                            AND p.is_deleted = 0 
                                            AND p.is_processed = 1 ",array($data[0],$txtselectedy))->fetch(PDO::FETCH_NUM);

        $_tax_collected_year = $con->myQuery("SELECT SUM(pd.tax_earning)
                                              FROM payroll_details pd
                                              JOIN payroll p 
                                              ON pd.payroll_id = p.id
                                              AND pd.payroll_code = p.payroll_code
                                              WHERE pd.employee_id = ?
                                              AND pd.payroll_year = ?
                                              AND p.is_deleted = 0
                                              AND p.is_processed = 1 ",array($data[0],$txtselectedy))->fetch(PDO::FETCH_NUM);                                               

        if($checker_no_previous_employer > 0){
            // Result Array
            $_result1[] = array(
                // "no"=>$x,
                // "lname"=>$data[2],
                // "fname"=>$data[3],
                // "mi"=>$data[4],
                // "emp_paygroupID" => number_format($data[6],2),
                // "basic_monthly_salary" => number_format($_13_month_details[3],2),
                // "eeshare_benifits_sss_philhealth" => number_format($_benifits[3],2),
                // "overtime_pay" => number_format(get_payroll_group_rates($data[6])['o_ot_rate'],2),
                // "13th_month_pay" => number_format($_13_month_details[1],2),
                // "empWithholdingtax_jan_dec" => number_format($_13_month_details[2],2),
                // "total_taxable_compensation" => number_format(($_13_month_details[3] + get_payroll_group_rates($data[6])['o_ot_rate'] + $_13_month_details[1]) - ($_benifits[3] + 30000),2),
                // "net_taxable_compensation" => number_format((($_13_month_details[3] + get_payroll_group_rates($data[6])['o_ot_rate'] + $_13_month_details[1]) - ($_benifits[3] + 30000)) - ($personal_exemption + $additional_exemption),2),
                // "total_tax_due" => number_format(((($_13_month_details[3] + get_payroll_group_rates($data[6])['o_ot_rate'] + $_13_month_details[1]) - ($_benifits[3] + 30000)) - ($personal_exemption + $additional_exemption) - 25000 * 0.3) + 50000,2),
                // "total_refund_for_the_year" => number_format((((($_13_month_details[3] + get_payroll_group_rates($data[6])['o_ot_rate'] + $_13_month_details[1]) - ($_benifits[3] + 30000)) - ($personal_exemption + $additional_exemption) - 25000 * 0.3) + 50000) - $_tax_collected_year[0],2)
                
                // Schedule 7.1
                "seq_no"=>$x,
                "tin"=>"",
                "lname"=>$data[2],
                "fname"=>$data[3],
                "mi"=>$data[4],
                "gross_compensation"=>$_tax_collected_year[0],
                "13th_month_pay"=>$_13_month_details[1],
                "de_minimis_benefits"=>$_13_month_details[4],
                "goverment_deduction"=>$_13_month_details[5],
                "salaries_other_forms_compensation"=>"",
                "total_non_taxable_exempt_compensation_income"=>"",
                "basic_salary"=>$_13_month_details[3],
                "salaries_other_forms_compensation"=>"",
                "code"=>"",
                "amount"=>"",
                "premiumpaid_on_health_or_hospital_insurance"=>"",
                "net_taxable_compensation_income"=>$_13_month_details[2],
                "tax_due"=>"",
                // Schedule 7.2
                "total_taxable_compensation_income"=>$_13_month_details[5]
            );
        }else{
            // Result Array
            $_result[] = array(
                // "no"=>$x,
                // "lname"=>$data[2],
                // "fname"=>$data[3],
                // "mi"=>$data[4],
                // "emp_paygroupID" => number_format($data[6],2),
                // "basic_monthly_salary" => number_format($_13_month_details[3],2),
                // "eeshare_benifits_sss_philhealth" => number_format($_benifits[3],2),
                // "overtime_pay" => number_format(get_payroll_group_rates($data[6])['o_ot_rate'],2),
                // "13th_month_pay" => number_format($_13_month_details[1],2),
                // "empWithholdingtax_jan_dec" => number_format($_13_month_details[2],2),
                // "total_taxable_compensation" => number_format(($_13_month_details[3] + get_payroll_group_rates($data[6])['o_ot_rate'] + $_13_month_details[1]) - ($_benifits[3] + 30000),2),
                // "net_taxable_compensation" => number_format((($_13_month_details[3] + get_payroll_group_rates($data[6])['o_ot_rate'] + $_13_month_details[1]) - ($_benifits[3] + 30000)) - ($personal_exemption + $additional_exemption),2),
                // "total_tax_due" => number_format(((($_13_month_details[3] + get_payroll_group_rates($data[6])['o_ot_rate'] + $_13_month_details[1]) - ($_benifits[3] + 30000)) - ($personal_exemption + $additional_exemption) - 25000 * 0.3) + 50000,2),
                // "total_refund_for_the_year" => number_format((((($_13_month_details[3] + get_payroll_group_rates($data[6])['o_ot_rate'] + $_13_month_details[1]) - ($_benifits[3] + 30000)) - ($personal_exemption + $additional_exemption) - 25000 * 0.3) + 50000) - $_tax_collected_year[0],2)
                
                // Schedule 7.1
                "seq_no"=>$x,
                "tin"=>"none",
                "lname"=>$data[2],
                "fname"=>$data[3],
                "mi"=>$data[4],
                "gross_compensation"=>$_tax_collected_year[0],
                "13th_month_pay"=>$_13_month_details[1],
                "de_minimis_benefits"=>$_13_month_details[4],
                "goverment_deduction"=>$_13_month_details[5],
                "salaries_other_forms_compensation"=>"none",
                "total_non_taxable_exempt_compensation_income"=>"none",
                "basic_salary"=>$_13_month_details[3],
                "salaries_other_forms_compensation"=>"none",
                "code"=>"none",
                "amount"=>"none",
                "premiumpaid_on_health_or_hospital_insurance"=>"none",
                "net_taxable_compensation_income"=>$_13_month_details[2],
                "tax_due"=>"none",
                // Schedule 7.2
                "total_taxable_compensation_income"=>$_13_month_details[5]
            );
        }                                            
        
        $x++;
    }



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

    $objPHPExcel->getProperties()->setCreator("SPARK GLOBAL TECH SOLUTIONS, INC")
                                 ->setTitle("Alphalist Report");

    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

    $inputFileName = 'files/alphalist_template.xlsx';

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $row=11;
    $start_row=$row;
    
        foreach($_result as $data) {
            $row++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['seq_no']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['tin']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['lname']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['fname']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['mi']);
            $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $data['gross_compensation']);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $data['13th_month_pay']);
            $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data['de_minimis_benefits']);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $data['goverment_deduction']);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data['total_non_taxable_exempt_compensation_income']);
            $objPHPExcel->getActiveSheet()->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $data['basic_salary']);
            $objPHPExcel->getActiveSheet()->getStyle('M'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->getStyle('N'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $data['code']);
            $objPHPExcel->getActiveSheet()->getStyle('O'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $data['amount']);
            $objPHPExcel->getActiveSheet()->getStyle('P'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $data['premiumpaid_on_health_or_hospital_insurance']);
            $objPHPExcel->getActiveSheet()->getStyle('Q'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $data['net_taxable_compensation_income']);
            $objPHPExcel->getActiveSheet()->getStyle('R'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $data['tax_due']);
            $objWorksheet->insertNewRowBefore($row + 1, 1);
        }
        $row++;
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, 'TOTAL');
        $objPHPExcel->getActiveSheet()->getStyle('F'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, '=SUM(F'.$start_row.':F'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('G'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=SUM(G'.$start_row.':G'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('H'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, '=SUM(H'.$start_row.':H'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('I'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, '=SUM(I'.$start_row.':I'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('J'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, '=SUM(J'.$start_row.':J'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('K'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, '=SUM(K'.$start_row.':K'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('L'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, '=SUM(L'.$start_row.':L'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('M'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, '=SUM(M'.$start_row.':M'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('O'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, '=SUM(O'.$start_row.':O'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('P'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, '=SUM(P'.$start_row.':P'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('Q'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, '=SUM(Q'.$start_row.':Q'.($row-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('R'.$row)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, '=SUM(R'.$start_row.':R'.($row-1).')');

        // schedule 7.3
        $objWorksheet1 = $objPHPExcel->getActiveSheet();
        $rows=$row+6;
        $start_rows=$rows;

        foreach($_result1 as $data) {
            $rows++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rows, $data['seq_no']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rows, $data['tin']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rows, $data['lname']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rows, $data['fname']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rows, $data['mi']);
            $objPHPExcel->getActiveSheet()->getStyle('F'.$rows)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rows, $data['gross_compensation']);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$rows)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rows, $data['13th_month_pay']);
            $objPHPExcel->getActiveSheet()->getStyle('F'.$rows)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rows, $data['de_minimis_benefits']);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$rows)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rows, $data['goverment_deduction']);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$rows)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rows, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->getStyle('K'.$rows)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rows, $data['total_non_taxable_exempt_compensation_income']);
            $objPHPExcel->getActiveSheet()->getStyle('L'.$rows)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rows, $data['basic_salary']);
            $objPHPExcel->getActiveSheet()->getStyle('M'.$rows)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rows, $data['13th_month_pay']);
            $objWorksheet1->insertNewRowBefore($rows + 1, 1);
        }
        $rows++;
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rows, 'TOTAL');
        $objPHPExcel->getActiveSheet()->getStyle('F'.$rows)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rows, '=SUM(F'.$start_rows.':F'.($rows-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('G'.$rows)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rows, '=SUM(G'.$start_rows.':G'.($rows-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('H'.$rows)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rows, '=SUM(H'.$start_rows.':H'.($rows-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('I'.$rows)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rows, '=SUM(I'.$start_rows.':I'.($rows-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('J'.$rows)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rows, '=SUM(J'.$start_rows.':J'.($rows-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('K'.$rows)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rows, '=SUM(K'.$start_rows.':K'.($rows-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('L'.$rows)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rows, '=SUM(L'.$start_rows.':L'.($rows-1).')');
        $objPHPExcel->getActiveSheet()->getStyle('M'.$rows)->getNumberFormat()->setFormatCode('P #,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rows, '=SUM(M'.$start_rows.':O'.($rows-1).')');

        //  schedule 7.3 Continuation
        // $objWorksheet2 = $objPHPExcel->getActiveSheet();
        // $rows1=$rows+6;
        // $start_row1=$rows1;

        // foreach($_result1 as $data){
        //     $rows1++;
        //     $objPHPExcel->getActiveSheet()->getStyle('G'.$rows)->getNumberFormat()->setFormatCode('#,##0.00');
        //     $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rows, $data['13th_month_pay']);
        // }

        // schedule 7.4
        // $objWorksheet3 = $objPHPExcel->getActiveSheet();
        // $rows2=$rows1+6;
        // $start_row2=$rows2;

        // foreach($_result1 as $data){
        //     $rows1++;
        //     $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rows, $data['seq_no']);
        //     $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rows, $data['tin']);
        //     $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rows, $data['lname']);
        //     $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rows, $data['fname']);
        //     $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rows, $data['mi']);
        //     $objWorksheet3->insertNewRowBefore($rows1 + 1, 1);
        // }


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Alphalist Report.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        die;                             
?>