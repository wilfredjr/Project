<?php
require_once('../support/config.php');
if(!isLoggedIn()){
	toLogin();
	die();
}

// $getSchedPay=$con->myQuery("SELECT p.emp_code,p.emp_tax_comp,CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as name, p.emp_netpay   
// 	FROM payroll_details p
// 	INNER JOIN employees e ON p.emp_code=e.code")->fetchAll(PDO::FETCH_ASSOC);

if(!empty($_GET['p_code'])){

	$exist=$con->myQuery("SELECT id FROM payroll WHERE is_deleted=0 AND id=?",array($_GET{'p_code'}))->fetch(PDO::FETCH_ASSOC);

	if(!empty($exist)){


		$getPayCode=$con->myQuery("SELECT payroll_code FROM payroll WHERE id=? AND is_deleted=0",array($_GET['p_code']))->fetch(PDO::FETCH_ASSOC);
		$getSchedPay1=$con->myQuery("SELECT sum(emp_netpay) AS sum_net 
			FROM payroll_details WHERE payroll_code=?",array($getPayCode['payroll_code']))->fetch(PDO::FETCH_ASSOC);

	}

	else{

		redirect("index.php");

	}

}else{

	redirect("index.php");
}
// var_dump($getSchedPay);
// die;

makeHead("Schedule Net Pay",1);

?>
<!-- 
<style type="text/css" media="print">
	
@media print {
   table { page-break-inside: :always }
  tr    { page-break-inside:avoid; page-break-after:auto }
  td    { page-break-inside:avoid; page-break-after:auto }
  thead { display:table-header-group }


</style> -->

<div class='col-md-12 no-print' align='right'>


	<br>
	<a href='index.php' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Back</a>
	<button onclick='window.print()'  class='btn btn-brand no-print'>Print &nbsp;<span class='fa fa-print'></span></button>  

</div>
<div class='page'>

	<div class="row">
		<br><br>
		<h2 align="center" >   <b>Spark Global Tech Systems Inc. </b></h2>

		<h4 align="center" > 1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City </h4>

		<h4 align="center" style="font-weight: bold;" > Schedule of Net Pay </h4>

		<br>

			<div class="col-md-12" style="padding-left: 50px" >
		
		<p align="left"  >Date Print : <?php echo date("d/m/Y") ?></p>

		</div>
	</div>
<!-- 
	<br>
	<hr> -->


	<div class="row col-md-12"  >



		<br/>
		<br/>
		

		<div class="col-md-12"  > 
			<div class="box box-default" style="background-color: #FFFFFF; padding: 15px" >
				<div class="row">
					<div class="col-lg-6 col-md-6"  >

						<table id='dataTables'>
							<thead >
								
								<tr >
									<th class="text-left" style="width:150px;"  >Employee Code </th>

									<th class="text-center" style="width:150px; padding-left: 20px">Tax Compensation </th>

									<th class="text-center" style="width:150px;  padding-left: 20px">Name</th>

									<th class="text-center" style="width:150px;  padding-left: 20px">Net Pay</th>
									
								</tr>

								

							</thead>


							<tbody align='center'>
								<?php

							// foreach ($getSchedPay as $row):

								?>

							<!-- 	<tr> 
									<td class="text-left" style="width:150px"><br> <?php //echo htmlspecialchars($row['emp_code']); ?> </td>
									<td class="text-center"><br><?php //echo htmlspecialchars($row['emp_tax_comp']); ?> </td>
									<td class="text-left" style="width:150px; padding-left: 20px"><br><?php //echo htmlspecialchars($row['name']); ?></td>
									<td class="text-right"><br><?php //echo htmlspecialchars($row['emp_netpay']); ?></td>
								

								</tr> -->


								<?php

							// endforeach;

								?>

								
								

							</tbody>

						</table>

						<div class="row" style="float: right;">

							<table >

								<tr>
									<th class="text-left" style="width:180px;  padding-left: 20px"> <hr><br>Total Net Pay:</th>

									<th class="text-right" style="width:150px; ""><hr>
										<br><?php echo htmlspecialchars(number_format($getSchedPay1['sum_net'],2)); ?></th>
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
		var dttable="";
		$(document).ready(function () 
		{
			dttable=$('#dataTables').DataTable(
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
					"url":"ajax/netpay_ajax.php?pay_code=<?php echo $getPayCode['payroll_code']; ?>",
					"data":function(d)
					{
	                //     // d.leave_type_id=$("select[name='leave_id']").val();
	                //     // // d.half_day_mode=$("select[name='half_day_mode']").val();
	                //     // d.start_date=$("input[name='date_start']").val();
	                //     // d.end_date=$("input[name='date_end']").val();
	                //     // d.e_code=$("input[name='e_code']").val();
	                //     // d.e_name=$("input[name='e_name']").val();
	                
	            }
	        }

	    });
		});

		function filter_search() 
		{
// document.getElementById("send").submit();
dttable.ajax.reload();
            //console.log(dttable);
        }


    </script>

    <?php
    makeFoot(WEBAPP,1);
    ?>
