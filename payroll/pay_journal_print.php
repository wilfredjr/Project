<?php
	require_once '../support/config.php';
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(!empty($_GET['cd']))
	{
		$getPayDetails=$con->myQuery("SELECT 
										e.code,
										p.tax_compensation, 
										p.basic_salary, 
										p.late, 
										p.absent, 
										p.overtime, 
										p.receivable, 
										p.de_minimis, 
										p.company_deduction, 
										p.tax_earning, 
										CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as name  
									FROM payroll_details p 
									INNER JOIN employees e ON p.employee_id=e.id 
									WHERE p.employee_id = ?",array($_GET['cd']))->fetch(PDO::FETCH_ASSOC);
	}

	makeHead("Receipt Payment Document Fee",1);
?>
<div class='col-md-12 no-print' align='right'>
	<br>
	<a href='pay_journal.php' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Back</a>
	<button onclick='window.print()' class='btn btn-brand no-print'>Print &nbsp;<span class='fa fa-print'></span></button>  
</div>
<div class='page'>
	<div class="row">
		<br><br>
		<h2 align="center" >   <b>Spark Global Tech Systems Inc. </b></h2>
		<h4 align="center" > 1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City </h4>
		<br>
		<div class="col-md-12" style="padding-left: 50px" >
		<p align="left"  >Date Print : <?php echo date("d/m/Y") ?></p>
		</div>
	</div>
	<hr>
	<div class="row col-md-12">
		<br>
		<br>
		<div class="col-md-6">
			<div class="box box-default" style="background-color: #FFFFFF; padding: 15px">
				<table>
					<thead class="text-center" >
						<tr>
							<th class="text-center" style="width:150px">Employee Code </th>
							<th class="text-center" style="width:150px">Name
							<th  class="text-center" style="width:150px">TC </th>
							<th  class="text-center" style="width:150px;">Basic Pay </th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="text-center" ><br><?php echo htmlspecialchars($getPayDetails['code']) ?></td>
							<td class="text-center"><br><?php echo htmlspecialchars($getPayDetails['name'])?></td>
							<td class="text-center"><br><?php echo htmlspecialchars($getPayDetails['tax_compensation'])?> </td>
							<td class="text-center"><br><?php echo htmlspecialchars(number_format($getPayDetails['basic_salary'],2))?> </td>
						</tr>
					</tbody>
				</table>	
			</div>
		</div>
		<div class="col-md-6"  style="float: right;">
			<div class="box box-default" style="background-color: #FFFFFF; padding: 15px">
				<div class="col-md-6">
					<table>
						<tr>
							<th class="text-left" style="width:150px">Late: </th>
							<td class="text-left"><?php echo htmlspecialchars(number_format($getPayDetails['late'],2))?></td>
						</tr>
						<tr>
							<th  class="text-left" style="width:150px">Absent: </th>
							<td class="text-left"><?php echo htmlspecialchars(number_format($getPayDetails['absent'],2))?> </td>
						</tr>
						<tr>
							<th  class="text-left" style="width:150px">Overtime: </th>
							<td class="text-left"><?php echo htmlspecialchars(number_format($getPayDetails['overtime'],2))?> </td>
						</tr>						
					</table>
				</div>
				<div class="col-md-6">
					<table>
						<tr>
							<th  class="text-left" style="width:150px">Receivables: </th>
							<td class="text-left"><?php echo htmlspecialchars(number_format($getPayDetails['receivable'],2))?> </td>
						</tr>
						<tr>
							<th  class="text-left" style="width:150px">De minimis: </th>
							<td class="text-left"><?php echo htmlspecialchars(number_format($getPayDetails['de_minimis'],2))?> </td>
						</tr>
						<tr>
							<th  class="text-left" style="width:150px">Company Deduction: </th>
							<td class="text-left"><?php echo htmlspecialchars(number_format($getPayDetails['company_deduction'],2))?> </td>
						</tr>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6" style=" margin-left: 30px" >
				<br>
					<table>
						<tr>
							<th class="text-left" style="width:150px;">Tax Earning: </th>
							<td class="text-left" ><?php echo htmlspecialchars(number_format($getPayDetails['tax_earning'],2))?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>


