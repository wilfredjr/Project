<?php
	require_once '../support/config.php';
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(!empty($_GET['p_code']))
	{
		$exist=$con->myQuery("SELECT id FROM payroll WHERE is_deleted=0 AND is_processed=1 AND id=?",array($_GET{'p_code'}))->fetch(PDO::FETCH_ASSOC);
	
		if(!empty($exist))
		{
			$getPayCode=$con->myQuery("SELECT id FROM payroll WHERE id=? AND is_processed=1 AND is_deleted=0",array($_GET['p_code']))->fetch(PDO::FETCH_ASSOC);
			$getSchedPay1=$con->myQuery("SELECT sum(withholding_tax) AS sum_wtax FROM payroll_details WHERE payroll_id=?",array($getPayCode['id']))->fetch(PDO::FETCH_ASSOC);
		}else
		{
			redirect("index.php");
		}
	}else
	{
		redirect("index.php");
	}

	makeHead("Witholding Tax",1);
?>

<div class='col-md-12 no-print' align='right'>
	<br>
	<a href='index.php' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Back</a>
	<a href='download_with_tax.php?pay_code=<?php echo $_GET['p_code']; ?>' class='btn btn-default'><span class='glyphicon glyphicon-download'></span> Download as Excel</a>
	<button onclick='window.print()' class='btn btn-brand no-print'>Print &nbsp;<span class='fa fa-print'></span></button>  
</div>
<div class='page'>
	<div class="row">
		<br><br>
		<h2 align="center" >   <b>SECRET 6 </b></h2>
		<h4 align="center" > U712-714 West Tower, Philippine Stock Exchange Centre, Exchange Road, Ortigas Center, Pasig City </h4>
		<h4 align="center" style="font-weight: bold;" > Witholding Tax </h4>
		<br>
			<div class="col-md-12" style="padding-left: 50px" >
		<p align="left"  >Date Print : <?php echo date("d/m/Y") ?></p>
		</div>
	</div>

	<div class="row col-md-12">
		<br/>
		<br/>
		<div class="col-md-12"  > 
			<div class="box box-default" style="background-color: #FFFFFF; padding: 15px" >
				<div class="row">
					<div class="col-lg-6 col-md-6"  >
						<table align='center' id="dataTables">
							<thead >
								<tr >
									<th class="text-left" style="width:150px;"  >Employee Code </th>
									<th class="text-center" style="width:150px; padding-left: 20px">Tax Compensation </th>
									<th class="text-center" style="width:150px;  padding-left: 20px">Name</th>
									<th class="text-center" style="width:150px;  padding-left: 20px">With Holding Tax</th>
								</tr>
							</thead>
							<tbody align='center'>

							</tbody>
						</table>

						<div class="row" style="float: right;">
							<table >
								<tr>
								<th class="text-left" style="width:180px;  padding-left: 20px"> <hr><br>Total Net Pay:</th>
								<th class="text-right" style="width:150px; "><hr>
									<br><?php echo htmlspecialchars(number_format($getSchedPay1['sum_wtax'],2)); ?></th>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>				
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function()
	{
		$('#dataTables').DataTable(
		{
			"scrollX": false,
			"scrollY": false,
			"processing": true,
			"serverSide": true,
			"searching": false,
			"bLengthChange": false,
			"bPaginate": false,
			"ordering": false,
			"ajax":
			{
				"url":"ajax/wtax_ajax.php?pay_code=<?php echo $getPayCode['id']; ?>"
    		}
    		,
    // 		dom: 'Bfrtip',
				// buttons: [
				// {
				// 	extend:"excel",
				// 	text:"<span class='fa fa-download'></span> Download as Excel File ",
				// 	extension:".xls"
				// }
				// ]
    	});
	});
</script>
<?php
	makeFoot(WEBAPP,1);
?>


