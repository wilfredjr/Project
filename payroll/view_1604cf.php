<?php
require_once("../support/config.php");
if(!isLoggedIn())
{
	toLogin();
	die();
}



makeHead("BIR 1604-CF",1);
?>
<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">BIR 1604-CF</h1>
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
						<form method="GET" action="view_1604cf.php" class="form-horizontal">
							<div class='form-group'>
								<label class='col-md-5 text-right' >Select Year * </label>
								<div class='col-md-3'>

									<select class='form-control cbo' name='txt_year' id='txt_year' data-placeholder='Select Year' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET['txt_year'])?$_GET['txt_year']:null; ?>" required>
										<?php
										for($x=date('Y'); $x>=1980; $x--):
											echo "<option value='{$x}'>{$x}</option>";
										endfor;
										?>
									</select>
								</div>
								<button type='submit' class='btn-flat btn btn-danger' ><span class="fa fa-search"></span> Generate</button>
							</div>
							
							
						</form>
						<br>
						<div class='form-group'>
							<?php if (!empty($_GET['txt_year'])) { ?> 
							<div class='col-md-12 text-right'>
								<form method="get" action="download_1604cf.php"  style="display:inline">
									<input type="hidden" name='id' value='<?php echo htmlspecialchars($_GET['txt_year']) ?>'>
									<button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
								</form>
							</div>
							<?php } ?>
						</div>
						<br><br>
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
						<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
							<thead>
								<tr>
									<th class='text-center'>Month-Year</th>
									<th class='text-center'>Date Process</th>
									<th class='text-center'>Tax Withheld</th>
									<th class='text-center'>Adjustment</th>
								</tr>
							</thead>
							<tbody align="right">

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
		data_table=$('#ResultTable').DataTable(
		{
			"processing": true,
			"serverSide": true,
			"searching": false,
			"ajax":
			{
				"url":"ajax/view_1601cf.php?txt_year=<?php echo $_GET['txt_year']; ?>"
			},
			"oLanguage": { "sEmptyTaWble": "No record/s found." }
		});

	});


</script>
<?php
makeFoot(WEBAPP,1);
?>