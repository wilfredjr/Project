<?php
  require_once("support/config.php");
   if(!isLoggedIn()){
    toLogin();
    die();
   }



  // $data=$con->myQuery("SELECT
  //   id,
  //   code,
  //   employee_name,
  //   supervisor,
  //   final_approver,
  //   no_hours,
  //   worked_done,
  //   status,
  //   date_from,
  //   date_to
  //   FROM vw_employees_ot
  //   WHERE employee_id=:employee_id AND 'x'='y'
  //   ",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']));
  makeHead("Adjustment Request");
?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Adjustment Request
          </h1>
        </section>
        <div class="row"><br>
            <form action="" method="" class="form-horizontal" id="frmclear">
                <div class='form-group'>
                    <label class="col-md-3 control-label">Date Start *</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control date_picker" id="date_start" name='date_start'>
                    </div>
                    <label class="col-md-2 control-label">Date End *</label>
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
                        <button  type='button' onclick="form_clear('frmclear')" class="btn btn-default">Clear</button>
                    </div>
                </div>
            </form>
        </div>
          <div class="row">

            <div class='col-md-12'>
              <?php
                Alert();
              ?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                    <div class='col-md-12 text-right'>
                      <a href='frm_adjustment_request.php' class='btn btn-warning' onclick=''> File New Adjustment <span class='fa fa-plus'></span> </a>
                    </div>
                    <br/>
                    <br/>
                    <div class="col-sm-12">
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
                              <!-- <th class='text-center'>Employee Code</th>
                              <th class='text-center'>Employee</th> -->
                              <th class='text-center date-td'>Date Filed</th>
                              <th class='text-center date-td'>Date</th>
                              <th class='text-center time-td'>Time In</th>
                              <th class='text-center time-td'>Time Out</th>
                              <th class='text-center time-td'>Adjusted Time In</th>
                              <th class='text-center time-td'>Adjusted Time Out</th>
                              <th class='text-center'>Reason</th>
                              <th class='text-center'>Status</th>
                              <th class='text-center'>Previous Approver</th>
                              <th class='text-center'>Step</th>
                              <th class='text-center' style='min-width:100px'>Action</th>
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

    <div class="modal" id='modal_adjustment'>
    <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="">Adjustment Type</h4>
          </div>
          <div class="modal-body" >
            <div class='' id='' style=''>
              <form class='form-horizontal' name='frm_adj_type' action='frm_adjustment_request.php' method="POST" onsubmit='return validate_adj(this)'>
                <!--<input type='hidden' value='' id='a_id' name='id'>-->
                  <div class='form-group'>
                    <label class="col-sm-4 control-label">Type of Adjustment * </label>
                    <div class="col-sm-8">
                      <select class="form-control cbo" name="adj_type" id="adj_type" data-placeholder="Select Type" required>
                        <option value=''>Select Type of Adjustment</option>
                        <option value='1'>Time-in Only</option>
                        <option value='2'>Time-out Only</option>
                        <option value='3'>Both Time-in And Time-out</option>
                      <select>
                        &nbsp;&nbsp;&nbsp;
                      <button type='submit' class='btn btn-warning'>
                        Proceed to Adjustment Form
                      </button>
                    </div>
                  </div>
              </form>
            </div>
          </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->


<?php
  $request_type="adjustment";
  $redirect_page="adjustment_request.php";
  require_once("include/modal_query.php");
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
            "url":"ajax/adjustment_request.php",
            "data":function(d)
            {
                d.date_start=$("input[name='date_start']").val();
                // d.half_day_mode=$("select[name='half_day_mode']").val();
                d.date_end=$("input[name='date_end']").val();
                d.dept_id=$("select[name='dept_id']").val();
                d.status=$("select[name='status']").val();
            }
        },
        "columnDefs": [{ "orderable": false, "targets": -1 }],
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
