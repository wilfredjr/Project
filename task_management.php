<?php
 require_once("support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
    $canFileForEmployees=canFileForEmployees1($_SESSION[WEBAPP]['user']['employee_id']);
    if (empty($canFileForEmployees)) {
        redirect("index.php");
    }
$employee=$_SESSION[WEBAPP]['user']['employee_id'];
$project_status=$con->myQuery("SELECT id, status_name AS name FROM project_status")->fetchAll(PDO::FETCH_ASSOC);
$project_name=$con->myQuery("SELECT p.id, p.name AS name FROM projects p 
JOIN projects_employees pe ON pe.project_id=p.id WHERE pe.employee_id=$employee AND p.is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
makeHead("Project Schedule");
// echo $_GET['action'];
// die;
?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section>
		<div class="content-header">
			<h1 class="page-header text-center">Task Management</h1>
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
		<!-- End of Adding -->
		<br>
		 <div class='row'>
                    <div class='col-sm-12'>
                    
                        <form method='get' class='form-horizontal' id='frm_search'>
                        <input type='hidden' value="<?php echo $_GET['action'];?>" name='page'>
                        <div class='form-group'>

                                        <label for="proj_name" class="col-sm-3 control-label">Project Name *</label>
                                        <div class='col-md-3'>
                                      <select class='form-control cbo' name='project_name' id='project_name' data-allow-clear='True' data-placeholder="Select Project Name">
                                      <?php echo makeOptions($project_name); ?>
                            </select>
                            </div> 
                                         <label class='col-md-2 text-right' >Status</label>
                                <div class='col-md-3'>
                                      <select class='form-control cbo' name='project_status' id='project_status' data-allow-clear='True' data-placeholder="Select Status">
                                      <?php echo makeOptions($project_status); ?>
                            </select>
                                </div>
                                    
                                    </div>
                       <div class='form-group'>
                        <label for="date_from" class="col-md-3 control-label">Date Start </label>
                          <div class="col-md-3">
                            <input type="text" class="form-control date_picker" id="start_date"  name='start_date' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['start_date']):''; ?>' required>
                          </div>
                        <label for="date_from" class="col-md-2 control-label">Date End </label>
                          <div class="col-md-3">
                            <input type="text" class="form-control date_picker" id="end_date"  name='end_date' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['end_date']):''; ?>' required>
                          </div>
                      </div>
                            <div class='form-group'>
                                <div class='col-md-2 col-md-offset-5 text-right'>
                                    <button type='button'  class=' btn btn-warning' onclick='filter_search(this)'><span class="fa fa-search"></span> Filter</button>
                                    <button type='button'  class=' btn btn-default' onclick='form_clear("frm_search")'> Clear</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
		<div class='panel panel-default'>
			<div class='panel-body ' >

				<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
					<thead>
						<tr>
							<th class='text-center'>Project Name</th>
							<th class='text-center'>Description</th>
							<th class='text-center'>Date Start</th>
							<th class='text-center'>Date End</th>
							<th class='text-center'>Status</th>
							<th class='text-center'>Team Leader BA</th>
							<th class='text-center'>Team Leader DEV</th>
							<th class='text-center'>Manager</th>
							<th class='text-center'>Action</th>
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
			"url":"ajax/task_management.php",
			"data":function(d){
                      d.start_date=$("input[name='start_date']").val();
                      d.end_date=$("input[name='end_date']").val();
                      d.project_status=$("select[name='project_status']").val();
                      d.project_name=$("select[name='project_name']").val();
                    }
		},
		"columnDefs": [{ "orderable": false, "targets": -1 },
		{"sClass": "text-center", "aTargets": [ -1 ]}],
          "order": [[ 2, "desc" ]],
		"oLanguage": { "sEmptyTaWble": "No Projects found." }
	});
});
function filter_search() 
{
    data_table.ajax.reload();
}
</script>
<?php
makeFoot(WEBAPP);
?>