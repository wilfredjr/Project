<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	$cbo_pay_group=$con->myQuery("SELECT payroll_group_id, name FROM payroll_groups WHERE is_deleted = 0")->fetchAll(PDO::FETCH_ASSOC);

	makeHead("Payroll Maintenance",1);
?>
<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Generate Payroll</h1>
		</div>
	</section>
	<section class="content">
		<div class="row">
            <div class='col-lg-12'>
                <?php Alert(); ?>
            </div>
        </div>  
        <br/>
		<div class="row">
			<div class='col-sm-12 col-md-12'>
				<div class='row'>
                    <div class='col-sm-12'>
						<form method="post" action="payroll_generate.php" class="form-horizontal">
							<div class='form-group'>
								<label class='col-md-3 text-right' >Date From * </label>
								<div class='col-md-3'>
									<input type='text' name='date_start' class='form-control date_picker' id='date_start' required>
								</div>
								<label class='col-md-2 text-right' >Date To * </label>
								<div class='col-md-3'>
									<input type='text' name='date_end' class='form-control date_picker' id='date_end' required>
								</div>
							</div>
							<div class='form-group'>	
								<label class='col-md-3 text-right' >Payroll Group * </label>
								<div class='col-md-3'>
									<select class='form-control cbo' name='pay_group' id='pay_group' data-placeholder='Select Payroll Group' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($get_master['shift_id'])?htmlspecialchars($get_master['shift_id']):''?>" required >
										<?php echo makeOptions($cbo_pay_group); ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class='col-md-2 col-md-offset-5 text-right'>
									<button type='submit' class='btn btn-flat btn-block btn-danger'><span class='fa fa-file'></span>  Generate Payroll </button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="page-header"></div>
		<div class="row">
			<div class='col-md-12'>
                <h4 class="text-red">List of Generated Payroll</h4>
            </div>
			<div class="col-sm-12">
				<div class='panel panel-default'>
					<div class='panel-body ' >
						<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
							<thead>
								<tr>
									<th class='text-center'>Payroll Code</th>
									<th class='text-center'>Date Generated</th>
									<th class='text-center'>Date From</th>
									<th class='text-center'>Date To</th>
									<th class='text-center'>Status</th>
									<th class='text-center'>Actions</th>
								</tr>
							</thead>
							<tbody>	
							
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
						<h4 class="modal-title" id="myModalLabel">Administrator Password:</h4>
					</div>
					<form action="delete_payroll_maintenance.php" method="POST">
						<div class="modal-body"> 
							<?php
								Alert();
								Modal();
							?>
							<div class='panel-body ' >
								<div class='form-group'>
									<label class='col-md-3 text-center' >Password :</label>
									<div class='col-md-9'>
										<input type='hidden' name='paymain_id' class='form-control' id='paymain_id' value="<?php echo !empty($_GET['id'])?htmlspecialchars($_GET['id']):''?>" required>
										<input type='password' name='admin_password' class='form-control' id='admin_password' required>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<div class="form-group">
									<div class="col-sm-9 col-md-offset-2 text-center">
										<a href='view_payroll_maintenance.php' class='btn btn-default'>Cancel</a>
										<button type='submit' class='btn btn-danger'>Save </button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>
</div>
<script type="text/javascript">
	<?php if(!empty($_GET['id'])): ?>
		$(document).ready(function()
		{
			$("#myModal").modal("show");
		});
		$('#myModal').modal(
		{
			backdrop: 'static',
			keyboard: false
		}); 
	<?php else: ?>
		$(document).ready(function()
		{
			$("#myModal").modal("hide");
		});
	<?php endif; ?>    

	$(function () 
	{
		data_table=$('#ResultTable').DataTable(
		{
			"processing": true,
			"serverSide": true,
			"searching": true,
			"scrollX": true,
			"columnDefs": [{"targets":5, "orderable":false}],
			"ajax":{
			"url":"ajax/view_payroll_maintenance.php"
		},
		"oLanguage": { "sEmptyTaWble": "No Shift found." }
		});
	});
</script>
<?php
	makeFoot(WEBAPP,1);
?>