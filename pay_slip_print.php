<?php
require_once 'support/config.php';
if (!isLoggedIn()) {
    toLogin();
    die();
}

if (!empty($_GET['pc'])) {
    $id = $_SESSION[WEBAPP]['user']['employee_id'];
    $company_profile=$con->myQuery("SELECT name, address, email, contact_no, website, foundation_day, fax_no FROM company_profile LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $getPayDetails=$con->myQuery("SELECT 
        e.code,
        p.payroll_code,
        CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as name,
        e.sss_no,
        e.tin,
        e.pagibig,
        e.philhealth,
        p.tax_compensation,
        p.basic_salary,
        p.late,
        p.absent,
        p.payroll_adjustment_minus,
        p.payroll_adjustment_plus,
        p.overtime,
        p.receivable,
        p.company_deduction,
        p.tax_earning AS gross,
        p.government_deduction,
        p.withholding_tax,
        p.total_deduction,
        p.loan,
        p.de_minimis,
        p.13th_month,
        p.net_pay
        FROM payroll_details p 
        INNER JOIN employees e ON p.employee_id=e.id 
        WHERE p.employee_id = ? AND p.payroll_code=?", array($id,$_GET['pc']))->fetch(PDO::FETCH_ASSOC);



    $getGovDeDetails=$con->myQuery("SELECT
        sss.sss_amount as 'sss_amount',
        philhealth.philhealth_amount as 'philhealth_amount',
        hdmf.hdmf_amount as 'hdmf_amount'
        FROM (SELECT
        payroll_code,
        employee_id
        FROM payroll_details
        WHERE payroll_code = ? AND employee_id = ?
        ) as details 
        LEFT OUTER JOIN(SELECT
        payroll_code,
        employee_id,
        govde_eeshare as 'sss_amount'
        FROM
        payroll_govde
        WHERE gov_desc = 'SSS') as sss ON details.payroll_code = sss.payroll_code AND details.employee_id = sss.employee_id
        LEFT OUTER JOIN(SELECT
        payroll_code,
        employee_id,
        govde_eeshare as 'philhealth_amount'
        FROM
        payroll_govde
        WHERE gov_desc = 'PhilHealth') as philhealth ON details.payroll_code = philhealth.payroll_code AND details.employee_id = philhealth.employee_id
        LEFT OUTER JOIN(SELECT
        payroll_code,
        employee_id,
        govde_eeshare as 'hdmf_amount'
        FROM
        payroll_govde
        WHERE gov_desc = 'HDMF') as hdmf ON details.payroll_code = hdmf.payroll_code AND details.employee_id = hdmf.employee_id", array($id,$_GET['pc']))->fetch(PDO::FETCH_ASSOC);

        // $getSSSdeduc=$con->myQuery("SELECT govde_eeshare FROM payroll_govde WHERE employee_id=? AND gov_desc='SSS'",array($_GET['cd']))->fetch(PDO::FETCH_ASSOC);
        // $getPhealthdeduc=$con->myQuery("SELECT govde_eeshare FROM payroll_govde WHERE employee_id=? AND gov_desc='PhilHealth'",array($_GET['cd']))->fetch(PDO::FETCH_ASSOC);
        // $getHdmfdeduc=$con->myQuery("SELECT govde_eeshare FROM payroll_govde WHERE employee_id=? AND gov_desc='HDMF'",array($_GET['cd']))->fetch(PDO::FETCH_ASSOC);

        // $total_earn = (doubleval($getPayDetails['emp_basicpay'])+doubleval($getPayDetails['emp_taxallowance'])+doubleval($getPayDetails['emp_ot'])+doubleval($getPayDetails['emp_receivable'])+doubleval($getPayDetails['emp_deminimis'])+doubleval($getPayDetails['emp_payrolladjustment'])+doubleval($getPayDetails['emp_ut']))-(doubleval($getPayDetails['emp_absent'])+doubleval($getPayDetails['emp_late'])+doubleval($getPayDetails['emp_leaveWOpay']));

        // $total_deduc = (doubleval($getPayDetails['emp_wtax'])+doubleval($getSSSdeduc['govde_eeshare'])+doubleval($getPhealthdeduc['govde_eeshare'])+doubleval($getHdmfdeduc['govde_eeshare']));
        $leave_bal=$con->myQuery("SELECT 
            eal.leave_id,
            (SELECT NAME FROM LEAVES WHERE id=eal.leave_id) as leave_type,
            eal.balance_per_year as leave_bal
            FROM employees_available_leaves eal
            WHERE is_cancelled=0 AND is_deleted=0 AND employee_id=?",array($id))->fetchAll(PDO::FETCH_ASSOC);
} else {
    redirect("../pay_slip.php");
}



makeHead("Pay Slip");
?>
<div class='col-xs-12 no-print' align='right'>
    <br>
    <a href='pay_slip.php' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Back</a>
    <button onclick='window.print()'  class='btn btn-brand no-print'>Print &nbsp;<span class='fa fa-print'></span></button>  
</div>
<div class='page'>
    <div class="row">
        <br><br>
        <h2 align="center" > <b><?php echo htmlspecialchars($company_profile['name']) ?> </b></h2>
        <h4 align="center" > <?php echo htmlspecialchars($company_profile['address']) ?> </h4>
        <br>
        <div class="col-xs-12" style="padding-left: 50px" >
            <p align="left"  >Date Print : <?php echo date("d/m/Y") ?></p>
        </div>
    </div>
    <!--<?php 
        var_dump($_SESSION);
        var_dump($getPayDetails);
    ?>-->
    <div class="row col-xs-12" >
        <br>
        <br>
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-default" align="left" style="background-color: #FFFFFF; padding: 15px">
                    <table>
                        <tr>
                            <td class="text-left" style="width:50%">Emp Code : </th>
                                <td class="text-left" style="width:50%"><?php echo htmlspecialchars($getPayDetails['code']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-left" style="width:50%">Name:</th>
                                    <td class="text-left" style="width:50%"><?php echo htmlspecialchars($getPayDetails['name'])?></td>
                                </tr>
                                <tr>
                                    <th class="text-left" style="width:150px">SSS No : </th>
                                    <td class="text-left" style="width:50%"><?php echo htmlspecialchars($getPayDetails['sss_no'])?> </td>
                                </tr>
                                <tr>
                                    <th class="text-left" style="width:50%">TIN No:  </th>
                                    <td class="text-left" style="width:50%"><?php echo htmlspecialchars($getPayDetails['tin'])?> </td>
                                </tr>
                                <tr>
                                    <th class="text-left" style="width:50%">HDMF No : </th>
                                    <td class="text-left" style="width:50%"><?php echo htmlspecialchars($getPayDetails['pagibig'])?> </td>
                                </tr>
                                <tr>
                                    <th class="text-left" style="width:50%">PhilHealth No : </th>
                                    <td class="text-left" style="width:50%"><?php echo htmlspecialchars($getPayDetails['philhealth'])?> </td>
                                </tr>
                                <tr>
                                    <th class="text-left" style="width:50%">Payroll Code : </th>
                                    <td class="text-left" style="width:50%"><?php echo htmlspecialchars($getPayDetails['payroll_code'])?> </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="box box-default">
                        <div class="box-body">
                            <div class="col-xs-6"  >
                                <div class="col-xs-12 " style="background-color: #FFFFFF; padding: 15px">
                                    <h4 >COMPENSATION</h4>
                                    <br>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Basic Salary :</label>
                                        <div class="col-xs-7 text-left">
                                            <label class="control-label pull-right"><?php echo number_format($getPayDetails['basic_salary'], 2) ?></label>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Overtime :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getPayDetails['overtime'], 2) ?></label>
                                        </div>
                                    </div>                      
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Taxable Allowance :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getPayDetails['receivable'], 2) ?></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Adjustment :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getPayDetails['payroll_adjustment_plus'], 2) ?></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">De Minimis :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getPayDetails['de_minimis'], 2) ?></label>
                                        </div>
                                    </div>
                                    <br><br>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Gross Pay :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo $getPayDetails['gross'] ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-6"  >
                                <div class="col-xs-12 " style="background-color: #FFFFFF; padding: 15px">
                                    <h4>DEDUCTION</h4>
                                    <br>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Late/Undertime :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getPayDetails['late'], 2) ?></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Absences :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getPayDetails['absent'], 2) ?></label>
                                        </div>
                                    </div>                      
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Adjustment :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getPayDetails['payroll_adjustment_minus'], 2) ?></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Company Deductions :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getPayDetails['company_deduction'], 2) ?></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">SSS :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getGovDeDetails['sss_amount'], 2) ?></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Philhealth :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getGovDeDetails['philhealth_amount'], 2) ?></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">HDMF :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo number_format($getGovDeDetails['hdmf_amount'], 2) ?></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Tax :</label>
                                        <div class="col-xs-7">  
                                            <label class="control-label pull-right"><?php echo $getPayDetails['withholding_tax'] ?></label>
                                        </div>
                                    </div>
                                    <br><br>
                                    <div class="row">
                                        <label class="col-xs-5 control-label">Total Deductions :</label>
                                        <div class="col-xs-7">
                                            <label class="control-label pull-right"><?php echo $getPayDetails['total_deduction'] ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="box box-default">
                        <div class="box-body">
                            <div class="col-xs-6">
                                <h4></h4>
                                <div class="row">
                                    <label class="col-xs-5 control-label">&nbsp;&nbsp;&nbsp; 13th Month :</label>
                                    <div class="col-xs-7">
                                        <label class="control-label pull-right"><?php echo number_format($getPayDetails['13th_month'],2) ?></label>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <label class="col-xs-5 control-label">&nbsp;&nbsp;&nbsp; Net Pay :</label>
                                    <div class="col-xs-7">
                                        <label class="control-label pull-right"><?php echo number_format($getPayDetails['net_pay'], 2) ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <h4>LEAVES</h4>
                                <?php
                                foreach ($leave_bal as $value) :
                                ?>
                                    <div class="row">
                                        <label class="col-xs-7 control-label"><?php echo htmlspecialchars($value['leave_type']) ?> :</label>
                                        <div class="col-xs-5 text-left">
                                            <label class="control-label pull-right"><?php echo htmlspecialchars($value['leave_bal']) ?></label>
                                        </div>
                                    </div>
                                    <br/>
                                <?php
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="box box-default">
                        <div class="box-body">
                            <div class="col-xs-12">
                                <h4>LOANS</h4>
                                <?php
                                $complete_query="SELECT 
                                emp_loans.emp_loan_id,
                                emp_loans.emp_loan_id as code, 
                                loans.loan_name as loan_name, 
                                emp_loans.loan_amount as loan_amount,
                                emp_loans.balance as balance, 
                                CONCAT(employees.last_name,', ',employees.first_name,' ',employees.middle_name) AS employee_name, 
                                loan_status.status_name as status 
                                FROM `emp_loans` 
                                INNER JOIN employees ON employees.id=emp_loans.employee_id 
                                INNER JOIN loans ON loans.loan_id=emp_loans.loan_id
                                INNER JOIN loan_status ON loan_status.status_id=emp_loans.status_id WHERE employee_id = {$id} AND loan_status.status_id IN (1, 2)";    
                                $employee_loans = $con->myQuery($complete_query)->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <table class="table">
                                <thead>
                                    <tr>
                                        <th>Loan</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($employee_loans as $employee_loan) :
                                ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($employee_loan['loan_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(number_format($employee_loan['loan_amount'],2)); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(number_format($employee_loan['balance'],2)); ?>
                                    </td>
                                </tr>
                                <?php
                                endforeach;
                                ?>
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <br>
            </div>
        </div>


