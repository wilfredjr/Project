<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    if (!empty($_GET['id'])) 
    {
        $data = $con->myQuery("SELECT 
                                    lp.id,
                                    lp.last_pay_code,
                                    lp.employee_id,
                                    e.code AS employee_code,
                                    CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                                    lp.date_start,
                                    lp.date_end,
                                    lp.last_salary,
                                    lp.13th_month,
                                    lp.total_last_pay,
                                    lp.date_generated,
                                    lp.is_processed 
                                FROM last_pay lp
                                INNER JOIN employees e ON e.id=lp.employee_id
                                WHERE lp.id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

        $data2 = $con->myQuery("SELECT id,adjustment_type,operation,amount,remarks FROM last_pay_adjustments WHERE last_pay_id=? AND is_deleted=0",array($_GET['id']));

        if (!empty($_GET['lp_id'])) 
        {
            $data3 = $con->myQuery("SELECT id,adjustment_type,operation,amount,remarks FROM last_pay_adjustments WHERE id=?",array($_GET['lp_id']))->fetch(PDO::FETCH_ASSOC);;
        }
    }else
    {
        redirect("index.php");
    }


    makeHead("Last Pay Breakdown",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            Last Pay Breakdown
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
                            <a href="last_pay.php" class="btn btn-default btn-flat"><span class="fa fa-arrow-left"></span> Return to List</a>
                            <?php
                                if($data['is_processed']==0):
                            ?>
                                    <form action="last_pay_process.php" method="post" style="display:inline" onsubmit="return confirm('Are you sure you want to process this transaction?')">
                                        <input type="hidden" name='lp_id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                        <button class="btn btn-danger btn-flat" ><span class='fa fa-rotate-left'></span> Process Last Pay </button>
                                    </form>
                            <?php
                                else:
                            ?>
                                    <form method="post" action="download_last_pay.php"  style="display:inline">
                                        <input type="hidden" name='id' value='<?php echo htmlspecialchars($_GET['id']) ?>'>
                                        <button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Breakdown </button>
                                    </form>
                            <?php
                                endif;
                            ?>
                        </div>  
                    </div>
                            <div class="row">
                                <div class='col-sm-12 col-md-12'>
                                    <div class='row'>
                                        <div class='col-sm-12'>
                                            <br>
                                            <form class="form-horizontal">
                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Employee Number </label>
                                                        <div class='col-md-4'>
                                                            <input type='text' name='transaction_code' class='form-control' value="<?php echo !empty($data)?$data['employee_code']:''; ?>" readonly>    
                                                        </div>
                                                    </div>
                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Employee Name </label>
                                                        <div class='col-md-4'>
                                                            <input type='text' name='transaction_code' class='form-control' value="<?php echo !empty($data)?$data['employee_name']:''; ?>" readonly>    
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Last Salary </label>
                                                        <div class='col-md-4'>
                                                            <input type='text' name='transaction_code' class='form-control' style="text-align:right" value="<?php echo !empty($data)?number_format($data['last_salary'],2):''; ?>" readonly>    
                                                        </div>
                                                    </div>
                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Pro-rated 13th Month </label>
                                                        <div class='col-md-4'>
                                                            <input type='text' name='transaction_code' class='form-control' style="text-align:right" value="<?php echo !empty($data)?number_format($data['13th_month'],2):''; ?>" readonly>
                                                        </div>
                                                    </div>
                                            </form>
                                <?php
                                    if($data['is_processed']==0):    
                                ?>
                                            <br>
                                            <form method="post" action="last_pay_view_adjust_save.php" class="form-horizontal" onsubmit="return validate_save()">
                                                    <input type="hidden" name="last_pay_id" value="<?php echo !empty($_GET['id'])?$_GET['id']:''; ?>">
                                                    <input type="hidden" name="last_pay_adjust_id" value="<?php echo !empty($_GET['lp_id'])?htmlspecialchars($data3['id']):''; ?>">
                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Type </label>
                                                        <div class='col-md-4'>
                                                            <select class='form-control cbo' name='lp_type' id='lp_type' onchange='operation_select()' data-placeholder='Select Type' style='width:100%' data-allow-clear='true' data-selected="<?php echo !empty($_GET['lp_id'])?htmlspecialchars($data3['adjustment_type']):''?>">
                                                                <option value=''></option>
                                                                <option value='Tax Refund'>Tax Refund</option>
                                                                <option value='13th Month Pay Adjustment'>13th Month Pay Adjustment</option>
                                                                <option value='ITR Adjustment'>ITR Adjustment</option>
                                                                <option value='Leave Conversion Adjustment'>Leave Conversion Adjustment</option>
                                                                <option value='Other Deductions'>Other Deductions</option>
                                                                <option value='Other Benefits'>Other Benefits</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group" id='frm_operation'>
                                                        <label class="col-md-4 text-right">Operation </label>
                                                        <div class="col-md-4">
                                                            <select class="form-control cbo" name='operation' id='operation' data-placeholder='Select Operation' style='width:100%' data-allow-clear='true'>
                                                                <option value=''></option>
                                                                <option value='Add'>Add</option>
                                                                <option value='Minus'>Minus</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Amount </label>
                                                        <div class='col-md-4'>
                                                            <input type='text' name='lp_amount' id='lp_amount' class='form-control' value="<?php echo !empty($_GET['lp_id'])?$data3['amount']:''; ?>">
                                                        </div>
                                                    </div>
                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Remarks </label>
                                                        <div class='col-md-4'>
                                                            <input type='text' name='lp_remarks' id='lp_remarks' class='form-control' value="<?php echo !empty($_GET['lp_id'])?$data3['remarks']:''; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class='col-md-3 col-md-offset-4 text-right'>
                                                            <button type='submit' class='btn-flat btn btn-danger' onclick=''><span class="fa fa-save"></span>  Save</button>
                                                            <a href='last_pay_view.php?id=<?php echo $_GET['id']; ?>' class='btn-flat btn btn-default' onclick=''><span class="fa fa-refresh"></span>  Reset</a>
                                                        </div>
                                                    </div>
                                            </form>


                                            <div class="row">
                                                <div class='col-md-8 col-md-offset-2'>
                                                    <div class='col-md-12'>
                                                        <h4 class="text-red">Others</h4>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class='panel panel-default'>
                                                            <div class='panel-body'>
                                                                <div class='dataTable_wrapper '>
                                                                    <table id='ResultTable' class='table table-bordered table-striped'>
                                                                        <thead>
                                                                            <tr>
                                                                                <th class='text-center' style=''>Operation</th>
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
                                                                                    <td class='text-left'><?php echo htmlspecialchars($row['operation']) ?></td>
                                                                                    <td class='text-left'><?php echo htmlspecialchars($row['adjustment_type']) ?></td>
                                                                                    <td class='text-right'><?php echo number_format($row['amount'],2); ?></td>
                                                                                    <td class='text-left'><?php echo htmlspecialchars($row['remarks']); ?></td>
                                                                                    <td class='text-left'>
                                                                                        <a href='last_pay_view.php?id=<?php echo $_GET['id']; ?>&lp_id=<?php echo $row['id']; ?>' class='btn-sm btn-warning btn-flat' title='Edit'><span class='fa fa-edit'></span></a>
                                                                                        <a href='last_pay_view_adjust_delete.php?id=<?php echo $_GET['id']; ?>&lp_id=<?php echo $row['id']; ?>' onclick="return confirm('Are you sure you want to delete this transaction?')" class='btn-sm btn-danger btn-flat' title='Delete'><span class='fa fa-close'></span></a>
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

                                <?php
                                    else:
                                ?>
                                            <form class="form-horizontal">
                                                <?php
                                                    while($row = $data2->fetch(PDO::FETCH_ASSOC)):
                                                ?>
                                                        <div class='form-group'>    
                                                            <label class='col-md-4 text-right' ><?php echo htmlspecialchars($row['adjustment_type'])."<br>(".htmlspecialchars($row['remarks']).")" ?></label>
                                                            <div class='col-md-4'>
                                                                <input type='text' class='form-control' style="text-align:right;" value="<?php echo $row['operation']=='Minus'?"- (".number_format($row['amount'],2).")":number_format($row['amount'],2) ?>" readonly>
                                                            </div>
                                                        </div>
                                                      
                                                <?php
                                                    endwhile;
                                                ?>
                                                    <br>
                                                    <div class='form-group'>    
                                                        <label class='col-md-4 text-right' >Total Last Pay </label>
                                                        <div class='col-md-4'>
                                                            <input type='text' class='form-control' style="text-align:right; font-weight:bolder; background-color:#CCCCCC; font-size:16px;" value="<?php echo number_format($data['total_last_pay'],2) ?>" readonly>
                                                        </div>
                                                    </div>
                                            </form>

                                <?php
                                    endif;
                                ?>


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
            $('#frm_operation').hide();

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
                    {"orderable":false, "targets":3},
                    {"orderable":false, "targets":4},
                ]
            });
    });

    function operation_select()
    {
        var lp_type = $('#lp_type').val();

        if (lp_type == "13th Month Pay Adjustment" || lp_type == "ITR Adjustment" || lp_type == "Leave Conversion Adjustment") 
        {
            $('#frm_operation').show();
        }else 
        {
            $('#frm_operation').hide();
        }
    }

    function validate_save()
    {
        var type        = $("#lp_type").val();
        var operation   = $("#operation").val();
        var amount      = $("#lp_amount").val();
        var remarks     = $("#lp_remarks").val();


        if (type == "" || amount == "" || remarks == "") 
        {
            alert("Fill all required fields.");
            return false;
        }else
        {
            if ((type == "13th Month Pay Adjustment" && operation == "") || (type == "ITR Adjustment" && operation == "") || (type == "Leave Conversion Adjustment" && operation == ""))
            {
                alert("Fill all required fields.");
                return false;
            }else
            {
                return true;
            }

        }
    }

</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>