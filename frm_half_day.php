<?php
	require_once("support/config.php");
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}
    // if(!AllowUser(array(1,2)))
    // {
    //     redirect("index.php");
    // }
	$data=""; 
    $leave_type=$con->myQuery("SELECT eal.leave_id,
                                  CONCAT((SELECT NAME FROM LEAVES WHERE id=eal.leave_id),' (',eal.balance_per_year,' day/s left)') AS leave_balance
                            FROM employees_available_leaves eal
                            WHERE is_cancelled=0 AND is_deleted=0 AND employee_id=?",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
    $canFileForEmployees=canFileForEmployees($_SESSION[WEBAPP]['user']['employee_id']);

	makeHead("Application for Leave Form");
?>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
<div class="content-wrapper">
    <section class="content-header text-center">
        <h1>
            Undertime Request Application Form
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-10 col-md-offset-1'>
				<?php	Alert();	?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class='col-md-12'>
                                <form class='form-horizontal disable-submit' action='save_half_day.php' method="POST">
                                    <div class='form-group'>
                                        <label for="ot_date" class="col-sm-2 control-label">Project *</label>
                                        <div class="col-sm-9">
                                            <select class='form-control cbo-project-id' name='project_id'  style='width:100%' data-allow-clear='true' <?php if (!empty($canFileForEmployees)) {?> onchange="getProjectEmployees(this)" <?php } ?> required=""></select>
                                        </div>
                                    </div>
                                    <?php
                                    if (!empty($canFileForEmployees)) {
                                    ?>
                                    <div class='form-group'>
                                        <label for="employees_id" class="col-sm-2 control-label">Employee *</label>
                                        <div class="col-sm-9">
                                          <select class='form-control ' name='employee_id' id='employees_id' style='width:100%' required="" disabled="" onchange="getEmployeeLeaves(this)" data-placeholder="Select Employee"></select>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <label for="name" class="col-sm-2 control-label">Type of Leave *</label>
                                        <div class='col-sm-9'>
                                            <select class='form-control cbo' name='leave_id' data-placeholder="Select Type of Leave" <?php echo !(empty($data))?"data-selected='".$data['leave_id']."'":NULL ?> required disabled >
                                            </select>
                                        </div>
                                    </div>
                                    <?php
                                    } else {
                                    ?>
                                    <div class='form-group'>
                                        <label for="name" class="col-sm-2 control-label">Type of Leave *</label>
                                        <div class='col-sm-9'>
                                            <select class='form-control cbo' name='leave_id' data-placeholder="Select Type of Leave" <?php echo !(empty($data))?"data-selected='".$data['leave_id']."'":NULL ?> required >           
                                                <?php
                                                        echo makeOptions($leave_type);
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php 
                                    }
                                    ?> 
                                    <div class='form-group'>
                                        <label for="time_hd" class="col-sm-2 control-label"> Half Day For *</label>
                                        <div class='col-sm-9 '>
                                            <select class='form-control cbo' name='time_hd' data-placeholder="Select Time" required>
                                                <option value=''>Select Time</option>
                                                <option value='1'>Morning</option>
                                                <option value='2'>Afternoon</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_hd" class="col-md-2 control-label">Date *</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control date_picker" id="date_hd" name='date_hd' required>
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label for="reason" class="col-md-2 control-label">Reason *</label>
                                        <div class="col-md-9">
                                            <textarea class='form-control' name='reason' id='reason'  required></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-9 col-md-offset-2 text-center">
                                            <button type='submit' class='btn btn-warning'>Save </button>
                                            <a href='employee_leave_request.php' class='btn btn-default' onclick="return confirm('Are you sure you want to cancel this application?')">Cancel</a>
                                        </div>
                                    </div>
                                </form>	
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    var employees_id="";

    $(function () 
    {
        $('#ResultTable').DataTable();
        employees_id=$("select[name='employee_id']").select2();

    });
    function getEmployeeLeaves(emp_select) {
        leave_select=$("select[name='leave_id']");
        leave_select.select2("val","");
        leave_select.select2("enable",false);
        leave_select.select2({
          ajax: {
            url:'./ajax/cbo_employee_leaves.php?emp_id='+emp_select.value,
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
        leave_select.removeAttr('disabled')
        leave_select.select2("enable", true);
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