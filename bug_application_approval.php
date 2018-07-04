<?php
	require_once("support/config.php");
	if(!isLoggedIn())
    {
	 	toLogin();
	 	die();
	}
    if(!AllowUser(array(4)))
    {
        redirect("index.php");
    }
    $employee=$con->myQuery("SELECT id, CONCAT(last_name,', ',first_name,' ',middle_name,' (',code,')') AS name FROM employees WHERE is_deleted=0 AND is_terminated=0")->fetchAll(PDO::FETCH_ASSOC);
    $project_name=$con->myQuery("SELECT id,name  FROM projects WHERE project_status_id=2")->fetchAll(PDO::FETCH_ASSOC);
    $phases=$con->myQuery("SELECT id,phase_name  FROM project_phases")->fetchAll(PDO::FETCH_ASSOC);
    //$department=$con->myQuery("SELECT id, name FROM departments")->fetchAll(PDO::FETCH_ASSOC);
    makeHead("Bug Application Approval");
?>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <section class="content-header text-center">
            <h1 class="page-header">
               Bug Application Approval
            </h1>
        </section>
        <section class="content">
            <div class="row">
                <form action="" method="" class="form-horizontal" id='frmclear'>
                    <div class='form-group'>
                        <label class="col-sm-3 control-label">Project Name </label>
                        <div class='col-sm-3'>
                            <select class='form-control cbo' name='project_name' id='project_name' data-placeholder="Select Project Name">
                                <?php echo makeOptions($project_name); ?>
                            </select>
                        </div>
                        <label class="col-sm-2 control-label">Account Manager Name </label>
                        <div class='col-sm-3'>
                            <select class='form-control cbo' name='employee_id1' id='employee_id1' data-placeholder="Select Employee Name">
                                <?php echo makeOptions($employee); ?>
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
                            <button type='button' class='btn-flat btn btn-warning' onclick='filter_search()'><span class="fa fa-search"></span> Filter</button>
                            <button type='button' onclick="form_clear('frmclear')" class="btn btn-default">Clear</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class='col-md-12'>
                    <?php  Alert(); ?>
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="row">
                               <!--  <div class="col-sm-12 text-right" style="margin-top: 10px; margin-bottom: 10px">
                                    <form class="" method="POST" onsubmit="return approve_all()" action="approve_all.php">
                                        <input type="hidden" name="approve_project_name">
                                        <input type="hidden" name="approve_employee_id">
                                        <input type="hidden" name="approve_request_id1">
                                        <input type="hidden" name="approve_start_date">
                                        <input type="hidden" name="approve_end_date">
                                        <input type="hidden" name="type" value='bug_application_approval'>
                                        <button class="btn btn-flat btn-success" title="Approve All Requests"><span class="ion ion-checkmark-round"></span> Approve All</button>
                                    </form>
                                </div> -->
                                <div class="col-sm-12">

                                    <table id='ResultTable' class='table table-bordered table-striped'>
                                        <thead>
                                            <tr>
                                                  <th class='text-center date-td'>Date Filed</th>
                                                  <th class='text-center'>Project</th>
                                                  <th class='text-center'>Bug Name</th>
                                                  <th class='text-center'>Description</th>
                                                  <th class='text-center'>Bug Rating</th>
                                                  <th class='text-center'>Account Manager</th>
                                                  <th class='text-center'>Project Manager</th>
                                                  <th class='text-center'>Status</th>
                                                  <th class='text-center'>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php
    $request_type="bug_application_approval";
    $redirect_page="bug_application_approval.php";
    require_once("include/modal_reject.php");
    require_once("include/modal_query.php");
?>
<script type="text/javascript">
    var dttable="";
    $(document).ready(function ()
    {
        dttable=$('#ResultTable').DataTable({
            "scrollX": true,
            "scrollY": false,
            "processing": true,
            "serverSide": true,
            "searching": false,
            "ajax":
            {
                "url":"ajax/bug_application_approval.php",
                "data":function(d)
                {
                    d.date_start=$("input[name='date_start']").val();
                    // d.half_day_mode=$("select[name='half_day_mode']").val();
                    d.date_end=$("input[name='date_end']").val();
                    d.project_name=$("select[name='project_name']").val();
                    d.employee_id1=$("select[name='employee_id1']").val();
                    d.request_id1=$("select[name='request_id1']").val();
                    d.status=$("select[name='status']").val();
                }
            },
            "columnDefs": [{ "orderable": false, "targets": -1 },
            {"sClass": "text-center", "aTargets": [ -1 ]}],
            "order": [[ 0, "desc" ]]
        });
    });
    function filter_search()
    {
            dttable.ajax.reload();
            //console.log(dttable);
    }
    function approve_all() {
        if (confirm('Are you sure you want to approve all requests?')) {
            $("input[name='approve_project_name']").val($("select[name='project_name']").val());
            $("input[name='approve_request_id1']").val($("select[name='employee_id1']").val());
            $("input[name='approve_start_date']").val($("input[name='date_start']").val());
            $("input[name='approve_end_date']").val($("input[name='date_end']").val());
            return true;
        }
        return false;
    }
</script>
<?php
    Modal();
	makeFoot();
?>
