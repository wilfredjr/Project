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
<style type="text/css">
	.loader {
		position: absolute;
		top: 35%;
		left: 50%;
		border: 16px solid #f3f3f3; /* Light grey */
		border-top: 16px solid #dd4b39; /* Blue */
		border-radius: 50%;
		width: 80px;
		height: 80px;
		animation: spin 2s linear infinite;
	}

	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}
	#overlay {
		position: fixed; /* Sit on top of the page content */
		display: none; /* Hidden by default */
		width: 100%; /* Full width (cover the whole page) */
		height: 100%; /* Full height (cover the whole page) */
		top: 0; 
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0,0,0,0.8); /* Black background with opacity */
		z-index: 2; /* Specify a stack order in case you're using a different order for other elements */
		cursor: wait; /* Add a pointer on hover */
	}
	#text{
		position: absolute;
		top: 50%;
		left: 53%;
		font-size: 15px;
		color: white;
		transform: translate(-50%,-50%);
		-ms-transform: translate(-50%,-50%);

	}

	
}
</style>

<div id="overlay">
	<div class="loader"></div>
	<div id="text">Please wait while processing payroll...</div>
	<!-- <div class="loader" id="loader">
</div> -->
</div> 
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
							<form action="save_payroll_compute.php" method="post" style="display:inline" onsubmit="return confirm('Are you sure you want to process this payroll?')">
								<input type="hidden" name='payroll_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
								<input type="hidden" name='date_start' value='<?php echo htmlspecialchars($_GET['date_start']) ?>'>   
								<input type="hidden" name='date_end' value='<?php echo htmlspecialchars($_GET['date_end']) ?>'>	
								<input type="hidden" name='pay_group' value='<?php echo htmlspecialchars($_GET['pay_group']) ?>'>                  	                         
								<button class="btn btn-danger btn-flat" onclick="on()" ><span class='fa fa-rotate-left'></span> Process Payroll </button>
							</form>
							<?php
							else:
								?>
							<form action="download_payroll_details.php"  method="post" style="display:inline"> <!-- FROM PAYROLL DETAILS TABLE -->
								<input type="hidden" name='p_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
								<button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
							</form>
							<form action="download_payroll_dtr_compute.php"  method="post" style="display:inline"> <!-- FROM DTR COMPUTE TABLE -->
								<input type="hidden" name='p_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
								<button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download DTR Computation </button>
							</form>
							<form action="bank_payroll_download.php" method="post" style="display:inline"> <!-- SUMMARY WITH CARD NUMBER -->
								<input type="hidden" name='frm_payroll_code' value='<?php echo $_GET['id']; //!empty($data)?htmlspecialchars($data['payroll_code']):'' ?>'>
								<button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Bank Summary </button>
							</form>
							<form action="bank_text_file_download.php" method="post" style="display:inline"> <!-- BANK TEXT FILE -->
								<input type="hidden" name='frm_payroll_code' value='<?php echo $_GET['id']; ?>'>
								<button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Text File </button>
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
											<th class='text-center' rowspan='2'>Code</th>
											<th class='text-center' rowspan='2'>Name</th>
											<th class='text-center' rowspan='2'>Time-In</th>
											<th class='text-center' rowspan='2'>Time-Out</th>
											<th class='text-center' rowspan='2'>Daily Rate</th>
											<th class='text-center' rowspan='2'>Hourly Rate</th>
											<th class='text-center' rowspan='2'>Night Rate</th>
											<th class='text-center' rowspan='2'>Late</th>
											<th class='text-center' rowspan='2'>Absent</th>
											<th class='text-center' rowspan='2'>Worked Hours</th>
											<th class='text-center' rowspan='2'>Special Holiday</th>
											<th class='text-center' rowspan='2'>Legal Holiday</th>
											<th class='text-center' colspan='4'>Overtime</th>
											<th class='text-center' colspan='3'>Rest Day</th>
											<th class='text-center' colspan='3'>Night Shift</th>
											<th class='text-center' colspan='2'>Premium</th>
											<th class='text-center' colspan='3'>Night Shift - Rest Day</th>
											
											<tr>
												<th class='text-center'>No of hour/s</th>
												<th class='text-center'>Regular</th>
											<!-- 		<th class='text-center' style="background-color: #FFFBD0">No of hour/s</th>
											<th class='text-center' style="background-color: #FFFBD0">Premium</th> -->
											<th class='text-center'>Special</th>
											<th class='text-center'>Legal</th>
											<th class='text-center'>Ordinary Day</th>
											<th class='text-center'>Special Holiday</th>
											<th class='text-center'>Legal Holiday</th>
											<th class='text-center'>Ordinary Day</th>
											<th class='text-center'>Special Holiday</th>
											<th class='text-center'>Legal Holiday</th>
											<th class='text-center'>Overtime</th>
											<th class='text-center'>Rest Day</th>
											<th class='text-center'>Ordinary Day</th>
											<th class='text-center'>Special Holiday</th>
											<th class='text-center'>Legal Holiday</th>
											
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
											<th class='text-center' rowspan='2'>Code</th>
											<th class='text-center' rowspan='2'>Name</th>
											<th class='text-center' rowspan='2'>TC</th>
											<th class='text-center' rowspan='2'>Basic Salary</th>
											<th class='text-center' rowspan='2'>Adjustment (add)</th>
											<th class='text-center' rowspan='2'>Overtime</th>
											<th class='text-center' rowspan='2'>Receivables</th>
											<th class='text-center' rowspan='2'>De Minimis</th>
											<th class='text-center' rowspan='2'>Late</th>
											<th class='text-center' rowspan='2'>Absent</th>
											<th class='text-center' rowspan='2'>Taxable Income</th>
											<!-- <th class='text-center'>Tax Allowance</th> -->
											<th class='text-center' rowspan='2'>Company Deduction</th>
											<th class='text-center' colspan='4'>Government Deduction</th>
											<th class='text-center' rowspan='2'>Withholding Tax</th>
											<th class='text-center' rowspan='2'>Total Deduction</th>
											<th class='text-center' rowspan='2'>Adjustment (less)</th>
											<th class='text-center' rowspan='2'>Loan</th>
											<th class='text-center' rowspan='2'>Net Pay</th>
										</tr>
										<tr>
											<th class='text-center'>SSS</th>
											<th class='text-center'>Philhealth</th>
											<th class='text-center'>Pag-ibig</th>
											<th class='text-center'>Total</th>
										</tr>
												<!-- <tr>
													
													
													
											</tr> -->
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
	function on() {
		document.getElementById("overlay").style.display = "block";
	}

</script>
<?php
makeFoot(WEBAPP,1);
?>