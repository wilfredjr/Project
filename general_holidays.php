<?php
require_once("support/config.php");
if (!isLoggedIn()) {
    toLogin();
    die();
}

if (!AllowUser(array(1,4))) {
    redirect("index.php");
}

  makeHead("General Holidays");
?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          General Holidays
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
                              <label class='col-md-3 text-right' >Start Date </label>
                              <div class='col-md-3'>
                                <input type='text' name='date_start' class='form-control date_picker' id='date_start' value='<?php echo !empty($_GET['date_start'])?htmlspecialchars($_GET['date_start']):''?>'>
                              </div>
                              <label class='col-md-3 text-right' >End Date </label>
                              <div class='col-md-3'>
                                <input type='text' name='date_end' class='form-control date_picker' id='date_end' value='<?php echo !empty($_GET['date_end'])?htmlspecialchars($_GET['date_end']):''?>'>
                              </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 text-right' >Category</label>
                                <div class='col-md-3'>
                                    <select class='cbo form-control cbo' name='category' data-placeholder='Filter By Category' style='width:100%' data-allow-clear='true'>
                                        <option value=""></option>
                                        <option value="Legal Holiday">Legal Holiday</option>
                                        <option value="Special Holiday">Special Holiday</option>
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
                    </div>
                </div>
                <div class="row">
                  <div class="col-sm-12">
                      <div class='col-ms-12 text-right'>
                        <a href='frm_general_holiday.php' class='btn btn-warning'> Create New <span class='fa fa-plus'></span> </a>
                      </div>
                      <br/>
                      <table id='ResultTable' class='table table-bordered table-striped'>
                        <thead>
                          <tr>
                            <th class='text-center'>Name</th>
                            <th class='text-center'>Date</th>
                            <th class='text-center'>Category</th>
                            <th class='text-center' style="max-width: 50px">Action</th>
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
                  "url":"ajax/general_holidays.php",
                  "data":function(d){
                      d.start_date=$("input[name='date_start']").val();
                      d.end_date=$("input[name='date_end']").val();
                      d.category=$("select[name='category']").val();
                    }
                  },
          "oLanguage": { "sEmptyTable": "No Holidays found." }
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