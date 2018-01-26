<?php
  require_once("support/config.php");
  if(!isLoggedIn()){
    toLogin();
    die();
  }


 //$val=$_SESSION[WEBAPP]['user']['employee_id'];
 //$disp=htmlspecialchars("{$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['middle_name']}");

  //var_dump($val." <br> ".$disp);
  //die();
    $query="";

    $employees=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name,' (',code,')') as employee_name FROM employees WHERE is_deleted=0 AND is_terminated=0 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);

     $departments=$con->myQuery("SELECT id,name FROM departments WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
  if(!empty($_GET['year'])){

    $inputs['year']=$_GET['year'];
//echo $inputs['year'];
//die();


    $query="SELECT
            e.id,
            e.code, 
            CONCAT(e.last_name,' ',e.first_name,' ',e.middle_name) AS employee_name,
            (SELECT d.name FROM departments d WHERE d.id=e.department_id) AS department,
            IFNULL((SELECT COUNT(eld.id) FROM employees_leaves_date eld INNER JOIN vw_employees_leave el ON el.id=eld.employees_leaves_id WHERE el.status='Approved' AND el.employee_id=e.id AND DATE_FORMAT(eld.date_leave,'%Y')=:year),0) AS annual_leave,
            (SELECT SUM(total_leave) FROM employees_available_leaves WHERE employee_id=:employees_id) AS total_entitlement,
            IFNULL((eal.balance_per_year),0) AS balance,
            l.name AS `leave`
          FROM employees_available_leaves eal INNER JOIN employees e ON eal.employee_id=e.id INNER JOIN `leaves` l ON l.id=eal.leave_id WHERE e.is_deleted=0 AND e.is_terminated=0 AND eal.is_converted=0 AND DATE_FORMAT(eal.date_added,'%Y')=:year ";

    $query="SELECT DISTINCT
            e.id,
            e.code, 
            CONCAT(e.last_name,' ',e.first_name,' ',e.middle_name) AS employee_name,
            (SELECT d.name FROM departments d WHERE d.id=e.department_id) AS department,
            IFNULL(
            (SELECT 
            COUNT(employees_leaves_date.date_leave)
            FROM employees_leaves 
            JOIN employees_leaves_date 
            ON employees_leaves.id = employees_leaves_date.employees_leaves_id
            WHERE DATE_FORMAT(employees_leaves_date.date_leave,'%Y')=:year AND employees_leaves.employee_id=e.id AND employees_leaves.leave_id=l.id),0) AS availed,
            (SELECT SUM(total_leave) FROM employees_available_leaves WHERE employee_id=e.id AND leave_id = eal.leave_id) AS total_entitlement,
            IFNULL((eal.balance_per_year),0) AS balance,
            l.name AS `leave`
            FROM employees e LEFT JOIN  employees_available_leaves eal ON eal.employee_id=e.id INNER JOIN `leaves` l ON l.id=eal.leave_id WHERE 'x'='x'";


    if(!empty($_GET['employees_id']) && $_GET['employees_id']!='NULL'){
      $inputs['employees_id']=$_GET['employees_id'];
      $query.=" AND e.id=:employees_id";
    }

   if(!empty($_GET['department_id']) && $_GET['department_id']!='NULL'){
    $inputs['department_id']=$_GET['department_id'];
     $query.=" AND e.department_id=:department_id";
   } else {
    unset($inputs['department_id']);
   }

   if (!empty($_GET['year'])) {
      $inputs['year']=$_GET['year'];
      $query.=" AND DATE_FORMAT(eal.date_added,'%Y')=:year";
   } else {
    unset ($inputs['year']);
   }
   // echo "<pre>";
     // print_r($query);
     
   // //   echo "</pre>";
     
    $data=$con->myQuery($query." ORDER BY eal.employee_id",$inputs)->fetchAll(PDO::FETCH_ASSOC);
    // var_dump($inputs);
    // die;

  }

  makeHead("Leave Entitlement");
?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Leave Entitlement
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-12'>
        <?php
          Alert();
        ?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                  <div class='col-md-12'>
                    <form class='form-horizontal' action='' method="GET">
                      <?php
                        if(AllowUser(array(1,4))):
						?>
            <div class="form-group">
                <label for="department_id" class="col-sm-3 control-label">Department </label>
                <div class="col-sm-9">
                  <select class='form-control cbo' name='department_id' onchange='getUsers()'  id='department_id' data-placeholder="Select Department" data-allow-clear="true" <?php echo !(empty($_GET))?"data-selected='".$_GET['department_id']."'":NULL ?> style='width:100%'>
                  <?php
                    echo makeOptions($departments);
                  ?>
                  </select>
                </div>
            </div>
            <div class="form-group">
                <label for="employees_id" class="col-sm-3 control-label">Employee </label>
                <div class="col-sm-9">
                  <select class='form-control cbo' id='employees_id' name='employees_id' data-allow-clear="true" data-placeholder="All Employees" value='<?php echo !(empty($_GET))?"data-selected='".$_GET['employees_id']."'":NULL ?>' <?php echo !(empty($_GET))?"data-selected='".$_GET['employees_id']."'":NULL ?> style='width:100%'>
                  <?php
                    echo makeOptions($employees);
                  ?>
                  </select>
                </div>
            </div>
						<?php
						else:
						?>
						<input type='hidden' name='employees_id' value='<?php echo $_SESSION[WEBAPP]['user']['employee_id'];?>'>
						<?php
						endif;
						?>
                        <div class="form-group">
                          <label for="year" class="col-sm-3 control-label">Year *</label>
                          <div class="col-sm-9">
                            <select class='form-control cbo' name='year' data-placeholder='Year' style='width:100%'>
                            <?php
                                for ($current_year=date("Y"); $current_year>1999; $current_year--) {
                                  echo "<option value='".$current_year."'>" . $current_year . "</option> ";
                                }
                            ?>
                            </select>
                          </div>
                      </div>

                        <div class="form-group">
                          <div class="col-sm-9 col-md-offset-3 text-center">
                            <button type='submit' class='btn btn-warning'>Filter </button>
                            <a href='leave_entitlement_reports.php' class='btn btn-default'>Cancel</a>
                          </div>
                        </div>
                    </form>
                  </div>
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
              <?php
                if(!empty($_GET)):
              ?>
              <div class="box box-solid">
                <div class="box-body">
                  <div class="row">
                  <div class='col-md-12'>
                    <table class='table table-bordered table-striped' id='ResultTable'>
                      <thead>
                        <th class='text-center'>Employee Code</th>
                        <th class='text-center'>Employee Name</th>
                        <th class='text-center'>Department</th>
                        <th class='text-center'>Availed</th>
                        <th class='text-center'>Entitlement</th>
                        <th class='text-center'>Balance</th>
                        <th class='text-center'>Leave Name</th>
                      </thead>
                      <tbody>
                        <?php
                          foreach ($data as $row):
                        ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['code']) ?></td>
                            <td><?php echo htmlspecialchars($row['employee_name']) ?></td>
                            <td><?php echo htmlspecialchars($row['department']) ?></td>
                            <td><?php echo $row['total_entitlement'] - $row['balance']; //htmlspecialchars($row['annual_leave']) ?></td>
                            <td><?php echo htmlspecialchars($row['total_entitlement']) //intval($row['annual_leave'])+intval($row['balance']); ?></td>
                            <td><?php echo htmlspecialchars($row['balance']) ?></td>
                            <td><?php echo htmlspecialchars($row['leave']) ?></td>
                          </tr>
                        <?php
                          endforeach;
                        ?>
                      </tbody>
                    </table>
                  </div>
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
              <?php
                endif;
              ?>
            </div>
          </div><!-- /.row -->
        </section><!-- /.content -->
  </div>
<?php
  if(!empty($_GET)):
?>
<script type="text/javascript">
  $(function () {
        $('#ResultTable').DataTable({
          "scrollX": true,
          searching:false,
          lengthChange:false
          <?php if(!empty($data)):?>
           ,dom: 'Bfrtip',
                buttons: [
                    {
                        extend:"excel",
                        text:"<span class='fa fa-download'></span> Download as Excel File ",
                        extension:".xls"
                    }
                    ]
          <?php endif; ?>
        });
      });
</script>
<?php
  endif;
?>
<script>
function getUsers() {
    // console.log($("#departments").val());
    $("select[name='employees_id']").val(null).trigger("change");
    $("select[name='employees_id']").load("ajax/cb_users.php?d_id="+$("select[name='department_id']").val());
}
</script>
<?php
  makeFoot();
?>
