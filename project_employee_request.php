<?php
  require_once("support/config.php");
   if(!isLoggedIn()){
    toLogin();
    die();
   }
  makeHead("Project Employee Request");
?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header text-center">
          <h1 class="page-header">
                Project Employee Request
            </h1>
        </section>
        <div class="row"><br>
            <form action="" method="" class="form-horizontal" id="frmclear">
                <div class='form-group'>
                    <label class="col-md-3 control-label">Date Applied Start *</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control date_picker" id="date_start" name='date_start'>
                    </div>
                    <label class="col-md-2 control-label">Date Applied End *</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control date_picker" id="date_end" name='date_end'>
                    </div>
                </div>
                <div class='form-group'>
                    <!-- <label class="col-sm-3 control-label">Department *</label>
                    <div class='col-sm-3'>
                        <select class='form-control cbo-department-id' name='dept_id' id='dept_id' data-placeholder="Select Department">
                        </select>
                    </div> -->
        <!--                     <label class="col-sm-2 control-label">Type of Half Day *</label>
                    <div class='col-sm-3'>
                        <select class='form-control cbo' name='half_day_mode' id='half_day_mode' data-placeholder="Select Type of Half Day">
                            <option value=""></option>
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div> -->
                    <label class="col-sm-3 control-label">Status *</label>
                    <div class='col-sm-3'>
                        <select class='form-control cbo-request-status-id' name='status' id='status' data-placeholder="Select Status">
                        </select>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-md-7 text-right'>
                        <button type='button' class='btn-flat btn btn-warning' onclick='filter_search()'><span class="fa fa-search"></span> Filter</button>
                        <button  type='button' onclick="form_clear('frmclear')" class="btn btn-default btn-flat">Clear</button>
                    </div>
                </div>
            </form>
        </div>

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
                    <br/>
                    <div class="col-sm-12">
                        <table id='ResultTable' class='table table-bordered table-striped' >
                          <thead>
                            <tr>
                              <!-- <th class='text-center'>Employee Code</th>
                              <th class='text-center'>Employee</th> -->
                              <th class='text-center date-td'>Date Filed</th>
                              <th class='text-center'>Employee Name</th>
                              <th class='text-center'>Project</th>
                              <th class='text-center'>Request Type</th>
                              <th class='text-center'>Designation</th>
                              <th class='text-center'>Current Approver</th>
                              <th class='text-center'>Status</th>
                              <th class='text-center'>Reason</th>
                              <th class='text-center'>Action</th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>
                    </div><!-- /.col -->
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
          </div><!-- /.row -->
        </section><!-- /.content -->
  </div>
<?php
  $request_type="project_approval_emp";
  $redirect_page="project_employee_request.php";
  require_once("include/modal_query.php");
  require_once("include/pic_modal.php");
  require_once("include/modal_query_logs.php");
?>

<script type="text/javascript">
var dttable="";
$(document).ready(function ()
{
    dttable=$('#ResultTable').DataTable({
        "scrollX": true,
        "processing": true,
        "serverSide": true,
        "searching": false,
        "ajax":
        {
            "url":"ajax/project_employee_request.php",
            "data":function(d)
            {
                d.date_start=$("input[name='date_start']").val();
                // d.half_day_mode=$("select[name='half_day_mode']").val();
                d.date_end=$("input[name='date_end']").val();
                d.dept_id=$("select[name='dept_id']").val();
                d.status=$("select[name='status']").val();
            }
        },
        "columnDefs": [{ "orderable": false, "targets": -1 },
        {"sClass": "text-center", "aTargets": [ -1 ]}],
        "order": [[ 0, "desc" ]]
    });
});

      function filter_search()
      {
              dttable.ajax.reload();
              //console.log(dttable);
      }
</script>

<?php
  Modal();
  makeFoot();
?>
