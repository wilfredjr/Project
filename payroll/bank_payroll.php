<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $cbo_payroll_code=$con->myQuery("SELECT id, payroll_code FROM payroll WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

    makeHead("Bank Payroll Report",1);
    ?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            Bank Payroll Report
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
                        <form method='get' class='form-horizontal' action="bank_payroll.php" onsubmit="">
                            <div class='form-group'>
                                <label class='col-md-2 col-md-offset-2 text-right' >Payroll Code </label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo' name='payroll_code' data-placeholder='Filter By Payroll Code' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET['payroll_code'])?htmlspecialchars($_GET['payroll_code']):''?>" required>
                                        <?php echo makeOptions($cbo_payroll_code); ?>
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
                <div class="row">
                    <?php
                        if( !empty($_GET)):
                    ?>
                            <div class="col-sm-12">
                                <div class='col-ms-12 text-right'>
                                    <div class='col-md-12 text-right'>
                                        <form action="bank_payroll_download.php">
                                            <input type="hidden" name='frm_payroll_code' value='<?php echo htmlspecialchars($_GET['payroll_code']) ?>'>
                                            <button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
                                        </form>
                                    </div>  
                                </div>
                                <br/>
                                <br/>
                                <table id='ResultTable' class='table table-bordered table-striped'>
                                    <thead>
                                        <tr>
                                            <th class='text-center' style=''>Employee Code</th>
                                            <th class='text-center' style=''>Employee Name</th>
                                            <th class='text-center' style=''>Card Number</th>
                                            <th class='text-center' style=''>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div> 
                    <?php
                        endif;
                    ?>
                </div>     
            </div>    
        </div>
    </section>
</div>

<script type="text/javascript">
    var data_table="";
    $(function () 
    {
        <?php
            if(!empty($_GET)):
        ?>
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
                    "url":"./ajax/bank_payroll.php",
                    "data":function(d)
                    {
                        d.payroll_code="<?php echo !empty($_GET['payroll_code'])?htmlspecialchars($_GET['payroll_code']):'' ?>";
                    }
                }
            });
        <?php
            endif;
        ?>
    });
</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>