<?php
    require_once("support/config.php");
if (!isLoggedIn()) {
    toLogin();
    die();
}

if (!AllowUser(array(4))) {
    redirect("index.php");
}
    makeHead("Users");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Users
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
                    <div class="col-sm-12">
                        <div class='col-ms-12 text-right'>
                          <a href='frm_users.php' class='btn btn-warning'> Create New <span class='fa fa-plus'></span> </a>
                        </div>
                        <br/>
                     <!--    <div class='col-sm-12'>
                        <form method='get' class='form-horizontal' id='frm_search'>
                            <div class='form-group'>
                                <label class='col-md-3 text-right' >Employee</label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo-employee-id' name='employee_id'  style='width:100%' data-allow-clear='true'>
                                    </select>
                                </div>
                                <label class='col-md-3 text-right' >User Type</label>
                                <div class='col-md-3'>
                                    <select class=' form-control cbo-user-type-id' name='user_type_id'  style='width:100%' data-allow-clear='true'>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-2 col-md-offset-5 text-right'>
                                    <button type='button'  class=' btn btn-warning' onclick='filter_search()'><span class="fa fa-search"></span> Filter</button>
                                    <button type='button'  class=' btn btn-default' onclick='form_clear("frm_search")'> Clear</button>
                                </div>
                            </div>
                        </form>
                    </div> -->
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
<!--                               <th class='text-center'>Employee Number</th> -->
                              <th class='text-center'>Employee Name</th>
                              <th class='text-center'>User Name</th>
                              <th class='text-center'>User Type</th>
<!--                               <th class='text-center'>Email</th>
                              <th class='text-center'>Contact No.</th> -->
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

<script type="text/javascript">
  $(function () {
        data_table=$('#ResultTable').DataTable({
          "processing": true,
          "serverSide": true,
          "searching": false,
          "ajax":{
                  "url":"ajax/users.php",
                  "data":function(d){
                      d.employee_id=$("select[name='employee_id']").val();
                      d.user_type_id=$("select[name='user_type_id']").val();
                    }
                  },
          "oLanguage": { "sEmptyTable": "No Employees found." }
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