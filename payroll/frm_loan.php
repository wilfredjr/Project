<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }
    
    makeHead("Employee Loan Setting",1);
    $lt=$con->myQuery("SELECT loan_id, loan_name FROM loans WHERE is_deleted=0" )->fetchAll(PDO::FETCH_ASSOC);
    $el=$con->myQuery("SELECT emp_loan_id, cut_off_no, loan_amount, balance FROM emp_loans" )->fetchAll(PDO::FETCH_ASSOC);
    $ls=$con->myQuery("SELECT status_id, status_name FROM loan_status WHERE is_deleted=0" )->fetchAll(PDO::FETCH_ASSOC);
    $em=$con->myQuery("SELECT employees.id, CONCAT(employees.first_name,' ',employees.middle_name,' ',employees.last_name) AS emp_name FROM employees
    INNER JOIN employment_status ON employees.employment_status_id = employment_status.id
    WHERE employees.is_terminated != '1' and employees.is_deleted ='0'")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>

</script>
<div class="content-wrapper">
    <section>
        <div class="content-header">
            <h1 class="page-header text-center text-red">Employee Loan Setting</h1>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-lg-12'>
                <div class="row">
                    <div class='col-sm-12 col-md-8 col-md-offset-2'>
                        <form class='form-horizontal' method='POST' action='save_employee_loan.php'>
                            <input type='hidden' name='emp_loan_id' value='<?php echo !empty($l)?htmlspecialchars($l['emp_loan_id']):''; ?>'>

                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Employee Name: <span class='text-red'>*</span></label>
                                <div class='col-md-7'>
                                    <select  class='form-control cbo' id='emp_name'  name="emp_name" data-placeholder="Select Employee Name" <?php echo!(empty($em['emp_name']))?"data-selected='".$em['emp_name']."'":NULL ?> style='width:100%' required> <?php echo makeOptions($em)  ?>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 text-right' >Loan Type: <span class='text-red'>*</span></label>
                                <div class='col-md-7'>
                                    <select  class='form-control cbo' id='loan_id'  name="loan_id" data-placeholder="Select Loan Type" <?php echo!(empty($lt['loan_id']))?"data-selected='".$lt['loan_id']."'":NULL ?> style='width:100%' required> <?php echo makeOptions($lt)  ?>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Loan Amount: <span class='text-red'>*</span></label>
                                <div class='col-md-7'>
                                    <input type="number" step="0.01" class="form-control" name="loan_amount" placeholder="Enter loan amount"  value= '<?php echo !empty($p)?htmlspecialchars($p['website']):''; ?>'  required >
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Number of cut-off: <span class='text-red'>*</span></label>
                                <div class='col-md-7'>
                                    <input type="number" class="form-control" name="cut_off_no" placeholder="Enter number of cut-off"  value= '<?php echo !empty($p)?htmlspecialchars($p['website']):''; ?>'  required >
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-sm-12 col-md-7 col-md-offset-5 '>
                                    <button type='submit' class='btn btn-danger btn-flat'"> Save</button>
                                    <a href='view_loan.php' class='btn btn-flat btn-default'>Cancel</a>
                                </div>
                            </div>     
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- /.content -->
</div>
<!--   <script>
    function validate_inputs() {
      var return_value=true;
      var str_error="";
      if($("#salary_period").val()=='Weekly'){
          // strerror+="Please select a school fee.\n";
          alert('weekly!');
          return_value=false;

        }else if ($("#salary_period").val()=='Bi-Monthly'){

          alert('BM!');
          return_value=false;

          
        }else if ($("#salary_period").val()=='Monthly'){
          alert('monthly!');
          return_value=false;
        }
        if(str_error!==""){
          alert("You have the following error: \n"+str_error);
        }
        return return_value;
      }
    </script> -->
    <?php
    makeFoot(WEBAPP,1);
    ?>