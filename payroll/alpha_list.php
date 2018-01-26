<?php
	require_once '../support/config.php';
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

    // // Variable
    // $date_from = (isset($_POST['txtdatefrom']) && !empty($_POST['txtdatefrom']))? $_POST['txtdatefrom'] : '';
    // $date_to = (isset($_POST['txtdateto']) && !empty($_POST['txtdateto']))? $_POST['txtdateto'] : '';
    // var_dump($date_from,$date_to);
    // // Load Combobox pay group
    // $cbo_pay_group=$con->myQuery("SELECT payroll_group_id, name FROM payroll_groups WHERE is_deleted = 0")->fetchAll(PDO::FETCH_ASSOC);

    // // Load All Employees
    // $all_employees = $con->myQuery("SELECT id,code,CONCAT(last_name,' ',first_name,' ',middle_name) AS employee_name,
    // --                                 basic_salary,payroll_group_id FROM employees WHERE is_deleted=0 AND is_terminated=0");

    // // $x=0;
    // // while($data = $all_employees->fetch(PDO::FETCH_ASSOC)):
    // //     $for_viewing[$x]['employee_id']     = $data['id'];
    // //     $for_viewing[$x]['employee_code']   = $data['code'];
    // //     $for_viewing[$x]['employee_name']   = $data['employee_name'];
    // //     $for_viewing[$x]['basic_salary']    = $data['basic_salary'];
    // //     $for_viewing[$x]['overtime_rate']   = get_payroll_group_rates($data['payroll_group_id'])['o_ot_rate'];

    // //     $_13_month_details = $con->myQuery("SELECT pd.id,
	// // --                 SUM(pd.13th_month) as total_13th
    // // --                 FROM payroll_details pd 
    // // --                 INNER JOIN payroll p ON p.id=pd.payroll_id
    // // --                 WHERE pd.employee_id=? AND p.is_deleted=0 AND p.is_processed=1 AND p.date_from>=? AND p.date_to<=?",array($data['id'],$date_from,$date_to))->fetch(PDO::FETCH_ASSOC);
        
    // // //     if(!empty($_13_month_details))
    // // //     {
    // // //         $for_viewing[$x]['total_13th_month'] = number_format($_13_month_details['total_13th'],2);
    // // //     }else
    // // //     {
    // // //         $for_viewing[$x]['total_13th_month'] = "0";
    // // //     }

    // // //     $x++;
    // // // endwhile; 

    // $_result = array();
    // while($data = $all_employees->fetch(PDO::FETCH_NUM)) {
    //     $_13_month_details = $con->myQuery("SELECT pd.id,SUM(pd.13th_month) as total_13th FROM payroll_details pd INNER JOIN payroll p ON p.id=pd.payroll_id WHERE pd.employee_id=? AND p.is_deleted=0 AND p.is_processed=1 AND p.date_from>=? AND p.date_to<=?",array($data[0],$date_from,$date_to))->fetch(PDO::FETCH_ASSOC);
    //     $_result[] = array(
    //         "employee_id" => $data[0],
    //         "employee_code" => $data[1],
    //         "employee_name" => $data[2],
    //         "basic_salary" => $data[3],
    //         "overtime_rate" => get_payroll_group_rates($data[4])['o_ot_rate'],
    //         "total_13th_month" => number_format($_13_month_details['total_13th'],2)  
    //     );
    // }

    // echo "<pre>";
    // print_r($_result);
    // echo "</pre>";
    
    // die();

    // Computation
    // $emp_id = $_SESSION[WEBAPP]['user']['id'];
    // Basic Salary

    // Overtime
    // $rate = get_payroll_group_rates();
	makeHead("Alpha List Generate Report",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
    <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            Alpha List Generate Report
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-lg-12'>
                <?php Alert(); ?>
            </div>
        </div>  
        <br/>
        <div class="row">
            <div class='col-sm-12 col-md-12'>
                <div class='row'>
                    <div class='col-sm-12'>
                        <form method='post' name="frmalphalist" class='form-horizontal' action="alphalist_handler.php">
                            <div class="container col-md-4 col-md-offset-4">
                                <div class='form-group'>
                                        <div class="col-md-12">
                                        <label for="txtselectedy" class="control-label">Select Year *</label>
                                            <select class='form-control cbo' data-allow-clear='true' name='txtselectedy'   id='txtselectedy' data-placeholder="Filter by year" style='width:100%'>
                                                <?php
                                                    // $already_selected_value = 1984;
                                                    // $earliest_year = 1950;
                                                    // foreach (range(date('Y'), $earliest_year) as $x) {
                                                    //     echo '<option value="'.$x.'"'.($x === $already_selected_value ? ' selected="selected"' : '').'>'.$x.'</option>';
                                                    // }
                                                    // Sets the top option to be the current year. (IE. the option that is chosen by default).
                                                    $currently_selected = date('Y'); 
                                                    // Year to start available options at
                                                    $earliest_year = 1950; 
                                                    // Set your latest year you want in the range, in this case we use PHP to just set it to the current year.
                                                    $latest_year = date('Y'); 
                                                    // Loops over each int[year] from current year, back to the $earliest_year [1950]
                                                    foreach ( range( $latest_year, $earliest_year ) as $i ) {
                                                        // Prints the option with the next year in range.
                                                        echo '<option value="'.$i.'"'.($i === $currently_selected ? ' selected="selected"' : '').'>'.$i.'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                </div> 
                            </div>
                            <div class='form-group'>
                                <div class='col-md-4  col-md-offset-4  text-right'>
                                    <button type='submit' class='btn-flat btn btn-block btn-danger' value="fpi1" name="fpi1"><span></span>FPI 7.1 (TERMINATED WITHIN CALENDAR YEAR)</button>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-4  col-md-offset-4  text-right'>
                                    <button type='submit' class='btn-flat btn btn-block btn-danger' value="fpi2" name="fpi2"><span></span>FPI 7.2 (EXEMPT FROM WITHOLDING TAX)</button>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-4  col-md-offset-4  text-right'>
                                    <button type='submit' class='btn-flat btn btn-block btn-danger' value="fpi3" name="fpi3"><span></span>FPI 7.3 (NO OTHER EMPLOYER WITHIN THE YEAR)</button>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-4  col-md-offset-4  text-right'>
                                    <button type='submit' class='btn-flat btn btn-block btn-danger' value="fpi4" name="fpi4"><span></span>FPI 7.4 (WITH PREVIOUS EMPLOYER/S WITHIN THE YEAR)</button>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-4  col-md-offset-4  text-right'>
                                    <button type='submit' class='btn-flat btn btn-block btn-danger' value="fpi5" name="fpi5"><span>FPI 7.5 (FINAL WITHHOLDING TAX)</span></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!--<script>
         $('input.clk').on('change', function() {
            $('input.clk').not(this).prop('checked', false);  
        });

        $('#valclk').click(function () {
            if (!$('.clk').is(':checked')) {
                alert('not checked');
                return false;
            }
        });
    </script>-->
    <!-- <script Language="JavaScript">

    function validate()
    {

        var x = document.getElementById("txtselectedy").options.length;
            
        if(x == 0){
            alert('Issuficient Data');
        }

    }
    </script> -->
<?php
    makeFoot(WEBAPP,1);
?>