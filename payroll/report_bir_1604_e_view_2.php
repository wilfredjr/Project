<?php
    require_once("../support/config.php");

    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    if (!empty($_GET['id'])) 
    {
        $data_master = $con->myQuery("SELECT id,is_processed FROM bir_1604_e_master WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $data = $con->myQuery("SELECT * FROM bir_1604_e_schedule_4 WHERE bir_1604_e_schedule_4.bir_1604_e_master_id = ?",array($_GET['id']));

        if (!empty($_GET['Eid'])) 
        {
            $data_sched4 = $con->myQuery("SELECT * FROM bir_1604_e_schedule_4 WHERE id=?",array($_GET['Eid']))->fetch(PDO::FETCH_ASSOC);
            
            $cbo_details = $con->myQuery("SELECT  bir_1601_e_details.id AS id, 
                                            CONCAT(bir_1601_e_details.nature_of_business,' ( ', bir_1601_e_details.atc_code,' )') AS name
                                        FROM bir_1604_e_schedule_1
                                        INNER JOIN bir_1601_e_master ON bir_1601_e_master.id=bir_1604_e_schedule_1.bir_1601_e_master_id
                                        INNER JOIN bir_1601_e_details ON bir_1601_e_details.bir_1601_e_master_id=bir_1601_e_master.id
                                        WHERE bir_1604_e_schedule_1.bir_1604_e_master_id=?",array($_GET['id']))->fetchAll(PDO::FETCH_ASSOC);
            $disabled = "disabled";
        }else
        {
            $cbo_details = $con->myQuery("SELECT  bir_1601_e_details.id AS id, 
                                            CONCAT(bir_1601_e_details.nature_of_business,' ( ', bir_1601_e_details.atc_code,' )') AS name
                                        FROM bir_1604_e_schedule_1
                                        INNER JOIN bir_1601_e_master ON bir_1601_e_master.id=bir_1604_e_schedule_1.bir_1601_e_master_id
                                        INNER JOIN bir_1601_e_details ON bir_1601_e_details.bir_1601_e_master_id=bir_1601_e_master.id
                                        WHERE bir_1604_e_schedule_1.bir_1604_e_master_id=?  
                                            AND (bir_1601_e_details.id NOT IN (SELECT bir_1601_e_details_id FROM bir_1604_e_schedule_4 WHERE is_deleted=0))",array($_GET['id']))->fetchAll(PDO::FETCH_ASSOC);       
            $disabled = "";
        }

    }else
    {
        redirect("index.php");
    }


// var_dump($cbo_details);
// die();  

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

                            <div class='col-md-12'>
                                <div class='col-md-12 '>
                                    <a href="report_bir_1604_e_view.php?id=<?php echo $_GET['id']; ?>" class="btn btn-danger btn-flat"><span class="fa fa-arrow-left"></span> Return to Schedule 1</a>
                                    <!-- <button type="submit" class="btn btn-danger btn-flat" onclick="return confirm('Are you sure you want to save this changes?')"><span class="fa fa-save"></span> Save Changes</a></button> -->
                                </div>  
                            </div>
                            <div class="row">
                                <div class='col-sm-12 col-md-12'>
                                    <div class='row'>
                                        <div class='col-sm-12'>
                                            <br><br>

                                            <?php if($data_master['is_processed']==0): ?>
                                            
                                                <form method="post" action="report_bir_1604_e_save_changes_2.php" class="form-horizontal" onsubmit="return validate_save()">
                                                    <input type="hidden" name="input_id" value="<?php echo !empty($_GET['id'])?$_GET['id']:''; ?>">
                                                    <input type="hidden" name="input_detail_id" value="<?php echo !empty($_GET['Eid'])?$_GET['Eid']:''; ?>">

                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Taxpayer Identification Number <small>(TIN)</small> * </label>
                                                        <div class='col-md-5'>
                                                            <input type='text' name='input_tin_tax_payer' id='input_tin_tax_payer' placeholder='TAXPAYER IDENTIFICATION NUMBER (TIN)' class='form-control' value="<?php echo !empty($_GET['Eid'])?$data_sched4['tin_tax_payer']:''; ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Name of Payees <small>(Last Name, First Name, Middle Name for Individuals, Complete Name for Non-individuals)</small> * </label>
                                                        <div class='col-md-5'>
                                                            <input type='text' name='input_name_payees' id='input_name_payees' placeholder='NAME OF PAYEES' class='form-control' value="<?php echo !empty($_GET['Eid'])?$data_sched4['name_payees']:''; ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >1601-E Details * </label>
                                                        <div class='col-md-5'>
                                                            <select class='form-control cbo' name='input_1601e_details' id='input_1601e_details' data-placeholder='Select 1601-E Details' style='width:100%' data-allow-clear='true'  data-selected="<?php echo !empty($_GET['Eid'])?$data_sched4['bir_1601_e_details_id']:''; ?>" required <?php echo $disabled; ?>>
                                                                <?php echo makeOptions($cbo_details); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <div class='col-md-3 col-md-offset-4 text-right'>
                                                            <button type='submit' class='btn-flat btn btn-danger' onclick=''><span class="fa fa-save"></span>  Save</button>
                                                            <a href='report_bir_1604_e_view_2.php?id=<?php echo $_GET['id']; ?>' class='btn-flat btn btn-default' onclick=''><span class="fa fa-refresh"></span>  Reset</a>
                                                        </div>
                                                    </div>
                                                </form>

                                            <?php endif; ?>

                                            <div class="row">
                                                <div class='col-md-12'>
                                                    <div class='col-md-12'>
                                                        <h4 class="text-red"> Alphalist of Payees Subject to Expanded Withholding Tax </h4>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class='panel panel-default'>
                                                            <div class='panel-body'>
                                                                <div class='dataTable_wrapper '>
                                                                    <table id='ResultTable' class='table table-bordered table-striped'>
                                                                        <thead>
                                                                            <tr>
                                                                                <th class='text-center' style='width:5%'>Seq. No.</th>
                                                                                <th class='text-center' style='width:10%'>TIN</th>
                                                                                <th class='text-center' style='width:18%'>Name of Payees</th>
                                                                                <th class='text-center' style='width:7%'>ATC</th>
                                                                                <th class='text-center' style='width:20%'>Nature of Income Payment</th>
                                                                                <th class='text-center' style='width:10%'>Amount of Income Payment</th>
                                                                                <th class='text-center' style='width:10%'>Tax Rate</th>
                                                                                <th class='text-center' style='width:10%'>Tax Required to be Withheld</th>
                                                                                <th class='text-center' style='width:10%'>Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php 
                                                                                $x = 1;
                                                                                while($row = $data->fetch(PDO::FETCH_ASSOC)): 
                                                                            ?>
                                                                                    <tr>
                                                                                        <td><?php echo $x; ?></td>
                                                                                        <td><?php echo htmlspecialchars($row['tin_tax_payer']); ?></td>
                                                                                        <td><?php echo htmlspecialchars($row['name_payees']); ?></td>
                                                                                        <td><?php echo htmlspecialchars($row['atc']); ?></td>
                                                                                        <td><?php echo htmlspecialchars($row['nature_of_income_payment']); ?></td>
                                                                                        <td class='text-right'><?php echo number_format($row['tax_base'],2); ?></td>
                                                                                        <td class='text-right'><?php echo number_format($row['tax_rate'],2)."%"; ?></td>
                                                                                        <td class='text-right'><?php echo number_format($row['tax_withheld'],2); ?></td>
                                                                                        <td>
                                                                                            <?php if($data_master['is_processed'] == 0): ?>
                                                                                                <a href='report_bir_1604_e_view_2.php?id=<?php echo $_GET['id']; ?>&Eid=<?php echo $row['id']; ?>' class='btn-sm btn-warning btn-flat' title='Edit'><span class='fa fa-edit'></span></a>
                                                                                                <a href='report_bir_1604_e_delete_sched_4.php?id=<?php echo $_GET['id']; ?>&Eid=<?php echo $row['id']; ?>' onclick="return confirm('Are you sure you want to delete this record?')" class='btn-sm btn-danger btn-flat' title='Delete'><span class='fa fa-close'></span></a>
                                                                                            <?php endif; ?>
                                                                                        </td>
                                                                                    </tr>
                                                                            <?php 
                                                                                    $x++;
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
     
                                            <!-- <form class="form-horizontal">
                                                <br>
                                                <div class='form-group'>    
                                                    <label class='col-md-9 text-right' >Total Tax Required to be Withheld and Remitted: </label>
                                                    <div class='col-md-2'>
                                                        <input type='text' class='form-control' style="text-align:right; font-weight:bolder; background-color:#CCCCCC; font-size:15px;" value="<?php //echo number_format($total_tax_withheld,2) ?>" readonly>
                                                    </div>
                                                </div>
                                            </form> -->

                                            <br><br><br><br><br>
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
            "ordering"          : false
        });
    });
</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>