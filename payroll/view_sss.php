<?php
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("SSS Table",1);
?>

<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section >
		<div class="content-header">
			<h1 class="page-header text-center text-red">SSS Table</h1>
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
				<form method="post" action="../payroll/save_sss.php" class="form-horizontal">


					<div class='form-group'>
						<label class='col-md-3 text-right' >Code :</label>
						<div class='col-md-3'>
							<input type='text' name='s_code' class='form-control' id='s_code' required>
						</div>
						<label class='col-md-2 text-right' >Employee Share :</label>
						<div class='col-md-3'>
							<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57'  name='ee_share' class='form-control' id='ee_share' required>
						</div>

					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Range Compensation From :</label>
						<div class='col-md-3'>
							<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name='r_comp_from' class='form-control' id='r_comp_from' required>
						</div>
						<label class='col-md-2 text-right' >Employee Compensation :</label>
						<div class='col-md-3'>
							<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name='e_comp' class='form-control' id='e_comp' required>
						</div>

					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Range Compensation To :</label>
						<div class='col-md-3'>
							<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name='r_comp_to' class='form-control' id='r_comp_to' required>
						</div>
						<label class='col-md-2 text-right' >Employer Share :</label>
						<div class='col-md-3'>
							<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name='er_share' class='form-control' id='er_share' required>
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
			<div class='panel-body ' >

				<table class='table table-bordered table-condensed table-hover ' id='dataTables'>
					<thead>
						<tr>
							<th class='text-center'>Code</th>
							<th class='text-center'>Range Comp From</th>
							<th class='text-center'>Range Comp To</th>
							<th class='text-center' >Employee Share</th>
							<th class='text-center'>Employer Share</th>
							<th class='text-center'>Employee Compensation</th>
							<th class='text-center'>Actions</th>

						</tr>
					</thead>
					<tbody align="center" >

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
						<h4 class="modal-title" id="myModalLabel">Update SSS Code Details:</h4>
					</div>

					<div class="modal-body"> 

						<form method='POST' action="save_sss.php">
							<div class='form-group'>
								<div class ="row">
									<div class = "col-md-3">
										<label class='control-label'> Range Comp From : </label>
									</div>
									<div class = "col-md-9">
										<input type="hidden" name="s_code1" class="form-control  text-right">
										<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name="r_comp_from1" class="form-control  text-right" required>
									</div>
								</div>
							</div>

							<div class='form-group'>
								<div class ="row">
									<div class = "col-md-3">
										<label class='control-label'> Range Comp To : </label>
									</div>
									<div class = "col-md-9">
										<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name="r_comp_to1" class="form-control  text-right"  required>
									</div>
								</div>
							</div>

							<div class='form-group'>
								<div class ="row">
									<div class = "col-md-3">
										<label class='control-label'> Employee Share : </label>
									</div>
									<div class = "col-md-9">
										<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name="ee_share1" class="form-control text-right"  required>
									</div>
								</div>
							</div>

							<div class='form-group'>
								<div class ="row">
									<div class = "col-md-3">
										<label class='control-label'> Employer Share : </label>
									</div>
									<div class = "col-md-9">
										<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57'name="er_share1" class="form-control text-right"  required>
									</div>
								</div>
							</div>

							<div class='form-group'>
								<div class ="row">
									<div class = "col-md-3">
										<label class='control-label'> Employee Compensation : </label>
									</div>
									<div class = "col-md-9">
										<input type='number' step="0.01" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name="e_comp1" class="form-control text-right"  required>
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
						"url":"ajax/sss_ajax.php",
						"data":function(d)
						{
                    // d.leave_type_id=$("select[name='leave_id']").val();
                    // // d.half_day_mode=$("select[name='half_day_mode']").val();
                    // d.start_date=$("input[name='date_start']").val();
                    // d.end_date=$("input[name='date_end']").val();
                    // d.status=$("select[name='status']").val();
                }
            },
            "columnDefs": [{ "orderable": false, "targets": 6}],


            // "columnDefs": [{
            // 	"targets": 0,
            // "data": "sss_code", //this name should exist in your JSON response
            // "render": function ( data, type, full, meta ) {
            // 	return '<span class="label label-danger">'+data+'</span>';
            // }
        // }]

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

				$("input[name='s_code1']").val($(btn).data("s_code"));
				$("input[name='r_comp_from1']").val($(btn).data("r_comp_from"));
				$("input[name='r_comp_to1']").val($(btn).data("r_comp_to"));
				$("input[name='ee_share1']").val($(btn).data("ee_share"));

				$("input[name='er_share1']").val($(btn).data("er_share"))
				$("input[name='e_comp1']").val($(btn).data("e_comp"));
			}


		</script>
		<?php
		makeFoot(WEBAPP,1);
		?>