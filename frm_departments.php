<?php
    require_once("support/config.php");
    if (!isLoggedIn()) {
        toLogin();
        die();
    }

    if (!AllowUser(array(1,4))) {
        redirect("index.php");
    }

    $data="";
    if (!empty($_GET['id'])) {
        $data=$con->myQuery("SELECT id,name,description,parent_id,approver_id,payroll_group_id as company_id FROM departments dpt WHERE is_deleted=0 AND id=? LIMIT 1", array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        if (empty($data)) {
            Modal("Invalid Record Selected");
            redirect("departments.php");
            die;
        } else {
          $paygroup=$con->myQuery("SELECT payroll_group_id,name FROM payroll_groups WHERE payroll_group_id=? LIMIT 1", array($data['company_id']))->fetch(PDO::FETCH_ASSOC);
        }
    }

  $parent_dept=$con->myQuery("SELECT id,name FROM departments WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
  $approver=$con->myQuery("SELECT id,CONCAT(first_name,' ',last_name) as name FROM employees WHERE is_deleted=0 and is_terminated=0")->fetchAll(PDO::FETCH_ASSOC);

    makeHead("Departments Form");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Departments Form
          </h1>
          <br/>
          <a href='departments.php' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Department list</a>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-12'>
				<?php
                    Alert();
                ?>
              <div class="nav-tabs-custom">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                  <li role="presentation" class="active"><a href="#department_form" aria-controls="department_form" role="tab" data-toggle="tab">Department Information</a></li>
                  <li role="presentation" class="<?php echo empty($data['id'])?"disabled":'' ?>"><a <?php echo empty($data['id'])?"onclick=\"alert('Please Save department Information.');return false;\"":'' ?> href="approval_flow.php<?php echo !empty($data['id'])?"?dep_id={$data['id']}":'' ?>" disabled>Approval Flow</a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                  <div role="tabpanel" class="tab-pane active" id="department_form">
                    <div class="row">
                      <div class='col-md-12'>
                        <form class='form-horizontal' action='save_departments.php' method="POST">
                          <input type='hidden' name='id' value='<?php echo !empty($data)?$data['id']:''; ?>'>

                          <div class="form-group">
                              <label for="name" class="col-sm-2 control-label">Department Name *</label>
                              <div class="col-sm-9">
                                <input type="text" class="form-control" id="name" placeholder="Department Code Name" name='name' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                              </div>
                          </div>

                          <div class="form-group">
                              <label for="name" class="col-sm-2 control-label">Description *</label>
                              <div class="col-sm-9">
                                <input type="text" class="form-control" id="description" placeholder="Description" name='description' value='<?php echo !empty($data)?htmlspecialchars($data['description']):''; ?>' required>
                              </div>
                          </div>

                          <div class='form-group'>
                              <label for="parent_dept" class="col-sm-2 control-label"> Pay Group*</label>
                              <div class='col-sm-9 '>
                                        <select class='form-control cbo-paygroup-id' name='paygroup_id' <?php echo!(empty($data))?"data-selected='".$data['company_id']."'":null ?> >
                                        <?php
                                        if (!empty($paygroup)) :
                                        ?>
                                            <option value='<?php echo $paygroup['payroll_group_id']?>'><?php echo htmlspecialchars($paygroup['name'])?></option>
                                        <?php
                                        endif;
                                        ?>
                                        </select>
                              </div>
                          </div>

                          <div class='form-group'>
                              <label for="parent_dept" class="col-sm-2 control-label"> Parent Department</label>
                              <div class='col-sm-9 '>
                                        <select class='form-control cbo' name='parent_id' data-placeholder="Select Parent Department" <?php echo!(empty($data))?"data-selected='".$data['parent_id']."'":null ?> data-allow-clear='true'>
                                            <?php
                                                echo makeOptions($parent_dept);
                                            ?>
                                        </select>
                              </div>
                          </div>

                          <!-- <div class='form-group'>
                              <label for="approver" class="col-sm-2 control-label"> Department Approver *</label>
                              <div class='col-sm-9 '>
                                        <select class='form-control cbo' name='approver_id' data-placeholder="Select Department Approver" <?php echo!(empty($data))?"data-selected='".$data['approver_id']."'":null ?> required>
                                            <?php
                                                echo makeOptions($approver);
                                            ?>
                                        </select>
                              </div>
                          </div> -->

                            <div class="form-group">
                              <div class="col-sm-10 col-md-offset-2 text-center">
                                <a href='departments.php' class='btn btn-default' onclick="return confirm('<?php echo empty($data)?"Cancel creation of new department?":"Candel modification of department?" ?>')">Cancel</a>
                                <button type='submit' class='btn btn-warning'>Save </button>
                              </div>
                            </div>
                        </form> 
                      </div>
                      </div><!-- /.row -->
                  </div>
                </div>
              </div>
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