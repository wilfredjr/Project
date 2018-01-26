<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    if (!empty($_GET['id'])) 
    {
        $data_master = $con->myQuery("SELECT id,is_processed,total_tax_withheld FROM bir_1604_e_master WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $total_tax_withheld = $data_master['total_tax_withheld'];
    }else
    {
        redirect("index.php");
    }

    makeHead("BIR 1604-E Form",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            BIR Form No. 1604-E
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

                        <form method="post" action="report_bir_1604_e_save_changes.php">

                                <div class='col-md-12'>
                                    <div class='col-md-12 '>
                                        <a href="report_bir_1604_e.php" class="btn btn-default btn-flat"><span class="fa fa-arrow-left"></span> Return to List</a>
                                        
                                        <?php if($data_master['is_processed']==0): ?>
                                            <button type="submit" class="btn btn-danger btn-flat" onclick="return confirm('Are you sure you want to save this changes?')"><span class="fa fa-save"></span> Save Changes</a></button>
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <a href="report_bir_1604_e_save_process.php?id=<?php echo $_GET['id']; ?>" class="btn btn-danger btn-flat" onclick="return confirm('Are you sure you want to save this BIR Form 1604-E?')"><span class="fa fa-save"></span> Save 1604-E Form</a>
                                        <?php else: ?>
                                            <a href="report_bir_1604_e_download.php?id=<?php echo $_GET['id']; ?>" class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download BIR Form 1604-E </a>
                                        <?php endif; ?>

                                        <a href="report_bir_1604_e_view_2.php?id=<?php echo !empty($_GET['id'])?$_GET['id']:''; ?>" class="btn btn-danger btn-flat pull-right"><span class="fa fa-arrow-right"></span> Go to Schedule 2</a>
                                    </div>  
                                </div>
                                <div class="row">
                                    <div class='col-sm-12 col-md-12'>
                                        <div class='row'>
                                            <div class='col-sm-12'>
                                                <div class="row">
                                                    <div class='col-md-12'>
                                                        <br>
                                                        <div class='col-md-12'>
                                                            <h4 class="text-red">Remittance per BIR Form No. 1601-E (Schedule 1)</h4>
                                                        </div>
                                                        <div class="col-sm-12">
                                                            <div class='panel panel-default'>
                                                                <div class='panel-body'>
                                                                    <div class='dataTable_wrapper '>
                                                                        <table class='table table-bordered table-striped'>
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class='text-center' style='width:5%'>Month</th>
                                                                                    <th class='text-center' style='width:10%'>Date of Remittance</th>
                                                                                    <th class='text-center' style='width:40%'>Bank Details/ROR No. If Any</th>
                                                                                    <th class='text-center' style='width:15%'>Taxes Withheld</th>
                                                                                    <th class='text-center' style='width:15%'>Penalties</th>
                                                                                    <th class='text-center' style='width:15%'>Tax Amount Remitted</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php 
                                                                                    $month  = array("JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC");
                                                                                    $z      = 0;
                                                                                    
                                                                                    for($a = 0; $a < 12; $a++): 
                                                                                        
                                                                                        $data_details = $con->myQuery("SELECT id, bir_1604_e_master_id, bir_1601_e_master_id, month, DATE_FORMAT(date_remittance,'%M %d, %Y') AS date_remittance, ror_details, tax_withheld, penalties, total_amount_remitted FROM bir_1604_e_schedule_1 WHERE bir_1604_e_master_id=? AND month=?",array($_GET['id'],str_pad($a+1, 2, '0', STR_PAD_LEFT)))->fetch(PDO::FETCH_ASSOC);

                                                                                            $id                     = !empty($data_details['id'])?$data_details['id']:'';
                                                                                            $ror_details            = !empty($data_details['ror_details'])?$data_details['ror_details']:'';
                                                                                            $penalties              = !empty($data_details['penalties'])?$data_details['penalties']:'';
                                                                                            $total_amount_remitted  = !empty($data_details['total_amount_remitted'])?$data_details['total_amount_remitted']:'';
                                                                                ?>
                                                                                        <tr>
                                                                                            <!-- MONTH -->
                                                                                            <td class="text-left">
                                                                                                <input type="hidden" name="sched_id" value="<?php echo !empty($_GET['id'])?$_GET['id']:''; ?>">
                                                                                                <?php echo htmlspecialchars($month[$a]); ?>
                                                                                                <?php echo !empty($data_details['id'])?"<input type='hidden' name='1604E_sched_id[".$z."]' value='".$id."' readonly>":"-"; ?>
                                                                                            </td>
                                                                                            <!-- DATE OF REMITTANCE -->
                                                                                            <td class="text-left">
                                                                                                <?php echo !empty($data_details['date_remittance'])?$data_details['date_remittance']:"-"; ?>
                                                                                            </td>
                                                                                            <!-- BANK DETAILS/ROR -->
                                                                                            <td>
                                                                                                <?php echo !empty($data_details['id'])?"<input type='text' style='width:100%' name='ror_details[".$data_details['id']."]' class='form-control' value='".$ror_details."' >":"-"; ?>
                                                                                            </td>
                                                                                            <!-- TAXES WITHHELD -->
                                                                                            <td class="text-right">
                                                                                                <?php echo !empty($data_details)?number_format($data_details['tax_withheld'],2):"-"; ?>
                                                                                            </td>
                                                                                            <!-- PENALTIES -->
                                                                                            <td>
                                                                                                <?php echo !empty($data_details['id'])?"<input type='text' style='width:100%' name='penalties[".$data_details['id']."]' class='form-control' value='".$penalties."'>":"-"; ?>
                                                                                            </td>
                                                                                            <!-- TAX AMOUNT REMITTED -->
                                                                                            <td>
                                                                                                <?php echo !empty($data_details['id'])?"<input type='text' style='width:100%' class='form-control' value='".$total_amount_remitted."' readonly>":"-"; ?>
                                                                                            </td>

                                                                                        </tr>
                                                                                <?php
                                                                                        if (!empty($data_details['id'])) 
                                                                                        {
                                                                                            $z++;  
                                                                                        }
                                                                                    endfor;
                                                                                ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>    
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> 
                                                </div>

                                                <?php if($data_master['is_processed']==1): ?>
                                                    <form class="form-horizontal">
                                                        <br>
                                                        <div class='form-group'>    
                                                            <label class='col-md-9 text-right' >Total Tax Required to be Withheld and Remitted : </label>
                                                            <div class='col-md-2'>
                                                                <input type='text' class='form-control' style="text-align:right; font-weight:bolder; background-color:#CCCCCC; font-size:15px;" value="<?php echo number_format($total_tax_withheld,2) ?>" readonly>
                                                            </div>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>

                                                <br><br><br><br><br>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        </form>

                </div>     
            </div>    
        </div>
    </section>
</div>

<?php
    Modal();
    makeFoot(WEBAPP,1);
?>