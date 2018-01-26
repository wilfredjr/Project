<?php
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("Shifts",1);
?>

<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Shifts</h1>
		</div>
	</section>



	<!-- Main content -->
	<section class="content">
		<div class="row">
			<?php
			Alert();
			Modal();
			?>
		</div>
		
		<div class='col-ms-12 text-right'>
			<a href='frm_shift.php' class='btn btn-danger'> Create New <span class='fa fa-plus'></span> </a>
		</div>
		<br>	
		
		<div class='panel panel-default'>
			<div class='panel-body ' >

				<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
					<thead>
						<tr>
							<th class='text-center'>Shift Name</th>
							<th class='text-center'>Time In</th>
							<th class='text-center'>Time Out</th>
							<th class='text-center'>Actions</th>

						</tr>
					</thead>
					<tbody>	
					</tbody>
				</table>


			</div>
		</div>

	</section>

</div>




<script type="text/javascript">
	$(function () {
		data_table=$('#ResultTable').DataTable({
			"processing": true,
			"serverSide": true,
			"searching": false,
			"ajax":{
				"url":"ajax/shift.php"
			},
			"oLanguage": { "sEmptyTaWble": "No Shift found." }
		});
	});

	function reset(){
		if(confirm("Are you sure you want to clear all fields?")){
			$("input").val("");
		}

	}

	function reset_modal(){

		$("input").val("");

	}

	function pass(btn){

		$("input[name='shift_id1']").val($(btn).data("shift_id"));
		$("input[name='shift_name1']").val($(btn).data("shift_name"));
		$("input[name='time_in1']").val($(btn).data("time_in"));
		$("input[name='time_out1']").val($(btn).data("time_out"));

	}


</script>
<?php
makeFoot(WEBAPP,1);
?>