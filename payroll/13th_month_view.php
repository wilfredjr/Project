<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $data=$con->myQuery("SELECT id,transaction_number,payroll_group_id,date_start,date_end,date_generated,is_processed FROM 13th_month WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);


    makeHead("13th Month",1);
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
                <div class="row">
                            <div class='col-md-12'>
                                <div class='col-md-12 '>
                                    <a href="13th_month.php" class="btn btn-default btn-flat"><span class="fa fa-arrow-left"></span> Return to List</a>
                                    <?php
                                        if($data['is_processed']==0):
                                    ?>
                                            <form action="13th_month_process.php" method="post" style="display:inline" onsubmit="return confirm('Are you sure you want to process this 13th month?')">
                                                <input type="hidden" name='p_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                                <input type="hidden" name='p_payroll_group_id' value='<?php echo htmlspecialchars($data['payroll_group_id']) ?>'>                                                
                                                <button class="btn btn-danger btn-flat" ><span class='fa fa-rotate-left'></span> Process 13th Month </button>
                                            </form>
                                    <?php
                                        else:
                                    ?>
                                            <form action="download_13th_month.php"  style="display:inline">
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
                                                        <th class='text-center' style=''>Employee Code</th>
                                                        <th class='text-center' style=''>Employee Name</th>
                                                        <th class='text-center' style=''>Amount</th>
                                                        <th class='text-center' style='width:10px'>Action</th>
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
            "processing": true,
            "serverSide": true,
            "searching": false,
            "ajax":
            {
                "url":"ajax/13th_month_view.php",
                "data":function(d)
                {
                    d.thmonth="<?php echo !empty($_GET['id'])?htmlspecialchars($_GET['id']):'' ?>";
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