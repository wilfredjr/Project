<?php
    require_once("support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}

if (!AllowUser(array(1,4))) {
        redirect("index.php");
}

    $data="";
if (!empty($_GET['id'])) {
    $data=$con->myQuery("SELECT id,holiday_name,DATE_FORMAT(holiday_date,'".DATE_FORMAT_SQL."') as holiday_date,holiday_category FROM default_holidays WHERE is_deleted=0 AND id=? LIMIT 1", array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    if (empty($data)) {
        Modal("Invalid Record Selected");
        redirect("general_holidays.php");
        die;
    }
}

    makeHead("Holiday Form");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Holiday Form
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
                    <form class='form-horizontal' action='save_general_holiday.php' method="POST">
                      <input type='hidden' name='id' value='<?php echo !empty($data)?$data['id']:''; ?>'>

                      <div class="form-group">
                          <label for="name" class="col-sm-2 control-label">Holiday Name *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" id="holiday_name" placeholder="Holiday Name" name='holiday_name' value='<?php echo !empty($data)?htmlspecialchars($data['holiday_name']):''; ?>' required>
                          </div>
                      </div>

                      <div class="form-group">
                          <label for="name" class="col-sm-2 control-label">Holiday Date *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="holiday_date" name='holiday_date' value='<?php echo !empty($data)?htmlspecialchars($data['holiday_date']):''; ?>' required>
                          </div>
                      </div>

                      <div class='form-group'>
                          <label for="approver" class="col-sm-2 control-label"> Holiday Category *</label>
                          <div class='col-sm-9 '>
                                    <select class='form-control cbo' name='holiday_category' data-placeholder="Select Holiday Category" <?php echo!(empty($data))?"data-selected='".$data['holiday_category']."'":null ?> required>
                                        <option value=""></option>
                                        <option value="Legal Holiday">Legal Holiday</option>
                                        <option value="Special Holiday">Special Holiday</option>
                                    </select>
                          </div>
                      </div>

                        <div class="form-group">
                          <div class="col-sm-10 col-md-offset-2 text-center">
                            <a href='general_holidays.php' class='btn btn-default' onclick="return confirm('<?php echo empty($data)?"Cancel creation of new general holiday?":"Cancel modification of general holiday?" ?>')">Cancel</a>
                            <button type='submit' class='btn btn-warning'>Save </button>
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
</script>

<?php
    makeFoot();
?>