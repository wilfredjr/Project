<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }
    if (!empty($_GET['id'])) 
    {
        $data=$con->myQuery("SELECT id,rta_code,rta_desc,rta_amount,rta_taxable FROM receivable_and_taxable_allowances WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    }
    makeHead("Receivable/Taxable Allowances",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class='content-wrapper'>
    <div class='content-header'>
        <h2 class='page-header text-center text-red'>Receivable/Taxable Form</h2>
    </div>
    <section class='content'>
        <div class="row">
            <div class='col-lg-12'>
                <?php
                    Alert();
                ?>    
                <div class='row'>
                    <div class='col-sm-12 col-md-8 col-md-offset-2'>
                        <form class='form-horizontal' method='POST' action='save_taxable_allowances.php'>
                            <input type="hidden" name="id" value="<?php echo !empty($data)?$data['id']:NULL; ?>">
                            <div class='form-group'>
                                <label class='col-md-3 control-label'> Receivable Allowance Code* </label>
                                <div class='col-md-9'>
                                    <input type='text' class='form-control' placeholder='Enter Receivable Allowance Code' name='rta_code' value='<?php echo !empty($data)?$data['rta_code']:"" ?>' required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label'> Receivable Allowance Description* </label>
                                <div class='col-md-9'>
                                    <input type='text' class='form-control' placeholder='Enter Receivable Allowance Description' name='rta_desc' value='<?php echo !empty($data)?$data['rta_desc']:"" ?>'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label'> Receivable Allowance Amount* </label>
                                <div class='col-md-6'>
                                    <input class='text-right form-control number' placeholder='Enter Receivable Allowance Amount' name='rta_amount' value='<?php echo !empty($data)?$data['rta_amount']:"" ?>'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label'> Tax(%)* </label>
                                <div class='col-md-6'>
                                    <input class='decimal text-right form-control' placeholder='Enter Tax(%)' name='rta_taxable' value='<?php echo !empty($data)?$data['rta_taxable']:"" ?>' maxlength='3'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-6 col-md-offset-6'>
                                    <button type='submit' class='btn btn-flat btn-danger'> <span class='fa fa-check'></span> Save</button>
                                    <a href='taxable_allowances.php' class='btn btn-flat btn-default' onclick="return confirm('<?php echo !empty($data['id'])?'Are you sure you want to cancel the modification of this data?':'Are you sure you want to cancel the creation of the new entry?';?>')">Cancel</a>
                                </div>
                            </div>                                
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>