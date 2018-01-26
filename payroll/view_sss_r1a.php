<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(!empty($_GET['date_start'])){
    	$date_start=date_create($_GET['date_start']);
  	}
  	else{
    	$date_start="";
  	}
  	if(!empty($_GET['date_end'])){
    	$date_end=date_create($_GET['date_end']);
  	}
  	else{
    	$date_end="";
  	}

	makeHead("SSS R1-A",1);
?>
<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">SSS R1-A</h1>
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
						<form method="GET" action="view_sss_r1a.php" class="form-horizontal" onsubmit="return filter_search()">
							<div class='form-group'>
								<label class='col-md-3 text-right' >Date From * </label>
								<div class='col-md-3'>
									<input type='text' name='date_start' class='form-control date_picker' id='date_start' value='<?php echo !empty($_GET['date_start'])?$_GET['date_start']:""?>' required>
								</div>
								<label class='col-md-2 text-right' >Date To * </label>
								<div class='col-md-3'>
									<input type='text' name='date_end' class='form-control date_picker' id='date_end' value='<?php echo !empty($_GET['date_end'])?$_GET['date_end']:""?>' required>
								</div>
							</div>
							<div class="form-group">
								<div class='col-md-2 col-md-offset-5 text-right'>
									<button type='submit' class='btn-flat btn btn-danger'  onclick=''><span class="fa fa-search"></span> Generate</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
 				<?php
					if(!empty($_GET)):
				?>
				<div class='panel panel-default'>
					<div class='panel-body ' >
						<div class='col-md-12 text-right'>
							<form action="report_sss_r1a_download.php">
								<input type="hidden" name='d_start' value='<?php echo htmlspecialchars($_GET['date_start']) ?>'>
								<input type="hidden" name='d_end' value='<?php echo htmlspecialchars($_GET['date_end']) ?>'>
								<button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
							</form>
						</div>
						<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
							<thead>
								<tr>
									<th class='text-center'>SSS Number</th>
									<th class='text-center'>Name of Employee</th>
									<th class='text-center'>Date of Birth</th>
									<th class='text-center'>Date of Employment</th>
									<th class='text-center'>Date of Seperation</th>
									<th class='text-center'>Monthly Compensation</th>
									<th class='text-center'>Position</th>
								</tr>
							</thead>
							<tbody align="center">
							
							</tbody>
						</table>
 					</div>
				</div>
	            <?php
            	    endif;
              	?>
			</div>		
		</div>	
	</section>
</div>

<script type="text/javascript">
	var data_table="";
	$(function () 
	{
		<?php
			if(!empty($_GET)):
		?>
		data_table=$('#ResultTable').DataTable(
		{
			"processing": true,
			"serverSide": true,
			"searching": false,
			"columnDefs": [{"targets":6, "orderable":false}],
			"ajax":{
					"url":"ajax/view_sss_r1a.php",
					"data":function(d)
	                {
	                    d.date_start=$("input[name='date_start']").val();
                    	d.date_end=$("input[name='date_end']").val();
	                   
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

		<?php
            endif;
        ?>

	});


	function filter_search() 
    {

        var s_date=$("input[name='date_start']").val();
        var e_date=$("input[name='date_end']").val();

        if(s_date!=="" && e_date=="") 
        {
            alert("End date is required.");
            return false;
        }
        if(e_date!=="" && s_date=="") 
        {
            alert("Start date is required.");
            return false;
        }
        if(Date.parse(s_date) > Date.parse(e_date))
        {
            alert("Start date cannot be greater than end date.");
            return false;
        }
        else
        {
        	// data_table.ajax.reload();
        	return true;
        }
    }

</script>
<?php
	makeFoot(WEBAPP,1);
?>