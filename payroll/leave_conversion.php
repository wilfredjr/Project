<?php
require_once("../support/config.php");
if(!isLoggedIn())
{
	toLogin();
	die();
}

$cbo_payroll_group=$con->myQuery("SELECT payroll_group_id, name FROM payroll_groups WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
$cbo_paycode=$con->myQuery("SELECT id,transaction_code FROM leave_conversion WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

makeHead("Leave Credit Conversion",1);
?>
<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Leave Credit Conversion</h1>
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
						<form method="post" action="leave_conversion_generate.php" class="form-horizontal">
							<div class='form-group'>	
								<label class='col-md-4 text-right' >Select Pay Group * </label>
								<div class='col-md-4'>
									<select class='form-control cbo' name='pay_group_id' id='pay_group_id' data-placeholder='Select Payroll Group' style='width:100%' data-allow-clear='true' data-selected="<?php //echo !empty($get_master['shift_id'])?htmlspecialchars($get_master['shift_id']):''?>" required >
										<?php echo makeOptions($cbo_payroll_group); ?>
									</select>
								</div>
							</div>	
							<div class='form-group'>
								<label class='col-md-4 text-right' >Select Year * </label>
								<div class='col-md-4'>

									<select class='form-control cbo' name='leave_year' id='leave_year' data-placeholder='Select Year' style='width:100%' data-allow-clear='true' required>
										<?php 
										$currently_selected = date('Y'); 
										$earliest_year = 1900;
										$latest_year = date('Y'); 

										foreach ( range( $latest_year, $earliest_year ) as $i ) {
											print '<option value="'.$i.'"'.($i === $currently_selected ? ' selected="selected"' : '').'>'.$i.'</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class='col-md-2 col-md-offset-5 text-right'>
									<button type='submit' class='btn btn-flat btn-block btn-danger'><span class='fa fa-file'></span>  Generate </button>
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
				<h4 class="text-red">Generated Leave Conversion</h4>
			</div>
			<div class='col-sm-12 col-md-12'>  
				<div class='row'>
					<div class='col-sm-12'>
						<form method="" class="form-horizontal">
							<div class='form-group'>
								<label class='col-md-3 text-right' >Transaction Code </label>
								<div class='col-md-3'>
									<select class="form-control cbo" name="pay_code_filter" id="pay_code_filter" data-placeholder="Filter by Transaction Code" style="width:100%" data-allow-clear="true">
										<?php echo makeOptions($cbo_paycode); ?>
									</select>
								</div>
								<label class='col-md-2 text-right' >Payroll Group </label>
								<div class='col-md-3'>
									<select class="form-control cbo" name="pay_group_filter" id="pay_group_filter" data-placeholder="Filter by Payroll Group" style="width:100%" data-allow-clear="true">
										<?php echo makeOptions($cbo_payroll_group); ?>
									</select>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-3 text-right' >Date Generated </label>
								<div class='col-md-3'>
									<input type='text' name='date_generated_filter' class='form-control date_picker' id='date_generated_filter' placeholder="Filter by Date Generated">
								</div>
								<label class='col-md-2 text-right' >Status </label>
								<div class='col-md-3'>
									<select class="form-control cbo" name="status_filter" id="status_filter" data-placeholder="Filter by Status" style="width:100%" data-allow-clear="true">
										<option value=""></option>
										<option value="2">Not Yet Processed</option>
										<option value="1">Processed</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class='col-md-2 col-md-offset-5 text-right'>
									<button type='button' class='btn btn-flat btn-danger' onclick='filter_search()'><span class='fa fa-search'></span>  Filter </button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>


			<div class="col-sm-12">
				<div class='panel panel-default'>
					<div class='panel-body ' >
						<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
							<thead>
								<tr>
									<th class='text-center'>Transaction Code</th>
									<th class='text-center'>Payroll Group</th>
									<th class='text-center'>Date Generated</th>
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
	</section>
</div>

<script type="text/javascript">
	var data_table = "";

	$(function () 
	{
		data_table = $('#ResultTable').DataTable(
		{
			"processing": true,
			"serverSide": true,
			"searching": false,
			"ordering":false,
			"columnDefs": [{"targets":4, "orderable":false}],
			"ajax":
			{
				"url":"ajax/leave_conversion.php",
				"data":function(d)
				{
					d.pay_code_filter           = $("select[name='pay_code_filter']").val();
					d.pay_group_filter          = $("select[name='pay_group_filter']").val();
					d.date_generated_filter     = $("#date_generated_filter").val();
					d.status_filter             = $("#status_filter").val();
				}
			},
			"oLanguage": { "sEmptyTaWble": "No Shift found." }
		});
	});
	function filter_search() 
	{
        // alert($("#status_filter").val());
        data_table.ajax.reload();
    }
</script>	
<?php
makeFoot(WEBAPP,1);
?>