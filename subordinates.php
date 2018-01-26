<?php
    require_once("support/config.php");
     if(!isLoggedIn()){
        toLogin();
        die();
     }

  $job_titles=$con->myQuery("SELECT id, description FROM job_title WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
  makeHead("Subordinates");

?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Subordinates
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">
              <form action="" method="" class="form-horizontal" id='frmclear'>

                  <div class='form-group'>
                      <label class="col-sm-2 control-label">Employee Name</label>
                      <div class='col-sm-3'>
                          <select class='form-control cbo-subordinate-id' name='employee_id' id='employee_id' data-allow-clear='True' data-placeholder="Select Subordinate">       
                             
                          </select>
                      </div>
                      <label class="col-sm-3 control-label">Department</label>
                        <div class="col-sm-3">
                          <select class='form-control cbo-department-id' name='department_id' id='department_id' data-placeholder="Select Department" data-allow-clear="true" style='width:100%'>
                          </select>
                        </div>
                  </div>
                  <div class='form-group'>
                      <label class="col-sm-2 control-label">Job Title</label>
                      <div class='col-sm-3'>
                          <select class='form-control cbo' name='job_title_id' id='job_title_id' data-allow-clear='True' data-placeholder="Select Job Title">       
                             <?php echo makeOptions($job_titles); ?>
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
            <div class='col-md-12'>
              <?php 
                Alert();
              ?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                    <div class="col-sm-12">
                      <br/>
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
                              <th class='text-center'>Employee Code</th>
                              <th class='text-center'>Employee</th>
                              <th class='text-center'>Job Title</th>
                              <th class='text-center'>Department</th>
                              <th class='text-center'>Email</th>
                              <th class='text-center'>Contact No</th>
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

<script type="text/javascript">
var data_table="";
  $(function () {
        data_table=$('#ResultTable').DataTable({
            "searching": false,
            "scrollX": true,
            "columnDefs": [{ "orderable": false, "targets": -1 }],
            "ajax":
            {    
                "url":"ajax/subordinates.php",
                "data":function(d)
                {
                  d.department_id=$("select[name='department_id']").val();
                  d.employee_id=$("select[name='employee_id']").val();
                  d.job_title_id=$("select[name='job_title_id']").val();
                }
            },
            "order": [[ 1, "asc" ]]

        });
      });
  function filter_search() 
  {
      data_table.ajax.reload();
  }
</script>

<?php
  Modal();
    makeFoot();
?>