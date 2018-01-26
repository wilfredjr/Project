<?php
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("Shifting Schedule",1);

if(!empty($_GET['id'])){
	$get_master=$con->myQuery("SELECT id, shift_id, date_from, date_to FROM employees_shift_master WHERE id = ?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
}

$cbo_shift=$con->myQuery("SELECT id, CONCAT(shift_name,' (',time_in,' - ',time_out,')') as shift_name FROM shifts WHERE is_deleted = '0'
	")->fetchAll(PDO::FETCH_ASSOC);

$cbo_dept=$con->myQuery("SELECT id, CONCAT(`name`, ' (',description,')') as department FROM departments WHERE is_deleted = '0'")->fetchAll(PDO::FETCH_ASSOC);

$cbo_job_title=$con->myQuery("SELECT id, description FROM job_title WHERE is_deleted = '0'")->fetchAll(PDO::FETCH_ASSOC);

$cbo_emp=$con->myQuery("SELECT employees.id, CONCAT(employees.first_name,' ',employees.middle_name,' ',employees.last_name) AS emp_name FROM employees
	INNER JOIN employment_status ON employees.employment_status_id = employment_status.id
	WHERE employment_status.`name` <> 'Resigned' and  employment_status.`name` <> 'Terminated' and employees.is_deleted ='0'")->fetchAll(PDO::FETCH_ASSOC);
	?>

	<style type="text/css">
		table.dataTable.select tbody tr,
		table.dataTable thead th:first-child {
			cursor: pointer;
		}
	</style>

	<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
	?>

	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<section>
			<div class="content-header">
				<h1 class="page-header text-center text-red">Shifting Schedule</h1>
			</div>
		</section>


		<!-- Main content -->
		<section class="content">
			<div class="row">
				<form method='POST' action="save_shifting_sched.php" id="frm-example" class='form-horizontal' onsubmit="return validate(this)">
					<?php
					Alert();
					Modal();
					?>

					<div class="col-lg-12 col-md-12">
						<div class='form-group'>
							<div class='col-md-3'>
							</div>
							<div class='col-md-3'>
								<input type='hidden' name='shifting_id' class='form-control' id='shifting_id' value="<?php echo !empty($_GET['id'])?htmlspecialchars($_GET['id']):''?>">
							</div>
						</div>
						<div class='form-group'>
							<label class='col-md-3 text-right' >Shift :</label>
							<div class='col-md-3'>
								<select class='form-control cbo' name='shift' data-placeholder='Select Shift' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($get_master['shift_id'])?htmlspecialchars($get_master['shift_id']):''?>" >
									<?php echo makeOptions($cbo_shift); ?>
								</select>
							</div>
						</div>
						<div class='form-group'>
							<label class='col-md-3 text-right' >Date From :</label>
							<div class='col-md-3'>
								<input type='text' name='dt_from' class='form-control date_picker' id='dt_from' data-placeholder='Select Date' value="<?php echo !empty($get_master['date_from'])?htmlspecialchars(date("m-d-Y", strtotime($get_master['date_from']))):''?>" required>
							</div>
							<label class='col-md-3 text-right' >Date To :</label>
							<div class='col-md-3'>
								<input type='text' name='dt_to' class='form-control date_picker' id='dt_to' data-placeholder='Select Date' value="<?php echo !empty($get_master['date_to'])?htmlspecialchars(date("m-d-Y", strtotime($get_master['date_to']))):''?>" required>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-10 col-md-offset-2 text-center">
								<a href='view_shifting_sched.php' class='btn btn-default' onclick="return confirm('<?php echo empty($data)?"Cancel creation of shifting schedule?":"Cancel modification of shifting schedule?" ?>')">Cancel</a>
								<button type='submit' class='btn btn-danger'>Save </button>
							</div>
						</div>

					</div>
					<br/>
				<!-- <div class="col-lg-12 col-md-12">
					<form method='get' class='form-horizontal' id='frm_search'>
						<div class='form-group'>

							<label class='col-md-3 text-right' >Employee Code :</label>
							<div class='col-md-3'>
								<input type='text' name='emp_code' class='form-control' id='emp_code'>
							</div>
							<label class='col-md-3 text-right' >Employee Name :</label>
							<div class='col-md-3'>
								<select class='form-control cbo' name='emp_name' data-placeholder='Select Employee' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET[''])?htmlspecialchars($_GET['']):''?>" >
									<?php echo makeOptions($cbo_emp); ?>
								</select>
							</div>
						</div>
						<div class='form-group'>
							<label class='col-md-3 text-right' >Department :</label>
							<div class='col-md-3'>
								<select class='form-control cbo' name='dept' data-placeholder='Select Department' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET[''])?htmlspecialchars($_GET['']):''?>" >
									<?php echo makeOptions($cbo_dept); ?>
								</select>
							</div>
							<label class='col-md-3 text-right' >Job Title :</label>
							<div class='col-md-3'>
								<select class='form-control cbo' name='job_title' data-placeholder='Select Job Title' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET[''])?htmlspecialchars($_GET['']):''?>" >
									<?php echo makeOptions($cbo_job_title); ?>
								</select>
							</div>

						</div>

						<div class='form-group'>
							<div class='col-md-7 text-right'>
								<button type='button'  class=' btn btn-danger' onclick='filter_search()'><span class="fa fa-search"></span> Filter</button>
							</div>
						</div>
					</form>
				</div> -->

				
				<!-- End of Adding -->


				<div class='panel-body ' >
					<!-- <table class='table table-bordered table-condensed table-hover display select' id='ResultTable'> -->

					<table id="example" class="table table-bordered table-condensed table-hover display select" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th><input name="select_all" value="1" type="checkbox"></th>
								<th class='text-center'>Code</th>
								<th class='text-center'>Employee Name</th>
								<th class='text-center'>Department</th>
								<th class='text-center'>Job Title</th>
								<?php if(!empty($_GET)) { ?>
								<th class='text-center'>Action</th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>	
						</tbody>
					</table>
				</div>

			</form>
		</div>

		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Update Employee Shifting Schedule:</h4>
					</div>

					<div class="modal-body"> 

						<form method='POST' action="save_shifting_sched.php" onsubmit="return validate1(this)">
							<?php 
							$get_emp=$con->myQuery("SELECT e.code as emp_code, 
								CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) AS emp_name, 
								CONCAT(d.name, ' (',d.description,')') as department, 
								jt.description AS job_title
								FROM
								employees AS e
								INNER JOIN job_title AS jt ON e.job_title_id = jt.id
								INNER JOIN employment_status AS es ON e.employment_status_id = es.id
								INNER JOIN departments AS d ON e.department_id = d.id
								INNER JOIN employees_shift_details esd ON esd.employee_id = e.id
								WHERE
								es.`name` <> 'Resigned' AND
								es.`name` <> 'Terminated' AND
								e.is_deleted = '0' AND
								esd.employee_shift_master_id = ? AND
								e.id = ?
								", array($_GET['id'],$_GET['eid']))->fetch(PDO::FETCH_ASSOC);
								?>


								<input type='hidden' name='ushifting_id' class='form-control' id='ushifting_id' value="<?php echo !empty($_GET['id'])?htmlspecialchars($_GET['id']):''?>" required>
								
								<input type='hidden' name='uemp_id' class='form-control' id='uemp_id' value="<?php echo !empty($_GET['eid'])?htmlspecialchars($_GET['eid']):''?>" required>

								<input type='hidden' name='for_u' class='form-control' id='for_u' value="<?php echo !empty($_GET['u'])?htmlspecialchars($_GET['u']):''?>" required>

								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Employee Code : </label>
										</div>
										<div class='col-md-9'>
											<input type='text' name='uemp_code' class='form-control' id='uemp_code' data-placeholder='Employee Name' value="<?php echo !empty($get_emp['emp_code'])?htmlspecialchars($get_emp['emp_code']):''?>" required readonly>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Employee Name : </label>
										</div>
										<div class='col-md-9'>
											<input type='text' name='uemp_name' class='form-control' id='uemp_name' data-placeholder='Employee Name' value="<?php echo !empty($get_emp['emp_name'])?htmlspecialchars($get_emp['emp_name']):''?>" required readonly>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Department : </label>
										</div>
										<div class='col-md-9'>
											<input type='text' name='udepartment' class='form-control' id='udepartment' data-placeholder='Department' value="<?php echo !empty($get_emp['department'])?htmlspecialchars($get_emp['department']):''?>" required readonly>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Job Title : </label>
										</div>
										<div class='col-md-9'>
											<input type='text' name='ujob_title' class='form-control' id='ujob_title' data-placeholder='Department' value="<?php echo !empty($get_emp['job_title'])?htmlspecialchars($get_emp['job_title']):''?>" required readonly>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Shift : </label>
										</div>
										<div class='col-md-9'>
											<select class='form-control cbo' name='ushift' data-placeholder='Select Shift' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($get_master['shift_id'])?htmlspecialchars($get_master['shift_id']):''?>" >
												<?php echo makeOptions($cbo_shift); ?>
											</select>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Date From : </label>
										</div>
										<div class='col-md-9'>
											<input type='date' name='udt_from' class='form-control' id='udt_from' data-placeholder='Select Date' value="<?php echo !empty($get_master['date_from'])?htmlspecialchars($get_master['date_from']):''?>" required>
										</div>
									</div>
								</div>
								<div class='form-group'>
									<div class ="row">
										<div class = "col-md-3">
											<label class='control-label'> Date To : </label>
										</div>
										<div class='col-md-9'>
											<input type='date' name='udt_to' class='form-control' id='udt_to' data-placeholder='Select Date' value="<?php echo !empty($get_master['date_to'])?htmlspecialchars($get_master['date_to']):''?>" required>
										</div>
									</div>
								</div>



								<div class ="modal-footer ">
									<button type="submit" class="btn btn-danger" >Update</button>
									<a class="btn btn-default" href='frm_shifting_sched.php?id=<?php echo $_GET['id'] ?>' class='btn btn-sm btn-danger'>Cancel</a>
								</div> 
							</form>
						</div>
					</div>
				</div>
			</div>
		</section><!-- /.content -->
	</div>

	<script type="text/javascript">
		<?php if(!empty($_GET['id']) &&  !empty($_GET['eid'])): ?>
		$(document).ready(function(){
			$("#myModal").modal("show");
		});
		$('#myModal').modal({
			backdrop: 'static',
			keyboard: false
		}); 
	<?php else: ?>
	$(document).ready(function(){
		$("#myModal").modal("hide");
	});
<?php endif; ?>    
		// $(function () {
		// 	data_table=$('#ResultTable').DataTable({
		// 		"processing": true,
		// 		"serverSide": true,
		// 		"searching": false,
		// 		"ajax":{
		// 			"url":"ajax/shifting_sched.php",
		// 			"data":function(s){
		// 				s.emp_code=$("input[name='emp_code']").val();
		// 				s.emp_name=$("select[name='emp_name']").val();
		// 				s.department=$("select[name='dept']").val();
		// 				s.job_title=$("select[name='job_title']").val();
		// 			}
		// 		},
		// 		"oLanguage": { "sEmptyTable": "No employees found." }


		// 	});
		// });


		function filter_search() 
		{
			//table.draw();
			table.ajax.reload();

		}


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

		function updateDataTableSelectAllCtrl(table){
			var $table             = table.table().node();
			var $chkbox_all        = $('tbody input[type="checkbox"]', $table);
			var $chkbox_checked    = $('tbody input[type="checkbox"]:checked', $table);
			var chkbox_select_all  = $('thead input[name="select_all"]', $table).get(0);

		   // If none of the checkboxes are checked
		   if($chkbox_checked.length === 0){
		   	chkbox_select_all.checked = false;
		   	if('indeterminate' in chkbox_select_all){
		   		chkbox_select_all.indeterminate = false;
		   	}

			   // If all of the checkboxes are checked
			} else if ($chkbox_checked.length === $chkbox_all.length){
				chkbox_select_all.checked = true;
				if('indeterminate' in chkbox_select_all){
					chkbox_select_all.indeterminate = false;
				}

			   // If some of the checkboxes are checked
			} else {
				chkbox_select_all.checked = true;
				if('indeterminate' in chkbox_select_all){
					chkbox_select_all.indeterminate = true;
				}
			}
		}

		$(document).ready(function (){
   // Array holding selected row IDs
   var rows_selected = [];

   var table = $('#example').DataTable({
   	"ajax":{
   		"url":"ajax/shifting_sched.php",
   		"data":function(s){
   			s.emp_code=$("input[name='emp_code']").val();
   			s.emp_name=$("select[name='emp_name']").val();
   			s.department=$("select[name='dept']").val();
   			s.job_title=$("select[name='job_title']").val();
   			s.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
   		}
   	},
   	'columnDefs': [{
   		'targets': 0,
   		'searchable': false,
   		'orderable': false,
   		'width': '1%',
   		'className': 'dt-body-center',
   		'render': function (data, type, full, meta){

   			if(full[6]==1){
   				return '<input type="checkbox" checked>';
   			} else {
   				return '<input type="checkbox" >';
   			}
   		}
   	}],
   	'order': [[1, 'asc']],
   	'rowCallback': function(row, data, dataIndex){
         // Get row ID
         var rowId = data[0];

         // If row ID is in the list of selected row IDs
         if($.inArray(rowId, rows_selected) !== -1){
         	$(row).find('input[type="checkbox"]').prop('checked', true);
         	$(row).addClass('selected');
         }
     }
 });

   // Handle click on checkbox
   $('#example tbody').on('click', 'input[type="checkbox"]', function(e){
   	var $row = $(this).closest('tr');

      // Get row data
      var data = table.row($row).data();

      // Get row ID
      var rowId = data[0];

      // Determine whether row ID is in the list of selected row IDs 
      var index = $.inArray(rowId, rows_selected);

      // If checkbox is checked and row ID is not in list of selected row IDs
      if(this.checked && index === -1){
      	rows_selected.push(rowId);

      // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
  } else if (!this.checked && index !== -1){
  	rows_selected.splice(index, 1);
  }

  if(this.checked){
  	$row.addClass('selected');
  } else {
  	$row.removeClass('selected');
  }

      // Update state of "Select all" control
      updateDataTableSelectAllCtrl(table);

      // Prevent click event from propagating to parent
      e.stopPropagation();
  });

   // Handle click on table cells with checkboxes
   $('#example').on('click', 'tbody td, thead th:first-child', function(e){
   	$(this).parent().find('input[type="checkbox"]').trigger('click');
   });

   // Handle click on "Select all" control
   $('thead input[name="select_all"]', table.table().container()).on('click', function(e){
   	if(this.checked){
   		$('#example tbody input[type="checkbox"]:not(:checked)').trigger('click');
   	} else {
   		$('#example tbody input[type="checkbox"]:checked').trigger('click');
   	}

      // Prevent click event from propagating to parent
      e.stopPropagation();
  });

   // Handle table draw event
   table.on('draw', function(){
      // Update state of "Select all" control
      updateDataTableSelectAllCtrl(table);
  });

   // Handle form submission event 
   $('#frm-example').on('submit', function(e){
   	var form = this;

      // Iterate over all selected checkboxes
      $.each(rows_selected, function(index, rowId){
         // Create a hidden element 
         $(form).append(
         	$('<input>')
         	.attr('type', 'hidden')
         	.attr('name', 'emp_id[]')
         	.val(rowId)
         	);
     });
  });

});

		function validate(frm) 
		{
			if(Date.parse($("#dt_from").val()) > Date.parse($("#dt_to").val()))
			{
				alert("Date From cannot be greater than Date To.");
				return false;

			}
		
			return true;
		}

		function validate1(frm) 
		{
			if(Date.parse($("#udt_from").val()) > Date.parse($("#udt_to").val()))
			{
				alert("Date From cannot be greater than Date To.");
				return false;

			} 
			return true;
		}

	</script>

	<?php
	makeFoot(WEBAPP,1);
	?>