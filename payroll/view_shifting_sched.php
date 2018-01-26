<?php
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("Shifting Schedule",1);
?>

<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Shifting Schedule</h1>
		</div>
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<?php
			Alert();
			Modal();
			?>

		</div>
		<!-- End of Adding -->
		<div class='col-ms-12 text-right'>
			<a href='frm_shifting_sched.php' class='btn btn-danger'> Create New <span class='fa fa-plus'></span> </a>
		</div>
		<br>
		<div class='panel panel-default'>
			<div class='panel-body ' >

				<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
					<thead>
						<tr>
							<th class='text-center'>Shift Name</th>
							<th class='text-center'>Date From</th>
							<th class='text-center'>Date To</th>
							<th class='text-center'>No of Employee</th>
							<th class='text-center'>Actions</th>

						</tr>
					</thead>
					<tbody>	
					</tbody>
				</table>


			</div>
		</div>

		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
						<h4 class="modal-title" id="myModalLabel">List of Employees:</h4>
					</div>

					<div class="modal-body"> 
						

						
						<div class='panel-body ' >

							<table class='table table-bordered table-condensed table-hover ' id='ResultTable1'>
								<thead>
									<tr>
										<th class='text-center'>Code</th>
										<th class='text-center'>Employee Name</th>
										<th class='text-center'>Department</th>
										<th class='text-center'>Job Title</th>

									</tr>
								</thead>
								<tbody>	
									
								</tbody>
							</table>
							<div class="modal-footer">

								<a class="btn btn-default" href='view_shifting_sched.php' class='btn btn-sm btn-danger'>Cancel</a>
							</div>

						</div>

					</div>
				</div>
			</div>
		</div>

	</section>

</div>

<script type="text/javascript">


	<?php if(!empty($_GET['id'])): ?>
	$(document).ready(function(){
		$("#myModal").modal("show");
	});

	$('#myModal').modal({
		backdrop: 'static',
		keyboard: false


	}); 
<?php else: ?>
	$(document).ready(function(){
		$("#myModal").modal("hide");
	});
<?php endif; ?>    



$(function () {
	data_table=$('#ResultTable').DataTable({
		"processing": true,
		"serverSide": true,
		"searching": false,
		"ajax":{
			"url":"ajax/view_shifting_sched.php"
		},
		"oLanguage": { "sEmptyTaWble": "No Shifting Schedule found." }
	});
});

$(function () {
	$('#ResultTable1').DataTable({
		"processing": true,
		"serverSide": true,
		"searching": false,
		"ajax":{
			"url":"ajax/view_shifting_sched_list.php",
			"data":function(s){
   			s.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
   		}
		},
   		
		"oLanguage": { "sEmptyTaWble": "No employee/s found." }
	});
});



</script>
<?php
makeFoot(WEBAPP,1);
?>