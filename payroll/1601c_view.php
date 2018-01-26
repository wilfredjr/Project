<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    if (!empty($_GET['id'])) 
    {        
        $data=$con->myQuery("SELECT * FROM sixteen_zero_one_c WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    }else
    {
        redirect("index.php");    
    }

    makeHead("BIR 1601-C",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            Generated BIR 1601-C
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
                <div class="row">
                            <div class='col-md-12'>
                                <div class='col-md-12 '>
                                    <a href="view_1601c.php" class="btn btn-default btn-flat"><span class="fa fa-arrow-left"></span> Return to List</a>
                                    <?php
                                        if($data['is_processed']==0):
                                    ?>
                                            <form action="1601c_process.php" method="post" style="display:inline" onsubmit="return confirm('Are you to process this 1601_C?')">
                                                <input type="hidden" name='p_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                                <button class="btn btn-danger btn-flat" ><span class='fa fa-rotate-left'></span> Process </button>
                                            </form>
                                    <?php
                                        else:
                                    ?>
                                            <form method="post" action="download_1601c.php?id=<?php echo $_GET['id']; ?>"  style="display:inline">
                                                <input type="hidden" name='p_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                                <button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
                                            </form>
                                    <?php
                                        endif;
                                    ?>
                                </div>  
                            </div>
                            <div class="col-sm-12">
                            <br/>
                                <div class='panel panel-default'>
                                    <div class='panel-body'>
                                        <div class='dataTable_wrapper '>
                                            <table id='ResultTable' class='table table-bordered table-striped'>
                                                <thead>
                                                    <tr>
                                                        <th class='text-center' style=''>Month-Year</th>
                                                        <th class='text-center' style=''>TIN</th>
                                                        <th class='text-center' style=''>RDO Code</th>
                                                        <th class='text-center' style=''>Total amount of compensation</th>
                                                        <th class='text-center' style=''>Other non-taxable compensation</th>
                                                        <th class='text-center' style=''>Tax required to be withheld</th>
                                                        <th class='text-center' style=''>Adjustment from 26 of section A</th>
                                                        <th class='text-center' style=''>Previous Month</th>
                                                        <th class='text-center' style=''>Section A(5) - Tax Paid</th>
                                                        <th class='text-center' style=''>Section A(6) - Should be Tax Due for the Month</th>
                                                        <th class='text-center' style=''>Total</th>
                                                    </tr>
                                                </thead>
                                             
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
            "scrollX":false,
            "ajax":
            {
                "url":"ajax/1601c_view_details.php",
                "data":function(d)
                {
                    d.id="<?php echo !empty($_GET['id'])?htmlspecialchars($_GET['id']):'' ?>";
                }
            },
            "columnDefs":
            [
                { "orderable": false, "targets": 3 }
            ]
        });
    });
</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>