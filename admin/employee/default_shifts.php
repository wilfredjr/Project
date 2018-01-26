<?php
  $data=$con->myQuery("SELECT ecd.id,ecd.emp_code,ecd.comde_code,ecd.emp_comde_amt,ecd.emp_comde_start_date,ecd.emp_comde_end_date,ecd.emp_deduct_type,cd.comde_desc FROM employee_company_deductions ecd JOIN company_deductions cd ON cd.comde_code=ecd.comde_code WHERE ecd.is_deleted=0 AND ecd.emp_id=?", array($employee['id']))->fetchAll(PDO::FETCH_ASSOC);
  $data=array();
if (!empty($_GET['ec_id'])) {
    $record=$con->myQuery("SELECT id,emp_code,comde_code,emp_comde_amt as amount,emp_comde_start_date as start_date,emp_comde_end_date as end_date,emp_deduct_type FROM employee_company_deductions WHERE emp_id=? AND id=? LIMIT 1", array($employee['id'],$_GET['ec_id']))->fetch(PDO::FETCH_ASSOC);
}
  $company_deductions=$con->myQuery("SELECT comde_code as id,comde_desc FROM company_deductions WHERE is_deleted=0 AND is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
  $deduction_types=$con->myQuery("SELECT id,name FROM deduction_types WHERE is_deleted=0 AND is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
  $tab=12;
?>
<?php
  $has_error=false;
if (!empty($_SESSION[WEBAPP]['Alert']) && $_SESSION[WEBAPP]['Alert']['Type']=="danger") {
    $has_error=true;
}
  Alert();
?>
<div class='text-right'>
<button class='btn btn-warning' data-toggle="collapse" data-target="#collapseForm" aria-expanded="false" aria-controls="collapseForm">Toggle Form </button>
</div>
<br/>
<div id='collapseForm' class='collapse'>
  <form class='form-horizontal' action='save_employee_default_shift.php' method="POST" onsubmit="return validate_form();">
    <input type='hidden' name='employee_id' value='<?php echo !empty($employee)?$employee['id']:''; ?>'>
      <div class="form-group">
          <label for="start_date" class="col-md-3 control-label">Start Date *:</label>
          <div class="col-md-7">
            <input type="text" class="form-control date_picker" id="start_date"  name='start_date' value='<?php echo !empty($record)?htmlspecialchars(DisplayDate($record['start_date'])):''; ?>' required>
          </div>
      </div>
      <div class='form-group'>
            <label class='col-md-3 control-label' >Time In *:</label>
            <div class='col-md-2'>
              <div class='input-group bootstrap-timepicker timepicker' >
                <input type='text' name='time_in' class='form-control time_picker' id='time_in' readonly="" required style="cursor: pointer;" title="Click to change" value="00:00:00">
              </div>
            </div>
      
            <label class='col-md-3 control-label' >Time Out *:</label>
            <div class='col-md-2'>
              <div class='input-group bootstrap-timepicker timepicker'>
              <input type='text' name='time_out' class='form-control time_picker' id='time_out' readonly="" required style="cursor: pointer;" title="Click to change" value="00:00:00">
              </div>
            </div>
      </div>
      <div class='form-group'>
            <label class='col-md-3 control-label' >Late Start *:</label>
            <div class='col-md-2'>
              <div class='input-group bootstrap-timepicker timepicker' >
                <input type='text' name='late_start' class='form-control time_picker' id='late_start' readonly="" required style="cursor: pointer;" title="Click to change" value="00:00:00">
              </div>
            </div>
      
            <label class='col-md-3 control-label' >Grace Period :</label>
            <div class='col-md-2'>
              <div class='input-group '>
              <input type='text' name='grace_minutes' class='form-control numeric' id='grace_minutes'  title="Grace Period (Minutes)" value="" maxlength="2">
              </div>
            </div>
      </div>
      
      <div class='form-group'>
        <label class='col-md-3 control-label' >Working Days :</label>
        <div class='col-md-2' >
          <div class="checkbox">
            <label>
              <input type="checkbox" name="working_days[]" value="M"> Monday
            </label>
          </div>
        </div>
      </div>
      <div class='form-group'>
        <div class='col-md-2 col-md-offset-3' >
          <div class="checkbox">
            <label>
              <input type="checkbox" name="working_days[]" value="T"> Tuesday
            </label>
          </div>
        </div>
      </div>
      <div class='form-group'>
        <div class='col-md-2 col-md-offset-3' >
          <div class="checkbox">
            <label>
              <input type="checkbox" name="working_days[]" value="W"> Wednesday
            </label>
          </div>
        </div>
      </div>
      <div class='form-group'>
        <div class='col-md-2 col-md-offset-3' >
          <div class="checkbox">
            <label>
              <input type="checkbox" name="working_days[]" value="TH"> Thursday
            </label>
          </div>
        </div>
      </div>
      <div class='form-group'>
        <div class='col-md-2 col-md-offset-3' >
          <div class="checkbox">
            <label>
              <input type="checkbox" name="working_days[]" value="F"> Friday
            </label>
          </div>
        </div>
      </div>
      <div class='form-group'>
        <div class='col-md-2 col-md-offset-3' >
          <div class="checkbox">
            <label>
              <input type="checkbox" name="working_days[]" value="SA"> Saturday
            </label>
          </div>
        </div>
      </div>
      <div class='form-group'>
        <div class='col-md-2 col-md-offset-3' >
          <div class="checkbox">
            <label>
              <input type="checkbox" name="working_days[]" value="SU"> Sunday
            </label>
          </div>
        </div>
      </div>

      <div class="form-group">
        <div class="col-sm-7 col-md-offset-3 text-center">
          <a href='frm_employee.php?id=<?php echo $employee['id']?>&tab=<?php echo $tab?>' class='btn btn-default' onclick="return confirm('<?php echo empty($record)?"Cancel creation of new default shift?":"Candel change of default shift?" ?>')">Cancel</a>
          <button type='submit' class='btn btn-warning'>Save </button>
        </div>
      </div>
  </form>
</div>
<br/>
<div class='table-responsive'></div>
<table id='CustomTable' class='table table-bordered table-striped'>
  <thead>
    <tr>
      <th class='text-center'>Start Date</th>
      <th class='text-center'>End Date</th>
      <th class='text-center'>Working Days</th>
      <th class='text-center'>Time in</th>
      <th class='text-center'>Time out</th>
      <th class='text-center'>Late Start</th>
      <th class='text-center'>Grace Period</th>
      <!-- <th class='text-center'>Beginning in</th>
      <th class='text-center'>Ending in</th>
      <th class='text-center'>Beginning out</th>
      <th class='text-center'>Ending out</th>
      <th class='text-center'>Break One Start</th>
      <th class='text-center'>Break One End</th>
      <th class='text-center'>Break Two Start</th>
      <th class='text-center'>Break Two End</th>
      <th class='text-center'>Break Three Start</th>
      <th class='text-center'>Break Three End</th> -->
      <th class='text-center'>Action</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>

<script type="text/javascript">
function validate_form() {
  /*
  Validate the inpus
   */
  var str_error="";
  if (validate_times($("#time_in").val(),$("#time_out").val())===false){
    str_error+="Invalid time in and time out. \n";
  } else if ($("#time_in").val()>=$("#time_out").val()) {
    str_error+="Time out should be greater than time in. \n";
  } 
  start_date=moment("2017-01-01 "+$("#time_in").val(), "YYYY-MM-DD hh:mm a");
  end_date=moment("2017-01-01 "+$("#time_out").val(), "YYYY-MM-DD hh:mm a");
  late_start=moment("2017-01-01 "+$("#late_start").val(), "YYYY-MM-DD hh:mm a");
  if (late_start.isBetwee(start_date, end_date)===false) {
    str_error+="Late start should be between time in and time out. \n"
  }
  if ($("#beginning_in").val()=="00:00" && $("#beginning_out").val()=="00:00") {
    str_error+="Invalid beginning in and beginning out. \n";
  } else if ($("#beginning_in").val()>=$("#ending_in").val()) {
    str_error+="Beginning in should be less than ending in. \n";
  } else if ($("#beginning_in").val() > $("#time_in").val()) {
    str_error+="Beginning in should be less than time in. \n";
  } 

  if ($("#ending_in").val()=="00:00" && $("#ending_out").val()=="00:00") {
    str_error+="Invalid ending in and ending out. \n";
  } else if ($("#ending_in").val()>=$("#ending_out").val()) {
    str_error+="Ending in should be less than ending out. \n";
  } else if ($("#ending_out").val() < $("#time_out").val()) {
    str_error+="Ending out should be greater than time out. \n";
  } 

  if ($("#break_one_start").val()!=="00:00" && $("#break_one_end").val()!=="00:00" && $("#break_one_start").val()>=$("#break_one_end").val()) {
    str_error+="Break one start should be less than break one end. \n";
  }

  if ($("#break_two_start").val()!=="00:00" && $("#break_two_end").val()!=="00:00" && $("#break_two_start").val()>=$("#break_two_end").val()) {
    str_error+="Break two start should be less than break two end. \n";
  }

  if ($("#break_three_start").val()!=="00:00" && $("#break_three_end").val()!=="00:00" && $("#break_three_start").val()>=$("#break_three_end").val()) {
    str_error+="Break three start should be less than break three end. \n";
  }

  if ($("input[name='working_days[]']:checked").length==0) {
      str_error+="Please select a working day.\n";
  }

  if (str_error=="") { 
    return confirm('This will replace the current active shift.');
  } else {
    alert('You have the following errors: \n'+str_error);
    return false;
  }
}
  $(function(){
<?php
if ($has_error===true || !empty($record)) :
?>
    $('#collapseForm').collapse({
      toggle: true
    });    
<?php
endif;
?>
    $('#CustomTable').DataTable({
          "columnDefs":[{
            "targets":[-1],
            "orderable":false
          }],
          "scrollX": true,
          "ajax":{
            "url":"ajax/employee_default_shifts.php",
            "data":function (d) {
              d.employee_id='<?php echo $employee['id'] ?>'
            }
          },
          "order": [[ 0, "desc" ]],
           dom: 'Bfrtip',
                buttons: [
                    {
                        extend:"excel",
                        text:"<span class='fa fa-download'></span> Download as Excel File "
                    }
                    ]
    });
  });

</script>

