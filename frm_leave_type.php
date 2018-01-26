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
  		$data=$con->myQuery("SELECT id,name,is_convertable,is_pay FROM leaves WHERE is_deleted=0 AND id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
  		if(empty($data))
        {
  			Modal("Invalid Record Selected");
  			redirect("leave_type.php");
  			die;
  		}
	}

	makeHead("Leave Type Form");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
<div class="content-wrapper">
    <section class="content-header text-center">
        <h1>
            Leave Type Form
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-10 col-md-offset-1'>
      			<?php	Alert();	?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class='col-md-12'>
                                <form class='form-horizontal' action='save_leave_type.php' method="POST">
                                    <input type='hidden' name='id' value='<?php echo !empty($data)?$data['id']:''; ?>'>
                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">Leave Type *</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="name" placeholder="Leave Type" name='name' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class='col-md-2 control-label'></label>
                                        <input class='col-md-1' type="checkbox" name="is_pay" value='1' <?php echo !empty($data)?$data['is_pay']==1?'checked':'':''; ?>> With Pay
                                    </div>
                                    <div class="form-group">
                                        <label class='col-md-2 control-label'></label>
                                        <input class='col-md-1' type="checkbox" name="is_convertable" value='1' <?php echo !empty($data)?$data['is_convertable']==1?'checked':'':''; ?>> Convertable To Cash
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-9 col-md-offset-2 text-center">
                                            <button type='submit' class='btn btn-warning'>Save </button>
                                            <a href='leave_type.php' class='btn btn-default'>Cancel</a>
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
<script type="text/javascript">
    $(function () 
    {
        $('#ResultTable').DataTable();
    });
</script>
<?php
    makeFoot();
?>