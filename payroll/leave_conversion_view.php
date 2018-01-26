<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    if (!empty($_GET['id'])) 
    {        
        $data=$con->myQuery("SELECT * FROM leave_conversion WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    }else
    {
        redirect("index.php");    
    }

    makeHead("Leave Conversion",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            Generated Leave Conversion
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
                                    <a href="leave_conversion.php" class="btn btn-default btn-flat"><span class="fa fa-arrow-left"></span> Return to List</a>
                                    <?php
                                        if($data['is_processed']==0):
                                    ?>
                                            <form action="leave_conversion_process.php" method="post" style="display:inline" onsubmit="return confirm('Are you to process this leave conversion?')">
                                                <input type="hidden" name='p_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                                <button class="btn btn-danger btn-flat" ><span class='fa fa-rotate-left'></span> Process </button>
                                            </form>
                                    <?php
                                        else:
                                    ?>
                                            <form method="post" action="download_leave_conversion.php"  style="display:inline">
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
                                                        <th class='text-center' style=''>Remaining Leave Credit</th>
                                                        <th class='text-center' style=''>Rate per day</th>
                                                        <th class='text-center' style=''>Amount</th>
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
            "scrollX":false,
            "ordering":false,
            "ajax":
            {
                "url":"ajax/leave_conversion_view.php",
                "data":function(d)
                {
                    d.lc_id="<?php echo !empty($_GET['id'])?htmlspecialchars($_GET['id']):'' ?>";
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