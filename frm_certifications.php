<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}

    if(!AllowUser(array(1,4))){
        redirect("index.php");
    }

	$data="";
	if(!empty($_GET['id'])){
  		$data=$con->myQuery("SELECT id,name,description FROM certifications WHERE id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
  		if(empty($data)){
  			Modal("Invalid Record Selected");
  			redirect("certifications.php");
  			die;
  		}
	}

	makeHead("Certification Form");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Certification Form
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-10 col-md-offset-1'>
				<?php
					Alert();
				?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                	<div class='col-md-12'>
		              	<form class='form-horizontal' action='save_certifications.php' method="POST">
		              		<input type='hidden' name='id' value='<?php echo !empty($data)?$data['id']:''; ?>'>
		              		<div class="form-group">
		                      <label for="name" class="col-sm-2 control-label">Certification *</label>
		                      <div class="col-sm-10">
		                        <input type="text" class="form-control" id="name" placeholder="Certification" name='name' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
		                      </div>
		                  </div>
                      <div class="form-group">
                          <label for="name" class="col-sm-2 control-label">Description </label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" id="description" placeholder="Description" name='description' value='<?php echo !empty($data)?htmlspecialchars($data['description']):''; ?>'>
                          </div>
                      </div>

		                    <div class="form-group">
		                      <div class="col-sm-10 col-md-offset-2 text-center">
		                      	<a href='certifications.php' class='btn btn-default'>Cancel</a>
		                        <button type='submit' class='btn btn-warning'>Save </button>
		                      </div>
		                    </div>
		                </form>	
                	</div>
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
          </div><!-- /.row -->
        </section><!-- /.content -->
  </div>

<script type="text/javascript">
  $(function () {
        $('#ResultTable').DataTable();
      });
</script>

<?php
	makeFoot();
?>