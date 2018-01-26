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

    makeHead("Offset Requests");
?>
<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
<div class="content-wrapper">
    <section class="content-header text-center">
        <h1 class="page-header">
            Offset Requests
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <form action="" method="" class="form-horizontal" id="frmclear">
                <div class='form-group'>
                    <label class="col-md-3 control-label">Offset Start Date *</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control date_picker" id="date_start" name='date_start'>
                    </div>
                    <label class="col-md-2 control-label">Offset End Date *</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control date_picker" id="date_end" name='date_end'>
                    </div>
                </div>
                <div class='form-group'>
                    <label class="col-sm-3 control-label">Request Type *</label>
                    <div class='col-sm-3'>
                        <select class='form-control cbo' name='request_type' id='request_type' data-placeholder="Select Request Type">
                            <option value=""></option>
                            <option value="1">Bank</option>
                            <option value="2">Avail</option>
                        </select>
                    </div>
                    <label class="col-sm-2 control-label">Status *</label>
                    <div class='col-sm-3'>
                        <select class='form-control cbo-request-status-id' name='status' id='status' data-placeholder="Select Status">
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Project</label>
                    <div class='col-sm-3'>
                      <select class='form-control cbo-all-project-id' name='project_id' id='project_id' data-placeholder="Select Project">
                      </select>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-md-7 text-right'>
                        <button type='button' class='btn-flat btn btn-warning' onclick='filter_search()'><span class="fa fa-search"></span> Filter</button>
                        <button  type='button' onclick="form_clear('frmclear')" class="btn btn-default">Clear</button>
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
                            <div class="col-sm-12">
                                <div class='col-ms-12 text-right'>
                                    <a href='frm_offset.php' class='btn btn-warning'><span class='fa fa-plus'></span>  File New Offset Request </a>
                                </div>
                                <br/>
                                <table id='ResultTable' class='table table-bordered table-striped'>
                                    <thead>
                                        <tr>
                                             <th class='text-center'>Employee Number</th>
                                            <th class='text-center'>Name</th> 
                                            <th class='text-center'>Request Type</th>
                                            <th class='text-center'>No. Hours</th>
                                            <th class='text-center date-td'>Start Date-time</th>
                                            <th class='text-center date-td'>End Date-time</th>
                                            <th class='text-center date-td'>Date Filed</th>
                                            <th class='text-center'>Remarks</th>
                                            <th class='text-center'>Project</th>
                                            <th class='text-center'>Status</th>
                                            <th class='text-center'>Previous Approver</th>
                                            <th class='text-center'>Step</th>
                                            <th class='text-center' style='min-width:100px'>Action</th>
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
    $request_type="offset";
    $redirect_page="offset.php";
    require_once("include/modal_query.php");
    require_once("include/modal_query_logs.php");
?>
<script type="text/javascript">
    var dttable="";
    $(document).ready(function ()
    {
        dttable=$('#ResultTable').DataTable({
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "searching": false,
            "ajax":
            {
                "url":"ajax/offset.php",
                "data":function(d)
                {
                    d.start_date=$("input[name='date_start']").val();
                    d.end_date=$("input[name='date_end']").val();
                    d.request_type=$("select[name='request_type']").val();
                    d.status=$("select[name='status']").val();
                    d.project_id=$("select[name='project_id']").val();
                }
            },
            "columnDefs": [{ "orderable": false, "targets": -1 }],
            "order": [[ 4, "desc" ]]
        });
    });
    function filter_search()
    {
        dttable.ajax.reload();
    }
</script>
<?php
    Modal();
    makeFoot();
?>
