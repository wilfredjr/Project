<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $data=$con->myQuery("SELECT 
                            md.id,
                            md.13th_month_id,
                            m.transaction_number,
                            m.date_generated,
                            md.employee_id,
                            e.code AS employee_code,
                            CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                            md.amount
                        FROM 13th_month_details md 
                        INNER JOIN 13th_month m ON m.id=md.13th_month_id
                        INNER JOIN employees e ON e.id=md.employee_id
                        WHERE md.id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

    $data2=$con->myQuery("SELECT id,adjustment_type,13th_month_details_id,amount,remarks FROM 13th_month_adjust WHERE is_deleted=0 AND 13th_month_details_id=?",array($_GET['id']));   

    if(!empty($_GET['a_id'])) 
    {
        $data3=$con->myQuery("SELECT id,adjustment_type,13th_month_details_id,amount,remarks FROM 13th_month_adjust WHERE id=?",array($_GET['a_id']))->fetch(PDO::FETCH_ASSOC);   
    }

    makeHead("Adjust 13th Month",1);
    ?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            Adjust 13th Month
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
            <div class='col-sm-12'>
                <a href="13th_month_view.php?id=<?php echo $data['13th_month_id']; ?>" class="btn btn-danger btn-flat"><span class="fa fa-arrow-left"></span> Return to List</a>
            </div>
            <div class='col-sm-12 col-md-12'>
                <div class='row'>
                    <div class='col-sm-12'>
                        <form method='post' class='form-horizontal' action="save_13th_month_adjust.php" onsubmit="">
                            <input type='hidden' name='id' value='<?php echo !empty($_GET)?$_GET['id']:'' ?>'>
                            <input type='hidden' name='a_id' value='<?php echo !empty($_GET['a_id'])?$_GET['a_id']:'' ?>'>
                            <div class='form-group'>
                                <label class="col-md-4 control-label">Employee Code </label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" value='<?php echo !empty($data)?$data['employee_code']:''; ?>' readonly>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class="col-md-4 control-label">Employee Name </label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" value='<?php echo !empty($data)?$data['employee_name']:''; ?>' readonly>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class="col-md-4 control-label">Original Amount </label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" value='<?php echo !empty($data)?$data['amount']:''; ?>' readonly>
                                </div>
                            </div>
                            <br/>
                            <div class='form-group'>
                                <label class='col-md-4 text-right' >Type of Adjustment *</label>
                                <div class='col-md-4'>
                                    <select class='form-control cbo' name='adjustment_type' data-placeholder='Filter By Adjustment Type' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET['a_id'])?htmlspecialchars($data3['adjustment_type']):''?>" required>
                                        <option value=''></option>
                                        <option value='1'>Add</option>
                                        <option value='2'>Minus</option>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class="col-md-4 control-label">Amount </label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="amount" id="amount" value="<?php echo !empty($_GET['a_id'])?$data3['amount']:''; ?>" required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class="col-md-4 control-label">Remarks </label>
                                <div class="col-md-4">
                                    <textarea type="text" class="form-control" name="remarks" id="remarks" required><?php echo !empty($_GET['a_id'])?$data3['remarks']:''; ?></textarea>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-3 col-md-offset-4 text-right'>
                                    <button type='submit' class='btn-flat btn btn-danger' onclick=''><span class="fa fa-save"></span>  Save</button>
                                    <a href='13th_month_adjust.php?id=<?php echo $_GET['id']; ?>' class='btn-flat btn btn-default' onclick=''><span class="fa fa-refresh"></span>  Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                        <div class='col-md-8 col-md-offset-2'>
                            <div class='col-md-12'>
                                <h4 class="text-red">Adjustments</h4>
                            </div>
                            <div class="col-sm-12">
                                <div class='panel panel-default'>
                                    <div class='panel-body'>
                                        <div class='dataTable_wrapper '>
                                            <table id='ResultTable' class='table table-bordered table-striped'>
                                                <thead>
                                                    <tr>
                                                        <th class='text-center' style=''>Adjustment Type</th>
                                                        <th class='text-center' style=''>Amount</th>
                                                        <th class='text-center' style=''>Remarks</th>
                                                        <th class='text-center' style=''>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                        while($row=$data2->fetch(PDO::FETCH_ASSOC)):
                                                    ?>
                                                        <tr>
                                                            <td class='text-left'><?php echo $row['adjustment_type']==1?'Add':'Minus'; ?></td>
                                                            <td class='text-right'><?php echo number_format($row['amount'],2); ?></td>
                                                            <td class='text-left'><?php echo htmlspecialchars($row['remarks']); ?></td>
                                                            <td class='text-left'>
                                                                <a href='13th_month_adjust.php?id=<?php echo $_GET['id']; ?>&a_id=<?php echo $row['id']; ?>' class='btn-sm btn-warning btn-flat' title='Edit'><span class='fa fa-edit'></span></a>
                                                                <a href='delete_13th_month_adjust.php?id=<?php echo $_GET['id']; ?>&a_id=<?php echo $row['id']; ?>' onclick="return confirm('Are you sure you want to delete this transaction?')" class='btn-sm btn-danger btn-flat' title='Delete'><span class='fa fa-close'></span></a>
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
                // "serverSide": true,
                "scrollX":true
                // "ajax":
                // {
                //     "url":"ajax/13th_month.php"
                //     // ,
                //     // "data":function(d)
                //     // {
                //         // d.govde="<?php echo !empty($_GET['govde'])?htmlspecialchars($_GET['govde']):'' ?>";
                //         // d.payroll_code="<?php echo !empty($_GET['payroll_code'])?htmlspecialchars($_GET['payroll_code']):'' ?>";
                //     // }
                // }

            });
    });
</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>