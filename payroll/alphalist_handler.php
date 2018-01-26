<?php
	require_once("../support/config.php");
    require_once("../support/PHPExcel.php"); 
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
    // Get Year
    $txtselectedy = (isset($_POST['txtselectedy']) && !empty($_POST['txtselectedy']))?$_POST['txtselectedy']:"";

    // Get Basic Information of Company TIN and Name
    $info = $con->myQuery("SELECT name,tin FROM company_profile")->fetch(PDO::FETCH_NUM); 
    
    // echo '<pre>';
    // print_r($_result);
    // echo '</pre>';
    // die();
    $objPHPExcel = new PHPExcel();
    PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());
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

    function cellColor($cells,$color){
        global $objPHPExcel;
        $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => $color
            )
        ));
    }

    function number($cell){
        global $objPHPExcel;
        $objPHPExcel->getActiveSheet()->getStyle($cell)
        ->getNumberFormat()->applyFromArray( 
            array( 
                'code' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            )
        );
    }

    function boldme($cell) {
        global $objPHPExcel;
        $objPHPExcel->getActiveSheet()->getStyle($cell)->getFont()->setBold(true);
    }
    
    if(isset($_POST['fpi1'])){
        // Query
        $all_employees = $con->myQuery("SELECT 
                                        id,
                                        CODE,
                                        CONCAT(
                                            last_name,
                                            ' ',
                                            first_name,
                                            ' ',
                                            middle_name
                                        ),
                                        tin,
                                        basic_salary,
                                        payroll_group_id,
                                        joined_date,
                                        termination_date 
                                        FROM
                                        employees 
                                        WHERE EXTRACT(YEAR FROM joined_date) = ?
                                        AND  EXTRACT(MONTH FROM termination_date) < 12 
                                        AND  EXTRACT(DAY FROM termination_date) < 31
                                        AND is_deleted = 0",array($txtselectedy));

        //A 
        $_result = array();
        $x=1;
        while($data = $all_employees->fetch(PDO::FETCH_NUM)) {;
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

            $tax_status = $con->myQuery("SELECT t.`code`
                                        FROM employees e
                                        JOIN tax_status t
                                        ON e.`tax_status_id` = t.`id`
                                        WHERE e.`id` = ?
                                        AND e.`is_deleted` = 0",array($data[0]))->fetch(PDO::FETCH_NUM);

            $total_exemption = $con->myQuery("SELECT exemption FROM tax_exemptions WHERE tax_code = ?",array($tax_status[0]))->fetch(PDO::FETCH_NUM);

            $net_taxable_comp_income=$_13_month_details[3]-$total_exemption[0]-0;

            $tax_due_details=$con->myQuery("SELECT
                                            amount,
                                            rate,
                                            of_excess_over
                                            FROM
                                            tax_due
                                            WHERE over <= ? AND but_not_over >= ? ",array($net_taxable_comp_income,$net_taxable_comp_income))->fetch(PDO::FETCH_NUM);

            // Result Array
                $_result[] = array(

                    "seq_no"=>$x,
                    "tin"=>$data[3],
                    "fullname"=>$data[2],
                    "from"=>$data[6],
                    "to"=>$data[7],
                    // Gross Compensation Income
                    "gross_compensation"=>$_tax_collected_year[0],
                    "13th_month_pay"=>$_13_month_details[1],
                    "de_minimis_benefits"=>$_13_month_details[4],
                    "goverment_deduction"=>$_13_month_details[5],
                    "salaries_other_forms_compensation"=>"",
                    "total_non_taxable_exempt_compensation_income"=>$_13_month_details[5],
                    // Taxable
                    "basic_salary"=>$_13_month_details[3],
                    "total_taxable_compensation_income"=>$_13_month_details[5],
                    "tax_status"=>$tax_status[0],
                    // Exemption
                    "code"=>$data[1],
                    "amount"=>$total_exemption[0],
                    "premiumpaid_on_health_or_hospital_insurance"=>"",
                    "net_taxable_compensation_income"=>$net_taxable_comp_income,
                    "tax_due_jan_dec"=>$tax_due_details[2],
                    "tax_withheld_jan_nov"=>$_13_month_details[2],
                    // Year End Adjustment
                    "amt_withheld_paid_for_december"=>$tax_due_details[2]-$_13_month_details[2],
                    "over_withheld_tax_employee"=>$_13_month_details[2]-$tax_due_details[2],
                    "amount_tax_withheld_as_adjusted"=>$_13_month_details[2]-($tax_due_details[2]-$_13_month_details[2]),
                    "substituted_filing_yes_no"=>""
                );                                           
            
            $x++;
        }

        // Title
        $objPHPExcel->getProperties()->setCreator("S6")->setTitle("Alphalist Report");

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

        $inputFileName = 'alphalist_file/FPI_7_1.xls';

        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $row=17;
        $start_row=$row;
        $objPHPExcel->getActiveSheet()->getStyle('A6')->getNumberFormat()->setFormatCode('###-###-###-###');
        $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'TIN: '.$info[1]);
        $objPHPExcel->getActiveSheet()->SetCellValue('A7', "WITHHOLDING AGENT'S NAME: ".$info[0]);

        foreach($_result as $data) {
            $row++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['seq_no']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['tin']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['fullname']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['from']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['to']);
            number('F'.$row.':X'.$row);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $data['gross_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $data['13th_month_pay']);
             // Gross Compensation Income
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data['de_minimis_benefits']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $data['goverment_deduction']);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data['total_non_taxable_exempt_compensation_income']);
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $data['basic_salary']);
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $data['13th_month_pay']);
            // Taxable
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $data['total_taxable_compensation_income']);
            // Exemption
            $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $data['tax_status']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $data['amount']);
            $objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $data['premiumpaid_on_health_or_hospital_insurance']);
            $objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $data['net_taxable_compensation_income']);
            $objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, $data['tax_due_jan_dec']);
            $objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, $data['tax_withheld_jan_nov']);
            // Year End Adjustment
            $objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, $data['amt_withheld_paid_for_december']);
            $objPHPExcel->getActiveSheet()->SetCellValue('W'.$row, $data['over_withheld_tax_employee']);
            $objPHPExcel->getActiveSheet()->SetCellValue('X'.$row, $data['amount_tax_withheld_as_adjusted']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$row, $data['substituted_filing_yes_no']);
        }
        
        $row++;
        number('F'.$row.':X'.$row);
        cellColor('F'.$row.':X'.$row,'D3D3D3');
        boldme('F'.$row.':Y'.$row);
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'Grand Total');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, '=SUM(F'.$start_row.':F'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=SUM(G'.$start_row.':G'.($row-1).')');
        // Gross Compensation Income
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, '=SUM(H'.$start_row.':H'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, '=SUM(I'.$start_row.':I'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, '=SUM(J'.$start_row.':J'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, '=SUM(K'.$start_row.':K'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, '=SUM(L'.$start_row.':L'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, '=SUM(M'.$start_row.':M'.($row-1).')');
        // Taxable
        $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, '=SUM(N'.$start_row.':N'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, '=SUM(O'.$start_row.':O'.($row-1).')');
        // Exemption
        $objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, '=SUM(S'.$start_row.':S'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, '=SUM(T'.$start_row.':T'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, '=SUM(U'.$start_row.':U'.($row-1).')');
         // Year End Adjustment
        $objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, '=SUM(V'.$start_row.':V'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('W'.$row, '=SUM(W'.$start_row.':W'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('X'.$row, '=SUM(X'.$start_row.':X'.($row-1).')');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="FPI_7_1.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        die;   
    }elseif(isset($_POST['fpi2'])){
         // Query
        $all_employees = $con->myQuery("SELECT 
                                        id,
                                        CODE,
                                        CONCAT(
                                            last_name,
                                            ' ',
                                            first_name,
                                            ' ',
                                            middle_name
                                        ),
                                        tin,
                                        basic_salary,
                                        payroll_group_id,
                                        joined_date,
                                        termination_date 
                                        FROM
                                        employees 
                                        WHERE EXTRACT(YEAR FROM joined_date) = ?
                                        and EXTRACT(MONTH FROM joined_date) >= 6 
                                        AND  EXTRACT(DAY FROM joined_date) >= 6
                                        AND  EXTRACT(MONTH FROM joined_date) <= 12 
                                        AND  EXTRACT(DAY FROM joined_date) <= 31
                                        AND is_deleted = 0",array($txtselectedy));

        //A 
        $_result = array();
        $x=1;
        while($data = $all_employees->fetch(PDO::FETCH_NUM)) {
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

            $tax_status = $con->myQuery("SELECT t.`code`
                                        FROM employees e
                                        JOIN tax_status t
                                        ON e.`tax_status_id` = t.`id`
                                        WHERE e.`id` = ?
                                        AND e.`is_deleted` = 0",array($data[0]))->fetch(PDO::FETCH_NUM);   

            $total_exemption = $con->myQuery("SELECT exemption FROM tax_exemptions WHERE tax_code = ?",array($tax_status[0]))->fetch(PDO::FETCH_NUM);

            $net_taxable_comp_income=$_13_month_details[3]-$total_exemption[0]-0;

            $tax_due_details=$con->myQuery("SELECT
                                            amount,
                                            rate,
                                            of_excess_over
                                            FROM
                                            tax_due
                                            WHERE over <= ? AND but_not_over >= ? ",array($net_taxable_comp_income,$net_taxable_comp_income))->fetch(PDO::FETCH_NUM);

            // Result Array
                $_result[] = array(

                    "seq_no"=>$x,
                    "tin"=>$data[3],
                    "fullname"=>$data[2],
                    "from"=>$data[6],
                    "to"=>$data[7],
                    // Gross Compensation Income
                    "gross_compensation"=>$_tax_collected_year[0],
                    "13th_month_pay"=>$_13_month_details[1],
                    "de_minimis_benefits"=>$_13_month_details[4],
                    "goverment_deduction"=>$_13_month_details[5],
                    "salaries_other_forms_compensation"=>"",
                    "total_non_taxable_exempt_compensation_income"=>$_13_month_details[5],
                    // Taxable
                    "basic_salary"=>$_13_month_details[3],
                    "total_taxable_compensation_income"=>$_13_month_details[5],
                    "tax_status"=>$tax_status[0],
                    // Exemption
                    "code"=>$data[1],
                    "amount"=>$total_exemption[0],
                    "premiumpaid_on_health_or_hospital_insurance"=>"",
                    "net_taxable_compensation_income"=>$net_taxable_comp_income,
                    "tax_due_jan_dec"=>$tax_due_details[2],
                    "tax_withheld_jan_nov"=>$_13_month_details[2],
                    // Year End Adjustment
                    "amt_withheld_paid_for_december"=>$tax_due_details[2]-$_13_month_details[2],
                    "over_withheld_tax_employee"=>$_13_month_details[2]-$tax_due_details[2],
                    "amount_tax_withheld_as_adjusted"=>$_13_month_details[2]-($tax_due_details[2]-$_13_month_details[2]),
                    "substituted_filing_yes_no"=>""
                );                                           
            
            $x++;
        }
        
        // Title
        $objPHPExcel->getProperties()->setCreator("S6")->setTitle("Alphalist Report");

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

        $inputFileName = 'alphalist_file/FPI_7_2.xls';

        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $row=17;
        $start_row=$row;
        $objPHPExcel->getActiveSheet()->getStyle('B6'.$row)->getNumberFormat()->setFormatCode('###-###-###-###');
        $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'TIN: '.$info[1]);
        $objPHPExcel->getActiveSheet()->SetCellValue('A7', "WITHHOLDING AGENT'S NAME: ".$info[0]);
        foreach($_result as $data) {
            $row++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['seq_no']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['tin']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['fullname']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['from']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['to']);
            number('F'.$row.':Q'.$row);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $data['gross_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $data['13th_month_pay']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data['de_minimis_benefits']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $data['goverment_deduction']);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data['total_non_taxable_exempt_compensation_income']);
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $data['basic_salary']);
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $data['total_taxable_compensation_income']);
            $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $data['tax_status']);
            $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $data['amount']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $data['premiumpaid_on_health_or_hospital_insurance']);
        }

        $row++;
        number('F'.$row.':Q'.$row);
        cellColor('F'.$row.':Q'.$row,'D3D3D3');
        boldme('F'.$row.':Q'.$row);
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'Grand Total');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, '=SUM(F'.$start_row.':F'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=SUM(G'.$start_row.':G'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, '=SUM(H'.$start_row.':H'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, '=SUM(I'.$start_row.':I'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, '=SUM(J'.$start_row.':J'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, '=SUM(K'.$start_row.':K'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, '=SUM(L'.$start_row.':L'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, '=SUM(M'.$start_row.':M'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, '=SUM(N'.$start_row.':N'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, '=SUM(Q'.$start_row.':Q'.($row-1).')');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="FPI_7_2.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        die();
    }elseif(isset($_POST['fpi3'])){
        // Query
        $all_employees = $con->myQuery("SELECT 
                                        e.id,
                                        CODE,
                                        CONCAT(
                                            e.last_name,
                                            ' ',
                                            e.first_name,
                                            ' ',
                                            e.middle_name
                                        ),
                                        e.tin,
                                        e.basic_salary,
                                        e.payroll_group_id,
                                        e.joined_date,
                                        e.termination_date 
                                        FROM
                                        employees e
                                        JOIN employees_employment_history eh
                                        ON e.`id` = eh.`employee_id`
                                        WHERE EXTRACT(YEAR FROM joined_date) = ?
                                        and EXTRACT(MONTH FROM joined_date) <= 12
                                        and EXTRACT(DAY FROM joined_date) <= 31
                                        AND e.is_deleted = 0",array($txtselectedy));

        //A 
        $_result = array();
        $x=1;
        while($data = $all_employees->fetch(PDO::FETCH_NUM)) {
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

            $tax_status = $con->myQuery("SELECT t.`code`
                                        FROM employees e
                                        JOIN tax_status t
                                        ON e.`tax_status_id` = t.`id`
                                        WHERE e.`id` = ?
                                        AND e.`is_deleted` = 0",array($data[0]))->fetch(PDO::FETCH_NUM);                                              
            $total_exemption = $con->myQuery("SELECT exemption FROM tax_exemptions WHERE tax_code = ?",array($tax_status[0]))->fetch(PDO::FETCH_NUM);

            $net_taxable_comp_income=$_13_month_details[3]-$total_exemption[0]-0;

            $tax_due_details=$con->myQuery("SELECT
                                            amount,
                                            rate,
                                            of_excess_over
                                            FROM
                                            tax_due
                                            WHERE over <= ? AND but_not_over >= ? ",array($net_taxable_comp_income,$net_taxable_comp_income))->fetch(PDO::FETCH_NUM);

            // Result Array
                $_result[] = array(

                    "seq_no"=>$x,
                    "tin"=>$data[3],
                    "fullname"=>$data[2],
                    "from"=>$data[6],
                    "to"=>$data[7],
                    // Gross Compensation Income
                    "gross_compensation"=>$_tax_collected_year[0],
                    "13th_month_pay"=>$_13_month_details[1],
                    "de_minimis_benefits"=>$_13_month_details[4],
                    "goverment_deduction"=>$_13_month_details[5],
                    "salaries_other_forms_compensation"=>"",
                    "total_non_taxable_exempt_compensation_income"=>$_13_month_details[5],
                    // Taxable
                    "basic_salary"=>$_13_month_details[3],
                    "total_taxable_compensation_income"=>$_13_month_details[5],
                    "tax_status"=>$tax_status[0],
                    // Exemption
                    "code"=>$data[1],
                    "amount"=>$total_exemption[0],
                    "premiumpaid_on_health_or_hospital_insurance"=>"",
                    "net_taxable_compensation_income"=>$net_taxable_comp_income,
                    "tax_due_jan_dec"=>$tax_due_details[2],
                    "tax_withheld_jan_nov"=>$_13_month_details[2],
                    // Year End Adjustment
                    "amt_withheld_paid_for_december"=>$tax_due_details[2]-$_13_month_details[2],
                    "over_withheld_tax_employee"=>$_13_month_details[2]-$tax_due_details[2],
                    "amount_tax_withheld_as_adjusted"=>$_13_month_details[2]-($tax_due_details[2]-$_13_month_details[2]),
                    "substituted_filing_yes_no"=>""
                );                                           
            
            $x++;
        }

          // Title
        $objPHPExcel->getProperties()->setCreator("S6")->setTitle("Alphalist Report");

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

        $inputFileName = 'alphalist_file/FPI_7_3.xls';

        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $row=16;
        $start_row=$row;
        $objPHPExcel->getActiveSheet()->getStyle('B6'.$row)->getNumberFormat()->setFormatCode('###-###-###-###');
        $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'TIN: '.$info[1]);
        $objPHPExcel->getActiveSheet()->SetCellValue('A7', "WITHHOLDING AGENT'S NAME: ".$info[0]);
        foreach($_result as $data) {
            $row++;
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':W'.$row)->ge‌​tNumberFormat()->set‌​FormatCode (PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['seq_no']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['tin']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['fullname']);
            number('B'.$row.':V'.$row);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['gross_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['13th_month_pay']);
             // Gross Compensation Income
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $data['de_minimis_benefits']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $data['goverment_deduction']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $data['total_non_taxable_exempt_compensation_income']);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['basic_salary']);
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data['13th_month_pay']);
            // Taxable
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $data['total_taxable_compensation_income']);
            // Exemption
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $data['tax_status']);
            $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $data['amount']);
            $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $data['premiumpaid_on_health_or_hospital_insurance']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $data['net_taxable_compensation_income']);
            $objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $data['tax_due_jan_dec']);
            $objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $data['tax_withheld_jan_nov']);
            // Year End Adjustment
            $objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, $data['amt_withheld_paid_for_december']);
            $objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, $data['over_withheld_tax_employee']);
            $objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, $data['amount_tax_withheld_as_adjusted']);
            $objPHPExcel->getActiveSheet()->SetCellValue('W'.$row, $data['substituted_filing_yes_no']);
        }

        $row++;
        number('B'.$row.':V'.$row);
        cellColor('D'.$row.':V'.$row,'D3D3D3');
        boldme('B'.$row.':V'.$row);
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'Grand Total');
        // Gross Compensation Income
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, '=SUM(D'.$start_row.':D'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, '=SUM(E'.$start_row.':E'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, '=SUM(F'.$start_row.':F'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=SUM(G'.$start_row.':G'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, '=SUM(H'.$start_row.':H'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, '=SUM(I'.$start_row.':I'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, '=SUM(J'.$start_row.':J'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, '=SUM(K'.$start_row.':K'.($row-1).')');
        // Taxable
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, '=SUM(L'.$start_row.':L'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, '=SUM(M'.$start_row.':M'.($row-1).')');
        // Exemption
        $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, '=SUM(Q'.$start_row.':Q'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, '=SUM(R'.$start_row.':R'.($row-1).')');
         // Year End Adjustment
        $objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, '=SUM(S'.$start_row.':S'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, '=SUM(T'.$start_row.':T'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, '=SUM(X'.$start_row.':U'.($row-1).')');
        $objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, '=SUM(V'.$start_row.':V'.($row-1).')');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="FPI_7_3.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        die;   
    }elseif(isset($_POST['fpi4'])){
        // Query
        $all_employees = $con->myQuery("SELECT 
                                        e.id,
                                        CODE,
                                        CONCAT(
                                            e.last_name,
                                            ' ',
                                            e.first_name,
                                            ' ',
                                            e.middle_name
                                        ),
                                        e.tin,
                                        e.basic_salary,
                                        e.payroll_group_id,
                                        e.joined_date,
                                        e.termination_date 
                                        FROM
                                        employees e
                                        JOIN employees_employment_history eh
                                        ON e.`id` = eh.`employee_id`
                                        WHERE EXTRACT(YEAR FROM joined_date) = ?
                                        and EXTRACT(MONTH FROM joined_date) <= 12
                                        and EXTRACT(DAY FROM joined_date) <= 31
                                        AND EXTRACT(YEAR FROM joined_date) <= date('y')
                                        AND e.is_deleted = 0",array($txtselectedy));

        //A 
        $_result = array();
        $x=1;
        while($data = $all_employees->fetch(PDO::FETCH_NUM)) {
            // // Validation
            // $checker_no_previous_employer = $con->myQuery("SELECT COUNT(e.`employee_id`)
            //                                             FROM employees_employment_history e
            //                                             WHERE e.`employee_id` = ? AND MONTH(e.`date_start`) = 12 AND DATE(e.`date_start`) = 31",array($data[0]))->fetch(PDO::FETCH_NUM);
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

            $tax_status = $con->myQuery("SELECT t.`code`
                                        FROM employees e
                                        JOIN tax_status t
                                        ON e.`tax_status_id` = t.`id`
                                        WHERE e.`id` = ?
                                        AND e.`is_deleted` = 0",array($data[0]))->fetch(PDO::FETCH_NUM);

            $total_exemption = $con->myQuery("SELECT exemption FROM tax_exemptions WHERE tax_code = ?",array($tax_status[0]))->fetch(PDO::FETCH_NUM);

            $net_taxable_comp_income=$_13_month_details[3]-$total_exemption[0]-0;

            $tax_due_details=$con->myQuery("SELECT
                                            amount,
                                            rate,
                                            of_excess_over
                                            FROM
                                            tax_due
                                            WHERE over <= ? AND but_not_over >= ? ",array($net_taxable_comp_income,$net_taxable_comp_income))->fetch(PDO::FETCH_NUM);                                           

                // Result Array
                $_result[] = array(

                    "seq_no"=>$x,
                    "tin"=>$data[3],
                    "fullname"=>$data[2],
                    "from"=>$data[6],
                    "to"=>$data[7],
                    // Gross Compensation Income
                    "gross_compensation"=>$_tax_collected_year[0],
                    "13th_month_pay"=>$_13_month_details[1],
                    "de_minimis_benefits"=>$_13_month_details[4],
                    "goverment_deduction"=>$_13_month_details[5],
                    "salaries_other_forms_compensation"=>"",
                    "total_taxable"=>$_13_month_details[3]+$_13_month_details[1]+0,
                    "total_non_taxable_exempt_compensation_income"=>$_13_month_details[5],
                    // Taxable
                    "basic_salary"=>$_13_month_details[3],
                    "total_taxable_compensation_income"=>$_13_month_details[5],
                    "tax_status"=>$tax_status[0],
                    // Exemption
                    "code"=>$data[1],
                    "amount"=>$total_exemption[0],
                    "premiumpaid_on_health_or_hospital_insurance"=>"",
                    "net_taxable_compensation_income"=>$net_taxable_comp_income,
                    "tax_due_jan_dec"=>$tax_due_details[2],
                    "tax_withheld_jan_nov"=>$_13_month_details[2],
                    // Year End Adjustment
                    "withholding_tax"=>$_13_month_details[2],
                    "amt_withheld_paid_for_december"=>$tax_due_details[2]-$_13_month_details[2],
                    "over_withheld_tax_employee"=>$_13_month_details[2]-$tax_due_details[2],
                    "amount_tax_withheld_as_adjusted"=>$_13_month_details[2]-($tax_due_details[2]-$_13_month_details[2]),
                );                                           
            
            $x++;
        }

        // Title
        $objPHPExcel->getProperties()->setCreator("S6")->setTitle("Alphalist Report");

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

        $inputFileName = 'alphalist_file/FPI_7_4.xls';

        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $row=16;
        $start_row=$row;
        $objPHPExcel->getActiveSheet()->getStyle('B6'.$row)->getNumberFormat()->setFormatCode('###-###-###-###');
        $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'TIN: '.$info[1]);
        $objPHPExcel->getActiveSheet()->SetCellValue('A7', "WITHHOLDING AGENT'S NAME: ".$info[0]);
         foreach($_result as $data) {
            $row++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['seq_no']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['tin']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['fullname']);
            number('D'.$row.':AG'.$row);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['gross_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['13th_month_pay']);
             // Gross Compensation Income
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $data['de_minimis_benefits']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $data['goverment_deduction']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $data['total_non_taxable_exempt_compensation_income']);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['basic_salary']);
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data['13th_month_pay']);
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $data['salaries_other_forms_compensation']);
            // Present Employer
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $data['total_taxable']);
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $data['13th_month_pay']);
            $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $data['de_minimis_benefits']);
            $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $data['goverment_deduction']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $data['total_non_taxable_exempt_compensation_income']);
            // Taxable
            $objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $data['basic_salary']);
            $objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, $data['13th_month_pay']);
            $objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, $data['salaries_other_forms_compensation']);
            $objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, $data['total_non_taxable_exempt_compensation_income']);
            $objPHPExcel->getActiveSheet()->SetCellValue('W'.$row, $data['total_taxable']);
            // Exemption
            $objPHPExcel->getActiveSheet()->SetCellValue('X'.$row, $data['tax_status']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$row, $data['amount']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$row, $data['premiumpaid_on_health_or_hospital_insurance']);
            $objPHPExcel->getActiveSheet()->SetCellValue('AA'.$row, $data['net_taxable_compensation_income']);
            $objPHPExcel->getActiveSheet()->SetCellValue('AB'.$row, $data['tax_due_jan_dec']);
            // Tax Withheld
            $objPHPExcel->getActiveSheet()->SetCellValue('AC'.$row, "");
            $objPHPExcel->getActiveSheet()->SetCellValue('AD'.$row, $data['withholding_tax']);
            // Year Adjustment
            $objPHPExcel->getActiveSheet()->SetCellValue('AE'.$row, $data['amt_withheld_paid_for_december']);
            $objPHPExcel->getActiveSheet()->SetCellValue('AF'.$row, $data['over_withheld_tax_employee']);
            $objPHPExcel->getActiveSheet()->SetCellValue('AG'.$row, $data['amount_tax_withheld_as_adjusted']);
         }
        
          $row++;
          number('D'.$row.':AG'.$row);
          cellColor('D'.$row.':AG'.$row,'D3D3D3');
          boldme('D'.$row.':AG'.$row);
          $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'Grand Total');
          $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, '=SUM(D'.$start_row.':D'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, '=SUM(E'.$start_row.':E'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, '=SUM(F'.$start_row.':F'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=SUM(G'.$start_row.':G'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, '=SUM(H'.$start_row.':H'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, '=SUM(I'.$start_row.':I'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, '=SUM(J'.$start_row.':J'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, '=SUM(K'.$start_row.':K'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, '=SUM(L'.$start_row.':L'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, '=SUM(M'.$start_row.':M'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, '=SUM(N'.$start_row.':N'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, '=SUM(O'.$start_row.':O'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, '=SUM(P'.$start_row.':P'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, '=SUM(Q'.$start_row.':Q'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, '=SUM(R'.$start_row.':R'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, '=SUM(S'.$start_row.':S'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, '=SUM(T'.$start_row.':T'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, '=SUM(U'.$start_row.':U'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, '=SUM(V'.$start_row.':V'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('W'.$row, '=SUM(W'.$start_row.':W'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('AA'.$row, '=SUM(AA'.$start_row.':AA'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('AB'.$row, '=SUM(AB'.$start_row.':AB'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('AC'.$row, '=SUM(AC'.$start_row.':AC'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('AD'.$row, '=SUM(AD'.$start_row.':AD'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('AE'.$row, '=SUM(AE'.$start_row.':AE'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('AF'.$row, '=SUM(AF'.$start_row.':AF'.($row-1).')');
          $objPHPExcel->getActiveSheet()->SetCellValue('AG'.$row, '=SUM(AG'.$start_row.':AG'.($row-1).')');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="FPI_7_4.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        die; 
    }elseif(isset($_POST['fpi5'])){
        // Query
        $all_employees = $con->myQuery("SELECT 
                                        e.id,
                                        CODE,
                                        CONCAT(
                                            e.last_name,
                                            ' ',
                                            e.first_name,
                                            ' ',
                                            e.middle_name
                                        ),
                                        e.tin,
                                        e.basic_salary,
                                        e.payroll_group_id,
                                        e.joined_date,
                                        e.termination_date 
                                        FROM
                                        employees e
                                        WHERE EXTRACT(YEAR FROM joined_date) = ?
                                        and e.is_deleted = 0",array($txtselectedy));

        //A 
        $_result = array();
        $x=1;
        while($data = $all_employees->fetch(PDO::FETCH_NUM)) {
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

            $tax_status = $con->myQuery("SELECT t.`code`
                                        FROM employees e
                                        JOIN tax_status t
                                        ON e.`tax_status_id` = t.`id`
                                        WHERE e.`id` = ?
                                        AND e.`is_deleted` = 0",array($data[0]))->fetch(PDO::FETCH_NUM);

            $total_exemption = $con->myQuery("SELECT exemption FROM tax_exemptions WHERE tax_code = ?",array($tax_status[0]))->fetch(PDO::FETCH_NUM);

            $net_taxable_comp_income=$_13_month_details[3]-$total_exemption[0]-0;

            $tax_due_details=$con->myQuery("SELECT
                                            amount,
                                            rate,
                                            of_excess_over
                                            FROM
                                            tax_due
                                            WHERE over <= ? AND but_not_over >= ? ",array($net_taxable_comp_income,$net_taxable_comp_income))->fetch(PDO::FETCH_NUM);

                // Result Array
                $_result[] = array(

                    "seq_no"=>$x,
                    "tin"=>$data[3],
                    "fullname"=>$data[2],
                    "from"=>$data[6],
                    "to"=>$data[7],
                    // Gross Compensation Income
                    "gross_compensation"=>$_tax_collected_year[0],
                    "13th_month_pay"=>$_13_month_details[1],
                    "de_minimis_benefits"=>$_13_month_details[4],
                    "goverment_deduction"=>$_13_month_details[5],
                    "salaries_other_forms_compensation"=>"",
                    "total_taxable"=>$_13_month_details[3]+$_13_month_details[1]+0,
                    "total_non_taxable_exempt_compensation_income"=>$_13_month_details[5],
                    // Taxable
                    "basic_salary"=>$_13_month_details[3],
                    "total_taxable_compensation_income"=>$_13_month_details[5],
                    "tax_status"=>$tax_status[0],
                    // Exemption
                    "code"=>$data[1],
                    "amount"=>$total_exemption[0],
                    "premiumpaid_on_health_or_hospital_insurance"=>"",
                    "net_taxable_compensation_income"=>$net_taxable_comp_income,
                    "tax_due_jan_dec"=>$tax_due_details[2],
                    "tax_withheld_jan_nov"=>$_13_month_details[2],
                    // Year End Adjustment
                    "withholding_tax"=>$_13_month_details[2],
                    "amt_withheld_paid_for_december"=>$tax_due_details[2]-$_13_month_details[2],
                    "over_withheld_tax_employee"=>$_13_month_details[2]-$tax_due_details[2],
                    "amount_tax_withheld_as_adjusted"=>$_13_month_details[2]-($tax_due_details[2]-$_13_month_details[2]),
                );                                                         
            
            $x++;
        }

        // Title
        $objPHPExcel->getProperties()->setCreator("S6")->setTitle("Alphalist Report");

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

        $inputFileName = 'alphalist_file/FPI_7_5.xls';

        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $row=15;
        $start_row=$row;
        $objPHPExcel->getActiveSheet()->getStyle('B6'.$row)->getNumberFormat()->setFormatCode('###-###-###-###');
        $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'TIN: '.$info[1]);
        $objPHPExcel->getActiveSheet()->SetCellValue('A7', "WITHHOLDING AGENT'S NAME: ".$info[0]);
        foreach($_result as $data) {
            $row++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['seq_no']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['tin']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, "");
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['fullname']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['tax_status']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, "");
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, "");
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, "");
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $data['tax_withheld_jan_nov']);
        }

        $row++;
        number('I'.$row);
        cellColor('I'.$row,'D3D3D3');
        boldme('I'.$row);
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'Grand Total');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, '=SUM(I'.$start_row.':I'.($row-1).')');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="FPI_7_5.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        die(); 
    }else{
        echo 'no data';
        die();
    }
?>