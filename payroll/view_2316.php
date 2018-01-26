<?php
require_once("../support/config.php");
if(!isLoggedIn())
{
	toLogin();
	die();
}

$cbo_employee=$con->myQuery("SELECT
	id,
	CONCAT(last_name,', ',first_name,' ',if(middle_name = '','',middle_name)) as emp_name
	FROM
	employees
	WHERE
	employees.is_deleted = 0 ")->fetchAll(PDO::FETCH_ASSOC);

makeHead("BIR 2316",1);
?>
<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">BIR 2316</h1>
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
						<form method="GET" action="download_2316.php" class="form-horizontal">
							<div class='form-group'>
								<label class='col-md-4 text-right' >Select Year * </label>
								<div class='col-md-4'>

									<select class='form-control cbo' name='year' id='year' data-placeholder='Select Year' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET['txt_year'])?$_GET['txt_year']:null; ?>" required>
										
										<?php 

										for($x=date('Y'); $x>=1980; $x--):
											echo "<option value='{$x}'>{$x}</option>";
										endfor;
										?>
									</select>
								</div>
								
							</div>
							<div class='form-group'>
								<label class='col-md-4 text-right' >Employee * </label>
								<div class='col-md-4'>
									<select class="form-control cbo" name="employee" id="employee" data-placeholder="Select Employee" style="width:100%" data-allow-clear="true">
										<?php echo makeOptions($cbo_employee); ?>
									</select>
								</div>
								
							</div>
							<div class='form-group'>
								<label class='col-md-7 text-right' >Previous Employer (if applicable)</label>
																
							</div>
							<div class='form-group'>
								<label class='col-md-4 text-right' >Taxable Compensation Income </label>
								<div class='col-md-4'>
									<input type="number" step="0.01" name='tci' class='form-control' id='tci' data-placeholder='Amount' onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
								</div>
								
							</div>
							<div class='form-group'>
								<label class='col-md-4 text-right' >Amount of Taxes Withheld </label>
								<div class='col-md-4'>
									<input type="number" step="0.01" name='atw' class='form-control' id='atw' data-placeholder='Amount' onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
								</div>
								
							</div>
							<div class='form-group'>
								<div class='col-md-8 text-right'>
									<button type='submit' class='btn-flat btn btn-danger' ><span class="fa fa-download"></span> Generate</button>
								</div>
							</div>
						</form>
						
					</div>
				</div>
			</div>
		</div>
		
	</section>
</div>

<?php
makeFoot(WEBAPP,1);
?>