<?php
    require_once("support/config.php");
    if(!isLoggedIn()){
        toLogin();
        die();
    }

if (empty($_GET['id'])) {
  redirect("overtime.php");
  die;
} else {
  $overtime=$con->myQuery("SELECT id,DATE_FORMAT(ot_date,'".DATE_FORMAT_SQL."') as ot_date,DAtE_FORMAT(time_from,'".TIME_FORMAT_SQL."') as time_from,DATE_FORMAT(time_to,'".TIME_FORMAT_SQL."')as time_to FROM employees_ot WHERE id=:ot_id AND request_status_id=2 AND id NOT IN (SELECT employees_ot_id FROM employees_ot_adjustments WHERE employees_ot_id=:ot_id AND (request_status_id=2 OR request_status_id=1)) LIMIT 1",array("ot_id"=>$_GET['id']))->fetch(PDO::FETCH_ASSOC);
  if (empty($overtime)) {
    Alert("Invalid record selected.");
    redirect("overtime.php?tab=3");
    die;
  }
}
    makeHead("Overtime Adjustment Request");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Overtime Adjustment Request
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-10 col-md-offset-1'>
                <?php
                    Alert();
                ?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                    <div class='col-md-12'>
                        <form class='form-horizontal disable-submit' action='save_ot_adjustment.php' method="POST" onsubmit='return validate(this)'>
                        <input type="hidden" name="ot_id" value="<?php echo $overtime['id'] ?>">
                      <div class='form-group'>
                        <label for="adj_date" class="col-sm-3 control-label">Date of Adjustment *</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control " id="adj_date"  name='adj_date' value='<?php echo $overtime['ot_date']  ?>' disabled="">
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="adj_time_in" class="col-sm-3 control-label">Adjusted Time In *</label>
                          <div class="col-sm-8">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="adj_time_in"  name='adj_time_in' value='<?php echo $overtime['time_from']  ?>' required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="adj_time_out" class="col-sm-3 control-label">Adjusted Time Out *</label>
                          <div class="col-sm-8">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="adj_time_out"  name='adj_time_out' value='<?php echo $overtime['time_to']  ?>' required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group '>
                        <div class='col-md-7 col-md-offset-5'>
                          <a href='overtime.php' class='btn btn-default'>Cancel</a>
                          <button type='submit' class='btn btn-warning'>
                            Request for Adjustment
                          </button>
                        </div>
                    </div>
                    </form>
                    </div>
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
          </div><!-- /.row -->
        </section><!-- /.content -->
  </div>

<script type="text/javascript">
  $(function () {
        $('#ResultTable').DataTable();
      });
  function validate(frm) {

    if(Date.parse($("#adj_time_in").val()) > Date.parse($("#adj_time_out").val())){
      alert("Time in cannot be greater than time out.");
      return false;
    }
    else if(Date.parse($("#adj_time_in").val()) == Date.parse($("#adj_time_out").val())){
      alert("Time out should be greater than time in.")
      return false;
    }

    return true;
  }
</script>

<?php
    makeFoot();
?>