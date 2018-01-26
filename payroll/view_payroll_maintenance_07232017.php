<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}



	$cbo_pay_group=$con->myQuery("SELECT payroll_group_id, name FROM payroll_groups WHERE is_deleted = 0")->fetchAll(PDO::FETCH_ASSOC);
	$cbo_paycode=$con->myQuery("SELECT id,payroll_code FROM payroll WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
	$cbo_paygroup=$con->myQuery("SELECT payroll_group_id,name FROM payroll_groups WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

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
						<form method="post" action="payroll_generate.php" class="form-horizontal" onsubmit="return validate(this)">
							<div class='form-group'>
								<label class='col-md-3 text-right' >Date From * </label>
								<div class='col-md-3'>
									<input type='text' name='date_start' pattern="\d{1,2}/\d{1,2}/\d{4}" class='form-control date_picker' id='date_start' required>
								</div>
								<label class='col-md-2 text-right' >Date To * </label>
								<div class='col-md-3'>
									<input type='text' name='date_end' pattern="\d{1,2}/\d{1,2}/\d{4}" class='form-control date_picker' id='date_end' required>
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
                <h4 class="text-red">Generated Payroll</h4>
            </div>
						<div class='col-sm-12 col-md-12'>	
			            	<div class='row'>
			                    <div class='col-sm-12'>
									<form method="" class="form-horizontal">
										<div class='form-group'>
											<label class='col-md-1 text-right' >Payroll Code </label>
											<div class='col-md-3'>
												<select class="form-control cbo" name="pay_code_filter" id="pay_code_filter" data-placeholder="Filter by Payroll Code" style="width:100%" data-allow-clear="true">
													<?php echo makeOptions($cbo_paycode); ?>
												</select>
											</div>
											<label class='col-md-1 text-right' >Payroll Group </label>
											<div class='col-md-3'>
												<select class="form-control cbo" name="pay_group_filter" id="pay_group_filter" data-placeholder="Filter by Payroll Group" style="width:100%" data-allow-clear="true">
													<?php echo makeOptions($cbo_paygroup); ?>
												</select>
											</div>
											<label class='col-md-1 text-right' >Date Generated </label>
											<div class='col-md-3'>
												<input type='text' name='date_generated_filter' class='form-control date_picker' id='date_generated_filter' placeholder="Filter by Date Generated">
											</div>
										</div>
										<div class='form-group'>
											<label class='col-md-1 text-right' >Date From </label>
											<div class='col-md-3'>
												<input type='text' name='date_start_filter' class='form-control date_picker' id='date_start_filter' placeholder="Filter by Date From">
											</div>
											<label class='col-md-1 text-right' >Date To </label>
											<div class='col-md-3'>
												<input type='text' name='date_end_filter' class='form-control date_picker' id='date_end_filter' placeholder="Filter by Date To">
											</div>
											<label class='col-md-1 text-right' >Status </label>
											<div class='col-md-3'>
												<select class="form-control cbo" name="status_filter" id="status_filter" data-placeholder="Filter by Status" style="width:100%" data-allow-clear="true">
													<option value=""></option>
													<option value="2">Not yet processed</option>
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
									<th class='text-center'>Payroll Code</th>
									<th class='text-center'>Payroll Group</th>
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
									<button type='submit' class='btn btn-danger'>Save </button>
										<a href='view_payroll_maintenance.php' class='btn btn-default'>Cancel</a>
										
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
			"searching": false,
			"columnDefs": [{"targets":6, "orderable":false}],
			"ajax":
			{
				"url":"ajax/view_payroll_maintenance.php",
                "data":function(d)
                {
                    d.pay_code_filter 			= $("select[name='pay_code_filter']").val();
                    d.pay_group_filter 			= $("select[name='pay_group_filter']").val();
                    d.date_generated_filter 	= $("#date_generated_filter").val();
                    d.date_start_filter 		= $("#date_start_filter").val();
                    d.date_end_filter 			= $("#date_end_filter").val();
                    d.status_filter 			= $("#status_filter").val();
                }
			},	
			"oLanguage": { "sEmptyTaWble": "No Records found." }
		});
	});
	function validate(frm) 
    {
        if(Date.parse($("#date_start").val()) > Date.parse($("#date_end").val()))
        {
            alert("Date From cannot be greater than Date To.");
            return false;
        } else if(Date.parse($("#date_start").val()) == Date.parse($("#date_end").val()))
        {
            alert("Date To should be greater than Date End.")
            return false;
        }
        
        return true;
    }
	function filter_search() 
    {
    	// alert($("#date_generated_filter").val());
        data_table.ajax.reload();
    }

   	
</script>
<?php
	makeFoot(WEBAPP,1);
?>