<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $cbo_payroll_code=$con->myQuery("SELECT id, payroll_code FROM payroll WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

    makeHead("BIR FORM 1604-E",1);
    ?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            BIR Form 1604-E
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
                        <form method='POST' class='form-horizontal' action="report_bir_1604_e_save_master.php" onsubmit="">
                            <div class='form-group'>
                                <!-- <input type="hidden" name="input_monthyear_start" value="07-2016"> -->
                                <!-- <input type="hidden" name="input_monthyear_end" value="06-2017"> -->

                                <label class="col-md-3 control-label">Start Date Date *</label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control date_picker" id="input_monthyear_start" name='input_monthyear_start' placeholder='mm/dd/yyyy' required>
                                </div>

                                <label class="col-md-2 control-label">Start Date Date *</label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control date_picker" id="input_monthyear_end" name='input_monthyear_end' placeholder='mm/dd/yyyy' required>
                                </div>
                            </div>
                            <br>
                            <div class='form-group'>
                                <div class='col-md-2 col-md-offset-5 text-right'>
                                    <button type='submit'  class='btn-flat btn btn-block btn-danger' onclick=''><span class="fa fa-file"></span> Generate Report</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
                <br>
                <div class="row">
					<div class="col-md-12">
	                	<div class='panel panel-default'>
							<div class='panel-body ' >
                                <br/>
                                <table id='ResultTable' class='table table-bordered table-striped'>
                                    <thead>
                                        <tr>
                                            <th class='text-center' style=''>Company Name</th>
                                            <th class='text-center' style=''>Year</th>
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
                "url":"ajax/report_bir_1604_e.php"
                // ,
                // "data":function(d)
                // {
                //     d.govde="<?php echo !empty($_GET['govde'])?htmlspecialchars($_GET['govde']):'' ?>";
                //     d.payroll_code="<?php echo !empty($_GET['payroll_code'])?htmlspecialchars($_GET['payroll_code']):'' ?>";
                // }
            },
            "columnDefs":
            [
                { "orderable": false, "targets": 4 },
                { "orderable": false, "targets": 3 }
            ]
        });
    });

    // function filter_search() 
    // {
    //     data_table.ajax.reload();
    // }

</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>