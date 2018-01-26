<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}



  $employees=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name,' (',code,')') as employee_name FROM employees WHERE is_deleted=0 AND is_terminated=0 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);

   $departments=$con->myQuery("SELECT id,name FROM departments WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

	makeHead("Attendance Report");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Attendance Report
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-12'>
				<?php
					Alert();
				?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                	<div class='col-md-12'>
		              	<form class='form-horizontal' action='' method="GET" onsubmit='return validate(this)'>
                      <?php
                        if(AllowUser(array(1,4))):
                      ?>
											<div class="form-group">
		                      <label for="department_id" class="col-sm-3 control-label">Department </label>
		                      <div class="col-sm-9">
                            <select class='form-control cbo' name='department_id' onchange='getUsers()'  id='department_id' data-placeholder="Select Department" data-allow-clear="true" <?php echo !(empty($_GET))?"data-selected='".$_GET['department_id']."'":NULL ?> style='width:100%'>
                            <?php
                              echo makeOptions($departments);
                            ?>
                            </select>
		                      </div>
		                  </div>
                      <div class="form-group">
                          <label for="employees_id" class="col-sm-3 control-label">Employee </label>
                          <div class="col-sm-9">
                            <select class='form-control cbo' id='employees_id' name='employees_id' data-allow-clear="true" data-placeholder="All Employees" value='<?php echo !(empty($_GET))?"data-selected='".$_GET['employees_id']."'":NULL ?>' <?php echo !(empty($_GET))?"data-selected='".$_GET['employees_id']."'":NULL ?> style='width:100%'>
                            <?php
                              echo makeOptions($employees);
                            ?>
                            </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="payroll_group_id" class="col-sm-3 control-label">Payroll Group </label>
                          <div class="col-sm-9">
                            <select class='form-control cbo-paygroup-id' id='payroll_group_id' name='payroll_group_id' data-allow-clear="true" data-placeholder="All Pay Groups" <?php echo !(empty($_GET))?"data-selected='".$_GET['payroll_group_id']."'":NULL ?> style='width:100%'>
                              <?php
                                if (!empty($_GET['payroll_group_id'])) {
                                  $payroll_group=$con->myQuery("SELECT payroll_group_id as id,name AS description FROM payroll_groups WHERE is_deleted=0 AND payroll_group_id=?", array($_GET['payroll_group_id']))->fetch(PDO::FETCH_ASSOC);
                                  if (!empty($payroll_group)) {
                                    echo "<option value='{$payroll_group['id']}'>{$payroll_group['description']}</option>";
                                  }
                                }
                              ?>
                            </select>
                          </div>
                      </div>
                      <?php
                        endif;
                      ?>
                      <div class='form-group'>
                        <label for="date_from" class="col-sm-3 control-label">Date Start *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="date_from"  name='date_from' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_from']):''; ?>' required>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="date_to" class="col-sm-3 control-label">Date End *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="date_to"  name='date_to' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_to']):''; ?>' required>
                          </div>
                      </div>

		                    <div class="form-group">
		                      <div class="col-sm-9 col-md-offset-3 text-center">
		                        <button type='submit' class='btn btn-warning'>Filter </button>
                            <a href='attendance_report.php' class='btn btn-default'>Cancel</a>
		                      </div>
		                    </div>
		                </form>
                	</div>
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->

              <?php
                if(!empty($_GET)):
              ?>
              <div class="box box-solid">
                <div class="box-body">
                  <div class="row">

                  <div class='col-md-12'>
                  <br/>
                    <table class='table table-bordered table-striped' id='ResultTable'>
                      <thead>
                        <th class='text-center' style='width:8%'>Employee Number</th>
                        <th class='text-center' style='width:15%'>Employee Name</th>
												<th class='text-center date-td'>Department</th>
                        <th class='text-center date-td'>Payroll Group</th>
                        <th class='text-center date-td'>Date</th>
                        <th class='text-center date-td'>Shift Start</th>
                        <th class='text-center date-td'>Shift End</th>
                        <th class='text-center date-td'>Regular Time In</th>
                        <th class='text-center date-td'>Regular Time Out</th>
                        <!-- <th class='text-center date-td'>Break Excess</th> -->
                        <th class='text-center'>Extended Hours</th>
                        <th class='text-center'>Status</th>
                        <th class='text-center'>Note</th>
                        <!-- <th class='text-center'>Late / Undertime</th> -->
                        <th class='text-center'>Late</th>
                        <th class='text-center'>Hours Worked</th>
                      </thead>
                      <tbody>

                      </tbody>
                    </table>
                  </div>
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
              <?php
                endif;
              ?>

            </div>
          </div><!-- /.row -->
        </section><!-- /.content -->
  </div>
<script type="text/javascript">
  $(document).ready(function () {
    $("#department_id").on("select2:select", function (e) { getUsers() });
  })
   function validate(frm) {

    if(Date.parse($("#date_from").val()) > Date.parse($("#date_to").val())){
      alert("Date Start cannot be greater than Date End.");
      return false;
    }
    else if(Date.parse($("#date_from").val()) == Date.parse($("#date_to").val())){
      alert("Date End should be greater than Date Start.")
      return false;
    }

    return true;
  }

  function getUsers() {
        // console.log($("#departments").val());
        $("select[name='employees_id']").val(null).trigger("change");
        $("select[name='employees_id']").load("ajax/cb_users.php?d_id="+$("select[name='department_id']").val());
    }
</script>
<?php
  if(!empty($_GET)):
?>
<script type="text/javascript">
  $(function () {
        $('#ResultTable').DataTable({
          "processing": true,
                // "serverSide": true,
                "scrollX": true,
                "searching":false,
                "ajax":{
                  "url":"ajax/attendance_report.php",
                  "dataSrc":"",
                  "data":function(d){
                    d.date_from='<?php echo !empty($_GET['date_from'])?$_GET['date_from']:''; ?>';
                    d.date_to='<?php echo !empty($_GET['date_to'])?$_GET['date_to']:''; ?>';
                    d.employees_id='<?php echo !empty($_GET['employees_id'])?$_GET['employees_id']:''; ?>';
                    d.department_id='<?php echo !empty($_GET['department_id'])?$_GET['department_id']:''; ?>';
                    d.payroll_group_id='<?php echo !empty($_GET['payroll_group_id'])?$_GET['payroll_group_id']:''; ?>';
                  }
                },
                  columns: [
                      { data: 'code' },
                      { data: 'employee' },
                      { data: 'payroll_group' },
											{ data: 'department_name'},
                      { data: 'date' },
                      { data: 'shift_start' },
                      { data: 'shift_end' },
                      { data: 'in_time' },
                      { data: 'out_time' },
                      // { data: 'break_excess' },
                      { data: 'ot' },
                      { data: 'status' },
                      { data: 'note' },
                      { data: 'lates' },
                      { data: 'hours_worked' }
                      // { data: 'undertime' },
                  ]
              ,dom: 'Bfrtip',
              buttons: [
                  {
                      extend:"excel",
                      text:"<span class='fa fa-download'></span> Download as Excel File ",
                      extension:".xls"
                  }
                  ]
        });

      });
</script>
<?php
  endif;
?>
<?php
	makeFoot();
?>
