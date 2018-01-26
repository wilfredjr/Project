<?php
  $tab=3;
?>
<div class="row"><br>
    <form action="" method="" class="form-horizontal" id="frmclear">
        <div class='form-group'>
            <label class="col-md-3 control-label">Date of OT Start *</label>
            <div class="col-md-3">
                <input type="text" class="form-control date_picker" id="ot_date_start" name='ot_date_start'>
            </div>
            <label class="col-md-2 control-label">Date of OT End *</label>
            <div class="col-md-3">
                <input type="text" class="form-control date_picker" id="ot_date_end" name='ot_date_end'>
            </div>
        </div>
        <div class='form-group'>
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

    <div class="box-body">
      <div class="row">
        <div class="col-sm-12">
            <table id='ResultTable' class='table table-bordered table-striped'>
              <thead>
                <tr>
                   <th class='text-center'>Employee Code</th>
                  <th class='text-center'>Employee</th> 
                  <th class='text-center date-td'>Date Filed</th>
                  <th class='text-center date-td'>Date</th>
                  <th class='text-center time-td'>Time In</th>
                  <th class='text-center time-td'>Time Out</th>
                  <th class='text-center'>Hours</th>
                  <th class='text-center time-td'>Adjusted Time In</th>
                  <th class='text-center time-td'>Adjusted Time Out</th>
                  <th class='text-center'>Adjusted Hours</th>
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
  $request_type="ot_adjustment";
  $redirect_page="overtime.php?tab=3";
  require_once("include/modal_query.php");
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
            "url":"ajax/overtime_adjustment_requests.php",
            "data":function(d)
            {
                d.start_date=$("input[name='ot_date_start']").val();
                // d.half_day_mode=$("select[name='half_day_mode']").val();
                d.end_date=$("input[name='ot_date_end']").val();
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
