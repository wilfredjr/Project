<?php
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("Tax Table",1);

$taxStatus=$con->myQuery("SELECT id,code FROM tax_status")->fetchAll(PDO::FETCH_ASSOC);

?>

<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section >
		<div class="content-header">
			<h1 class="page-header text-center text-red">Tax Table</h1>
		</div>
	</section>



	<!-- Main content -->
	<section class="content">
		<div class="row">
			<?php
			Alert();
			Modal();
			?>

			<div class="col-lg-12 col-md-12">
				<form method="post" action="../payroll/save_tax.php" class="form-horizontal">


					<div class='form-group'>
						<label class='col-md-3 text-right' >Code :</label>
						<div class='col-md-3'>
							<input type='text' name='t_code' class='form-control' id='s_code' required>
						</div>
						<label class='col-md-2 text-right' >Ceiling :</label>
						<div class='col-md-3'>
							<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57'  name='cling' class='form-control'  required>
						</div>

					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Status :</label>
						<div class='col-md-3'>
							<select  name='stat' class='form-control select2' data-placeholder="Select Status" required>
								<?php
								echo makeOptions($taxStatus);
								?>
							</select>
						</div>
						<label class='col-md-2 text-right' >Additional Tax :</label>
						<div class='col-md-3'>
							<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name='adt' class='form-control' id='er_share' required>
						</div>


					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Operand :</label>
						<div class='col-md-3'>
							<select  name='opr' class='form-control select2'  required>
								
								<option value=">" >></option>
								<option value="<" ><</option>
								<option value="=" >=</option>
							</select>
						</div>

						<label class='col-md-2 text-right' >Tax Percentage :</label>
						<div class='col-md-3'>
							<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' max="100" name='txp' class='form-control' required>
						</div>
						
					</div>

					<div class='form-group'>
						

						<label class='col-md-3 text-right' >Amount Compensation :</label>
						<div class='col-md-3'>
							<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name='amtc' class='form-control'  required>
						</div>
						
					</div>


					<div class='form-group'>
						<div class='col-md-7 text-right'>
							<button type='submit'  class='btn-flat btn btn-danger' ><span class="fa fa-plus"></span>&nbsp;Add</button>
							<a   class='btn-flat btn btn-danger' onclick="reset()" >Clear</a>
						</div>
					</div>

				</form>
			</div>
		</div>

		<br/>
		<!-- End of Adding -->

		<div class='panel panel-default'>
			<div class='panel-body'>

				<table class='table table-bordered table-condensed table-hover ' id='dataTables'>
					<thead>
						<tr>
							<th class='text-center'>Code</th>
							<th class='text-center'>Status</th>
							<th class='text-center'>Operand</th>
							<th class='text-center' >Amount Compensation</th>
							<th class='text-center'>Ceiling</th>
							<th class='text-center'>Additional Tax</th>
							<th class='text-center'>Rate</th>
							<th class='text-center'>Actions</th>

						</tr>
					</thead>
					<tbody>
					
								</tbody>
							</table>


						</div>
					</div>

				</section><!-- /.content -->
			</div>

			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="myModalLabel">Update Tax Code Details:</h4>
						</div>

						<div class="modal-body"> 

							<form method='POST' action="save_tax.php">
								<div class='form-group'>
									<div class ="row">
										<label class='col-md-3 text-right' >Ceiling :</label>
										<div class='col-md-9'>
											<input type='hidden' name='t_code1' class='form-control' id='s_code' required>
											<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name='cling1' class='form-control'  required>
										</div>
									</div>
								</div>

								<div class='form-group'>
									<div class ="row">
										<label class='col-md-3 text-right' >Status :</label>
										<div class='col-md-9'>
											<select  name='stat1' id='stat1' class='form-control select2' data-placeholder="Select Status" style="width: 100%" required>
												<?php
												echo makeOptions($taxStatus);
												?>
											</select>
										</div>
									</div>

								</div>

								<div class='form-group'>
									<div class ="row">
										<label class='col-md-3 text-right' >Additional Tax :</label>
										<div class='col-md-9'>
											<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name='adt1' class='form-control' id='er_share' required>
										</div>
									</div>
								</div>

								<div class='form-group'>
									<div class ="row">
										<label class='col-md-3 text-right' >Operand :</label>
										<div class='col-md-9'>
											<select  name='opr1' class='form-control select2' style="width: 100%"  required>

												<option value=">" >></option>
												<option value="<" ><</option>
												<option value="=" >=</option>
											</select>
										</div>
									</div>


								</div>

								<div class='form-group'>
									<div class ="row">
										<label class='col-md-3 text-right' >Tax Percentage :</label>
										<div class='col-md-9'>
											<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' max="100" name='txp1' class='form-control' required>
										</div>
									</div>
								</div>

								<div class='form-group'>

									<div class ="row">
										<label class='col-md-3 text-right' >Amount Compensation :</label>
										<div class='col-md-9'>
											<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name='amtc1' class='form-control'  required>
										</div>
									</div>
								</div>



								<div class ="modal-footer ">
									<button type="submit" class="btn btn-danger" >Update</button>
									<button type="button" class="btn btn-default"  data-dismiss="modal" ">Cancel</button>
								</div> 
							</form>
						</div>
					</div>
				</div>
			</div>


			<script type="text/javascript">

				$(document).ready(function () 
				{
					dttable=$('#dataTables').DataTable({
						"scrollX": true,
						"processing": true,
						"serverSide": true,
						"searching": false,
						"fixedColumns":   true,
						"ajax":
						{    
							"url":"ajax/tax_ajax.php",
							"data":function(d)
							{
                    // d.leave_type_id=$("select[name='leave_id']").val();
                    // // d.half_day_mode=$("select[name='half_day_mode']").val();
                    // d.start_date=$("input[name='date_start']").val();
                    // d.end_date=$("input[name='date_end']").val();
                    // d.status=$("select[name='status']").val();
                }
            },
            "columnDefs": [{ "orderable": false, "targets": 7 }]
        });
				});

				function reset(){
					if(confirm("Are you sure you want to clear all fields?")){
						$("input").val("");
					}

				}

				function reset_modal(){

					$("input").val("");

				}

				function pass(btn){

					$("input[name='t_code1']").val($(btn).data("t_code"));
					$("input[name='cling1']").val($(btn).data("cling"));
					
					$("input[name='adt1']").val($(btn).data("adt"));

					
					$("input[name='txp1']").val($(btn).data("txp")*100);
					$("input[name='amtc1']").val($(btn).data("amtc"));

					$("select[name='stat1']").val($(btn).data("stat")).change();

					$("select[name='opr1']").val($(btn).data("opr")).change();
					// $("input[name='option1'][value=" +$(btn).data("ph_cont_option")+ "]").prop('checked', true);
				}


			</script>
			<?php
			makeFoot(WEBAPP,1);
			?>