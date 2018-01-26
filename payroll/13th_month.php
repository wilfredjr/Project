<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $cbo_paycode=$con->myQuery("SELECT id,transaction_number FROM 13th_month WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
    $cbo_payroll_group=$con->myQuery("SELECT payroll_group_id, name FROM payroll_groups WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
    $cbo_paygroup=$con->myQuery("SELECT payroll_group_id,name FROM payroll_groups WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

    makeHead("Generate 13th Month",1);
    ?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            Generate 13th Month
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-lg-12'>
                <?php Alert(); ?>
            </div>
        </div>  
        <br/>
        <div class="row">
            <div class='col-sm-12 col-md-12'>
                <div class='row'>
                    <div class='col-sm-12'>
                        <form method='post' class='form-horizontal' action="13th_month_generate.php" onsubmit="return validate(this)">
                            <div class='form-group'>
                                <label class="col-md-3 control-label">Start Date *</label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control date_picker" id="date_start" name='date_start' pattern="\d{1,2}/\d{1,2}/\d{4}" required>
                                </div>
                                <label class="col-md-2 control-label">End Date *</label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control date_picker" id="date_end" name='date_end' pattern="\d{1,2}/\d{1,2}/\d{4}" required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 text-right' >Payroll Group *</label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo' name='payroll_group' data-placeholder='Filter By Payroll Group' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET['payroll_group'])?htmlspecialchars($_GET['payroll_group']):''?>" required>
                                        <?php echo makeOptions($cbo_payroll_group) ?>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-2 col-md-offset-5 text-right'>
                                    <button type='submit'  class='btn-flat btn btn-block btn-danger' onclick=''><span class="fa fa-file"></span> Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="page-header"></div>
                <div class="row">
                    <div class='col-md-12'>
                        <h4 class="text-red">Generated 13th Month</h4>
                    </div>
                        <div class='col-sm-12 col-md-12'>  
                            <div class='row'>
                                <div class='col-sm-12'>
                                    <form method="" class="form-horizontal">
                                        <div class='form-group'>
                                            <label class='col-md-1 text-right' >Transaction Code </label>
                                            <div class='col-md-3'>
                                                <select class="form-control cbo" name="pay_code_filter" id="pay_code_filter" data-placeholder="Filter by Transaction Code" style="width:100%" data-allow-clear="true">
                                                    <?php echo makeOptions($cbo_paycode); ?>
                                                </select>
                                            </div>
                                            <label class='col-md-1 text-right' >Payroll Group </label>
                                            <div class='col-md-3'>
                                                <select class="form-control cbo" name="pay_group_filter" id="pay_group_filter" data-placeholder="Filter by Payroll Group" style="width:100%" data-allow-clear="true">
                                                    <?php echo makeOptions($cbo_payroll_group); ?>
                                                </select>
                                            </div>
                                            <label class='col-md-1 text-right' >Date Generated </label>
                                            <div class='col-md-3'>
                                                <input type='text' name='date_generated_filter' class='form-control date_picker' id='date_generated_filter' placeholder="Filter by Date Generated">
                                            </div>
                                        </div>
                                        <div class='form-group'>
                                            <label class='col-md-1 text-right' >Date From </label>
                                            <div class='col-md-3'>
                                                <input type='text' name='date_start_filter' class='form-control date_picker' id='date_start_filter' placeholder="Filter by Date From">
                                            </div>
                                            <label class='col-md-1 text-right' >Date To </label>
                                            <div class='col-md-3'>
                                                <input type='text' name='date_end_filter' class='form-control date_picker' id='date_end_filter' placeholder="Filter by Date To">
                                            </div>
                                            <label class='col-md-1 text-right' >Status </label>
                                            <div class='col-md-3'>
                                                <select class="form-control cbo" name="status_filter" id="status_filter" data-placeholder="Filter by Status" style="width:100%" data-allow-clear="true">
                                                    <option value=""></option>
                                                    <option value="2">Not yet processed</option>
                                                    <option value="1">Processed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class='col-md-2 col-md-offset-5 text-right'>
                                                <button type='button' class='btn btn-flat btn-danger' onclick='filter_search()'><span class='fa fa-search'></span>  Filter </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                            <div class="col-sm-12">
                                <!-- <br/> -->
                                <div class='panel panel-default'>
                                    <div class='panel-body'>
                                        <div class='dataTable_wrapper '>
                                            <table id='ResultTable' class='table table-bordered table-striped'>
                                                <thead>
                                                    <tr>
                                                        <th class='text-center' style=''>Transaction Number</th>
                                                        <th class='text-center' style=''>Payroll Group</th>
                                                        <th class='text-center' style=''>Cut-off Start Date</th>
                                                        <th class='text-center' style=''>Cut-off End Date</th>
                                                        <th class='text-center' style=''>Date Generated</th>
                                                        <th class='text-center' style=''>Status</th>
                                                        <th class='text-center' style=''>Action</th>
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
        </div>
    </section>
</div>
<script type="text/javascript">
    var data_table="";
    $(function () 
    {
            $('#ResultTable thead td').each( function () 
            {
                var title = $(this).text();
            });
            data_table=$('#ResultTable').DataTable(
            {
                "orderCellsTop": true,
                "processing":true,
                "searching": false,
                "serverSide": true,
                "scrollX":true,
                "ajax":
                {
                    "url":"ajax/13th_month.php",
                    "data":function(d)
                    {
                        d.pay_code_filter           = $("select[name='pay_code_filter']").val();
                        d.pay_group_filter          = $("select[name='pay_group_filter']").val();
                        d.date_generated_filter     = $("#date_generated_filter").val();
                        d.date_start_filter         = $("#date_start_filter").val();
                        d.date_end_filter           = $("#date_end_filter").val();
                        d.status_filter             = $("#status_filter").val();
                    }
                },
                "columnDefs":
                [
                    { "orderable": false, "targets": 6 }
                ]
            });
    });
        
    function validate(frm) 
    {
        if(Date.parse($("#date_start").val()) > Date.parse($("#date_end").val()))
        {
            alert("Start Date cannot be greater than time out.");
            return false;
        } else if(Date.parse($("#date_start").val()) == Date.parse($("#date_end").val()))
        {
            alert("End Date should be greater than time in.")
            return false;
        }
        
        return true;
    }
    function filter_search() 
    {
        // alert($("#status_filter").val());
        data_table.ajax.reload();
    }
</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>