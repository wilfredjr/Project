<?php
  $tab=2;
?>
<div class="row"><br>
    <form action="" method="" class="form-horizontal" id="frmclear">
        <div class='form-group'>
            <label class="col-md-3 control-label">Date of OT Start</label>
            <div class="col-md-3">
                <input type="text" class="form-control date_picker" id="ot_date_start" name='ot_date_start'>
            </div>
            <label class="col-md-2 control-label">Date of OT End</label>
            <div class="col-md-3">
                <input type="text" class="form-control date_picker" id="ot_date_end" name='ot_date_end'>
            </div>
        </div>
        <div class='form-group'>
            <label class="col-sm-3 control-label">Status</label>
            <div class='col-sm-3'>
              <select class='form-control cbo-request-status-id' name='status' id='status' data-placeholder="Select Status">
              </select>
            </div>
            <label class="col-sm-2 control-label">Project</label>
            <div class='col-sm-3'>
              <select class='form-control cbo-all-project-id' name='project_id' id='project_id' data-placeholder="Select Project">
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

    <div class="box-body">
      <div class="row">
        <div class='col-md-12 text-right'>
          <a href='frm_ot_claim.php' class='btn btn-warning'> File OT Claim <span class='fa fa-plus'></span> </a>
        </div>
        <br/>
        <br/>
        <div class="col-sm-12">
            <table id='ResultTable' class='table table-bordered table-striped'>
              <thead>
                <tr>
                   <th class='text-center'>Employee Code</th>
                  <th class='text-center'>Employee</th> 
                  <th class='text-center date-td'>Date Filed</th>
                  <th class='text-center date-td'>Date of OT</th>
                  <th class='text-center time-td'>Start Time</th>
                  <th class='text-center time-td'>Estimated Time Out</th>
                  <th class='text-center'>Estimated Hours</th>
                  <th class='text-center time-td'>Actual Time Out</th>
                  <th class='text-center'>Actual Hours</th>
                  <th class='text-center'>Work To Do</th>
                  <th class='text-center'>Project</th>
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


<?php
  $request_type="overtime";
  $redirect_page="overtime.php?tab=2";
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
            "url":"ajax/overtime_requests.php",
            "data":function(d)
            {
                d.ot_date_start=$("input[name='ot_date_start']").val();
                d.project_id=$("select[name='project_id']").val();
                d.ot_date_end=$("input[name='ot_date_end']").val();
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
