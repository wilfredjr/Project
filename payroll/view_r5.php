<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}


	makeHead("SSS R5",1);
?>
<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">SSS R5 Files</h1>
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
						
							<div class='form-group'>
								<label class='col-md-5 text-right' style="padding-top:7px"> Select Month & Year * </label>
								<div class='col-md-3'>
									<input type='month' name='month_year' class='form-control' id='month_year' value='<?php echo !empty($_GET['month_year'])?$_GET['month_year']:""?>' required>

								</div>
								
							</div>
							<br>
							<div class="form-group">
							<br>
								<div class='col-md-2 col-md-offset-5 text-right'>
									<a class='btn-flat btn btn-danger' id="filter_list"><span class="fa fa-search"></span> Filter</a>
								</div>
							</div>
						
					</div>
					<br>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
 				<br>
				<div class='panel panel-default'>
					<div class='panel-body ' >
						<!-- <div class='col-md-12 text-right'>
							<form action="report_sss_r1a_download.php">
								<input type="hidden" name='d_start' value='<?php echo htmlspecialchars($_GET['date_start']) ?>'>
								<input type="hidden" name='d_end' value='<?php echo htmlspecialchars($_GET['date_end']) ?>'>
								<button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
							</form>
						</div> -->
							<a class="btn btn-danger btn-flat" href="frm_sss_r5.php" style="display:inline;float:right;margin-right: 18px"><span class="fa fa-plus">   </span> Create New</a>
						<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
						
							<thead>
								<tr>
									<th class='text-center' style="width:20px">Ref. No.</th>
									<th class='text-center'>Month/Year</th>
									<th class='text-center'>SS Contribution</th>	
									<th class='text-center'>EC Contribution</th>
									<th class='text-center'> Action </th>
								</tr>
							</thead>
							<tbody align="center">
							
							</tbody>
						</table>
 					</div>
				</div>
	          
			</div>		
		</div>	
	</section>
</div>

<script type="text/javascript">
	var data_table="";
	$(document).ready(function() {
		data_table=$('#ResultTable').DataTable(
		{
			"processing": true,
			"serverSide": true,
			"searching": false,
			"columnDefs": [{"targets":1, "orderable":false}],
			"ajax":{
					"url":"ajax/view_r5.php",
					"data":function(d)
	                {
	                    d.month_year=$("input[name='month_year']").val();

	                }
				},
			"oLanguage": { "sEmptyTaWble": "No R5 file(s) found." },
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

		$( "#filter_list" ).click(function() {
			if($("input[name='month_year']").val()==""){

				alert('Please select a month.');

			}else{
					$('#ResultTable').dataTable().fnDestroy();

					data_table=$('#ResultTable').DataTable(
		{
			"processing": true,
			"serverSide": true,
			"searching": false,
			"columnDefs": [{"targets":1, "orderable":false}],
			"ajax":{
					"url":"ajax/view_r5.php",
					"data":function(d)
	                {
	                    d.month_year=$("input[name='month_year']").val();

	                }
				},
			"oLanguage": { "sEmptyTaWble": "No R5 file(s) found." },
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

			}

		});
	});
	


	// function filter_search() 
 //    {

 //        var s_date=$("input[name='date_start']").val();
 //        var e_date=$("input[name='date_end']").val();

 //        if(s_date!=="" && e_date=="") 
 //        {
 //            alert("End date is required.");
 //            return false;
 //        }
 //        if(e_date!=="" && s_date=="") 
 //        {
 //            alert("Start date is required.");
 //            return false;
 //        }
 //        if(Date.parse(s_date) > Date.parse(e_date))
 //        {
 //            alert("Start date cannot be greater than end date.");
 //            return false;
 //        }
 //        else
 //        {
 //        	// data_table.ajax.reload();
 //        	return true;
 //        }
 //    }

</script>
<?php
	makeFoot(WEBAPP,1);
?>