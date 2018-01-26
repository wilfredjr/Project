<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}



    $data="";
    if (!empty($_GET['id'])) {
        $data=$con->myQuery("SELECT ot_date,time_from,time_to,worked_done FROM employees_ot_pre WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    }


	makeHead("Overtime Request");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Overtime Request
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
		              	<form class='form-horizontal disable-submit' action='save_overtime_request.php' method="POST">
                      <input type='hidden' name='get_id' value='<?php echo !empty($_GET['id'])?$_GET['id']:''; ?>'>



                      <div class='form-group'>
                        <label class="col-sm-3 control-label">Date of OT *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="ot_date"  name='ot_date' value='<?php echo !empty($data)?htmlspecialchars(DisplayDate($data['ot_date'])):''; ?>'  <?php echo !empty($data)?'disabled':''; ?> required>
                          </div>
                      </div>


                      <div class='form-group'>
                        <label for="time_start" class="col-sm-3 control-label">Start Time *</label>
                          <div class="col-sm-9">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="time_start"  name='time_start' value='<?php echo !empty($data)?htmlspecialchars($data['time_from']):''; ?>'  required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="time_end" class="col-sm-3 control-label">End Time *</label>
                          <div class="col-sm-9">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="time_end"  name='time_end' value='<?php echo !empty($data)?htmlspecialchars($data['time_to']):''; ?>' required>
                            </div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="worked_done" class="col-sm-3 control-label">Work to be done * </label>
                          <div class="col-sm-9">
                            <textarea class='form-control' id='worked_done' name='worked_done' rows='5' value='<?php echo !empty($data)?$data['worked_done']:''; ?>' required=""></textarea>
                          </div>
                      </div>

		                    <div class="form-group">
		                      <div class="col-sm-10 col-md-offset-2 text-center">
		                      	<a href='overtime.php' class='btn btn-default'>Cancel</a>
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
<?php
  if(!empty($data)):
?>
<script type="text/javascript">
  $(document).ready(function(){
      $('#time_start').timepicker('setTime', "<?php echo !empty($data)?date("h:i A",strtotime($data['time_from'])):''; ?>");
      $('#time_end').timepicker('setTime', "<?php echo !empty($data)?date("h:i A",strtotime($data['time_to'])):''; ?>");
  });
</script>
<?php
  endif;
?>
<?php
  makeFoot();
?>
