<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    // Variable
    $txtselectedy = (isset($_POST['txtselectedy']) && !empty($_POST['txtselectedy']))? $_POST['txtselectedy'] : '';

    // Single or Married
    $personal_exemption = 50000;

    // 4 maximum , if 21 years old hindi na masasama sa computation
    $additional_exemption = 50000;
    
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
            "gross_compensation"=>number_format($_tax_collected_year[0],2),
            "13th_month_pay"=>number_format($_13_month_details[1],2),
            "de_minimis_benefits"=>number_format($_13_month_details[4],2),
            "goverment_deduction"=>number_format($_13_month_details[5],2),
            "salaries_other_forms_compensation"=>"none",
            "total_non_taxable_exempt_compensation_income"=>"none",
            "basic_salary"=>number_format($_13_month_details[3],2),
            "salaries_other_forms_compensation"=>"none",
            "code"=>"none",
            "amound"=>"none",
            "premiumpaid_on_health_or_hospital_insurance"=>"none",
            "net_taxable_compensation_income"=>number_format($_13_month_details[2],2),
            "tax_due"=>"none",
            // Schedule 7.2
            "total_taxable_compensation_income"=>number_format($_13_month_details[5],2)
        );

        $x++;
    }
 
    // echo "<pre>";
    // print_r($_result);
    // echo "</pre>";
    // die();
    makeHead("Alphalist",1);
?>

<?php
    // Modal();
    makeFoot(WEBAPP,1);
?>