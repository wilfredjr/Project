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
	<div id="text">Please wait while generating payroll...</div>
	<!-- <div class="loader" id="loader">
</div> -->
</div> 
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
							<div class="form-group">
								<label class='col-md-2 text-right' >Cut Off Date * </label>
								<div class='col-md-4'>
									<select name="selected_date" id="selected_date" class="form-control cbo" data-placeholder='Select cut-off date' style='width:100%' data-allow-clear='true' required>
										
										<?php

										$curY = date('Y');
										$curPone = date('Y') + 1; 
										$curMone = date('Y') - 1;

										$months = array("December 26, " . $curMone . " to January 10, " . $curY,
											"January 11, " . $curY . " to January 25, " . $curY,
											"January 26, " . $curY . " to February 10, " . $curY,
											"February 11, " . $curY . " to February 25, " . $curY,
											"February 26, " . $curY . " to March 10, " . $curY,
											"March 11, " . $curY . " to March 25, " . $curY,
											"March 26, " . $curY . " to April 10, " . $curY,
											"April 11, " . $curY . " to April 25, " . $curY,
											"April 26, " . $curY . " to May 10, " . $curY,
											"May 11, " . $curY . " to May 25, " . $curY,
											"May 26, " . $curY . " to June 10, " . $curY,
											"June 11, " . $curY . " to June 25, " . $curY,
											"June 26, " . $curY . " to July 10, " . $curY,
											"July 11, " . $curY . " to July 25, " . $curY,
											"July 26, " . $curY . " to August 10, " . $curY,
											"August 11, " . $curY . " to August 25, " . $curY,
											"August 26, " . $curY . " to September 10, " . $curY,
											"September 11, " . $curY . " to September 25, " . $curY,
											"October 26, " . $curY . " to November 10, " . $curY,
											"November 11, " . $curY . " to November 25, " . $curY,
											"November 26, " . $curY . " to December 10, " . $curY,
											"December 11, " . $curY . " to December 25, " . $curY,
											"December 26, " . $curY . " to January 10, " . $curPone
											);
										foreach ($months as $month) {
											echo "<option value=\"" . $month . "\">" . $month . "</option>";
										}

										?>
									</select>
								</div>
							
							<!-- <div class='form-group'>
								<label class='col-md-3 text-right' >Date From * </label>
								<div class='col-md-3'>
									<input type='text' name='date_start' class='form-control date_picker' id='date_start' required>
								</div>
								<label class='col-md-2 text-right' >Date To * </label>
								<div class='col-md-3'>
									<input type='text' name='date_end' class='form-control date_picker' id='date_end' required>
								</div>
							</div> -->
								
								<label class='col-md-2 text-right' >Payroll Group * </label>
								<div class='col-md-3'>
									<select class='form-control cbo' name='pay_group' id='pay_group' data-placeholder='Select Payroll Group' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($get_master['shift_id'])?htmlspecialchars($get_master['shift_id']):''?>" required >
										<?php echo makeOptions($cbo_pay_group); ?>
									</select>
								</div>
							</div>
							
							<div class="form-group">
								<div class='col-md-3 col-md-offset-4 text-right'>
									<button type='submit' name='click_button'  id='click_button' class='btn btn-flat btn-danger'><span class='fa fa-file'></span>  Generate Payroll </button>
									<a class='btn-flat btn btn-default' onclick="reset()" >Clear</a>
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
									<a   class='btn-flat btn btn-default' onclick="reset1()" >Clear</a>
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
		trigger_click(2);
	} else if(Date.parse($("#date_start").val()) == Date.parse($("#date_end").val()))
	{
		alert("Date To should be greater than Date End.")
		return false;
		trigger_click(2);
	}
	trigger_click(1);
	return true;
}
function filter_search() 
{
    	// alert($("#date_generated_filter").val());
    	data_table.ajax.reload();
    }

    function trigger_click(a){
    	if(a==1){
    		document.getElementById("overlay").style.display = "block";	
    	}else{
    		document.getElementById("overlay").style.display = "none";
    	}
    }

    function reset(){
		$("select[name='selected_date']").each(function(){
			$(this).val('').trigger('change');
		});
		$("select[name='pay_group']").each(function(){
			$(this).val('').trigger('change');
		});
	}

	function reset1(){
		$("select[name='pay_code_filter']").each(function(){
			$(this).val('').trigger('change');
		});
		$("select[name='pay_group_filter']").each(function(){
			$(this).val('').trigger('change');
		});
		$("input[name='date_generated_filter']").val('');
		$("input[name='date_start_filter']").val('');
		$("input[name='date_end_filter']").val('');
		$("select[name='status_filter']").each(function(){
			$(this).val('').trigger('change');
		});
	}

    

</script>
<?php
makeFoot(WEBAPP,1);
?>