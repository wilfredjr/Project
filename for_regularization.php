<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }

     if(!AllowUser(array(1,4))){
         redirect("index.php");
     }
  $employees=$con->myQuery("SELECT 
e.id,CONCAT(e.last_name,', ',e.first_name,' ',IFNULL(e.middle_name,'')) AS 'employee', es.name as employment_status
FROM employees e LEFT JOIN job_title jt ON e.job_title_id=jt.id LEFT JOIN departments d ON e.department_id=d.id LEFT JOIN employment_status es ON es.id=e.employment_status_id WHERE e.is_deleted=0 AND e.is_terminated=0 AND es.is_regular=0 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);
	makeHead("For Regularization");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            For Regularization
          </h1>
        </section>

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
                    <div class="col-xs-12">
                      <form class='form-horizontal' action='' method="GET" onsubmit='return false' id='frm_search'>
                        <?php
                          if(AllowUser(array(1,4))):
                        ?>
                        <div class="form-group">
                            <label for="department_id" class="col-sm-3 control-label">Department </label>
                            <div class="col-sm-9">
                              <select class='form-control cbo-department-id' name='department_id'  id='department_id' data-placeholder="Select Department" data-allow-clear="true"  style='width:100%'>
                              
                              </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="employees_id" class="col-sm-3 control-label">Employee </label>
                            <div class="col-sm-9">
                              <select class='form-control cbo' id='employee_id' name='employee_id' data-allow-clear="true" data-placeholder="All Employees"  style='width:100%'>
                                <?php
                                  echo makeOptions($employees);
                                ?>
                              </select>
                            </div>
                        </div>
                        <?php
                          endif;
                        ?>
                        <div class='form-group'>
                          <label for="date_from" class="col-sm-3 control-label">Date Start *</label>
                            <div class="col-sm-9">
                              <input type="text" class="form-control date_picker" id="date_from"  name='date_from' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_from']):''; ?>' required>
                            </div>
                        </div>
                        <div class='form-group'>
                          <label for="date_to" class="col-sm-3 control-label">Date End *</label>
                            <div class="col-sm-9">
                              <input type="text" class="form-control date_picker" id="date_to"  name='date_to' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_to']):''; ?>' required>
                            </div>
                        </div>
                        
                          <div class="form-group">
                            <div class="col-sm-12 text-center">
                              <button type='button' class='btn btn-warning' onclick='filter_search()'>Filter </button>
                              <button type='button'  class=' btn btn-default' onclick='form_clear("frm_search")'> Clear</button>
                            </div>
                          </div>
                      </form> 
                    </div>
                    <div class="col-sm-12">
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
                              <th class='text-center'>Employee Code</th>
                              <th class='text-center'>Employee</th>
                              <th class='text-center'>Job Title</th>
                              <th class='text-center'>Department</th>
                              <th class='text-center'>Email</th>
                              <th class='text-center'>Contact No</th>
                              <th class='text-center'>Employment Status</th>
                              <th class='text-center' style='min-width:50px'>Joined Date</th>
                              <th class='text-center' style='min-width:50px'>Expected Regularization Date</th>
                              <!-- <th class='text-center' style='max-width:20px'></th> -->
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
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "searching":false,
            "ajax":{
                  "url":"ajax/for_regularization.php",
                  "data":function(d){
                      d.employee_id=$("select[name='employee_id']").val();
                      d.department_id=$("select[name='department_id']").val();
                      d.date_start=$("input[name='date_from']").val();
                      d.date_end=$("input[name='date_to']").val();
                    }
                  },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend:"excel",
                    exportOptions: {
                        columns: [1,2,3,4,5,6,7]
                    },
                    text:"<span class='fa fa-download'></span> Download as Excel File "
                }
                ],
            "columnDefs": [
                    { "orderable": false, "targets": -1 }
                ],
            "order": [[ 7, "asc" ]]

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