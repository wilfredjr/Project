<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if (!empty($_GET['id']))
	{
		$data=$con->myQuery("SELECT * FROM payroll WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
	}else
	{
		redirect("index.php");
		die();
	}
	makeHead("Generate Payroll",1);
?>
<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Generated Payroll (per DTR)</h1>
		</div>
	</section>
	<section class="content">
		<div class="row">
			<?php
				Alert();
				Modal();
			?>
			<div class='col-sm-12 col-md-12'>
                <div class="row">
						<div class='col-md-12'>
                            <div class='col-md-12 '>
                                <a href="view_payroll_maintenance.php" class="btn btn-default btn-flat"><span class="fa fa-arrow-left"></span> Return to List</a>
                                <?php
                                    if($data['is_processed']==0):
                                ?>
                                        <form action="save_payroll_compute.php" method="post" style="display:inline" onsubmit="return confirm('Are you to process this payroll?')">
                                            <input type="hidden" name='payroll_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                            <input type="hidden" name='date_start' value='<?php echo htmlspecialchars($_GET['date_start']) ?>'>   
                                            <input type="hidden" name='date_end' value='<?php echo htmlspecialchars($_GET['date_end']) ?>'>	
                                            <input type="hidden" name='pay_group' value='<?php echo htmlspecialchars($_GET['pay_group']) ?>'>                  	                         
                                            <button class="btn btn-danger btn-flat" ><span class='fa fa-rotate-left'></span> Process Payroll </button>
                                        </form>
                                <?php
                                    else:
                                ?>
                                        <form action="download_payroll_details.php" method="post" style="display:inline"> <!-- FROM PAYROLL DETAILS TABLE -->
                                            <input type="hidden" name='p_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                            <button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
                                        </form>
                                        <form action="download_payroll_dtr_compute.php"  method="post" style="display:inline"> <!-- FROM DTR COMPUTE TABLE -->
                                        	<input type="hidden" name='p_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                            <button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download DTR Computation </button>
                                        </form>
                                        <form action="bank_payroll_download.php" method="post" style="display:inline"> <!-- SUMMARY WITH CARD NUMBER -->
                                        	<input type="hidden" name='frm_payroll_code' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                            <button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Bank Report </button>
                                        </form>
                                <?php
                                    endif;
                                ?>
                            </div>  
                        </div>

                        <div class="col-sm-12">
                        </br>
                        	<?php
                                if($data['is_processed']==0):
                            ?>
                        	<div class='panel panel-default'>
								<div class='panel-body' >
									<div class='dataTable_wrapper '>
										<table id="ResultTable" class="table table-bordered table-condensed table-hover" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th class='text-center'>Code</th>
													<th class='text-center'>Name</th>
													<th class='text-center'>Time-In</th>
													<th class='text-center'>Time-Out</th>
													<th class='text-center'>Daily Rate</th>
													<th class='text-center'>Hourly Rate</th>
													<th class='text-center'>Night Rate</th>
													<th class='text-center'>Late</th>
													<th class='text-center'>OT</th>
													<th class='text-center'>OT Special</th>
													<th class='text-center'>OT Legal</th>
													<th class='text-center'>Special</th>
													<th class='text-center'>Legal</th>
													<th class='text-center'>RD</th>
													<th class='text-center'>RD Special</th>
													<th class='text-center'>RD Legal</th>
													<th class='text-center'>NS</th>
													<th class='text-center'>NS RD</th>
													<th class='text-center'>NS Special</th>
													<th class='text-center'>NS Legal</th>
													<th class='text-center'>NS RD Special</th>
													<th class='text-center'>NS RD Legal</th>
												</tr>
											</thead>
											<tbody>	
										
											</tbody>
										</table>
									</div>
								</div>
							</div> <!-- END -->
							<?php
                                else:
                            ?>
                        	<div class='panel panel-default'>
								<div class='panel-body' >
									<div class='dataTable_wrapper '>
										<table id="ResultTableProcessed" class="table table-bordered table-condensed table-hover" cellspacing="0" width="100%">
											<thead>
												<tr>
													<th class='text-center'>Code</th>
													<th class='text-center'>Name</th>
													<th class='text-center'>TC</th>
													<th class='text-center'>Basic Salary</th>
													<th class='text-center'>Adjustment (add)</th>
													<th class='text-center'>Receivables</th>
													<th class='text-center'>Overtime</th>
													<th class='text-center'>Tax Earning</th>
													<th class='text-center'>Absent</th>
													<th class='text-center'>Late</th>	
													<th class='text-center'>Adjustment (less)</th>
													<th class='text-center'>Company Deduction</th>
													<th class='text-center'>Government Deduction</th>
													<th class='text-center'>Withholding Tax</th>
													<th class='text-center'>Total Deduction</th>
													<th class='text-center'>Loan</th>
													<th class='text-center'>De Minimis</th>
													<th class='text-center'>13 Month</th>
													<th class='text-center'>Net Pay</th>
													
												</tr>
											</thead>
											<tbody>	
										
											</tbody>
										</table>
									</div>
								</div>
							</div> <!-- END -->
                        	<?php
                                endif;
                            ?>
						</div>

				</div>
			</div>
		</div>
	</section>
</div>


<script type="text/javascript">
	var data_table="";
	$(document).ready(function () 
	{
		data_table=$('#ResultTable').DataTable({
			"searching": false,
			"scrollX": true,
			"ordering":false,
			"processing": true,
			"serverSide": true,
			"ajax":{
				"url":"ajax/generate_payroll.php",
				"data":function(d)
				{
					d.id="<?php echo $_GET['id'] ?>";
				}
			},
			"oLanguage": { "sEmptyTable": "No employees found." }

		});
	});

	var data_table_processed="";
	$(document).ready(function () 
	{
		data_table_processed=$('#ResultTableProcessed').DataTable({
			"searching": false,
			"scrollX": true,
			"ordering":false,
			"processing": true,
			"serverSide": true,
			"ajax":{
				"url":"ajax/payroll_summary.php",
				"data":function(d)
				{
					d.id="<?php echo $_GET['id'] ?>";
				}
			},
			"oLanguage": { "sEmptyTable": "No employees found." }

		});
	});


	// function validate(frm) {

	// 	if(Date.parse($("#date_from").val()) > Date.parse($("#date_to").val())){
	// 		alert("Date from in cannot be greater than date to.");
	// 		return false;
	// 	}
	// 	else if(Date.parse($("#date_from").val()) == Date.parse($("#date_to").val())){
	// 		alert("Date to should be greater than date from.")
	// 		return false;
	// 	}

	// 	return true;
	// }
	function filter_search()
	{
		// alert('boom');
		// validate(this);
		data_table.ajax.reload();
	}
	
</script>
<?php
makeFoot(WEBAPP,1);
?>