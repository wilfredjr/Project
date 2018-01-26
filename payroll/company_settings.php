<?php

require_once("../support/config.php");

if(!isLoggedIn()){
	toLogin();
	die();
}

makeHead("Company Settings",1);

$getProfile=$con->myQuery("SELECT * FROM company_profile")->fetch(PDO::FETCH_ASSOC);

?>

<?php
require_once("../template/header.php");
require_once("../template/sidebar.php");
?>
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Company Settings</h1>
		</div>
	</section>
	<section class="content">
		<div class="row">
			<?php
			Alert();
		
			?>

			<div class="col-lg-12 col-md-12">
				<form method="post" action="save_c_settings.php" class="form-horizontal">


					<div class='form-group'>
						<label class='col-md-3 text-right' >Name :</label>
						<div class='col-md-5'>
							<input type='text' name='n' class='form-control' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['name']):""; ?>" required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Address :</label>
						<div class='col-md-5'>
							<input type='text'  name='ad' class='form-control' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['address']):""; ?>" required>
						</div>


					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Email :</label>
						<div class='col-md-5'>
							<input type="email" name="em" class='form-control' pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['email']):""; ?>" required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Contact No. :</label>
						<div class='col-md-5'>
							<input type="text" maxlength="11" name="cn" class='form-control' pattern="[0-9]{7,11}"  value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['contact_no']):""; ?>"required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Fax No. :</label>
						<div class='col-md-5'>
							<input type="text" name="fn" class='form-control' pattern="[0-9()_-+]" value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['fax_no']):""; ?>" required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Website :</label>
						<div class='col-md-5'>
							<input type="text" name="wb" class='form-control' pattern="www+.[A-Z0-9a-z]+.[a-z]{2,3}" value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['website']):""; ?>" required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-3 text-right' >Foundation Day :</label>
						<div class='col-md-5'>
							<input type="date" name="fd" class='form-control ' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['foundation_day']):""; ?>" required>
						</div>
						

					</div>




					<div class='form-group'>
						<div class='col-md-6 text-right'>
							<button type='submit'  class='btn-flat btn btn-danger' ><span class="fa fa-plus"></span>&nbsp;Save</button>
							<a   class='btn-flat btn btn-default btn-flat' onclick="reset()" >Cancel</a>
						</div>
					</div>

				</form>
			</div>
		</div>

		<br/>
		<!-- End of Adding -->

		

	</section><!-- /.content -->
</div>




<script type="text/javascript">

	function reset(){
		if(confirm("Are you sure you want to clear all fields?")){
			$("input").val("");
		}

	}

	function reset_modal(){

		$("input").val("");

	}

	function pass(btn){

		$("input[name='h_code1']").val($(btn).data("h_code"));
		$("input[name='r_comp_from1']").val($(btn).data("r_comp_from"));
		$("input[name='r_comp_to1']").val($(btn).data("r_comp_to"));
		$("input[name='ee_share1']").val($(btn).data("ee_share"));

		$("input[name='er_share1']").val($(btn).data("er_share"))
		$("input[name='option1'][value=" +$(btn).data("hdmf_cont_option")+ "]").prop('checked', true);
	}


</script>
