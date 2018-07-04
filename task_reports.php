<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}

//    if(!AllowUser(array(1,4))){
//        redirect("index.php");
//    }

 $val=$_SESSION[WEBAPP]['user']['employee_id'];
  $disp=htmlspecialchars("{$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['middle_name']}");

  $employees=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name) as employee_name FROM employees WHERE is_deleted=0 AND is_terminated=0 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);
    $project_name=$con->myQuery("SELECT id,name  FROM projects WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
    $project_phase=$con->myQuery("SELECT id,phase_name  FROM project_phases")->fetchAll(PDO::FETCH_ASSOC);
    $status1=$con->myQuery("SELECT id,status_name  FROM project_status")->fetchAll(PDO::FETCH_ASSOC);
  $filter_by="";
  if(!empty($_GET['date_from']) && !empty($_GET['date_to'])){
    $date_from=date_format(date_create($_GET['date_from']),'Y-m-d');
    $date_to=date_format(date_create($_GET['date_to']),'Y-m-d');
    $inputs['date_from']=$date_from;
    $inputs['date_to']=$date_to;


    $query="SELECT ptl.id,ptl.worked_done,ptl.work_done, p.name,p.id,ptl.status_id,ps.status_name,pp.phase_name,ptl.manager_id,ptl.employee_id,ptl.date_finished,
(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e WHERE e.id=ptl.manager_id) AS manager,ptl.date_start,ptl.date_end,
(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e WHERE e.id=ptl.employee_id) AS employee,ptl.project_phase_id,ptl.project_id
FROM project_task_list ptl
JOIN projects p ON p.id=ptl.project_id JOIN project_phases pp ON pp.id=ptl.project_phase_id
JOIN project_status ps ON ps.id=ptl.status_id 
           WHERE (ptl.date_start>=:date_from) OR (ptl.date_end<=:date_to)";

    $order="";


         if(!empty($_GET['project_id']) && $_GET['project_id']!='NULL')
    {
      $inputs['project_id']=$_GET['project_id'];
      $query.=" AND ptl.project_id=:project_id ";
    }
    else{
      unset($inputs['project_id']);
    }

    if(!empty($_GET['phase_id']) && $_GET['phase_id']!='NULL')
    {
      $inputs['phase_id']=$_GET['phase_id'];
      $query.=" AND ptl.project_phase_id=:phase_id ";
    }
    else{
      unset($inputs['phase_id']);
    }

     if(!empty($_GET['status']) && $_GET['status']!='NULL')
    {
      $inputs['status']=$_GET['status'];
      $query.=" AND ptl.status_id=:status";
    }
    else{
      unset($inputs['status']);
    }

    if(!empty($_GET['employees_id']) && $_GET['employees_id']!='NULL')
    {
      $inputs['employees_id']=$_GET['employees_id'];
      $query.=" AND ptl.employee_id=:employees_id ";
      $order="ORDER BY ptl.employee_id";
    }elseif (!empty($_GET['department_id']) && $_GET['employees_id']!='NULL') {
        $query.=" AND eo.department_id=:department_id";
        unset($inputs['employees_id']);
        $inputs['department_id']=$_GET['department_id'];
      }
    else
    {
      unset($inputs['employees_id']);
      $order=" ORDER BY ptl.status_id ";
    }

    // if(!empty($_GET['approved_employee_id']) && AllowUser(array(1,4))){
    //   $inputs['approved_employee_id']=$_GET['approved_employee_id'];
    //   $query.=" AND (eo.supervisor_id=:approved_employee_id || eo.final_approver_id=:approved_employee_id) ";
    // }

    $query.=$order;
    // echo $query;
    // var_dump($inputs);
  //    var_dump($val." <br> ".$disp."<br>".$inputs['employees_id']);
 // die();

    $data=$con->myQuery($query,$inputs)->fetchAll(PDO::FETCH_ASSOC);

  }

	makeHead("Employee Tasks Report");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
           Employee Tasks Report
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
		              	<form class='form-horizontal' action='task_reports.php' method="GET" onsubmit='return validate(this)'>
                      <div class="form-group">
                          <label for="project_id" class="col-sm-3 control-label">Project </label>
                          <div class="col-sm-6">
                            <select class='form-control cbo' name='project_id' id='project_id' data-allow-clear='true' data-placeholder="All Projects" <?php echo !(empty($_GET))?"data-selected='".$_GET['project_id']."'":NULL ?> >
                            <?php
                              echo makeOptions($project_name,"All Projects");
                            ?>
                            </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="phase_id" class="col-sm-3 control-label">Project Phase </label>
                          <div class="col-sm-6">
                            <select class='form-control cbo' name='phase_id' data-allow-clear='true' data-placeholder="All Project Phases" <?php echo !(empty($_GET))?"data-selected='".$_GET['phase_id']."'":NULL ?> >
                            <?php
                              echo makeOptions($project_phase,"All Project Phases");
                            ?>
                            </select>
                          </div>
                      </div>
		              		<div class="form-group">
		                      <label for="employees_id" class="col-sm-3 control-label">Employee </label>
		                      <div class="col-sm-6">
                            <select class='form-control cbo' name='employees_id' data-allow-clear='true' data-placeholder="All Employees" <?php echo !(empty($_GET))?"data-selected='".$_GET['employees_id']."'":NULL ?> >
                            <?php
                              echo makeOptions($employees,"All Employees");
                            ?>
                            </select>
		                      </div>
		                  </div>
                      <div class='form-group'>
                        <label for="date_from" class="col-sm-3 control-label">Status</label>
                          <div class="col-sm-6">
                            <select class='form-control cbo' name='status' data-allow-clear='true' data-placeholder="Filter by Status" <?php echo !(empty($_GET))?"data-selected='".$_GET['status']."'":NULL ?> >
                              <?php
                              echo makeOptions($status1);
                            ?>
                            </select>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="date_from" class="col-sm-3 control-label">Date Start *</label>
                          <div class="col-sm-6">
                            <input type="text" class="form-control date_picker" id="date_from"  name='date_from' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_from']):''; ?>' required>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="date_to" class="col-sm-3 control-label">Date End *</label>
                          <div class="col-sm-6">
                            <input type="text" class="form-control date_picker" id="date_to"  name='date_to' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_to']):''; ?>' required>
                          </div>
                      </div>

		                    <div class="form-group">
		                      <div class="col-sm-6 col-md-offset-3 text-center">
		                        <button type='submit' class='btn btn-warning'>Filter </button>
                            <a href='task_reports.php' class='btn btn-default'>Clear</a>
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
                        <th class='text-center'>Project</th>
                        <th class='text-center'>Project Phase</th>
												<th class='text-center'>Employee</th>
                        <th class='text-center date-td'>Date Start</th>
                        <th class='text-center date-td'>Date End</th>
                        <th class='text-center'>Status</th>
                        <th class='text-center'>Work to Do</th>
                        <th class='text-center'>Work Done</th>
                        <th class='text-center'>Date Finished</th>
                        <th class='text-center'>Manager</th>
                        
                      </thead>
                      <tbody>
                        <?php
                          foreach ($data as $row):
                        ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['name']) ?></td>
														<td><?php echo htmlspecialchars($row['phase_name']) ?></td>
                            <td><?php echo htmlspecialchars($row['employee']) ?></td>
                            <td><?php echo htmlspecialchars($row['date_start']) ?></td>
                            <td><?php echo htmlspecialchars($row['date_end']) ?></td>
                            <td><?php echo htmlspecialchars($row['status_name']) ?></td>
                            <td><?php echo htmlspecialchars($row['worked_done']) ?></td>
														<td><?php echo htmlspecialchars($row['work_done']) ?></td>
                            <td><?php 
                            if($row['date_finished']=="0000-00-00"){
                              echo "----------";
                            }else{
                            echo htmlspecialchars($row['date_finished']);}
                            ?></td>
                            <td><?php echo htmlspecialchars($row['manager']) ?></td>
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
<script type="text/javascript">
  function getUsers() {
        // console.log($("#departments").val());
        $("select[name='employees_id']").val(null).trigger("change");
        $("select[name='employees_id']").load("ajax/cb_users.php?d_id="+$("select[name='department_id']").val());
    }
   function validate(frm) {
    if(Date.parse($("#date_from").val()) > Date.parse($("#date_to").val())){
      alert("Date Start cannot be greater than Date End.");
      return false;
    }

    return true;
  }
</script>
<?php
	makeFoot();
?>
