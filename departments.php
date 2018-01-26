<?php
    require_once("support/config.php");
    if(!isLoggedIn()){
     toLogin();
     die();
    }

    if(!AllowUser(array(1,4))){
        redirect("index.php");
    }

    makeHead("Departments");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Departments
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
                  <div class='row'>
                    <div class='col-sm-12'>
                        <form method='get' class='form-horizontal' id='frm_search'>
                            <div class='form-group'>
                                <label class='col-md-3 text-right' >Department</label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo-department-id' name='department_id'  style='width:100%' data-allow-clear='true' data-company-id='2'>
                                    </select>
                                </div>
                                <label class='col-md-3 text-right' >Parent Department</label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo-department-id' name='parent_department_id'  style='width:100%' data-allow-clear='true'>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 text-right' >Pay Group</label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo-paygroup-id' name='company_id'  style='width:100%' data-allow-clear='true'>
                                    </select>
                                </div>
                                <!-- <label class='col-md-3 text-right' >Approver</label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo-employee-id' name='approver_id'  style='width:100%' data-allow-clear='true'>
                                    </select>
                                </div> -->
                            </div>
                            <div class='form-group'>
                                <div class='col-md-2 col-md-offset-5 text-right'>
                                    <button type='button'  class=' btn btn-warning' onclick='filter_search(this)'><span class="fa fa-search"></span> Filter</button>
                                    <button type='button'  class=' btn btn-default' onclick='form_clear("frm_search")'> Clear</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                  <div class="row">
                    <div class="col-sm-12">
                        <div class='col-ms-12 text-right'>
                          <a href='frm_departments.php' class='btn btn-warning'> Create New <span class='fa fa-plus'></span> </a>
                        </div>
                        <br/>
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
                              <th class='text-center'>Name</th>
                              <th class='text-center'>Description</th>
                              <th class='text-center'>Parent Department</th>
                              <!-- <th class='text-center'>Approver</th> -->
                              <th class='text-center'>Pay Group</th>
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
                  "url":"ajax/departments.php",
                  "data":function(d){
                      d.company_id=$("select[name='company_id']").val();
                      // d.approver_id=$("select[name='approver_id']").val();
                      d.department_id=$("select[name='department_id']").val();
                      d.parent_department_id=$("select[name='parent_department_id']").val();
                    }
                  },
          "oLanguage": { "sEmptyTable": "No Departments found." }
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