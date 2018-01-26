<?php
	require_once("../support/config.php");

	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	
	makeHead("BIR 1601-E Reference",1);
?>

<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section class="content-header">	
		<h1 class="page-header text-center text-red">BIR 1601-E - Schedule of Alphanumeric Tax Codes </h1>
	</section>
	<section class="content">
		<div class="row">
			<?php
				Alert();
				Modal();
			?>
		</div>
		<br/>
		<div class='panel panel-default'>
			<br>
			<div class='col-md-12 text-right'>
            	<a href='bir_1601_e_form.php' class='btn btn-danger'> Add New <span class='fa fa-plus'></span> </a>
            </div> 
			<div class='panel-body ' >
				<table class='table table-bordered table-condensed table-hover ' id='dataTables'>
					<thead>
						<tr>
							<th class='text-center'>Nature of Business</th>
							<th class='text-center'>Tax Rate (%)</th>
							<th class='text-center'>ATC Type</th>
							<th class='text-center'>ATC Code</th>
							<th class='text-center'>Action</th>
						</tr>
					</thead>
					<tbody align="center" >

					</tbody>
				</table>
			</div>
		</div>
	</section>
</div>

<script type="text/javascript">
	var dttable="";
	$(document).ready(function () 
	{
		dttable=$('#dataTables').DataTable(
		{
			"scrollX": true,
			"processing": true,
			"serverSide": true,
			"searching": false,
			"fixedColumns":  true,
			"ajax":
			{    
				"url":"ajax/bir_1601_e_reference.php",
				"data":function(d)
				{
            // d.leave_type_id=$("select[name='leave_id']").val();
        		}
        	},
        	"columnDefs": [{ "orderable": false, "targets": 4}]
		});
	});
</script>

<?php
	makeFoot(WEBAPP,1);
?>