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

  $employees=$con->myQuery("SELECT id,pbr.desc FROM project_bug_rate pbr")->fetchAll(PDO::FETCH_ASSOC);
    $project_name=$con->myQuery("SELECT id,name  FROM projects WHERE is_deleted=0 AND project_status_id=2")->fetchAll(PDO::FETCH_ASSOC);
    $project_phase=$con->myQuery("SELECT id,name  FROM project_bug_phase")->fetchAll(PDO::FETCH_ASSOC);
    $status1=$con->myQuery("SELECT id,status_name  FROM project_status")->fetchAll(PDO::FETCH_ASSOC);
  $filter_by="";
  if(!empty($_GET['date_from']) && !empty($_GET['date_to'])){

    $date_from=date_format(date_create($_GET['date_from']),'Y-m-d');
    $date_to=date_format(date_create($_GET['date_to']),'Y-m-d');
    $inputs['date_from']=$date_from;
    $inputs['date_to']=$date_to;


    $query="SELECT pbl.id,pbl.name as bug_name,pbl.description,p.name AS project_name, pbl.project_id,pbl.project_status_id,pbp.name as phase_name, ps.status_name, pbl.date_filed,pbl.date_start,pbl.date_end,pbr.desc as bug_rate,pbl.admin_id,pbl.bug_phase_id,pbl.bug_rate_id,pbl.date_finished,
(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e WHERE e.id=pbl.manager_id) AS manager,
(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e WHERE e.id=pbl.ba_test) AS team_lead_ba,
(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e WHERE e.id=pbl.dev_control) AS team_lead_dev
FROM project_bug_list pbl JOIN project_status ps ON pbl.project_status_id=ps.id JOIN projects p ON pbl.project_id=p.id
JOIN project_bug_phase pbp ON pbl.bug_phase_id=pbp.id JOIN project_bug_rate pbr ON pbr.id=pbl.bug_rate_id           
WHERE (pbl.date_start<=:date_from) OR (pbl.date_end<=:date_to)";

    $order="";

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
      $order=" ORDER BY pbl.project_status_id ";
    }

    // if(!empty($_GET['approved_employee_id']) && AllowUser(array(1,4))){
    //   $inputs['approved_employee_id']=$_GET['approved_employee_id'];
    //   $query.=" AND (eo.supervisor_id=:approved_employee_id || eo.final_approver_id=:approved_employee_id) ";
    // }
     if(!empty($_GET['project_id']) && $_GET['project_id']!='NULL')
    {
      $inputs['project_id']=$_GET['project_id'];
      $query.=" AND pbl.project_id=:project_id ";
    }
    else{
      unset($inputs['project_id']);
    }

    $query.=$order;
    // echo $query;
    // var_dump($inputs);
  //    var_dump($val." <br> ".$disp."<br>".$inputs['employees_id']);
 // die();

    $data=$con->myQuery($query,$inputs)->fetchAll(PDO::FETCH_ASSOC);

  }

	makeHead("Bug Report");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
           Bug Report
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
		              	<form class='form-horizontal' action='' method="GET" onsubmit='return validate(this)'>
                      <div class="form-group">
                          <label for="project_id" class="col-sm-3 control-label">Project </label>
                          <div class="col-sm-6">
                            <select class='form-control cbo' name='project_id' data-allow-clear='true' data-placeholder="All Projects" <?php echo !(empty($_GET))?"data-selected='".$_GET['project_id']."'":NULL ?> >
                            <?php
                              echo makeOptions($project_name,"All Projects");
                            ?>
                            </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="phase_id" class="col-sm-3 control-label">Bug Phase </label>
                          <div class="col-sm-6">
                            <select class='form-control cbo' name='phase_id' data-allow-clear='true' data-placeholder="All Project Phases" <?php echo !(empty($_GET))?"data-selected='".$_GET['phase_id']."'":NULL ?> >
                            <?php
                              echo makeOptions($project_phase,"All Project Phases");
                            ?>
                            </select>
                          </div>
                      </div>
		              		<div class="form-group">
		                      <label for="employees_id" class="col-sm-3 control-label">Bug Rating </label>
		                      <div class="col-sm-6">
                            <select class='form-control cbo' name='bug_rate_id' data-allow-clear='true' data-placeholder="All Employees" <?php echo !(empty($_GET))?"data-selected='".$_GET['bug_rate_id']."'":NULL ?> >
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
                            <a href='bug_reports.php' class='btn btn-default'>Clear</a>
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
                         <th class='text-center date-td'>Date Filed</th>
                          <th class='text-center'>Project</th>
                          <th class='text-center'>Bug Name</th>
                          <th class='text-center'>Current Phase</th>
                          <th class='text-center date-td'>Date Start</th>
                          <th class='text-center date-td'>Date End</th>
                          <th class='text-center'>Bug Rating</th>
                          <th class='text-center'>Description</th>
                          <th class='text-center'>Status</th>
                          <th class='text-center'>Date Finished</th>
                          <th class='text-center'>Manager</th>
                          <th class='text-center'>Bug Control</th>
                          <th class='text-center'>Bug Assessment</th>
                    
                      </thead>
                      <tbody>
                        <?php
                          foreach ($data as $row):
                        ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['date_filed']) ?></td>
														<td><?php echo htmlspecialchars($row['project_name']) ?></td>
                            <td><?php echo htmlspecialchars($row['bug_name']) ?></td>
                            <td><?php echo htmlspecialchars($row['phase_name']) ?></td>
                            <td><?php echo htmlspecialchars($row['date_start']) ?></td>
                            <td><?php echo htmlspecialchars($row['date_end']) ?></td>
                            <td><?php echo htmlspecialchars($row['bug_rate']) ?></td>
                            <td><?php echo htmlspecialchars($row['description']) ?></td>
														<td><?php echo htmlspecialchars($row['status_name']) ?></td>
                            <td><?php 
                            if($row['date_finished']=="0000-00-00"){
                              echo "----------";
                            }else{
                            echo htmlspecialchars($row['date_finished']);}
                            ?></td>
                            <td><?php echo htmlspecialchars($row['manager']) ?></td>
                            <td><?php echo htmlspecialchars($row['team_lead_dev']) ?></td>
                            <td><?php echo htmlspecialchars($row['team_lead_ba']) ?></td>
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
