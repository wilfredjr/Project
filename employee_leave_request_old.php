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

    $leave_type=$con->myQuery("SELECT eal.leave_id,
                                  CONCAT((SELECT NAME FROM LEAVES WHERE id=eal.leave_id),' (',eal.balance_per_year,' day/s left)') AS leave_balance
                            FROM employees_available_leaves eal
                            WHERE is_cancelled=0 AND is_deleted=0 AND employee_id=?",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
    makeHead("Leave Filed");
?>
<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
<div class="content-wrapper">
    <section class="content-header text-center">
        <h1 class="page-header">
            Leave Request
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <form action="" method="" class="form-horizontal" id="frmclear">
                <div class='form-group'>
                    <label class="col-md-3 control-label">Leave Start Date *</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control date_picker" id="date_start" name='date_start'>
                    </div>
                    <label class="col-md-2 control-label">Leave End Date *</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control date_picker" id="date_end" name='date_end'>
                    </div>
                </div>
                <div class='form-group'>
                    <label class="col-sm-3 control-label">Type of Leave *</label>
                    <div class='col-sm-3'>
                        <select class='form-control cbo' name='leave_id' id='leave_id' data-placeholder="Select Type of Leave">
                            <?php echo makeOptions($leave_type); ?>
                        </select>
                    </div>
<!--                     <label class="col-sm-2 control-label">Type of Half Day *</label>
                    <div class='col-sm-3'>
                        <select class='form-control cbo' name='half_day_mode' id='half_day_mode' data-placeholder="Select Type of Half Day">
                            <option value=""></option>
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div> -->
                    <label class="col-sm-2 control-label">Status *</label>
                    <div class='col-sm-3'>
                        <select class='form-control cbo' name='status' id='status' data-placeholder="Select Status">
                          <option value=""></option>
                          <option value="Approved">Approved</option>
                          <option value="Supervisor Approval">Level 1 Approval</option>
                          <option value="Final Approver Approval">Level 2 Approval</option>
                          <option value="Query (Supervisor)">Query (Level 1)</option>
                          <option value="Query (Final Approver)">Query (Level 2)</option>
                          <option value="Rejected (Supervisor)">Rejected (Level 1)</option>
                          <option value="Rejected (Final Approver)">Rejected (Level 2)</option>
                          <option value="Cancelled">Cancelled</option>
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
                                    <a href='frm_leave_request.php' class='btn btn-warning'><span class='fa fa-plus'></span>  File New Leave  </a> &nbsp;
                                    <a href='frm_half_day.php' class='btn btn-warning'><span class='fa fa-plus'></span>  File Half Day </a>
                                </div>
                                <br/>
                                <table id='ResultTable' class='table table-bordered table-striped'>
                                    <thead>
                                        <tr>
                                            <!-- <th class='text-center'>Employee Number</th>
                                            <th class='text-center'>Name</th> -->
                                            <th class='text-center'>Type of Leave</th>
                                            <th class='text-center'>For Halfday</th>
                                            <th class='text-center date-td'>Date Start</th>
                                            <th class='text-center date-td'>Date End</th>
                                            <th class='text-center date-td'>Date Filed</th>
                                            <th class='text-center'>Reason</th>
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
    $request_type="leave";
    $redirect_page="employee_leave_request.php";
    require_once("include/modal_query.php");
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
                "url":"ajax/leave_requests.php",
                "data":function(d)
                {
                    d.leave_type_id=$("select[name='leave_id']").val();
                    // d.half_day_mode=$("select[name='half_day_mode']").val();
                    d.start_date=$("input[name='date_start']").val();
                    d.end_date=$("input[name='date_end']").val();
                    d.status=$("select[name='status']").val();
                }
            },
            "columnDefs": [{ "orderable": false, "targets": 7 }],
            "order": [[ 6, "desc" ]]
        });
    });
    function filter_search()
    {
            dttable.ajax.reload();
            //console.log(dttable);
    }
</script>
<?php
    Modal();
    makeFoot();
?>
