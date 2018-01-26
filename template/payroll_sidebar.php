<aside class="main-sidebar bg-LightGray ">
    <section class="sidebar">
        <ul class="sidebar-menu">
    <!--  DASHBOARD -->
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="index.php"?"active":"";?>">
                <a href="index.php">            
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>
    <!-- PAYROLL -->
            <!-- <li class='header'>PAYROLL</li> -->
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_payroll_maintenance.php"?"active":"";?>">
                <a href="view_payroll_maintenance.php">
                    <i class="fa fa-money"></i> <span>Generate Payroll</span>
                </a>
            </li>
    <!-- 13TH MONTH -->
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="13th_month.php"?"active":"";?>">
                <a href="13th_month.php">
                    <i class="fa fa-gift"></i> <span>13th Month</span>
                </a>
            </li>
    <!-- LEAVE CONVERSION -->
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="leave_conversion.php"?"active":"";?>">
                <a href="leave_conversion.php">
                    <i class="fa fa-gift"></i> <span>Leave Credits Conversion</span>
                </a>
            </li>
    <!-- LAST PAY -->
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="last_pay.php"?"active":"";?>">
                <a href="last_pay.php">
                    <i class="fa fa-money"></i> <span>Last Pay Computation</span>
                </a>
            </li>
    <!-- PAYROLL ADJUSTMENTS -->
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="frm_payroll_adjustment.php"?"active":"";?>">
                <a href="frm_payroll_adjustment.php">
                    <i class="fa fa-adjust"></i> <span>Payroll Adjustment</span>
                </a>
            </li>
    <!-- REPORTS -->
            <li class='header'>PAYROLL REPORTS</li>
            <li class='treeview <?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("pay_journal.php","pay_slip.php","report_govde.php","sched_net_pay.php","with_tax.php","late_overtime.php","company_deduction.php")))?"active":"";?>'>
                <a href="#">
                    <i class="fa fa-file-text"></i>
                    <span>Reports</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class='treeview-menu'>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="report_govde.php"?"active":"";?>">
                        <a href="report_govde.php"><i class="fa fa-file-text-o"></i> <span>Government Deduction</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="pay_journal.php"?"active":"";?>">
                        <a href="pay_journal.php"><i class="fa fa-file-text-o"></i> <span>Pay Journal</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="pay_slip.php"?"active":"";?>">
                        <a href="pay_slip.php"><i class="fa fa-file-text-o"></i> <span>Pay Slip</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="company_deduction.php"?"active":"";?>">
                        <a  href="#" data-toggle="modal" data-target="#comde"><i class="fa fa-file-text-o"></i> <span>Company Deduction</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="late_overtime.php"?"active":"";?>">
                        <a  href="#" data-toggle="modal" data-target="#lateover"><i class="fa fa-file-text-o"></i> <span>Late and Overtime</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="sched_net_pay.php"?"active":"";?>">
                        <a href="#" data-toggle="modal" data-target="#netpay"><i class="fa fa-file-text-o"></i> <span>Schedule of Net Pay</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="with_tax.php"?"active":"";?>">
                        <a  href="#" data-toggle="modal" data-target="#withtax"><i class="fa fa-file-text-o"></i> <span>Witholding Tax</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="alpha_list.php"?"active":"";?>">
                        <a  href="alpha_list.php"><i class="fa fa-file-text-o"></i> <span>Alphalist</span></a>
                    </li>
                    <li class='<?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("view_ph_er2.php", "view_ph_rf1.php")))?"active":"";?>'>
                        <a href="#">
                            <i class="fa fa-file-text-o"></i>
                            <span>Philhealth</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class='treeview-menu'>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_ph_er2.php"?"active":"";?>">
                                <a href="view_ph_er2.php"><i class="fa fa-circle-o"></i> <span>ER-2</span></a>
                            </li>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_ph_rf1.php"?"active":"";?>">
                                <a href="view_ph_rf1.php"><i class="fa fa-circle-o"></i> <span>RF-1</span></a>
                            </li>
                        </ul>
                    </li>
                    <li class='<?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("view_sss_r1a.php", "")))?"active":"";?>'>
                        <a href="#">
                            <i class="fa fa-file-text-o"></i>
                            <span>Social Security System</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class='treeview-menu'>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_sss_r1a.php"?"active":"";?>">
                                <a href="view_sss_r1a.php"><i class="fa fa-circle-o"></i> <span>R-1A</span></a>
                            </li>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_sss_r3.php"?"active":"";?>">
                                <a href="view_sss_r3.php"><i class="fa fa-circle-o"></i> <span>R-3</span></a>
                            </li>
                        </ul>
                    </li>
                    <li class='<?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("view_hdmf_m1.php", "")))?"active":"";?>'>
                        <a href="#">
                            <i class="fa fa-file-text-o"></i>
                            <span>Pag Ibig</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class='treeview-menu'>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_hdmf_m1.php"?"active":"";?>">
                                <a href="view_hdmf_m1.php"><i class="fa fa-circle-o"></i> <span>R-1A</span></a>
                            </li>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_sss_r3.php"?"active":"";?>">
                                <a href="view_sss_r3.php"><i class="fa fa-circle-o"></i> <span></span></a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
    <!-- PAYROLL SETTINGS -->   
            <li class='header'>PAYROLL SETTINGS</li>
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_loan.php"?"active":"";?>">
                <a href="view_loan.php">
                    <i class="fa fa-money"></i> <span>Employee's Loans</span>
                </a>
            </li>
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_payroll_group_rates.php"?"active":"";?>">
                <a href="view_payroll_group_rates.php">
                    <i class="fa fa-gears"></i> <span>Payroll Group Rates</span>
                </a>
            </li>
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_shifting_sched.php"?"active":"";?>">
                <a href="view_shifting_sched.php">
                    <i class="fa fa-calendar-plus-o"></i> <span>Shifting Schedule</span>
                </a>
            </li>
    <!-- ADMINISTRATOR -->
            <li class='header'>ADMINISTRATOR MENU</li>
            <li class='treeview <?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("view_payrollgroups.php","view_shift.php","taxable_allowances.php","deminimis.php","view_sss.php","view_phealth.php","view_housing.php","view_tax.php")))?"active":"";?>'>
                <a href=''><i class="fa fa-gear"></i><span>Administrator</span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class='treeview-menu'>
                    
                    
                    <li class='<?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("view_sss.php","view_phealth.php","view_housing.php","view_tax.php","view_loan_list.php")))?"active":"";?>'>
                        <a href="#">
                            <i class="fa fa-table"></i>
                            <span>Government Tables</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class='treeview-menu'>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_sss.php"?"active":"";?>">
                                <a href="view_sss.php"><i class="fa fa-circle-o"></i> <span>SSS Table</span></a>
                            </li>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_phealth.php"?"active":"";?>">
                                <a href="view_phealth.php"><i class="fa fa-circle-o"></i> <span>Philhealth Table</span></a>
                            </li>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_housing.php"?"active":"";?>">
                                <a href="view_housing.php"><i class="fa fa-circle-o"></i> <span>Pagibig Table</span></a>
                            </li>
                            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_tax.php"?"active":"";?>">
                                <a href="view_tax.php"><i class="fa fa-circle-o"></i> <span>Tax Table</span></a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_loan_list.php"?"active":"";?>">
                        <a href="view_loan_list.php">
                            <i class="fa fa-users"></i> <span>Loan Types</span>
                        </a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_payrollgroups.php"?"active":"";?>">
                        <a href="view_payrollgroups.php">
                            <i class="fa fa-users"></i> <span>Payroll Groups</span>
                        </a>
                    </li>
                    
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="view_shift.php"?"active":"";?>">
                        <a href="view_shift.php">
                            <i class="fa fa-calendar"></i> <span>Shifts</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </section>
</aside>

<?php
    $getPayCode=$con->myQuery("SELECT id,payroll_code FROM payroll WHERE is_deleted=0 AND is_processed=1")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- COMPANY DEDUCTION MODAL -->
<div class="modal fade" id="comde" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Select Payroll Code</h4>
            </div>
            <div class="modal-body">      
                <form method='get' action="company_deduction.php">
                    <div class="form-group">
                        <div class='form-group'>
                            <div class ="row">
                                <div class = "col-md-3">
                                    <label class='control-label'> Payroll Code : </label>
                                </div>
                                <div class = "col-md-9">
                                    <select class="form-control cbo" name="p_code" data-placeholder="Select PayCode" style="width: 100%"  required> 
                                        <?php echo makeOptions($getPayCode); ?> 
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class ="modal-footer ">
                        <button type="submit" class="btn btn-danger btn-flat" >Filter</button>
                        <button type="button" class="btn btn-default btn-flat"  data-dismiss="modal" id="reset" >Cancel</button>
                    </div> 
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END COMPANY DEDUCTION MODAL -->

<!-- SCHEDULE PAYROLL MODAL -->
<div class="modal fade" id="netpay" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Select Payroll Code</h4>
            </div>
            <div class="modal-body">       
                <form method='get' action="sched_net_pay.php">
                    <div class="form-group">
                        <div class='form-group'>
                            <div class ="row">
                                <div class = "col-md-3">
                                    <label class='control-label'> Payroll Code : </label>
                                </div>
                                <div class = "col-md-9">
                                    <select class="form-control cbo" name="p_code" data-placeholder="Select PayCode" style="width: 100%"  required> 
                                        <?php echo makeOptions($getPayCode); ?> 
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class ="modal-footer ">
                        <button type="submit" class="btn btn-danger btn-flat" >Proceed</button>
                        <button type="button" class="btn btn-default btn-flat"  data-dismiss="modal" id="reset" >Cancel</button>
                    </div> 
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END SCHEDULE PAYROLL MODAL -->

<!-- WITH TAX MODAL -->
<div class="modal fade" id="withtax" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Select Payroll Code</h4>
            </div>
            <div class="modal-body">       
                <form method='get' action="with_tax.php">
                    <div class="form-group">
                        <div class='form-group'>
                            <div class ="row">
                                <div class = "col-md-3">
                                    <label class='control-label'> Payroll Code : </label>
                                </div>
                                <div class = "col-md-9">
                                    <select class="form-control cbo" name="p_code" data-placeholder="Select PayCode" style="width: 100%"  required> 
                                        <?php echo makeOptions($getPayCode); ?> 
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class ="modal-footer ">
                        <button type="submit" class="btn btn-danger btn-flat" >Proceed</button>
                        <button type="button" class="btn btn-default btn-flat"  data-dismiss="modal" id="reset" >Cancel</button>
                    </div> 
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END WITH TAX MODAL -->

<!-- LATE/OVERTIME MODAL -->
<div class="modal fade" id="lateover" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Select Payroll Code</h4>
            </div>
            <div class="modal-body">       
                <form method='get' action="late_overtime.php">
                    <div class="form-group">
                        <div class='form-group'>
                            <div class ="row">
                                <div class = "col-md-3">
                                    <label class='control-label'> Payroll Code : </label>
                                </div>
                                <div class = "col-md-9">
                                    <select class="form-control cbo" name="p_code" data-placeholder="Select PayCode" style="width: 100%"  required> 
                                        <?php echo makeOptions($getPayCode); ?> 
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class ="modal-footer ">
                        <button type="submit" class="btn btn-danger btn-flat" >Filter</button>
                        <button type="button" class="btn btn-default btn-flat"  data-dismiss="modal" id="reset" >Cancel</button>
                    </div> 
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END LATE/OVERTIME MODAL -->
