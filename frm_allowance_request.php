<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}
  if (empty($_SESSION[WEBAPP]["user"]["can_apply_for_meal_transpo"])) {
    redirect("index.php");
    die;
  }
	makeHead("Allowance Request");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Allowance Request
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
		              	<form class='form-horizontal disable-submit' action='save_allowance_request.php' method="POST" enctype="multipart/form-data">
                      <div class='form-group'>
                        <label class="col-sm-3 control-label">Date to be applied *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="date_applied"  name='date_applied' required>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="food_allowance" class="col-sm-3 control-label">Food Allowance </label>
                          <div class="col-sm-4">
                            <input type="text" class="form-control numeric " id="food_allowance"  name='food_allowance'>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="transpo_allowance" class="col-sm-3 control-label">Transportation Allowance  </label>
                          <div class="col-sm-4">
                            <input type="text" class="form-control numeric " id="transpo_allowance"  name='transpo_allowance'>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="purpose" class="col-sm-3 control-label">Receipt *<br/> <small>Upload Limit: <?php echo ini_get('upload_max_filesize')."B";?> </small></label>
                          <div class="col-sm-9">
                            <input type='file' name='evidence' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true' accept="image/*" required="">

                          </div>
                      </div>
                      <div class="form-group">
                          <label for="reason" class="col-sm-3 control-label">Reason *</label>
                          <div class="col-sm-9">
                            <textarea class='form-control' id='reason' name='reason' rows='5'  required=""></textarea>
                          </div>
                      </div>

		                    <div class="form-group">
		                      <div class="col-sm-10 col-md-offset-2 text-center">
		                      	<a href='allowance_request.php' class='btn btn-default'>Cancel</a>
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
  makeFoot();
?>