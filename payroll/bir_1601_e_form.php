<?php
    require_once("../support/config.php");

    if(!isLoggedIn())
    {
        toLogin();
        die();
    }   

    if(!empty($_GET['id']))
    {
        $data = $con->myQuery("SELECT * FROM bir_1601_e_reference WHERE id =?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);  
    }
// var_dump($data);
// die();

    makeHead("BIR 1601-E Reference",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>

<div class="content-wrapper">
    <section class="content-header">
        <?php if(!empty($_GET['id'])): ?>
                <h1 class="text-red">Update BIR 1601-E Schedule</h1>
        <?php else: ?>
            <h1 class="text-red">Add New BIR 1601-E Schedule</h1>                
        <?php endif; ?>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-12 '>
                <?php Alert(); ?>
                <div class="box box-danger">
                    <div class="box-body">
                        <div class="row">
                            <div class='col-sm-12 col-md-8 col-md-offset-2'>
                                <br/>
                                <form class='form-horizontal' method='POST' action='bir_1601_e_save.php'>
                                    <input type='hidden' name='id' value="<?php echo !empty($_GET['id'])?htmlspecialchars($data['id']):''; ?>">
                                    
                                    <div class='form-group'>
                                        <label class='col-md-3 control-label'>Nature of Income Payment * </label>
                                        <div class='col-md-7'>
                                            <textarea class="form-control" name="nature_of_business" id="nature_of_business" placeholder="Enter Nature of Income Payment" required><?php echo !empty($_GET['id'])?$data['nature_of_business']:''; ?></textarea>
                                        </div>
                                    </div>  
                                    
                                    <div class='form-group'>
                                        <label class='col-md-3 control-label'>Tax Rate (%)* </label>
                                        <div class='col-md-7'>
                                            <input type="text" class="form-control" name="tax_rate" placeholder="Enter Tax Rate in Percentage (%)" value='<?php echo !empty($_GET['id'])?$data['tax_rate']:''; ?>'  required>
                                        </div>
                                    </div>  

                                    <div class='form-group'>
                                        <label class='col-md-3 control-label'>ATC Type * </label>
                                        <div class='col-md-7'>
                                            <select class="form-control cbo" name="atc_type" data-placeholder="Enter ATC Code" style="width:100%" data-allow-clear="true" <?php echo !empty($_GET['id'])?"data-selected='".$data['atc_type']."'":null; ?> required>
                                                <option value=""></option>
                                                <option value="1">Individual</option>
                                                <option value="2">Corporation</option>
                                            </select>
                                        </div>
                                    </div>  

                                    <div class='form-group'>
                                        <label class='col-md-3 control-label'>ATC Code * </label>
                                        <div class='col-md-7'>
                                            <input type="text" class="form-control" name="atc_code" placeholder="Enter ATC Code" value='<?php echo !empty($_GET['id'])?$data['atc_code']:''; ?>' required>
                                        </div>
                                    </div>  

                                    <div class='form-group'>
                                        <div class='col-sm-12 col-md-5 col-md-offset-5'>
                                            <button type='submit' class='btn btn-danger btn-flat'> <span class='fa fa-save'></span> Save</button>
                                            <a href='bir_1601_e_reference.php' class='btn btn-flat btn-default'>Cancel</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
    makeFoot(WEBAPP,1);
?>