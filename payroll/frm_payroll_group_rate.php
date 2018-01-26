<?php
require_once("../support/config.php");
if(!isLoggedIn()){
  toLogin();
  die();
}
makeHead("Payroll Group Rates",1);

if(!empty($_GET['pg_id'])){
  // var_dump($_GET['company_id']);
  // die;
  $p_rates=$con->myQuery("SELECT payroll_group_rate_id,
    payroll_group_id,  
    rd_rate,
    sh_rate,
    rd_sh_rate,
    rh_rate,
    rd_rh_rate,
    o_ot_rate,
    rd_ot_rate,
    sh_ot_rate,
    rd_sh_ot_rate,
    rh_ot_rate,
    rd_rh_ot_rate,
    n_rate
    FROM payroll_group_rates
    WHERE  payroll_group_id=? LIMIT 1",array($_GET['pg_id']))->fetch(PDO::FETCH_ASSOC);

  $p_settings=$con->myQuery("SELECT * FROM payroll_settings WHERE pay_group_id=? LIMIT 1",array($_GET['pg_id']))->fetch(PDO::FETCH_ASSOC);
// var_dump($p_settings);
// die;
  $p= $con->myQuery("SELECT payroll_group_id,
    name
    FROM payroll_groups 
    WHERE payroll_group_id=? LIMIT 1",array($_GET['pg_id']))->fetch(PDO::FETCH_ASSOC);
  // var_dump($com_rates);
  //  die;

  $cbo_pay_period=$con->myQuery("SELECT id, period_name FROM pay_period")->fetchAll(PDO::FETCH_ASSOC);

  $cbo_cut_off=$con->myQuery("SELECT id, cut_off_name FROM cut_off")->fetchAll(PDO::FETCH_ASSOC);

  if(!empty($com_rates['is_deleted'])){
    if($com_rates['is_deleted']==1  ){
      redirect("view_payroll_group_rates.php");
      die;
    }
  }
}else{
  redirect("index.php");
  die;
}

require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>
<script type="text/javascript">
  function isNumberKey(evt, element) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 46 || charCode == 8))
      return false;
    else {
      var len = $(element).val().length;
      var index = $(element).val().indexOf('.');
      if (index > 0 && charCode == 46) {
        return false;
      }
    }
    return true;
  } 

</script>
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
          <!-- <h1>
            Create New User
          </h1> -->
          <!-- <?php
          if(!empty($_GET['company_id'])){
            ?>
            
            <?php
          }
          else{                    
            ?>
            <h1 class="text-yellow">Create New Product Category  </h1>                
            <?php
          }
          ?> -->
          <h1 >Payroll Group Setting </h1>

          <!-- <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="view_product_categories.php" ><i class="fa fa-cube"></i> Product Categories</a></li>
            <li class="active">Product Category Form </li>
          </ol>
        -->
      </section>

      <!-- Main content -->
      <section class="content">

        <!-- Main row -->
        <div class="row">

          <div class='col-md-12 '>
            <?php
            Alert();
            ?>
            <div class="box box-danger">
              <div class="box-body">
                <div class="row">
                 <div class='col-sm-12 col-md-8 col-md-offset-2'>
                   <br/>
                   <form class='form-horizontal' method='POST' action='save_pg_rates.php'>
                     <input type='hidden' name='payroll_group_id' value='<?php echo !empty($p)?htmlspecialchars($p['payroll_group_id']):''; ?>'>
                     <input type='hidden' name='payroll_group_rate_id'  value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['payroll_group_rate_id']):''; ?>'>
                     <input type='hidden' name='pg_sal_and_ded_id'  value='<?php echo !empty($p_settings)?htmlspecialchars($p_settings['id']):''; ?>'>
                     


                     <div class='form-group'>

                      <label class='col-sm-12 col-md-3 control-label' >Payroll Group :</label>
                      <div class='col-sm-12 col-md-9'>
                        <input type="text" class="form-control" name="payroll_group_name"  value= '<?php echo !empty($p)?htmlspecialchars($p['name']):''; ?>'  required readonly>
                      </div>
                    </div>
                    <br/>

                    <h4 class= "page-header text-red">Payroll Group Rates </h4>
                    <div class='row'>
                      <div class='col-md-6'>
                       <div class='form-group'>
                         <label class='col-sm-12 col-md-6 control-label'>Rest Day: <span class='text-red'>*</span> </label>
                         <div class='col-sm-12 col-md-6'>
                          <input type="text" class="form-control" name="rd_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['rd_rate']):''; ?>'   required>
                        </div>

                      </div> 
                    </div>
                    <div class='col-md-6'>
                     <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'>Special Holiday: <span class='text-red'>*</span> </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="sh_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['sh_rate']):''; ?>'   required>
                      </div>

                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'>Rest Day and Special Holiday: <span class='text-red'>*</span> </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="rd_sh_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['rd_sh_rate']):''; ?>'   required>
                      </div>

                    </div>  
                  </div>
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'>Regular Holiday: <span class='text-red'>*</span> </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="rh_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['rh_rate']):''; ?>'   required>
                      </div>

                    </div>
                  </div>
                </div>

                <div class='row'>
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'>Rest Day and Regular Holiday: <span class='text-red'>*</span> </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="rd_rh_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['rd_rh_rate']):''; ?>'   required>
                      </div>

                    </div> 
                  </div>
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'>Night Differential: <span class='text-red'>*</span> </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="n_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['n_rate']):''; ?>'   required>
                      </div>

                    </div> 
                  </div>
                </div>







                <h4 class= "page-header text-red">Overtime Rates </h4>
                <div class= 'row'>
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'>Ordinary Day<span class='text-red'>*</span>: </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="o_ot_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['o_ot_rate']):''; ?>'   required>
                      </div>

                    </div>  
                  </div>

                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'>Rest Day<span class='text-red'>*</span>: </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="rd_ot_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['rd_ot_rate']):''; ?>'   required>
                      </div>

                    </div>
                  </div>
                </div>
                
                <div class='row'>
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'> Special Holiday<span class='text-red'>*</span>: </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="sh_ot_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['sh_ot_rate']):''; ?>'   required>
                      </div>

                    </div>
                  </div>
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'> Rest Day and Special Holiday<span class='text-red'>*</span>: </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="rd_sh_ot_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['rd_sh_ot_rate']):''; ?>'   required>
                      </div>

                    </div>
                  </div>
                </div>
                
                <div class='row'>
                  <div class='col-md-6'>
                    <div class='form-group'>

                      <label class='col-sm-12 col-md-6 control-label'> Regular Holiday<span class='text-red'>*</span>: </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="rh_ot_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['rh_ot_rate']):''; ?>'   required>
                      </div>

                    </div>
                  </div>
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'> Rest Day and Regular Holiday<span class='text-red'>*</span>: </label>
                      <div class='col-sm-12 col-md-6'>
                        <input type="text" class="form-control" name="rd_rh_ot_rate" onkeypress="return isNumberKey(event,this)" placeholder="Enter Rate" value='<?php echo !empty($p_rates)?htmlspecialchars($p_rates['rd_rh_ot_rate']):''; ?>'  required>
                      </div>

                    </div>
                  </div>
                </div>
                
                <h4 class= "page-header text-red">Salary and Deduction Settings </h4>
                
                <!-- Payroll Settings :))-->

                <div class='row'>
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <div class='form-group'>  
                        <label class='col-sm-12 col-md-6 control-label'> Salary Period<span class='text-red'>*</span>: </label>
                        <div class='col-sm-12 col-md-6'>
                          <select class='form-control cbo' name='salary_period' id='salary_period' data-placeholder='Select Salary Period' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($p_settings['salary_settings'])?htmlspecialchars($p_settings['salary_settings']):''?>" required >
                            <?php echo makeOptions($cbo_pay_period); ?>
                          </select>
                        </div>
                      </div>
                      

                    </div>
                  </div>
                  <div class='col-md-6'>
                    <div class='form-group'>
                      <label class='col-sm-12 col-md-6 control-label'> Government Deduction Period<span class='text-red'>*</span>: </label>
                      <div class='col-sm-12 col-md-6'>
                        <select class='form-control cbo' name='government_ded_period' id='government_ded_period' data-placeholder='Select Government Period' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($p_settings['government_settings'])?htmlspecialchars($p_settings['government_settings']):''?>" required >
                          <?php echo makeOptions($cbo_pay_period); ?>
                        </select>
                      </div>
                      
                    </div>

                  </div>
                </div>



                <div class='row'>
                 <div class='col-md-6'>
                   <div class='form-group'>
                    <label class='col-sm-12 col-md-6 control-label'> Tax Deduction Period<span class='text-red'>*</span>: </label>
                    
                    <div class='col-sm-12 col-md-6'>
                      <select class='form-control cbo' name='tax_ded_period' id='tax_ded_period' data-placeholder='Select Tax Period' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($p_settings['tax_settings'])?htmlspecialchars($p_settings['tax_settings']):''?>" required >
                        <?php echo makeOptions($cbo_pay_period); ?>
                      </select>
                    </div>


                  </div>
                </div>
                <div class='col-md-6'>
                 <div class='form-group'>
                  <label class='col-sm-12 col-md-6 control-label'> Company Deduction Period<span class='text-red'>*</span>: </label>

                  <div class='col-sm-12 col-md-6'>
                    <select class='form-control cbo' name='company_ded_period' id='company_ded_period' data-placeholder='Select Tax Period' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($p_settings['company_settings'])?htmlspecialchars($p_settings['company_settings']):''?>" required >
                      <?php echo makeOptions($cbo_pay_period); ?>
                    </select>
                  </div>

                  
                </div>
              </div>
            </div>

            <div class='row'>
             <div class='col-md-6'>
              <div class='form-group'>
                <label class='col-sm-12 col-md-6 control-label'> Minimum Wage: </label>
                <div class='col-sm-12 col-md-6'>

                  <input type="text" class="form-control" name="minimum_wage" onkeypress="return isNumberKey(event,this)" placeholder="Enter Minimum Rage" value= '<?php echo !empty($p_settings)?$p_settings['minimum_wage']:""?>'  required>
                </div>
              </div>
            </div>
            <div class='col-md-6'>
              <div class='form-group'>
                <label class='col-sm-12 col-md-6 control-label'> 13th Month Release Date: </label>
                <div class='col-sm-12 col-md-6'>

                 <input type='text' name='13th_month_release_date' class='form-control date_picker' id='13th_month_release_date' value='<?php echo !empty($p_settings)?htmlspecialchars(DisplayDate($p_settings['13th_month_release_date'])):''; ?>' required> 
               </div>
             </div>
           </div>
         </div>
         <br/>



         <div class="row">
          <div class='col-md-6'>
            <div class='form-group'>
              <label class='col-sm-12 col-md-6 control-label'> Days per month: </label>
              <div class='col-sm-12 col-md-6'>
                <input type="text" class="form-control" name="days_per_month" onkeypress="return isNumberKey(event,this)" placeholder="Enter Days Per Month" value= '<?php echo !empty($p_settings)?$p_settings['days_per_month']:""?>'  required>
              </div>
            </div>
          </div>
          
        </div>

        <div class="row">
          <div class='col-md-6'>
            <div class='form-group'>
              <label class='col-sm-12 col-md-6 control-label'> First Cut-Off: </label>
              <div class='col-sm-12 col-md-6'>
                <!-- <select class='form-control cbo' name='first_cut_off' id='first_cut_off' data-placeholder='Select First Cut-Off' style='width:100%' data-allow-clear='true' data-selected="<?php //echo !empty($p_settings['first_cut_off'])?htmlspecialchars($p_settings['tax_settings']):''?>">
                  <?php //for($i=1;$i<=31;$i++):?>
                    <option value="<?php //echo $i;?>"><?php //echo $i;?></option>
                  <?php //endfor;?>
                </select> -->
                <input type="number" min="1" max="31" class="form-control" name="first_cut_off" onkeypress="return isNumberKey(event,this)" placeholder="Enter First Cut-Off" value= '<?php echo !empty($p_settings)?$p_settings['first_cut_off']:""?>'>
              </div>
            </div>
          </div>
          <div class='col-md-6'>
            <div class='form-group'>
              <label class='col-sm-12 col-md-6 control-label'> Second Cut-Off: </label>
              <div class='col-sm-12 col-md-6'>
                <!-- <select class='form-control cbo' name='second_cut_off' id='second_cut_off' data-placeholder='Select Second Cut-Off' style='width:100%' data-allow-clear='true' data-selected="<?php //echo !empty($p_settings['second_cut_off'])?htmlspecialchars($p_settings['tax_settings']):''?>">
                  <?php //for($i=1;$i<=31;$i++):?>
                    <option value="<?php //echo $i;?>"><?php //echo $i;?></option>
                  <?php //endfor;?>
                </select> -->
                <input type="number" min="1" max="31" class="form-control" name="second_cut_off" onkeypress="return isNumberKey(event,this)" placeholder="Enter First Cut-Off" value= '<?php echo !empty($p_settings)?$p_settings['second_cut_off']:""?>'>
              </div>
            </div>
          </div>
          <div class='col-md-6'>
            <div class='form-group'>
              <div class='form-group'>  
              <label class='col-sm-12 col-md-6 control-label'> SSS Deduction<span class='text-red'>*</span>: </label>
                <div class='col-sm-12 col-md-6'>
                  <select class='form-control cbo' name='sss_ded' id='sss_ded' data-placeholder='Select Cut-Off' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($p_settings['sss_deduction'])?htmlspecialchars($p_settings['sss_deduction']):''?>" required >
                    <?php echo makeOptions($cbo_cut_off); ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class='col-md-6'>
            <div class='form-group'>
              <div class='form-group'>  
              <label class='col-sm-12 col-md-6 control-label'> Philhealth Deduction<span class='text-red'>*</span>: </label>
                <div class='col-sm-12 col-md-6'>
                  <select class='form-control cbo' name='philhealth_ded' id='philhealth_ded' data-placeholder='Select Cut-Off' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($p_settings['philhealth_deduction'])?htmlspecialchars($p_settings['philhealth_deduction']):''?>" required >
                    <?php echo makeOptions($cbo_cut_off); ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class='col-md-6'>
            <div class='form-group'>
              <div class='form-group'>  
              <label class='col-sm-12 col-md-6 control-label'> Pag-ibig Deduction<span class='text-red'>*</span>: </label>
                <div class='col-sm-12 col-md-6'>
                  <select class='form-control cbo' name='pagibig_ded' id='pagibig_ded' data-placeholder='Select Cut-Off' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($p_settings['pagibig_deduction'])?htmlspecialchars($p_settings['pagibig_deduction']):''?>" required >
                    <?php echo makeOptions($cbo_cut_off); ?>
                  </select>
                </div>
              </div>


            </div>
          </div>
        </div>

        <div class='form-group'>
          <div class='col-sm-12 col-md-9 col-md-offset-3 '>
            <button type='submit' class='btn btn-danger btn-flat' onclick="return validate_inputs()"> Save</button>
            <a href='view_payroll_group_rates.php' class='btn btn-flat btn-default'>Cancel</a>
          </div>

        </div>     

      </form>
    </div>
  </div><!-- /.row -->
</div><!-- /.box-body -->
</div><!-- /.box -->
</div>
</div><!-- /.row -->
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