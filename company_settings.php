<?php

require_once 'support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}

if (!AllowUser(array(1,4))) {
    redirect("index.php");
}
makeHead("Company Settings");

$getProfile=$con->myQuery("SELECT * FROM company_profile")->fetch(PDO::FETCH_ASSOC);

?>
<script type="text/javascript">
    function isNumberKey(evt, element) {

      var charCode = (evt.which) ? evt.which : event.keyCode
       //alert(charCode);
      if ((charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 8)) && charCode !== 45)
        return false;
      else {
        
      }
      return true;
    } 

     $('.tin').datepicker();  
        $(".tin").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
</script>
<?php
require_once("template/header.php");
require_once("template/sidebar.php");
?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section >
		<div class="content-header">
			<h1 class="page-header text-center">Company Settings</h1>
		</div>
	</section>



	<!-- Main content -->
	<section class="content">
		<div class="row">
			<?php
			Alert();
		
			?>
		
                     
			<div class="col-lg-12 col-md-12">
				<form method="post" action="save_c_settings.php" class="form-horizontal">


					<div class='form-group'>

						<label class='col-md-4 text-right' >Name :</label>
						<div class='col-md-5'>
							<input type='text' name='n' class='form-control' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['name']):""; ?>" required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-4 text-right' >Address :</label>
						<div class='col-md-5'>
							<input type='text'  name='ad' class='form-control' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['address']):""; ?>" required>
						</div>


					</div>

					<div class='form-group'>
						<label class='col-md-4 text-right' >Email :</label>
						<div class='col-md-5'>
							<input type="email" name="em" class='form-control' pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['email']):""; ?>" required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-4 text-right' >Contact No. :</label>
						<div class='col-md-5'>
							<input type="text" maxlength="11" name="cn" class='form-control' pattern="[0-9]{7,11}"  value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['contact_no']):""; ?>"required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-4 text-right' >Fax No. :</label>
						<div class='col-md-5'>
							<input type="text" name="fn" class='form-control' pattern="[0-9()_-+]" value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['fax_no']):""; ?>" required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-4 text-right' >Website :</label>
						<div class='col-md-5'>
							<input type="text" name="wb" class='form-control' pattern="www+.[A-Z0-9a-z]+.[a-z]{2,3}" value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['website']):""; ?>" required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-4 text-right' >Foundation Day :</label>
						<div class='col-md-5'>
							<input type="date" name="fd" class='form-control ' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['foundation_day']):""; ?>" required>
						</div>
						

					</div>
					<div class='form-group'>
						<label class='col-md-4 text-right' >Zip Code :</label>
						<div class='col-md-5'>
							<input type="text" name="zip" pattern="[0-9]{4}" class='form-control zip' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['zip_code']):""; ?>" required>
						</div>
						

					</div>
					<div class='form-group'>
						<label class='col-md-4 text-right' >SSS Number :</label>
						<div class='col-md-5'>
							<input type="text" name="sss" pattern="[0-9]{2}-[0-9]{7}-[0-9]{2}"  class='form-control sss' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['sss_no']):""; ?>" required>
						</div>
						

					</div>
					<div class='form-group'>
						<label class='col-md-4 text-right' >PhilHealth Number :</label>
						<div class='col-md-5'>
							<input type="text" name="ph" pattern="[0-9]{2}-[0-9]{9}-[0-9]{1}" class='form-control philhealth' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['philhealth_no']):""; ?>" required>
						</div>
						

					</div>
					<div class='form-group'>
						<label class='col-md-4 text-right' >Tax Identification Number :</label>
						<div class='col-md-5'>
							<input type="text" name="tin" pattern="[0-9]{3}-[0-9]{3}-[0-9]{3}-[0-9]{3}" class='form-control tin' id='tin' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['tin']):""; ?>" required>
						</div>
						

					</div>
					<div class='form-group'>
						<label class='col-md-4 text-right' >Pagibig number :</label>
						<div class='col-md-5'>
							<input type="text" name="pagibig"  pattern="[0-9]{4}-[0-9]{4}-[0-9]{4}" class='form-control pagibig' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['pagibig_no']):""; ?>" required>
						</div>
						

					</div>

					<div class='form-group'>
						<label class='col-md-4 text-right' >RDO Code :</label>
						<div class='col-md-5'>
							<input type="text" name="rdo" class='form-control ' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['rdo_code']):""; ?>" required>
						</div>
						

					</div>
					<div class='form-group'>
						<label class='col-md-4 text-right' >Line of Business :</label>
						<div class='col-md-5'>
							<input type="text" name="lob" class='form-control ' value="<?php echo !empty($getProfile)?htmlspecialchars($getProfile['line_of_business']):""; ?>" required>
						</div>
						

					</div>
					<div class='form-group'>
						<div class='col-md-7 text-right'>
							<button type='submit'  class='btn-flat btn btn-warning' ><span class="fa fa-plus"></span>&nbsp;Save</button>
							<a   class='btn-flat btn btn-default' onclick="reset()" >Clear</a>
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
<?php
    makeFoot();
?>