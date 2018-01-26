<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }

     if(!AllowUser(array(1,4))){
         redirect("index.php");
     }

	$data="";
	if(!empty($_GET['id'])){
  		$data=$con->myQuery("SELECT id,level,can_apply_for_meal_transpo, allow_overtime, view_employee_leave_calendar FROM pay_grade WHERE is_deleted=0 AND id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
  		if(empty($data)){
  			Modal("Invalid Record Selected");
  			redirect("pay_grade.php");
  			die;
  		}
	}

	makeHead("Pay Grade Form");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Pay Grade Form
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
		              	<form class='form-horizontal' action='save_pay_grade.php' method="POST">
		              		<input type='hidden' name='id' value='<?php echo !empty($data)?$data['id']:''; ?>'>

		              		<div class="form-group">
		                      <label for="name" class="col-sm-2 control-label">Pay Grade *</label>
		                      <div class="col-sm-9">
		                        <input type="text" class="form-control" id="name" placeholder="Pay Grade Name" name='name' value='<?php echo !empty($data)?htmlspecialchars($data['level']):''; ?>' required>
		                      </div>
		                  </div>
                      <div class="form-group">
                        <label for="can_apply_for_meal_transpo" class="col-md-9 col-md-offset-2">
                          <input type="checkbox" class="" id="can_apply_for_meal_transpo" name='can_apply_for_meal_transpo' <?php echo !empty($data) && !empty($data['can_apply_for_meal_transpo'])?'checked="true"':''; ?> title='Can Apply for meal and transportation.' value='1'>
                          Application for meal and transportation
                        </label>
                      </div>
                      <div class="form-group">
                        <label for="view_employee_leave_calendar" class="col-md-9 col-md-offset-2">
                          <input type="checkbox" class="" id="view_employee_leave_calendar" name='view_employee_leave_calendar' <?php echo !empty($data) && !empty($data['view_employee_leave_calendar'])?'checked="true"':''; ?> title='Can Apply for meal and transportation.' value='1'>
                          View Employee Calendar of Leaves
                        </label>
                      </div>
                      <div class="form-group">
                        <label for="access_project_management" class="col-md-9 col-md-offset-2">
                          <input type="checkbox" class="" id="access_project_management" name='access_project_management' <?php echo !empty($data) && !empty($data['access_project_management'])?'checked="true"':''; ?> title='Can Manage Projects.' value='1'>
                          Access Project Management
                        </label>
                      </div>
                      <div class="form-group">
                        <label for="allow_overtime" class="col-md-9 col-md-offset-2">
                          <input type="checkbox" class="" id="allow_overtime" name='allow_overtime' <?php echo !empty($data) && !empty($data['allow_overtime'])?'checked="true"':''; ?> title='Allow Overtime.' value='1'>
                          Overtime
                        </label>
                      </div>
		                    <div class="form-group">
		                      <div class="col-sm-10 col-md-offset-2 text-center">
		                      	<a href='pay_grade.php' class='btn btn-default'>Cancel</a>
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