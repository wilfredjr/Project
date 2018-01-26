<?php
  require_once("support/config.php");
   if(!isLoggedIn()){
    toLogin();
    die();
   }

  makeHead("Allowance Approval");
  require_once("template/header.php");
  require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Allowance Approval
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="col-sm-12">
              <form action="" method="" class="form-horizontal" id='frmclear'>
                  <div class='form-group'>

                    <label class="col-sm-3 control-label">Department*</label>
                          <div class="col-sm-3">
                            <select class='form-control cbo-department-id' name='department_id' id='department_id' data-placeholder="Select Department" >
               
                            </select>
                          </div>
                    
                      <label class="col-sm-3 control-label">Employee Name *</label>
                      <div class='col-sm-3'>
                          <select class='form-control cbo-employee-id' name='employee_id' id='employee_id' data-placeholder="Select Employee">       
                          </select>
                      </div>
                  </div>
                  <div class='form-group'>
                      <label class="col-md-3 control-label"> Date Applied Start *</label>
                      <div class="col-md-3">
                          <input type="text" class="form-control date_picker" id="date_start" name='date_start'>
                      </div>
                      <label class="col-md-3 control-label"> Date Applied End *</label>
                      <div class="col-md-3">
                          <input type="text" class="form-control date_picker" id="date_end" name='date_end'>
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
          <!-- Main row -->
          <div class="row">

            <div class='col-md-12'>
              <?php 
                Alert();
              ?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                    <div class="col-sm-12 text-right" style="margin-top: 10px; margin-bottom: 10px">
                        <form class="" method="POST" onsubmit="return approve_all()" action="approve_all.php">
                            <input type="hidden" name="approve_emp_id">
                            <input type="hidden" name="approve_dep_id">
                            <input type="hidden" name="approve_start_date">
                            <input type="hidden" name="approve_end_date">
                            <input type="hidden" name="type" value='allowance'>
                            <button class="btn btn-flat btn-success" title="Approve All Requests"><span class="ion ion-checkmark-round"></span> Approve All</button>
                        </form>
                    </div>
                    <div class="col-sm-12">
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
                              <th class='text-center'>Employee Code</th>
                              <th class='text-center'>Employee</th>
                              <th class='text-center'>Department</th>
                              <th class='text-center date-td'>Date Filed</th>
                              <th class='text-center date-td'>Date Applied</th>
                              <th class='text-center'>Food Allowance</th>
                              <th class='text-center'>Transportation Allowance</th>
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
  <?php
    $request_type="allowance";
    $redirect_page="allowance_approval.php";
    require_once("include/modal_reject.php");
    require_once("include/modal_query.php");
    require_once("include/pic_modal.php");
  ?>


<script type="text/javascript">
  var dttable="";
  $(function () {
        dttable=$('#ResultTable').DataTable({
          "scrollX": true,
            "scrollY": false,
            "processing": true,
            "serverSide": true,
            "searching": false,
            "ajax":
            {    
                "url":"ajax/allowance_approval.php",
                "data":function(d)
                {
                    d.dep_id=$("select[name='department_id']").val();
                    d.emp_id=$("select[name='employee_id']").val();
                    d.start_date=$("input[name='date_start']").val();
                    d.end_date=$("input[name='date_end']").val();
                }
            },
            "columnDefs": [{ "orderable": false, "targets": -1 }],
            "order": [[ 3, "desc" ]]
        });
      });
  function filter_search() 
  {
          dttable.ajax.reload();
          //console.log(dttable);
  }
  function approve_all() {
        if (confirm('Are you sure you want to approve all requests?')) {
            $("input[name='approve_dep_id']").val($("select[name='department_id']").val());
            $("input[name='approve_emp_id']").val($("select[name='employee_id']").val());
            $("input[name='approve_start_date']").val($("input[name='date_start']").val());
            $("input[name='approve_end_date']").val($("input[name='date_end']").val());
            return true;
        }
        return false;
    }
</script>

<?php
  Modal();
  makeFoot();
?>