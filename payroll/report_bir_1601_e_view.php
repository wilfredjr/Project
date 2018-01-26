<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $data_master    = $con->myQuery("SELECT * FROM bir_1601_e_master WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    $data_details   = $con->myQuery("SELECT id,nature_of_business,atc_code,tax_base,tax_rate,tax_withheld FROM bir_1601_e_details WHERE is_deleted=0 AND bir_1601_e_master_id=?",array($_GET['id']));
    $cbo_bir1601    = $con->myQuery("SELECT id,CONCAT(nature_of_business,'(',atc_code,', ',tax_rate,'%)') AS name FROM bir_1601_e_reference WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($_GET['Eid'])) 
    {
        $data = $con->myQuery("SELECT id,bir_1601_e_master_id,nature_of_business,reference_id,atc_code,tax_base,tax_rate,tax_withheld FROM bir_1601_e_details WHERE id=?",array($_GET['Eid']))->fetch(PDO::FETCH_ASSOC);
    }


    makeHead("BIR 1601-E Form",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            BIR Form No. 1601-E
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
                            <a href="report_bir_1601_e.php" class="btn btn-default btn-flat"><span class="fa fa-arrow-left"></span> Return to List</a>

                            <?php if($data_master['is_processed']==0): ?>
                                <a href="report_bir_1601_e_save_process.php?id=<?php echo $_GET['id']; ?>" class="btn btn-danger btn-flat" onclick="return confirm('Are you sure you want to save this BIR Form 1601-E?')"><span class="fa fa-save"></span> Save 1601-E Form</a>
                            <?php else: ?>
                                <form action="report_bir_1601_e_download.php" method="post" style="display:inline">
                                    <input type="hidden" name='master_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                    <button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download BIR Form 1601-E </button>
                                </form>
                            <?php endif; ?>
                        </div>  
                    </div>
                        <div class="row">
                            <div class='col-sm-12 col-md-12'>
                                <div class='row'>
                                    <div class='col-sm-12'>
                                        <br>
                                     <!--    <form class="form-horizontal">
                                            <div class='form-group'>    
                                                <label class='col-md-4 text-right'>Tin Number </label>
                                                <div class='col-md-4'>
                                                    <input type='text' name='' class='form-control' value="<?php //echo !empty($data)?$data['tin']:''; ?>" readonly>    
                                                </div>
                                            </div>
                                            <div class='form-group'>    
                                                <label class='col-md-4 text-right' >RDO Number </label>
                                                <div class='col-md-4'>
                                                    <input type='text' name='' class='form-control' value="<?php //echo !empty($data)?$data['rdo_code']:''; ?>" readonly>    
                                                </div>
                                            </div>
                                            <div class='form-group'>    
                                                <label class='col-md-4 text-right' >Line of Business </label>
                                                <div class='col-md-4'>
                                                    <input type='text' name='' class='form-control' value="<?php //echo !empty($data)?$data['line_of_business']:''; ?>" readonly>    
                                                </div>
                                            </div>
                                            <div class='form-group'>    
                                                <label class='col-md-4 text-right' >Company Name </label>
                                                <div class='col-md-4'>
                                                    <input type='text' name='' class='form-control' value="<?php //echo !empty($data)?$data['company_name']:''; ?>" readonly>    
                                                </div>
                                            </div>
                                        </form> -->

                                    <?php if($data_master['is_processed']==0): ?>

                                        <br>
                                        <form method="post" action="report_bir_1601_e_save_detail.php" class="form-horizontal" onsubmit="return validate_save()">
                                                <input type="hidden" name="input_id" value="<?php echo !empty($_GET['id'])?$_GET['id']:''; ?>">
                                                <input type="hidden" name="input_detail_id" value="<?php echo !empty($_GET['Eid'])?$_GET['Eid']:''; ?>">

                                                <div class='form-group'>    
                                                    <label class='col-md-4 text-right' >Nature of Income Payment * </label>
                                                    <div class='col-md-4'>
                                                        <textarea name='input_nature_of_income_payment' id='input_nature_of_income_payment' class='form-control' required><?php echo !empty($_GET['Eid'])?$data['nature_of_business']:''; ?></textarea>
                                                    </div>
                                                </div>
                                                <div class='form-group'>    
                                                    <label class='col-md-4 text-right' >ATC CODE & RATE * </label>
                                                    <div class='col-md-4'>
                                                        <select class='form-control cbo' name='input_atc_details' id='input_atc_details' data-placeholder='Select ATC Code and Rate' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET['Eid'])?$data['reference_id']:''; ?>" required>
                                                            <?php echo makeOptions($cbo_bir1601); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class='form-group'>    
                                                    <label class='col-md-4 text-right' >TAX Base * </label>
                                                    <div class='col-md-4'>
                                                        <input type='text' name='input_tax_base' id='input_tax_base' class='form-control' value="<?php echo !empty($_GET['Eid'])?$data['tax_base']:''; ?>" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <div class='col-md-3 col-md-offset-4 text-right'>
                                                        <button type='submit' class='btn-flat btn btn-danger' onclick=''><span class="fa fa-save"></span>  Save</button>
                                                        <a href='report_bir_1601_e_view.php?id=<?php echo $_GET['id']; ?>' class='btn-flat btn btn-default' onclick=''><span class="fa fa-refresh"></span>  Reset</a>
                                                    </div>
                                                </div>
                                        </form>
                                    <?php endif; ?>

                                        <div class="row">
                                            <div class='col-md-10 col-md-offset-1'>
                                                <div class='col-md-12'>
                                                    <h4 class="text-red">Computation of Tax</h4>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class='panel panel-default'>
                                                        <div class='panel-body'>
                                                            <div class='dataTable_wrapper '>
                                                                <table id='ResultTable' class='table table-bordered table-striped'>
                                                                    <thead>
                                                                        <tr>
                                                                            <th class='text-center' style=''>Nature of Income Payment</th>
                                                                            <th class='text-center' style=''>ATC</th>
                                                                            <th class='text-center' style=''>Tax Base</th>
                                                                            <th class='text-center' style=''>Tax Rate</th>
                                                                            <th class='text-center' style=''>Tax Required to be Withheld</th>
                                                                            <th class='text-center' style=''>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php 
                                                                            $total_tax_withheld = 0;
                                                                            while($row1 = $data_details->fetch(PDO::FETCH_ASSOC)): 

                                                                                $total_tax_withheld = $total_tax_withheld + floatval($row1['tax_withheld']);
                                                                        ?>
                                                                                <tr>
                                                                                    <td class="text-left"><?php echo htmlspecialchars($row1['nature_of_business']); ?></td>
                                                                                    <td class="text-left"><?php echo htmlspecialchars($row1['atc_code']); ?></td>
                                                                                    <td class="text-right"><?php echo number_format($row1['tax_base'],2); ?></td>
                                                                                    <td class="text-left"><?php echo htmlspecialchars($row1['tax_rate']."%"); ?></td>
                                                                                    <td class="text-right"><?php echo number_format($row1['tax_withheld'],2); ?></td>
                                                                                    <td class='text-left'>
                                                                                        <?php if($data_master['is_processed'] == 0): ?>
                                                                                            <a href='report_bir_1601_e_view.php?id=<?php echo $_GET['id']; ?>&Eid=<?php echo $row1['id']; ?>' class='btn-sm btn-warning btn-flat' title='Edit'><span class='fa fa-edit'></span></a>
                                                                                            <a href='report_bir_1601_e_delete_detail.php?id=<?php echo $_GET['id']; ?>&Eid=<?php echo $row1['id']; ?>' onclick="return confirm('Are you sure you want to delete this record?')" class='btn-sm btn-danger btn-flat' title='Delete'><span class='fa fa-close'></span></a>
                                                                                        <?php endif; ?>
                                                                                    </td>
                                                                                </tr>
                                                                        <?php 
                                                                            endwhile; 
                                                                        ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>    
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
 
                                        <form class="form-horizontal">
                                            <br>
                                            <div class='form-group'>    
                                                <label class='col-md-9 text-right' >Total Tax Required to be Withheld and Remitted: </label>
                                                <div class='col-md-2'>
                                                    <input type='text' class='form-control' style="text-align:right; font-weight:bolder; background-color:#CCCCCC; font-size:15px;" value="<?php echo number_format($total_tax_withheld,2) ?>" readonly>
                                                </div>
                                            </div>
                                        </form>

                                        <br><br><br><br><br>
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
            "processing"        : true,
            "searching"         : false,
            "scrollX"           : true,
            "bLengthChange"     : false,
            "columnDefs"        : 
            [
                {"orderable":false, "targets":5}
            ]
        });
    });
</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>