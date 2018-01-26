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

	makeHead("HDMF STLRF",1);

	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Pag-ibig Short-term Loan Remittance Form</h1>
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
						<form method="GET" action="view_hdmf_stlrf.php" class="form-horizontal" onsubmit="return filter_search()">
							<div class="form-group">
								<label class='col-md-5 text-right' >Loan Type *</label>
                                <div class='col-md-3'>
                                    <select  class='form-control cbo' id='loan'  name="loan" data-placeholder="Select Loan Type"  style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET['loan'])?htmlspecialchars($_GET['loan']):''?>" required> 
                                        <option value=""></option>
                                        <option value="HDMF">HDMF Loan</option>
                                        <option value="SSS">SSS Loan</option>
                                    </select>
                                </div>
                            </div>							
							<div class='form-group'>
								<label class='col-md-5 text-right' >Select Start Month & Year * </label>
								<div class='col-md-3'>
									<input type='month' name='month_year' class='form-control' id='month_year' value='<?php echo !empty($_GET['month_year'])?$_GET['month_year']:""?>' required>
								</div>
							</div>
							<div class="form-group">
								<div class='col-md-8 text-right'>
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
							<form action="report_hdmf_stlrf_download.php">
								<input type="hidden" name='loan' value='<?php echo htmlspecialchars($_GET['loan']) ?>'>
								<input type="hidden" name='month_year' value='<?php echo htmlspecialchars($_GET['month_year']) ?>'>
								<button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
							</form>
						</div>
						<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
							<thead>
								<tr>
									<th class='text-center'>MID Number</th>
									<th class='text-center'>Employee Name</th>
									<th class='text-center'>Loan Type</th>
									<th class='text-center'>Amount</th>
									<!-- <th class='text-center'>Employer Remarks</th> -->
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
			"scrollX": false,
			"processing": true,
			"serverSide": true,
			"searching": false,
			"ordering": false,
			"ajax":{
					"url":"ajax/view_hdmf_stlrf.php",
					"data":function(d)
	                {
	                    d.month_year=$("input[name='month_year']").val();
	                    d.loan=$("input[name='loan']").val();
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