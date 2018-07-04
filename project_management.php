<?php
 require_once("support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
if (empty($_SESSION[WEBAPP]['user']['access_project_management'])) {
    redirect("index.php");
    die;
}
makeHead("Project Application");
?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
  $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
  $job_title=$con->myQuery("SELECT jb.id,jb.description AS name
		FROM job_title jb WHERE jb.is_deleted='0'")->fetchAll(PDO::FETCH_ASSOC);

  $project_name=$con->myQuery("SELECT id,name  FROM project_application WHERE (request_status_id!=4 AND request_status_id!=5)")->fetchAll(PDO::FETCH_ASSOC);
  $project_status=$con->myQuery("SELECT id,status_name  FROM project_status")->fetchAll(PDO::FETCH_ASSOC);
	
?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section>
		<div class="content-header">
			<h1 class="page-header text-center">Project Application</h1>
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
		<div class="row"><br>
              <form action="" method="" class="form-horizontal" id="frmclear">
              	  <div class='form-group'>
                      
                      <label class="col-sm-3 control-label">Project Name *</label>
                      <div class='col-sm-3'>
                          <select class='form-control cbo' name='proj_name' id='proj_name' data-placeholder="Select Project">
                          <?php
                           	echo makeOptions($project_name);

                           	?>

                          </select>
                      </div>
                      <label class="col-sm-2 control-label">Status *</label>
                      <div class='col-sm-3'>
                          <select class='form-control cbo-request-status-id' name='status' id='status' data-placeholder="Select Status">
                          </select>
                      </div>
                  </div>
                  <div class='form-group'>
                      <label class="col-md-3 control-label">Start Date *</label>
                      <div class="col-md-3">
                          <input type="text" class="form-control date_picker" id="date_start" name='date_start'>
                      </div>
                      <label class="col-md-2 control-label">End Date *</label>
                      <div class="col-md-3">
                          <input type="text" class="form-control date_picker" id="date_end" name='date_end'>
                      </div>
                  </div>
                  <div class='form-group'>
                      <div class='col-md-7 text-right'>
                          <button type='button' class='btn-flat btn btn-warning' onclick='filter_search1()'><span class="fa fa-search"></span> Filter</button>
                          <button  type='button' onclick="form_clear('frmclear')" class="btn btn-default">Clear</button>
                      </div>
                  </div>
                  
              </form>
          </div>
		<!-- End of Adding -->
		<div class='col-ms-12 text-right'>
			<a href='frm_project_management.php' class='btn btn-warning'> Create New <span class='fa fa-plus'></span> </a>
		</div>
		<br>
		<div class='panel panel-default'>
			<div class='panel-body ' >

				<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
					<thead>
						<tr>
							<th class='text-center'>Date Filed</th>
							<th class='text-center'>Project Name</th>
							<th class='text-center'>Description</th>
							<th class='text-center'>Date Start</th>
							<th class='text-center'>Status</th>
							<th class='text-center'>Team Lead BA</th>
							<th class='text-center'>Team Lead Dev</th>
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
<?php
    $request_type="project_application_approval";
    $redirect_page="project_management.php";
    require_once("include/modal_reject.php");
    require_once("include/modal_query.php");
?>
<script type="text/javascript">

$(function () {
	dtable=$('#ResultTable').DataTable({
		"processing": true,
		"serverSide": true,
		"searching": false,

		"ajax":{
			"url":"ajax/project_management.php",
			"data":function(d)
            {
                d.date_start=$("input[name='date_start']").val();
                // d.half_day_mode=$("select[name='half_day_mode']").val();
                d.date_end=$("input[name='date_end']").val();
                d.proj_name=$("select[name='proj_name']").val();
                d.status=$("select[name='status']").val();
                d.manager=$("select[name='manager']").val();
            }
		},
		"columnDefs": [{ "orderable": false, "targets": -1},
		{"sClass": "text-center", "aTargets": [ -1 ]}],
          "order": [[ 0, "desc" ]]
		
	});
});

function filter_search1()
      {
              dtable.ajax.reload();
              //consol
              
      }


</script>
<?php
makeFoot(WEBAPP);
?>