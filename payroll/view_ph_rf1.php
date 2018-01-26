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

	makeHead("Philhealth RF-1",1);
?>
<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Philhealth RF-1 Form</h1>
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
						<form method="GET" action="view_ph_rf1.php" class="form-horizontal" onsubmit="return filter_search()">
							<div class='form-group'>
								<label class='col-md-4 text-right' >Select Start Month & Year * </label>
								<div class='col-md-3'>
									<input type='month' name='month_year' class='form-control' id='month_year' value='<?php echo !empty($_GET['month_year'])?$_GET['month_year']:""?>' required>
								</div>
								<div class='col-md-1'>
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
							<form action="report_ph_rf1_download.php">
								<input type="hidden" name='month_year' value='<?php echo htmlspecialchars($_GET['month_year']) ?>'>
								<button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
							</form>
						</div>
						<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
							<thead>
								<tr>
									<th class='text-center'>Philhealth Number</th>
									<th class='text-center'>Employee Name</th>
									<th class='text-center'>Date of Birth</th>
									<th class='text-center'>Sex</th>
									<th class='text-center'>MSB</th>
									<th class='text-center'>Employee Share</th>
									<th class='text-center'>Employer Share</th>
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
			"scrollX": true,
			"processing": true,
			"serverSide": true,
			"searching": false,
			"ordering": false,
			"ajax":{
					"url":"ajax/view_ph_rf1.php",
					"data":function(d)
	                {
	                    d.month_year=$("input[name='month_year']").val();
	                }
				},
			"oLanguage": { "sEmptyTaWble": "No records found." },
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