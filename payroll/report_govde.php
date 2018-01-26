<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $cbo_payroll_code=$con->myQuery("SELECT id, payroll_code FROM payroll WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

    makeHead("Government Deduction Report",1);
    ?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            Government Deduction Report
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
                        <form method='get' class='form-horizontal' action="report_govde.php" onsubmit="">
                            <div class='form-group'>
                                <label class='col-md-3 text-right' >Government Deduction Type </label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo' name='govde' data-placeholder='Filter By Government Deduction Type' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET['govde'])?htmlspecialchars($_GET['govde']):''?>" required>
                                        <option value=""></option>
                                        <option value="SSS">SSS</option>
                                        <option value="Philhealth">Philhealth</option>
                                        <option value="HDMF">HDMF</option>
                                    </select>
                                </div>
                                <label class='col-md-2 text-right' >Payroll Code </label>
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
                                        <form action="report_govde_download.php">
                                            <input type="hidden" name='frm_govde' value='<?php echo htmlspecialchars($_GET['govde']) ?>'>
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
                                            <th class='text-center' style=''>Employee No</th>
                                            <th class='text-center' style=''>Employee Name</th>
                                            <th class='text-center' style=''>Deduction Code</th>
                                            <th class='text-center' style=''>Employee Share</th>
                                            <th class='text-center' style=''>Employer Share</th>
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
                "ordering":false,
                "ajax":
                {
                    "url":"ajax/report_govde.php",
                    "data":function(d)
                    {
                        d.govde="<?php echo !empty($_GET['govde'])?htmlspecialchars($_GET['govde']):'' ?>";
                        d.payroll_code="<?php echo !empty($_GET['payroll_code'])?htmlspecialchars($_GET['payroll_code']):'' ?>";
                    }
                },
                "columnDefs":
                [
                    { "orderable": false, "targets": 4 },
                    { "orderable": false, "targets": 3 }
                ]
            });
        <?php
            endif;
        ?>
    });
    // function filter_search() 
    // {
    //     data_table.ajax.reload();
    // }
    // function validate_form(the_form) 
    // {
    //     var s_date=$("input[name='date_start']").val();
    //     var e_date=$("input[name='date_end']").val();

    //     if(s_date!=="" && e_date=="") 
    //     {
    //         alert("End date is required.");
    //         return false;
    //     }
    //     if(e_date!=="" && s_date=="") 
    //     {
    //         alert("Start date is required.");
    //         return false;
    //     }
    //     if(Date.parse(s_date) > Date.parse(e_date))
    //     {
    //         alert("Start date cannot be greater than end date.");
    //         return false;
    //     }
    // }
</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>