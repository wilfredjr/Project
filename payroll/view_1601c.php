<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}



	makeHead("BIR 1601-C",1);
?>
<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">BIR 1601-C</h1>
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
						<form method="GET" action="save_1601c.php" class="form-horizontal">
							<div class='form-group'>
								<label class='col-md-5 text-right' >Select Start Month & Year * </label>
								<div class='col-md-3'>
									<input type='month' name='month_year' class='form-control' id='month_year' value='<?php echo !empty($_GET['month_year'])?$_GET['month_year']:""?>' required>
									
								</div>
								<button type='submit' class='btn-flat btn btn-danger' ><span class="fa fa-search"></span> Generate</button>
						    </div>
							
							
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-12">
				<div class='panel panel-default'>
					<div class='panel-body ' >
						<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
							<thead>
								<tr>
									<th class='text-center'>Month-Year</th>
									<th class='text-center'>Status</th>
									<th class='text-center'>Action</th>
								</tr>
							</thead>
							<tbody>	
							
							</tbody>
						</table>
					</div>
				</div>
			</div>

		
	</section>
</div>

<script type="text/javascript">
	var data_table="";
	$(function () 
	{
		
		data_table=$('#ResultTable').DataTable(
		{
			"processing": true,
			"serverSide": true,
			"searching": false,
			"columnDefs": [{"targets":2, "orderable":false}],
			"ajax":{
					"url":"ajax/view_1601c.php",
					"data":function(d)
	                {
	                    d.month_year=$("input[name='month_year']").val();
              	                   
	                }
				},
			"oLanguage": { "sEmptyTaWble": "No employee/s found." },
			// dom: 'Blrtip',
   //              buttons: [
   //              {
   //                extend:"pdf",
   //                orientation: "landscape",
   //                pageSize: "A1",
   //                text:"<span class='fa fa-download'></span> Download PDF File ",
   //                extension:".pdf",
   //                exportOptions: {
   //                  columns: [0,1,2,3,4,5]
   //                }
   //              }],
		});


	});


</script>
<?php
	makeFoot(WEBAPP,1);
?>