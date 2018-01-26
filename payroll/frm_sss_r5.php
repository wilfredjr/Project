<?php
require_once("../support/config.php");
if(!isLoggedIn())
{
  toLogin();
  die();
}

makeHead("SSS R5 Form",1);
if(!empty($_GET['ref_no'])){
  $r5 = $con->myQuery("SELECT * FROM sss_r5_main WHERE is_deleted=0 and ref_no = ?", array($_GET['ref_no']) )->fetch(PDO::FETCH_ASSOC);  
  // var_dump(substr($r5['for_date_of'], 0, -3));
  // die;
}

    // $lt=$con->myQuery("SELECT loan_id, loan_name FROM loans WHERE is_deleted=0" )->fetchAll(PDO::FETCH_ASSOC);
    // $el=$con->myQuery("SELECT emp_loan_id, cut_off_no, loan_amount, balance FROM emp_loans" )->fetchAll(PDO::FETCH_ASSOC);
    // $ls=$con->myQuery("SELECT status_id, status_name FROM loan_status WHERE is_deleted=0" )->fetchAll(PDO::FETCH_ASSOC);
    // $em=$con->myQuery("SELECT employees.id, CONCAT(employees.first_name,' ',employees.middle_name,' ',employees.last_name) AS emp_name FROM employees
    // INNER JOIN employment_status ON employees.employment_status_id = employment_status.id
    // WHERE employees.is_terminated != '1' and employees.is_deleted ='0'")->fetchAll(PDO::FETCH_ASSOC);

?>
<?php
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
        //alert(index);
        
        if (index >= 0 && charCode == 46) {
          return false;
        }
      }
      return true;
    } 
    

  </script>
  <div class="content-wrapper">
    <section>
      <div class="content-header">
        <?php if(!empty($r5)){?>
        <h1 class="page-header text-center text-red">Update R5 form</h1>
        <?php }else{?>
        <h1 class="page-header text-center text-red">Create R5 form</h1>
        <?php }?>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class='col-lg-12'>
          <?php Alert(); ?>
        </div>
      </div>  
      <div class="row">

        <div class='col-lg-12'>
          <div class="row">
            <div class='col-sm-12 col-md-8 col-md-offset-2'>
              <form class='form-horizontal disable-submit' method='POST' action='save_sss_r5.php'>
               <input type='hidden' name='ref_no' value='<?php echo !empty($r5)?htmlspecialchars($r5['ref_no']):''; ?>'>

               <div class='form-group'>
                <label class='col-md-3 control-label' >Month: <span class='text-red'>*</span></label>
                <div class='col-md-7'>

                  <input type='month' name='month_year' class='form-control' id='month_year' value='<?php echo !empty($r5['for_date_of'])?substr($r5['for_date_of'], 0, -3):""?>' required <?php if(!empty($r5)){ echo 'readonly';}?> >

                </div>
                <div class='col-md-12 text-center'>
                  <br>
                  <?php if(empty($r5)){ ?>
                  <a  id="generator" class='btn btn-danger btn-flat'"> Generate</a>
                  <?php }?>
                </div>
              </div>



              <div class="page-header text-center text-red" style="font-size:18px">Contribution for this month</div>




              <div class="panel panel-danger" style="border-color:#dd4b39">
                <div class="panel-heading text-center" style="background-color:#dd4b39;color:white">Fill-out by employer</div>
                <br>

                <div class='form-group'>
                  <label class='col-md-4 control-label' >SS Contribution: </label>
                  <div class="col-xs-12 col-md-7" id="ss_contrib" style="padding-top:8px;color:green"><?php echo !empty($r5['ss_contribution'])?htmlspecialchars(number_format($r5['ss_contribution'],2,'.',',') ." PHP"):""?></div>
                  <input type="hidden" name='ss_contribution' class='form-control' id='ss_contribution' value='<?php echo !empty($r5['ss_contribution'])?str_replace(',','',$r5['ss_contribution']):""?>' required>

                </div>
                <div class='form-group'>
                  <label class='col-md-4 control-label' >EC Contribution: </label>
                  <div class="col-xs-12 col-md-7" id="ec_contrib" style="padding-top:8px;color:green"><?php echo !empty($r5['ec_contribution'])?htmlspecialchars(number_format($r5['ec_contribution'],2,'.',',') ." PHP" ):""?></div>
                  <input type="hidden" name='ec_contribution' class='form-control' id='ec_contribution' value='<?php echo !empty($r5['ec_contribution'])?str_replace(',','',$r5['ec_contribution']):""?>'  required>

                </div>
                <div class='form-group'>
                  <label class='col-md-4 control-label' >SS Contribution: <span class='text-red'>*</span></label>
                  <div class='col-md-5'>
                    <input type="text" name='SS' class='form-control' id='SS' value='<?php echo !empty($r5['amt_ss_contribution'])?$r5['amt_ss_contribution']:""?>' onkeypress="return isNumberKey(event,this)" required>
                  </div>

                </div>
                <div class='form-group'>
                  <label class='col-md-4 control-label' >EC Contribution: <span class='text-red'>*</span></label>
                  <div class='col-md-5'>
                    <input type="text" name='EC' class='form-control' id='EC' value='<?php echo !empty($r5['amt_ec_contribution'])?$r5['amt_ec_contribution']:""?>' onkeypress="return isNumberKey(event,this)" required>
                  </div>

                </div>
                <br>
                <div class="page-header text-center text-red" style="font-size:15px">Fill up if there is underpayment</div>

                <div class='form-group'>
                  <div class='col-sm-12 col-md-7 col-md-offset-4 '>
                    <input id="underpayment" name='underpayment' type="checkbox" data-toggle="toggle" data-on="On" data-off="Off" name='school year' value='1' <?php 
                    if(!empty($r5)){
                      if($r5['w_underpayment'] == 1){
                        echo 'checked';}
                      }?>>&nbsp;<strong>Underpayment<strong>

                    </div>
                  </div>
                  <div class='form-group'>
                    <label class='col-md-4 control-label' >Remarks : <span class='text-red for-disabling'></span></label>
                    <div class='col-md-5'>
                      <textarea type="textarea" name='remarks' class='form-control' id='remarks' disabled ><?php echo !empty($r5['remarks'])?$r5['remarks']:""?></textarea>
                      <br>
                    </div>


                    <div class='form-group'>
                      <div class='col-sm-12 col-md-7 col-md-offset-5 '>
                        <button id='submit_button' class='btn btn-danger btn-flat'"> Save</button>
                        <a href='view_r5.php' class='btn btn-flat btn-default'>Cancel</a>
                      </div>
                    </div> 
                  </div>
                  <br>


                </form>
              </div>
            </div>
          </div>
        </div>
      </section><!-- /.content -->
    </div>
    <script>
      $(document).ready(function() {
        $('#submit_button').click(function(){

          if($('#month_year').val() != ""){
            //alert("i'm GAY");
            ss_con =  $('#ss_contrib').text();
            arr = ss_con.split(' ');
            arr[0] = arr[0].replace(",","");
            ec_con = $('#ec_contrib').text();
            arr2 = ec_con.split(' ');
            arr2[0] = arr2[0].replace(",","");

            error = "You have following error/s:";
            if($('#SS').val()>arr[0]){
              //alert(':/');
              error = error.concat("\nInput amount is greater than SS Contribution.");
            }
            if($('#EC').val()>arr2[0]){
              error = error.concat("\nInput amount is greater than EC Contribution.");
            }

           if (error.length>27){
            alert(error);
            $("#submit_button").button('reset');
            return false;
           }
            //
            
          }else{
            ss_con = <?php echo !empty($r5['ss_contribution'])?str_replace(',','',$r5['ss_contribution']):""?>
            alert(ss_con);
            return;
            $("#submit_button").button('reset');
          }


        });      

        $('#generator').click(function(){

          if($("input[name='month_year']").val()==""){
            alert('Please select Month/Year.');
            return;
          }

          //validate
          

          month_year=$("input[name='month_year']").val();
          $.getJSON("ajax/get_SSECContributions.php?month_year=" + month_year, function(result){

            $.each(result, function(i, field){
              console.log(field);
              $('#ss_contrib').text(field['ss_contribution'] + ' PHP');
              $('#ec_contrib').text(field['ec_contribution'] + ' PHP');
              $('#ss_contribution').val(field['ss_contribution'].replace(",",""));
              $('#ec_contribution').val(field['ec_contribution'].replace(",",""));

            });

          });


        });
        if( $('#underpayment').is(':checked') ){
                    //enabled attack
                    $('#remarks').prop('disabled', false);



                  }else{
                    //disabled attack
                    $('#remarks').prop('disabled', true);                    
                    $("#remarks").each(function(){
                      $('#underpayment').val('').trigger('change');
                    });
                  }
                  $("#underpayment").click( function(){
                    if( $(this).is(':checked') ){
                    //enabled attack
                    $('#remarks').prop('disabled', false);



                  }else{
                    //disabled attack
                    $('#remarks').prop('disabled', true);                    
                    $("#remarks").each(function(){
                      $(this).val('').trigger('change');
                    });
                  }

                });


                });
              </script>
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