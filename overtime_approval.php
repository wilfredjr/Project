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
  //   DATE_FORMAT(ot_date,'%m-%d-%Y') as ot_date,
  //   time_from,
  //   time_to,
  //   DATE_FORMAT(date_filed,'%m-%d-%Y') as date_filed
  //   FROM vw_employees_ot
  //   WHERE CASE 
  //   when status='Supervisor Approval' then supervisor_id 
  //   when status='Final Approver Approval' then final_approver_id
  //   end 
  //   =:employee_id
  //   ",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']));
  


  // $data2=$con->myQuery("SELECT 
  //   id,
  //   code,
  //   employee_name,
  //   supervisor,
  //   no_hours,
  //   worked_done,
  //   status,
  //   DATE_FORMAT(ot_date,'%m-%d-%Y') as ot_date,
  //   time_from,
  //   time_to,
  //   DATE_FORMAT(date_filed,'%m-%d-%Y') as date_filed 
  //   FROM vw_employees_ot_pre
  //   WHERE status='Supervisor Approval' and supervisor_id=:employee_id
  //   ",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']));


  $employee=$con->myQuery("SELECT id, CONCAT(last_name,', ',first_name,' ',middle_name,' (',code,')') AS name FROM employees WHERE is_deleted=0 AND is_terminated=0")->fetchAll(PDO::FETCH_ASSOC);
  makeHead("Overtime Approval");

?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Overtime Approval
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">

            <div class="row">
                <form action="" method="" class="form-horizontal" id='frmclear'>

                    <div class='form-group'>
                        <label class="col-sm-3 control-label">Employee Name*</label>
                        <div class='col-sm-3'>
                            <select class='form-control cbo' name='employee_id' id='employee_id' data-allow-clear='True' data-placeholder="Select Employee">       
                                <?php echo makeOptions($employee); ?>
                            </select>
                        </div>
                        <!-- <label class="col-sm-2 control-label">Type of Overtime *</label>
                        <div class='col-sm-3'>
                            <select class='form-control cbo' name='request_type' id='request_type' data-allow-clear='True' data-placeholder="Select Type of OT">       
                                <option value=""></option>
                                <option value="Pre-approval OT">Pre-approved OT</option>
                                <option value="OT Claim">OT Claim</option>
                            </select>
                        </div> -->
                        <label class="col-sm-2 control-label">Department*</label>
                              <div class="col-sm-3">
                                <select class='form-control cbo-department-id' name='department_id' id='department_id' data-placeholder="Select Department" >
                   
                                </select>
                              </div>
                    </div>
                    <div class='form-group'>
                      <label class="col-md-3 control-label">Start Date *</label>
                      <div class="col-md-3">
                          <input type="text" class="form-control date_picker" id="date_start" name='date_start'>
                      </div>
                      <label class="col-md-2 control-label">End Date *</label>
                      <div class="col-md-3">
                          <input type="text" class="form-control date_picker" id="date_end" name='date_end'>
                      </div>
                    </div>
                    <div class='form-group'>
                      <label class="col-sm-3 control-label">Project</label>
                      <div class='col-sm-3'>
                        <select class='form-control cbo-all-project-id' name='project_id' id='project_id' data-placeholder="Select Project">
                        </select>
                      </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-md-7 text-right'>
                            <button type='button' class='btn-flat btn btn-warning' onclick='filter_search()'><span class="fa fa-search"></span> Filter</button>
                            <button type='button' onclick="form_clear('frmclear')" class="btn btn-default">Clear</button>
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
                                       
                                        <!-- <input type="hidden" name="approve_ot_type"> -->
                                        <input type="hidden" name="approve_emp_id">
                                        <input type="hidden" name="approve_dep_id">
                                        <input type="hidden" name="approve_start_date">
                                        <input type="hidden" name="approve_end_date">
                                        <input type="hidden" name="type" value='overtime'>
                                        <button class="btn btn-flat btn-success" title="Approve All Requests"><span class="ion ion-checkmark-round"></span> Approve All</button>
                                    </form>
                                </div>
                    <div class="col-sm-12">
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
                              <!-- <th class='text-center'>Type of OT</th> -->
                              <th class='text-center'>Employee Code</th>
                              <th class='text-center'>Employee</th>
                              <th class='text-center'>Department</th>
                              <th class='text-center date-td'>Date Filed</th>
                              <th class='text-center'>OT Hours</th>
                              <th class='text-center date-td'> OT Date &nbsp; </th>
                              <th class='text-center time-td'>OT Start</th>
                              <th class='text-center time-td'>OT End</th>
                              <th class='text-center time-td'>Actual Time Out</th>
                              <th class='text-center'>Actual Hours</th>
                              <th class='text-center date-time-td'>Work To Do</th>
                              <th class='text-center'>Project</th>
                              <th class='text-center'>Status</th>
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
    $redirect_page="overtime_approval.php";
    $request_type="overtime";
    require_once("include/modal_reject.php");
    require_once("include/modal_query.php");
    // require_once("include/modal_reject_pre_ot_overtime.php");
    // require_once("include/modal_reject_ot_overtime.php");
    // require_once("include/modal_query_ot.php");
    // require_once("include/modal_query_pre_ot.php");
 //   require_once("include/modal_query.php");
  ?>

<script type="text/javascript">

var dttable="";
  $(function () {

       dttable=$('#ResultTable').DataTable({
            "scrollX": true,

            "processing": true,
            "serverSide": true,
            "searching": false,
            "order": [[ 3, "desc" ]], 
    

            "ajax":
              {    
                "url":"ajax/overtime_approval.php",
                "data":function(d)
                {
                    d.dep_id=$("select[name='department_id']").val();
                    d.project_id=$("select[name='project_id']").val();
                    d.emp_id=$("select[name='employee_id']").val();
                    d.start_date=$("input[name='date_start']").val();
                    d.end_date=$("input[name='date_end']").val();
                }
     
            
            },
            "columnDefs": [{ "orderable": false,"targets": -1 }], "order": [[ 3, "desc" ]]
          
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
    function reject_ot(id){
            $('#modal_ot_reject').modal('show');
            $('#reject_id_ot').val(id);
        }
        function reject_pre_ot(id){
          
            $('#modal_pre_ot_reject').modal('show');
            $('#reject_id_ot_pre').val(id);
        }
    function query_pre_ot(id){
            $('#modal_pre_ot').modal('show');
            $("#comment_table_pre_ot").html("<span class='fa fa-refresh fa-pulse'></span>")
            $("#comment_table_pre_ot").load("ajax/comments.php?id="+id+"&request_type=pre_overtime");

            $("#request_id_pre").val(id);
        }
     function query_ot(id){
            $('#modal_ot').modal('show');
            $("#comment_table_ot").html("<span class='fa fa-refresh fa-pulse'></span>")
            $("#comment_table_ot").load("ajax/comments.php?id="+id+"&request_type=overtime");

            $("#request_id_ot").val(id);
        }   
</script>

<?php
  Modal();
  makeFoot();
?>