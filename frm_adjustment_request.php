<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}

    

	$data="";
  $timeIN="";
  $timeOUT="";
  //echo $_POST['adj_type'];
  //die();

/*  if ($_POST['adj_type']==1)
  {
    $timeIN="";
    $timeOUT="disabled";
  }
  if ($_POST['adj_type']==2)
  {
    $timeIN="disabled";
    $timeOUT="";
  }
  if ($_POST['adj_type']==3)
  {
    $timeIN="";
    $timeOUT="";
  }
*/
	makeHead("Adjustment Request");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Adjustment Request
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
		              	<form class='form-horizontal disable-submit' action='save_adjustment.php' method="POST" onsubmit='return validate(this)'>

                      <div class='form-group'>
                        <label for="adj_date" class="col-sm-3 control-label">Date of Adjustment *</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control date_picker" id="adj_date"  name='adj_date' value='' required>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="adj_in_time" class="col-sm-3 control-label">Adjusted Time In *</label>
                          <div class="col-sm-8">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="adj_in_time"  name='adj_in_time' value='' <?php echo $timeIN; ?> required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="adj_out_time" class="col-sm-3 control-label">Adjusted Time Out *</label>
                          <div class="col-sm-8">
                            <div class="input-group bootstrap-timepicker timepicker">
                              <input type="text" class="form-control time_picker" id="adj_out_time"  name='adj_out_time' value='' <?php echo $timeOUT; ?> required>
                            </div>
                          </div>
                      </div>
                      <div class='form-group'>
                          <label class='col-md-3 control-label'>Enter Reason *</label>
                        <div class='col-md-8' >
                          <textarea name='reason' required="" class='form-control ' style='resize: none' rows='4'></textarea>
                        </div>
                      </div>
                      <div class='form-group '>
                        <div class='col-md-9 col-md-offset-3'>
                          <a href='adjustment_request.php' class='btn btn-default'>Cancel</a>
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

    if(Date.parse($("#adj_in_time").val()) > Date.parse($("#adj_out_time").val())){
      alert("Time in cannot be greater than time out.");
      return false;
    }
    else if(Date.parse($("#adj_in_time").val()) == Date.parse($("#adj_out_time").val())){
      alert("Time out should be greater than time in.")
      return false;
    }

    return true;
  }
</script>

<?php
	makeFoot();
?>