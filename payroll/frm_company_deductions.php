<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }
    if (!empty($_GET['id'])) 
    {
        $data=$con->myQuery("SELECT id,comde_code,comde_desc FROM company_deductions WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    }
    makeHead("Company Deductions",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class='content-wrapper'>
    <div class='content-header'>
        <h2 class='page-header text-center text-red'>Company Deductions Form</h2>
    </div>
    <section class='content'>
        <div class="row">
            <div class='col-lg-12'>
                <?php
                    Alert();
                ?>    
                <div class='row'>
                    <div class='col-sm-12 col-md-8 col-md-offset-2'>
                        <form class='form-horizontal' method='POST' action='save_company_deductions.php'>
                            <input type="hidden" name="id" value="<?php echo !empty($data)?$data['id']:NULL; ?>">
                            <div class='form-group'>
                                <label class='col-md-3 control-label'> Company Deduction Code* </label>
                                <div class='col-md-9'>
                                    <input type='text' class='form-control' placeholder='Enter Company Deduction Code' name='comde_code' value='<?php echo !empty($data)?$data['comde_code']:"" ?>' required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label'> Company Deduction Description* </label>
                                <div class='col-md-9'>
                                    <input type='text' class='form-control' placeholder='Enter Company Deduction Description' name='comde_desc' value='<?php echo !empty($data)?$data['comde_desc']:"" ?>'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-6 col-md-offset-6'>
                                    <button type='submit' class='btn btn-flat btn-danger'> <span class='fa fa-check'></span> Save</button>
                                    <a href='company_deductions.php' class='btn btn-flat btn-default' onclick="return confirm('<?php echo !empty($data['id'])?'Are you sure you want to cancel the modification of this data?':'Are you sure you want to cancel the creation of the new entry?';?>')">Cancel</a>
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