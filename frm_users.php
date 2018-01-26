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
    $get_id=$_GET['id'];
  }
  else{
    $get_id="";
  }

	if(!empty($_GET['id'])){
  		$data=$con->myQuery("SELECT id,employee_id,username,password,user_type_id,password_question,password_answer,first_name,last_name,middle_name FROM users WHERE is_deleted=0 AND id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
  		if(empty($data)){
  			Modal("Invalid Record Selected");
  			redirect("users.php");
  			die;
  		}
	}
  if(empty($_GET['id'])){
    $employee=$con->myQuery("SELECT id,CONCAT(first_name,' ',last_name) as name FROM employees e WHERE is_deleted=0 and is_terminated=0 AND id NOT IN(SELECT employee_id FROM users WHERE is_deleted=0 AND employee_id=e.id)")->fetchAll(PDO::FETCH_ASSOC);    
  }else{
    $employee=$con->myQuery("SELECT id,CONCAT(first_name,' ',last_name) as name FROM employees WHERE is_deleted=0 and is_terminated=0")->fetchAll(PDO::FETCH_ASSOC);    
  }
  $user_type=$con->myQuery("SELECT id,description FROM user_type WHERE id!=4")->fetchAll(PDO::FETCH_ASSOC);

	makeHead("User Form");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            User Form
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
                  
		              	<form class='form-horizontal disable-submit' action='save_users.php' method="POST" name='frm_user' onsubmit='return validate(this)'>

                      <input type='hidden' name='get_id' value='<?php echo !empty($get_id)?$get_id:''; ?>'>
		              		<input type='hidden' name='id' value='<?php echo !empty($data)?$data['id']:''; ?>'>
                            <?php 
                              if ($get_id>0) {
                            ?>
                            <input type='hidden' name='emp_id' value='<?php  echo htmlspecialchars($data['employee_id'])?>'>          
                            <?php
                              }
                            ?> 
                        <div class="form-group">
                          <label for="name" class="col-sm-2 control-label">First Name *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" id=fname placeholder="First Name" name='fname' value='<?php echo !empty($data)?htmlspecialchars($data['first_name']):''; ?>' required>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="name" class="col-sm-2 control-label">Middle Name *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" id=mname placeholder="Middle Name" name='mname' value='<?php echo !empty($data)?htmlspecialchars($data['middle_name']):''; ?>' required>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="name" class="col-sm-2 control-label">Last Name *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" id="lname" placeholder="Last Name" name='lname' value='<?php echo !empty($data)?htmlspecialchars($data['last_name']):''; ?>' required>
                          </div>
                        </div>
  		              		<div class="form-group">
  	                      <label for="name" class="col-sm-2 control-label">User Name *</label>
  	                      <div class="col-sm-9">
  	                        <input type="text" class="form-control" id="username" placeholder="Username" name='username' value='<?php echo !empty($data)?htmlspecialchars($data['username']):''; ?>' required>
  	                      </div>
  		                  </div>

                        <div class="form-group">
                          <label for="name" class="col-sm-2 control-label">Password *</label>
                          <div class="col-sm-9">
                            <input type="password" class="form-control" id="password" placeholder="Password" name='password' value='<?php echo !empty($data)?htmlspecialchars(decryptIt($data['password'])):''; ?>' required>
                          </div>
                        </div>

                        <div class="form-group">
                          <label for="name" class="col-sm-2 control-label">Confirm Password *</label>
                          <div class="col-sm-9">
                            <input type="password" class="form-control" id="con_password" placeholder="Confirm Password" name='con_password' value='<?php echo !empty($data)?htmlspecialchars(decryptIt($data['password'])):''; ?>' required>
                          </div>
                        </div>

                        <div class='form-group'>
                          <label for="name" class="col-sm-2 control-label">User Type *</label>  
                            <div class='col-sm-9'>
                              <select class='form-control cbo' name='utype_id' data-placeholder="Select User Type" <?php echo!(empty($data))?"data-selected='".$data['user_type_id']."'":NULL ?> required>
                                <?php
                                  echo makeOptions($user_type);
                                ?>  
                              </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                          <label for="pas_q" class="col-sm-2 control-label">Secret Question *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" id="pass_q" placeholder="Secret Question" name='pass_q' value='<?php echo !empty($data)?htmlspecialchars($data['password_question']):''; ?>' required>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="pass_a" class="col-sm-2 control-label">Answer *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" id="pass_a" placeholder="Answer" name='pass_a' value='<?php echo !empty($data)?htmlspecialchars($data['password_answer']):''; ?>' required>
                          </div>
                        </div>

		                    <div class="form-group">
		                      <div class="col-sm-9 col-md-offset-2 text-center">
                            <button type='submit' class='btn btn-warning'>Save </button>
		                      	<a href='users.php' class='btn btn-default'>Cancel</a>
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

    function validate(frm) 
    {
      var js_new_pass = document.forms["frm_user"]["password"].value;
      var js_confirm_pass = document.forms["frm_user"]["con_password"].value;

      if (js_new_pass !== js_confirm_pass) 
      {
        alert("Retry Confirm Password.");
        return false;
      }
      if (checkPassword(js_new_pass)==false)
      {
        alert("Password should consist of atleast 1 Capital Letter and atleast 1 Number");
        return false;
      }
      return true;
    }

    function checkPassword(pwd)
    {
      var letterSmall = /[a-z]/;
      var letterCap = /[A-Z]/; 
      var number = /[0-9]/;
      var valid = number.test(pwd) && letterCap.test(pwd) && letterSmall.test(pwd); 
      return valid;
    }
</script>

<?php
	makeFoot();
?>