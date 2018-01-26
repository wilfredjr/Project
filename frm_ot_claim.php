<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}

    
    $canFileForEmployees=canFileForEmployees($_SESSION[WEBAPP]['user']['employee_id']);
    $data="";
    if (!empty($_GET['id'])) {
        $data=$con->myQuery("SELECT ot_date,time_from,time_to,worked_done FROM employees_ot_pre WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    }

	makeHead("Overtime Request");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Overtime Request
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
		              	<form class='form-horizontal' id='frm_submit' action='save_ot_claim.php' method="POST" onsubmit='return validate_form()'>
                      <input type='hidden' name='get_id' value='<?php echo !empty($_GET['id'])?$_GET['id']:''; ?>'>
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
                        <label for="ot_date" class="col-sm-3 control-label">Date of OT *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="ot_date"  name='ot_date' value='<?php echo !empty($data)?htmlspecialchars($data['ot_date']):''; ?>' <?php echo !empty($data)?'disabled':''; ?> required>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="time_start" class="col-sm-3 control-label">Start Time *</label>
                          <div class="col-sm-9">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="time_start"  name='time_start' value='<?php echo !empty($data)?htmlspecialchars($data['time_from']):''; ?>' required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="time_end" class="col-sm-3 control-label">End Time *</label>
                          <div class="col-sm-9">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="time_end"  name='time_end' value='<?php echo !empty($data)?htmlspecialchars($data['time_to']):''; ?>' required>
                            </div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="worked_done" class="col-sm-3 control-label">Work to be done * </label>
                          <div class="col-sm-9">
                            <textarea class='form-control' id='worked_done' name='worked_done' rows='5' value='<?php echo !empty($data)?$data['worked_done']:''; ?>' required=""></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                        <div class="checkbox col-sm-9 col-sm-offset-3">
                          <label>
                            <input type="checkbox" value="" name="apply_change_shift">
                            Apply Change shift
                          </label>
                        </div>
                      </div>
		                    <div class="form-group">
		                      <div class="col-sm-10 col-md-offset-2 text-center">
		                      	<a href='overtime.php' class='btn btn-default'>Cancel</a>
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

<script type="text/javascript">
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
  function validate_form() {
    let str_error="";
    
    if (validate_times($("#time_start").val(), $("#time_end").val())===false) {
      str_error+="Invalid time start and time end.";
    }
    if (str_error=="") { 
      $(frm).closest('form').find(':submit').button("loading");
      return true;
    } else {
      alert('You have the following errors: \n'+str_error);
      return false;
    }
  }
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

<?php
	makeFoot();
?>