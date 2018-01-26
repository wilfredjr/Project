<?php
require_once("../support/config.php");
if(!isLoggedIn())
{
  toLogin();
  die();
}

if (!AllowUser(array(3, 4)))
{
    redirect("../index.php");
}

makeHead("Payroll System",1);
?>
<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
    <section class="content-header">
     <div class="page-header text-center text-red">
        <h1>Dashboard</h1>
    </div>
</section>
<section class="content">
    <div class = "row">
        <div class ="col-lg-12">
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading bg-red">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-money fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="" style='font-size:40px'></div>
                                <div>Generate Payroll</div>
                            </div>
                        </div>
                    </div>
                    <a href="../payroll/view_payroll_maintenance.php">
                        <div class="panel-footer">
                            <span class="pull-left text-red">View All</span>
                            <span class="pull-right"><i class="fa fa-arrow-right text-red"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading bg-red">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-calendar fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="" style='font-size:40px'></div>
                                <div>Shifting Schedule</div>
                            </div>
                        </div>
                    </div>
                    <a href="../payroll/view_shifting_sched.php">
                        <div class="panel-footer">
                            <span class="pull-left text-red">View All</span>
                            <span class="pull-right"><i class="fa fa-arrow-right text-red"></i></span>
                            <div class="clearfix"></div>

                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading bg-red">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-list fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="" style='font-size:40px'></div>
                                <div>View Pay Slip</div>
                            </div>
                        </div>
                    </div>
                    <a href="../payroll/pay_slip.php">
                        <div class="panel-footer">
                            <span class="pull-left text-red">View All</span>
                            <span class="pull-right"><i class="fa fa-arrow-right text-red"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading bg-red">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-list-alt fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="" style='font-size:40px'></div>
                                <div>Payroll Adjustment</div>
                            </div>
                        </div>
                    </div>
                    <a href="../payroll/frm_payroll_adjustment.php">
                        <div class="panel-footer">
                            <span class="pull-left text-red">View All</span>
                            <span class="pull-right"><i class="fa fa-arrow-right text-red"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>

            <div class='row'>
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-danger">
                                <div class="box-header with-border panel-red">
                                    <h3 class="box-title">Payroll For Process</h3>
                                    <div class="box-tools pull-right">
                                        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class='panel-body'>
                                            <div class='dataTable_wrapper '>
                                            <table class="table responsive table-bordered table-condensed table-hover" id='ResultTable'>
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">Payroll Code</th>
                                                            <th class="text-center">Payroll Group</th>
                                                            <th class="text-center">Date Generated</th>
                                                            <th class="text-center">From Date</th>
                                                            <th class="text-center">To Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                </div>
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
                "url":"ajax/dashboard.php"
            },
            "columnDefs":
            [
            { "orderable": false}
            ]
        });
    });
</script>
<?php
makeFoot(WEBAPP,1);
?>