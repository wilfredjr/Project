<?php
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("Employee Loans",1);
?>

<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Loan Table</h1>
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
			<a href='frm_loan.php' class='btn btn-danger'> Create New <span class='fa fa-plus'></span> </a>
		</div>
		<br>
		<div class='panel panel-default'>
			<div class='panel-body ' >

				<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
					<thead>
						<tr>
							<th class='text-center'>Code</th>
							<th class='text-center'>Loan Type</th>
							<th class='text-center'>Employee Name</th>
							<th class='text-center'>Loan Amount</th>
							<!-- <th class='text-center'>Amount Paid</th> -->
							<th class='text-center'>Loan Balance</th>
							<th class='text-center'>Status</th>
							<th class='text-center'>Actions</th>

						</tr>
					</thead>
					<tbody>	
					</tbody>
				</table>


			</div>
		</div>

<!--   -->

	</section>

</div>

<script type="text/javascript">

$(function () {
	data_table=$('#ResultTable').DataTable({
		"processing": true,
		"serverSide": true,
		"searching": false,
		"ajax":{
			"url":"ajax/view_loan.php"
		},
		"oLanguage": { "sEmptyTaWble": "No Loan found." }
	});
});

</script>
<?php
makeFoot(WEBAPP,1);
?>