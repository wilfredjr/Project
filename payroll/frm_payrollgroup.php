<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }
    
    makeHead("Payroll Group Settings",1);
    if(!empty($_GET['pg_id']))
    {
        $p= $con->myQuery("SELECT *
                            FROM payroll_groups 
                            WHERE payroll_group_id=? LIMIT 1",array($_GET['pg_id']))->fetch(PDO::FETCH_ASSOC);
  
        if(!empty($p['is_deleted']))
        {
            if($com_rates['is_deleted']==1  )
            {
                redirect("view_payroll_group_rates.php");
                die;
            }
        }
    }
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<script type="text/javascript">
    function isNumberKey(evt, element) 
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 46 || charCode == 8))
        {
            return false;
        }else 
        {
            var len = $(element).val().length;
            var index = $(element).val().indexOf('.');
            if (index > 0 && charCode == 46) 
            {
                return false;
            }
        }
        return true;
  } 

</script>
<div class="content-wrapper">
    <section>
        <div class="content-header">
            <h1 class="page-header text-center text-red">Payroll Group Setting </h1>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <?php
                Alert();
            ?>
            <div class='col-lg-12'>
                <div class="row">
                    <div class='col-sm-12 col-md-8 col-md-offset-2'>
                        <form class='form-horizontal' method='POST' action='save_payrollgroup.php'>
                            <input type='hidden' name='payroll_group_id' value='<?php echo !empty($p)?htmlspecialchars($p['payroll_group_id']):''; ?>'>
                            
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Payroll Group <span class='text-red'>*</span></label>
                                <div class='col-md-7'>
                                    <input type="text" class="form-control" name="payroll_group_name"  value= '<?php echo !empty($p)?htmlspecialchars($p['name']):''; ?>'  required >
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Address <span class='text-red'>*</span></label>
                                <div class='col-md-7'>
                                    <textarea  class="form-control" name="address" placeholder="Enter Address" required ><?php echo !empty($p)?$p['address']:""?></textarea>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Email <span class='text-red'>*</span></label>
                                <div class='col-md-7'>
                                    <input type="text" class="form-control" name="email" placeholder="Enter Email"  value= '<?php echo !empty($p)?htmlspecialchars($p['email']):''; ?>'  required >
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Website <span class='text-red'>*</span></label>
                                <div class='col-md-7'>
                                    <input type="text" class="form-control" name="website" placeholder="Enter Website"  value= '<?php echo !empty($p)?htmlspecialchars($p['website']):''; ?>'  required >
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Mobile No. <span class='text-red'>*</span></label>
                                <div class='col-md-7'>
                                    <input type="text" class="form-control" name="mobile_no" onkeypress="return isNumberKey(event,this)" placeholder="Enter Mobile No" value='<?php echo !empty($p)?htmlspecialchars($p['mobile_no']):''; ?>'   required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Telephone No. </label>
                                <div class='col-md-7'>
                                    <input type="text" class="form-control" name="telephone_no" onkeypress="return isNumberKey(event,this)" placeholder="Enter Tel. No" value='<?php echo !empty($p)?htmlspecialchars($p['telephone_no']):''; ?>'   >
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Fax No. </label>
                                <div class='col-md-7'>
                                    <input type="text" class="form-control" name="fax_no" onkeypress="return isNumberKey(event,this)" placeholder="Enter Fax No" value='<?php echo !empty($p)?htmlspecialchars($p['fax_no']):''; ?>'   >
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label' >Bank Account Number * </label>
                                <div class='col-md-7'>
                                    <input type="text" class="form-control" name="bank_account_number" onkeypress="return isNumberKey(event,this)" placeholder="Enter Bank Account Number" value='<?php echo !empty($p)?htmlspecialchars($p['bank_account_number']):''; ?>'  required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-sm-12 col-md-7 col-md-offset-5 '>
                                    <button type='submit' class='btn btn-danger btn-flat' onclick="return validate_inputs()"> Save</button>
                                    <a href='view_payrollgroups.php' class='btn btn-flat btn-default'>Cancel</a>
                                </div>
                            </div>     
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- /.content -->
</div>
  <script>

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
    </script>
    <?php
    makeFoot(WEBAPP,1);
    ?>