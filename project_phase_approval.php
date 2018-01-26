<?php
	require_once("support/config.php");
	if(!isLoggedIn())
    {
	 	toLogin();
	 	die();
	}
    // if(!AllowUser(array(1,2)))
    // {
    //     redirect("index.php");
    // }
    $employee=$con->myQuery("SELECT id, CONCAT(last_name,', ',first_name,' ',middle_name,' (',code,')') AS name FROM employees WHERE is_deleted=0 AND is_terminated=0")->fetchAll(PDO::FETCH_ASSOC);
    $project_name=$con->myQuery("SELECT id,name  FROM projects WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
    $phases=$con->myQuery("SELECT id,phase_name  FROM project_phases")->fetchAll(PDO::FETCH_ASSOC);
    //$department=$con->myQuery("SELECT id, name FROM departments")->fetchAll(PDO::FETCH_ASSOC);
    makeHead("Project Phase Approval");
?>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <section class="content-header text-center">
            <h1 class="page-header">
                Project Phase Approval
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
                        <label class="col-sm-2 control-label">Employee Name </label>
                        <div class='col-sm-3'>
                            <select class='form-control cbo' name='employee_id1' id='employee_id1' data-placeholder="Select Employee Name">
                                <?php echo makeOptions($employee); ?>
                            </select>
                        </div>
                    </div>
                     <div class='form-group'>
                        <label class="col-sm-3 control-label">Phase Name </label>
                        <div class='col-sm-3'>
                            <select class='form-control cbo' name='request_id1' id='request_id1' data-placeholder="Select Project Phase">
                                <?php echo makeOptions($phases); ?>
                            </select>
                        </div>
                        <label class="col-sm-2 control-label">Status</label>
                        <div class='col-sm-3'>
                            <select class='form-control cbo-request-status-id' name='status' id='status' data-placeholder="Select Status">
                            </select>
                        </div>
                    </div>
                     <div class='form-group'>
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
                                <div class="col-sm-12 text-right" style="margin-top: 10px; margin-bottom: 10px">
                                    <form class="" method="POST" onsubmit="return approve_all()" action="approve_all.php">
                                        <input type="hidden" name="approve_project_name">
                                        <input type="hidden" name="approve_employee_id">
                                        <input type="hidden" name="approve_request_id1">
                                        <input type="hidden" name="approve_status">
                                        <input type="hidden" name="type" value='project_approval_phase'>
                                        <button class="btn btn-flat btn-success" title="Approve All Requests"><span class="ion ion-checkmark-round"></span> Approve All</button>
                                    </form>
                                </div>
                                <div class="col-sm-12">

                                    <table id='ResultTable' class='table table-bordered table-striped'>
                                        <thead>
                                            <tr>
                                                  <th class='text-center date-td'>Date Filed</th>
                                                  <th class='text-center'>Project</th>
                                                  <th class='text-center'>Phase Name</th>
                                                  <th class='text-center'>Requested By</th>
                                                  <th class='text-center'>Type</th>
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
    $request_type="project_approval_phase";
    $redirect_page="project_phase_approval.php";
    require_once("include/modal_reject_project_phase.php");
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
                "url":"ajax/project_phase_approval.php",
                "data":function(d)
                {
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
            $("input[name='approve_request_id1']").val($("select[name='request_id1']").val());
            $("input[name='approve_employee_id']").val($("select[name='employee_id1']").val());
            $("input[name='approve_status']").val($("select[name='status']").val());
            return true;
        }
        return false;
    }
</script>
<?php
    Modal();
	makeFoot();
?>
