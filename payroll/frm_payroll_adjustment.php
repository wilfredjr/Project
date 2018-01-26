<?php
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("Payroll Adjustment",1);

if(!empty($_GET['id'])){
	$get_master=$con->myQuery("SELECT id, shift_id, date_from, date_to FROM employees_shift_master WHERE id = ?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
}
$cbo_emp=$con->myQuery("SELECT employees.id, CONCAT(employees.first_name,' ',employees.middle_name,' ',employees.last_name) AS emp_name FROM employees
	INNER JOIN employment_status ON employees.employment_status_id = employment_status.id
	WHERE employment_status.`name` <> 'Resigned' and  employment_status.`name` <> 'Terminated' and employees.is_deleted ='0'")->fetchAll(PDO::FETCH_ASSOC);
	?>

	<style type="text/css">
		table.dataTable.select tbody tr,
		table.dataTable thead th:first-child {
			cursor: pointer;
		}
	</style>

	<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
	?>

	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<section>
			<div class="content-header">
				<h1 class="page-header text-center text-red">Payroll Adjustment</h1>
			</div>
		</section>


		<!-- Main content -->
		<section class="content">
			<div class="row">
				<form method='POST' action="save_payroll_adjustment.php" class='form-horizontal'>
					<?php
					Alert();
					Modal();
					?>

					<div class="col-lg-12 col-md-12">
						<div class='form-group'>
							<div class='col-md-3'>
							</div>
						</div>
						<div class='form-group'>

							<label class='col-md-3 text-right' >Employee Name :</label>
							<div class='col-md-3'>
								<select class='form-control cbo' name='emp_id' data-placeholder='Select Employee' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET[''])?htmlspecialchars($_GET['']):''?>" required >
									<?php echo makeOptions($cbo_emp); ?>
								</select>
							</div>
							<label class='col-md-3 text-right' >Amount :</label>
							<div class='col-md-3'>
								<input type="number" step="0.01" name='amount' class='form-control' id='amount' data-placeholder='Amount' onkeypress='return event.charCode >= 48 && event.charCode <= 57' value="<?php echo !empty($get_master['amount'])?htmlspecialchars($get_master['amount']):''?>" required>
							</div>
						</div>
						<div class='form-group'>
							
							
							<label class='col-md-3 text-right' >Date Occur :</label>
							<div class='col-md-3'>
								<input type='text' name='dt_occur' class='form-control date_picker' id='dt_to' data-placeholder='Select Date' value="<?php echo !empty($get_master['dt_occur'])?htmlspecialchars(date("m-d-Y", strtotime($get_master['dt_occur']))):''?>" pattern="\d{1,2}/\d{1,2}/\d{4}" required>
							</div>
							<label class='col-md-3 text-right' >Reason :</label>
							<div class='col-md-3'>
								<input type='text' name='reason' class='form-control' id='reason' data-placeholder='Reason' value="<?php echo !empty($get_master['reason'])?htmlspecialchars($get_master['dt_occur']):''?>" required>
							</div>

						</div>
						<div class='form-group'>
							<label class='col-md-3 text-right' >Type :</label>
							<div class='col-md-3'>
								<select name="type" class="form-control" style='width:100%' <?php echo!(empty($students))?"data-selected='".$students['gender']."'":NULL ?> required>
									<option value="1">PLUS</option>
									<option value="0">LESS</option>
								</select>
							</div>

						</div>
						<div class="form-group">
							<div class="col-sm-10 col-md-offset-2 text-center">
							<button type='submit' class='btn btn-danger'>Save </button>
								<!-- <a href='view_shifting_sched.php' class='btn btn-default' onclick="return confirm('<?php //echo empty($data)?"Cancel creation of shifting schedule?":"Cancel modification of shifting schedule?" ?>')">Cancel</a> -->
								
							</div>
						</div>

					</div>
					<br/>

					<div class='panel-body ' >
						<table id="ResultTable" class="table table-bordered table-condensed table-hover" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th class='text-center'>Code</th>
									<th class='text-center'>Name</th>
									<th class='text-center'>Date Created</th>
									<th class='text-center'>Date Occur</th>
									<th class='text-center'>Amount</th>
									<th class='text-center'>Reason</th>
									<th class='text-center'>Status</th>
									<th class='text-center'>Type</th>
									<th class='text-center'>Actions</th>
								</tr>
							</thead>
							<tbody>	
							</tbody>
						</table>
					</div>

				</form>
			</div>

			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="myModalLabel">Update Employee Payroll Adjustment:</h4>
						</div>

						<div class="modal-body"> 

							<form method='POST' action="save_payroll_adjustment.php">

								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Employee Code : </label>
										</div>
										<div class='col-md-9'>
											<input type="hidden" name="uid" class="form-control  text-right">
											<input type='text' name='uemp_code' class='form-control' id='uemp_code' data-placeholder='Employee Name' required readonly>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Employee Name : </label>
										</div>
										<div class='col-md-9'>
											<input type='text' name='uemp_name' class='form-control' id='uemp_name' data-placeholder='Employee Name'  required readonly>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Date Occur : </label>
										</div>
										<div class='col-md-9'>
											<input type='text' name='udt_occur' class='form-control date_picker' id='udt_occur' pattern="\d{1,2}/\d{1,2}/\d{4}" required>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Amount : </label>
										</div>
										<div class='col-md-9'>
											<input type="number" step="0.01" name='uamount' class='form-control' id='uamount' required>
										</div>
									</div>
								</div>
								
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Reason : </label>
										</div>
										<div class='col-md-9'>
											<input type='text' name='ureason' class='form-control' id='udt_to' required>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Type : </label>
										</div>
										<div class='col-md-9'>
											<select name="utype" class="form-control" required>
												<option value="1">PLUS</option>
												<option value="0">LESS</option>
											</select>
										</div>

									</div>
								</div>


								<div class ="modal-footer ">
									<button type="submit" class="btn btn-danger" >Update</button>
									<a class="btn btn-default" href='frm_payroll_adjustment.php' class='btn btn-sm btn-danger'>Cancel</a>
								</div> 
							</form>
						</div>
					</div>
				</div>
			</div>
		</section><!-- /.content -->
	</div>

	<script type="text/javascript">
		$(function () {
			data_table=$('#ResultTable').DataTable({
				"processing": true,
				"serverSide": true,
				"searching": false,
				"ajax":{
					"url":"ajax/payroll_adjustment.php"
				},
				"oLanguage": { "sEmptyTaWble": "No Shift found." }
			});
		});

		function pass(btn){
			$("input[name='uid']").val($(btn).data("id"));
			$("input[name='uemp_code']").val($(btn).data("emp_code"));
			$("input[name='uemp_name']").val($(btn).data("emp_name"));
			$("input[name='udt_occur']").val($(btn).data("dt_occur"));
			$("input[name='uamount']").val($(btn).data("amount"));
			$("input[name='ureason']").val($(btn).data("reason"));
			$("select[name='utype']").val($(btn).data("adjustment_type")).change();

		}

	</script>

	<?php
	makeFoot(WEBAPP,1);
	?>