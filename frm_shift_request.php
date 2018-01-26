<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}

    

    $canFileForEmployees=canFileForEmployees($_SESSION[WEBAPP]['user']['employee_id']);
	$data="";


	makeHead("Change Shift Request");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Change Shift Request
          </h1> 
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-10 col-md-offset-1'>
				<?php
					Alert();
				?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                	<div class='col-md-12'>
		              	<form class='form-horizontal ' action='save_shift_request.php' method="POST" onsubmit='return validate(this)'>
                    <div class='form-group'>
                        <label for="ot_date" class="col-sm-3 control-label">Project *</label>
                          <div class="col-sm-9">
                            <select class='form-control cbo-project-id' name='project_id'  style='width:100%' data-allow-clear='true' <?php if (!empty($canFileForEmployees)) {?> onchange="getProjectEmployees(this)" <?php } ?> required=""></select>
                          </div>
                      </div>
                      <?php
                      if (!empty($canFileForEmployees)) {
                      ?>
                      <div class='form-group'>
                        <label for="ot_date" class="col-sm-3 control-label">Employees *</label>
                          <div class="col-sm-9">
                            <select class='form-control' name='employees_id[]' id='employees_id' style='width:100%' disabled="" required="" multiple="multiple"></select>
                          </div>
                      </div>
                      <?php
                      }
                      ?>
		              		<div class='form-group'>
                        <label for="date_from" class="col-sm-3 control-label">Date Start *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="date_from"  name='date_from' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="date_to" class="col-sm-3 control-label">Date End *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="date_to"  name='date_to' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="orig_in_time" class="col-sm-3 control-label">Original In Time *</label>
                          <div class="col-sm-9">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="orig_in_time"  name='orig_in_time' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="orig_out_time" class="col-sm-3 control-label">Original Out Time *</label>
                          <div class="col-sm-9">
                          <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="orig_out_time"  name='orig_out_time' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group'>
                          <label class='control-label col-sm-3'> Shift* </label>
                          <div class = "col-sm-9">
                            <input type='hidden' id="shift" name='shift' value=''>
                            <div class="input-group">
                              <input type="text" class="form-control" id="shift_display" placeholder="Select Shift "  maxlength='250' disabled="" required="">
                              <span class="input-group-btn">
                              <button type='button' class='btn btn-warning btn-flat'   data-toggle="modal" data-target="#shifts_modal"><span class='fa fa-search'></span></button>
                              </span>
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
                      <!--
                      <div class='form-group'>
                        <label for="adj_in_time" class="col-sm-3 control-label">Requested In Time *</label>
                          <div class="col-sm-9">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="adj_in_time"  name='adj_in_time' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="adj_out_time" class="col-sm-3 control-label">Requested Out Time *</label>
                          <div class="col-sm-9">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="adj_out_time"  name='adj_out_time' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                            </div>
                          </div>
                      </div>
		              		-->
                      <div class="form-group">
                          <label for="shift_reason" class="col-sm-3 control-label">Reason * </label>
                          <div class="col-sm-9">
                            <textarea class='form-control' name='shift_reason' id='shift_reason' rows='5' required=""></textarea>
                          </div>
                      </div>

		                    <div class="form-group">
		                      <div class="col-sm-10 col-md-offset-2 text-center">
		                      	<a href='shift_request.php' class='btn btn-default' onclick='return confirm("Are you sure you want to cancel?")'>Cancel</a>
		                        <button type='submit' class='btn btn-warning'>Save </button>
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
<div class="modal " role="dialog" aria-labelledby="shifts_modal" id="shifts_modal">
  <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title">Select shifts</h4>
        </div>
        <div class="modal-body">
          <div class="row">           
            <div class="col-md-12">
          <table class='table responsive table-bordered table-condensed table-hover ' id='shift_tables'>
              <thead>
                  <tr>
                      <th class='text-center'>Shift Name</th>
                      <th class='text-center'>Time in</th>
                      <th class='text-center'>Time out</th>
                      <!-- <th class='text-center'>Break One</th>
                      <th class='text-center'>Break Two</th>
                      <th class='text-center'>Break Three</th> -->
                      <th class='text-center'>Action</th>
                  </tr>
              </thead>
              <tbody>
                  
              </tbody>
          </table>
            </div>
          </div>
        </div>
      </div>
  </div>
</div>
<script type="text/javascript">
  function getProjectEmployees(project_select) {
    employees_id.select2("val", "");
    employees_id.select2("enable", false);
    employees_id.select2({
      ajax: {
        url:'./ajax/cbo_project_employees.php?project_id='+project_select.value,
        dataType: "json",
        data: function (params) {

            var queryParameters = {
                term: params.term
            }
            return queryParameters;
        },
        processResults: function (data) {
              return {
                  results: $.map(data, function (item) {
                      // console.log(item);
                      return {
                          text: item.description,
                          id: item.id
                      }
                  })
              };
        }
      }
    });
    employees_id.removeAttr('disabled')
    employees_id.select2("enable", true);
    // $.get( "./ajax/cbo_project_employees.php", { project_id: project_select.value } )
    // .done(function( data ) {
      
    // });
  }
</script>
<script type="text/javascript">
var shifts_table="";
var employees_id="";
  $(function () {
        $('#ResultTable').DataTable();
        <?php
        if (!empty($canFileForEmployees)) {
        ?>
        employees_id=$("select[name='employees_id[]']").select2();
        <?php
        }
        ?>
      });
      
  $(function () {
        $('#ResultTable').DataTable();
        $("#shifts_modal").on("show.bs.modal",function (e) {
            shifts_table=$('#shift_tables').DataTable({
                "scrollY":"400px",
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "searching": true,
                "ajax": "ajax/select_shift.php",
                "columnDefs": [
                    { "orderable": false, "targets": -1 }
                  ]
            });
        });
        $("#shifts_modal").on("hide.bs.modal",function (e) {
          shifts_table.destroy();
        });
  });
    function validate(frm) {
    str_error="";
    if(Date.parse($("#date_from").val()) > Date.parse($("#date_to").val())){
      str_error+="Start date cannot be greater than end date.\n";
    }
    
    if(Date.parse($("#orig_in_time").val()) == Date.parse($("#orig_out_time").val())){
      str_error+="Time out and time in should be different.\n";
    }
    if ($("#shift").val()==""){
      str_error+="Please select a shift.\n";
    }

    if ($("input[name='working_days[]']:checked").length==0) {
      str_error+="Please select a working day.\n";
    }
    if (str_error!="") {
      alert(str_error);
      return false;
    } else {
      $(this).closest('form').find(':submit').button("loading");
    }

    return true;
  }

  function select_shift(id, shift_name) {
    $("#shift").val(id);
    $('#shift_display').val(shift_name);
    $("#shifts_modal").modal('toggle');
  }

</script>

<?php
	makeFoot();
?>