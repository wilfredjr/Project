<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}

    

	$data="";


	makeHead("Official Business Request");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Official Business Request
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
		              	<form class='form-horizontal' action='save_ob_request.php' method="POST" onsubmit="return validate_form()" enctype="multipart/form-data">
		              		
                      <div class='form-group'>
                        <label for="ob_date" class="col-sm-3 control-label">Date of OB *</label>
                          <div class="col-sm-9 col-md-3">
                            <input type="text" class="form-control date_picker" id="ob_date"  name='ob_date' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="time_start" class="col-sm-3 control-label">Start Time *</label>
                          <div class="col-sm-9 col-md-3">
                            <div class="input-group bootstrap-timepicker timepicker" style="width:100%">
                              <input type="text" class="form-control time_picker" id="time_start"  name='time_start' value='<?php echo !empty($data)?htmlspecialchars($data['time_from']):''; ?>' required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="time_end" class="col-sm-3 control-label">End Time *</label>
                          <div class="col-sm-9 col-md-3">
                            <div class="input-group bootstrap-timepicker timepicker" style="width:100%">
                              <input type="text" class="form-control time_picker" id="time_end"  name='time_end' value='<?php echo !empty($data)?htmlspecialchars($data['time_to']):''; ?>' required>
                            </div>
                          </div>
                      </div>
		              		<div class="form-group">
		                      <label for="destination" class="col-sm-3 control-label">Destination *</label>
		                      <div class="col-sm-9">
		                        <input type="text"  class="form-control" id="destination"  name='destination' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
		                      </div>
		                  </div>
                      <div class="form-group">
                          <label for="purpose" class="col-sm-3 control-label">Purpose * </label>
                          <div class="col-sm-9">
                            <textarea class='form-control' name='purpose' id='purpose' rows='5' required=""></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="purpose" class="col-sm-3 control-label">Evidence of Meeting *<br/> <small>Upload Limit: <?php echo ini_get('upload_max_filesize')."B";?> </small></label>
                          <div class="col-sm-9">
                            <input type='file' name='evidence' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true' accept="image/*" required="">

                          </div>
                      </div>

		                    <div class="form-group">
		                      <div class="col-sm-10 col-md-offset-2 text-center">
		                      	<a href='ob_request.php' class='btn btn-default'>Cancel</a>
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
  function validate_form() {
    let str_error="";
    
    if (validate_times($("#time_start").val(), $("#time_end").val())===false) {
      str_error+="Invalid time start and time end.";
    }
    if (str_error=="") { 
      $(frm).closest('form').find(':submit').button("loading");
      return true;
    } else {
      alert('You have the following errors: \n'+str_error);
      return false;
    }
  }
</script>

<?php
	makeFoot();
?>