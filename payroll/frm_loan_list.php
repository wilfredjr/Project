<?php
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
// if(!AllowUser(array(1,2))){
//         redirect("index.php");
// }
 // if(!hasAccess(20)){
//   redirect("index.php");
// }

if(!empty($_GET['loan_id'])){
    $loan_info=$con->myQuery("SELECT * FROM loans 
     WHERE loan_id =? and is_deleted=0",array($_GET['loan_id']))->fetch(PDO::FETCH_ASSOC);
  
}

makeHead("Loan Type Form",1);



require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
          <!-- <h1>
            Create New User
          </h1> -->
          <?php
          if(!empty($_GET['loan_id'])){
            ?>
            <h1 class="text-red">Update Loan Type</h1>
            <?php
          }
          else{                    
            ?>
            <h1 class="text-red">Create New Loan Type</h1>                
            <?php
          }
          ?>
          <!-- <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="view_companies.php" ><i class="fa fa-sticky-note"></i> Company Management</a></li>
            <li class="active">Company Profile Form  </li>
          </ol> -->

        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-12 '>
              <?php
              Alert();
              ?>
              <div class="box box-danger">
                <div class="box-body">
                  <div class="row">
                   <div class='col-sm-12 col-md-8 col-md-offset-2'>
                   <br/>
                    <form class='form-horizontal' method='POST' action='save_loan_type.php'>
                      <input type='hidden' name='loan_id' value='<?php echo !empty($loan_info)?htmlspecialchars($loan_info['loan_id']):''; ?>'>
                      <div class='form-group'>
                      <label class='col-sm-12 col-md-3 control-label'>Loan Name<span class='text-red'>*</span>: </label>
                        <div class='col-sm-12 col-md-9'>
                          <input type="text" class="form-control" name="loan_name" placeholder="Enter Loan Name" value='<?php echo !empty($loan_info)?htmlspecialchars($loan_info['loan_name']):''; ?>'  required>
                        </div>

                      </div>  

                 


                      

                     <div class='form-group'>
                      <div class='col-sm-12 col-md-9 col-md-offset-3 '>
                        <button type='submit' class='btn btn-danger btn-flat'> <!-- <span class='fa fa-save'></span> --> Save</button>
                        <a href='view_loan_list.php' class='btn btn-flat btn-default'>Cancel</a>
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
    <?php
    makeFoot(WEBAPP,1);
    ?>