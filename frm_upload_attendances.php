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
    $data=$con->myQuery("SELECT id,holiday_name,DATE_FORMAT(holiday_date,'".DATE_FORMAT_SQL."') as holiday_date,holiday_category,payroll_group_id as company_id FROM holidays WHERE is_deleted=0 AND id=? LIMIT 1", array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    if (empty($data)) {
        Modal("Invalid Record Selected");
        redirect("holidays.php");
        die;
    } else {
        $company=$con->myQuery("SELECT payroll_group_id,name FROM payroll_groups WHERE payroll_group_id=? LIMIT 1", array($data['company_id']))->fetch(PDO::FETCH_ASSOC);
    }
}

    makeHead("Upload Attendances");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Upload Attendances
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
                    <form class='form-horizontal' action='upload_attendances.php' method="POST" enctype="multipart/form-data">
                      <div class='form-group'>
                          <label for="approver" class="col-sm-2 control-label"> Pay Group *</label>
                          <div class='col-sm-9 '>
                                    <select class='form-control cbo-paygroup-id' name='company_id' required="" <?php echo!(empty($data))?"data-selected='".$data['company_id']."'":null ?>>
                                    <?php
                                    if (!empty($company)) :
                                    ?> 
                                        <option value='<?php echo $company['payroll_group_id']?>'><?php echo htmlspecialchars($company['name'])?></option>
                                    <?php
                                    endif;
                                    ?>
                                    </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="purpose" class="col-sm-2 control-label">Excel file *<br/> <small>Upload Limit: <?php echo ini_get('upload_max_filesize')."B";?> </small></label>
                          <div class="col-sm-9">
                            <input type='file' name='file' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true' accept=".xls,.xlsx" required="">

                          </div>  
                      </div>

                        <div class="form-group">
                          <div class="col-sm-10 col-md-offset-2 text-center">
                            <a href='holidays.php' class='btn btn-default' onclick="return confirm('<?php echo empty($data)?"Cancel upload of attendances?":"" ?>')">Cancel</a>
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