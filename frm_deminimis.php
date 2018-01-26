<?php
    require_once("support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }
    if (!empty($_GET['id'])) 
    {
        $data=$con->myQuery("SELECT id,dmb_code,dmb_desc,dmb_amount FROM de_minimis_benefits WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    }
    makeHead("De Minimis/Non-taxable Allowance");
?>
<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
<script type="text/javascript">
    function isNumberKey(evt, element) {
      var charCode = (evt.which) ? evt.which : event.keyCode
      if (charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 46 || charCode == 8))
        return false;
      else {
        var len = $(element).val().length;
        var index = $(element).val().indexOf('.');
        //alert(index);
        
        if (index >= 0 && charCode == 46) {
          return false;
        }
      }
      return true;
    } 
    

</script>
<div class='content-wrapper'>
    <div class='content-header'>
        <h2 class='page-header text-center text-warning'>De Minimis/Non-taxable Allowance<</h2>
    </div>
    <section class='content'>
        <div class="row">
            <div class='col-lg-12'>
                <?php
                    Alert();
                ?>    
                <div class='row'>
                    <div class='col-sm-12 col-md-8 col-md-offset-2'>
                        <form class='form-horizontal' method='POST' action='save_deminimis.php'>
                            <input type="hidden" name="id" value="<?php echo !empty($data)?$data['id']:NULL; ?>">
                            <div class='form-group'>
                                <label class='col-md-3 control-label'> De Minimis Benefit Code* </label>
                                <div class='col-md-9'>
                                    <input type='text' class='form-control' placeholder='Enter De Minimis Benefit Code' name='dmb_code' value='<?php echo !empty($data)?$data['dmb_code']:"" ?>' required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label'> De Minimis Benefit Description* </label>
                                <div class='col-md-9'>
                                    <input type='text' class='form-control' placeholder='Enter De Minimis Benefit Description' name='dmb_desc' value='<?php echo !empty($data)?$data['dmb_desc']:"" ?>'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-md-3 control-label'> De Minimis Benefit Amount* </label>
                                <div class='col-md-6'>
                                    <input class='decimal text-right form-control format_number' onkeypress="return isNumberKey(event,this)" placeholder='Enter De Minimis Benefit Amount' name='dmb_amount' value='<?php echo !empty($data)?$data['dmb_amount']:"" ?>'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-md-6 col-md-offset-6'>
                                    <button type='submit' class='btn btn-flat btn-warning'> <span class='fa fa-check'></span> Save</button>
                                    <a href='deminimis.php' class='btn btn-flat btn-default' onclick="return confirm('<?php echo !empty($data['id'])?'Are you sure you want to cancel the modification of this data?':'Are you sure you want to cancel the creation of the new entry?';?>')">Cancel</a>
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
    makeFoot();
?>