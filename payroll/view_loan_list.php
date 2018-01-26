<?php
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("Loan Maintenance",1);
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
			<div class='col-md-12'>
				<?php
				Alert();
				Modal();
				?>
			</div>

		</div>
		<!-- End of Adding -->
		<div class='col-ms-12 text-right'>
			<a href='frm_loan_list.php' class='btn btn-danger btn-flat'> Create New <span class='fa fa-plus'></span> </a>
		</div>
		<br>
		<div class='panel panel-default'>
			<div class='panel-body ' >

				<table class='table table-bordered table-condensed table-hover' id='dataTables'>
					<thead>
						<tr>
							<th class='text-center'>Code</th>
							<th class='text-center'>Loan Name</th>						
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

	var dttable="";
	$(document).ready(function() {
		dttable=$('#dataTables').DataTable({
                //"scrollY":"400px",
                "scrollX":"100%",
                "processing": true,
                "serverSide": true,
                "select":true,
                "ajax":{
                	"url":"ajax/view_loan_list.php"
                },"language": {
                	"zeroRecords": "Loan/s Not Found."
                },

            });
	});
</script>
<?php
makeFoot(WEBAPP,1);
?>