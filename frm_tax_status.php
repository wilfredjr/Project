<?php
	require_once("support/config.php");
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}

    if(!AllowUser(array(1,4)))
    {
        redirect("index.php");
    }

	$data="";
	
    if(!empty($_GET['id']))
    {
  		$data=$con->myQuery("SELECT id,code,description FROM tax_status WHERE is_deleted=0 AND id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
  	
    	if(empty($data))
        {
  			Modal("Invalid Record Selected");
  			redirect("tax_status.php");
  			die;
  		}
	}

	makeHead("Tax Status");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Tax Status Form
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-10 col-md-offset-1'>
				<?php
					Alert();
				?>
                <div class="box box-warning">
                    <div class="box-body">
                        <div class="row">
            	           <div class='col-md-12'>
	              	            <form class='form-horizontal' action='save_tax_status.php' method="POST">
	              		            <input type='hidden' name='id' value='<?php echo !empty($data)?$data['id']:''; ?>'>
              		                <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">Code *</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="name" placeholder="Code" name='name' value='<?php echo !empty($data)?htmlspecialchars($data['code']):''; ?>' required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-2 control-label">Description *</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="description" placeholder="Description" name='description' value='<?php echo !empty($data)?htmlspecialchars($data['description']):''; ?>' required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-9 col-md-offset-2 text-center">
                                            <button type='submit' class='btn btn-warning'>Save </button>
                                            <a href='tax_status.php' class='btn btn-default'>Cancel</a>
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
	makeFoot();
?>