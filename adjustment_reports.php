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

  $employees=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name,' (',code,')') as employee_name FROM employees WHERE is_deleted=0 AND is_terminated=0 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);
  $departments=$con->myQuery("SELECT id,name FROM departments WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
$filter_by="";
  if(!empty($_GET['date_from']) && !empty($_GET['date_to'])){
    $date_from=date_create($_GET['date_from']);
    $date_to=date_create($_GET['date_to']);
    $date_from=date_format($date_from,'Y-m-d');
    $date_to=date_format($date_to,'Y-m-d');
    $inputs['date_from']=$date_from;
    $inputs['date_to']=$date_to;

    if(!empty($_GET['status'])){
      $inputs['status']=$_GET['status'];
    }

    $query="SELECT
            ea.id,
            ea.employee_id as employees_id,
              e.code,
              CONCAT(e.first_name,' ',e.last_name) AS employee_name,
              ea.orig_in_time,
              ea.orig_out_time,
              ea.adj_in_time,
              ea.adj_out_time,
              ea.adjustment_reason,
              ea.status,
              ea.date_filed,
							departments.name as department,
              step_name
            FROM vw_employees_adjustments ea
            INNER JOIN employees e
              ON e.id=ea.employee_id INNER JOIN departments on departments.id=e.department_id
           WHERE DATE_FORMAT(ea.adj_in_time,'%Y-%m-%d') BETWEEN :date_from AND :date_to";

    $order="";

    if(!empty($_GET['employees_id']) && $_GET['employees_id']!='NULL'){
      $inputs['employees_id']=$_GET['employees_id'];
      $query.=" AND ea.employee_id=:employees_id ";
      $order=" ORDER BY ea.employee_id";
    }
    elseif (!empty($_GET['department_id']) && $_GET['employees_id']!='NULL') {
        $query.=" AND e.department_id=:department_id";
        unset($inputs['employees_id']);
        $inputs['department_id']=$_GET['department_id'];
      }
    else
    {
      unset($inputs['employee_id']);

      $order=" ORDER BY ea.adj_in_time ";
    }

    if(!empty($_GET['approved_employee_id']) && AllowUser(array(1,4))){
      $inputs['approved_employee_id']=$_GET['approved_employee_id'];
      $query.=" AND :approved_employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = ea.step_id) ";
    }

    if(AllowUser(array(2,3))){
      $filter_by=$_GET['filter_report'];
      if($filter_by=="1"){
          $inputs['employees_id']=$val;
          $query.=" AND :employees_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = ea.step_id) ";
      }
      else{
        $query.=" AND ea.employee_id=:employees_id ";
        $inputs['employees_id']=$val;
      }
    }

    if(!empty($inputs['status'])){
      $query.=" AND ea.request_status_id = :status ";
      $status=$con->myQuery("SELECT id,name FROM request_status WHERE id =?", array($inputs['status']))->fetch(PDO::FETCH_ASSOC);
    }
    else{
      unset($inputs['status']);
    }



    $query.=$order;
      //var_dump($val." <br> ".$disp."<br>".$inputs['employees_id']);
  //die();
    

    $data=$con->myQuery($query,$inputs)->fetchAll(PDO::FETCH_ASSOC);
  }

	makeHead("Attendance Adjustment Report");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Attendance Adjustment Report
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-12'>
				<?php
					Alert();
          
      // echo "<pre>";
      // print_r($inputs);
      // echo "</pre>";
				?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                	<div class='col-md-12'>
		              	<form class='form-horizontal' action='' method="GET" onsubmit='return validate(this)'>
		              <?php
                        if(AllowUser(array(1,4))):
						?>
                      <div class="form-group">
                          <label for="department_id" class="col-sm-3 control-label">Department </label>
                          <div class="col-sm-9">
                            <select class='form-control cbo' name='department_id' onchange='getUsers()' id='department_id' data-placeholder="Select Department" data-allow-clear="true" <?php echo !(empty($_GET))?"data-selected='".$_GET['department_id']."'":NULL ?> style='width:100%'>
                            <?php
                              echo makeOptions($departments,"Select Department");
                            ?>
                            </select>
                          </div>
                      </div>
		              		<div class="form-group">
		                      <label for="employees_id" class="col-sm-3 control-label">Employee </label>
		                      <div class="col-sm-9">
                            <select class='form-control cbo' name='employees_id' data-allow-clear='true' data-placeholder="All Employees" <?php echo !(empty($_GET))?"data-selected='".$_GET['employees_id']."'":NULL ?> style='width:100%'>
                            <?php
                              echo makeOptions($employees,"All Employees");
                            ?>
                            </select>
		                      </div>
		                  </div>
                      <div class="form-group">
                          <label for="approved_employee_id" class="col-sm-3 control-label">Approver </label>
                          <div class="col-sm-9">
                            <select class='form-control cbo' name='approved_employee_id'  data-allow-clear='true' data-placeholder="Filter by Approver" <?php echo !(empty($_GET))?"data-selected='".$_GET['approved_employee_id']."'":NULL ?> style='width:100%'>
                            <?php
                              echo makeOptions($employees,"Filter by Approver");
                            ?>
                            </select>
                          </div>
                      </div>
						<?php
						else:
						?>
						        <div class="form-group">
                          <label for="employees_id" class="col-sm-3 control-label">Filter Reports</label>
                          <div class="col-sm-9">
                            <div class="radio">
                              <label>
                                <input type='radio' name='filter_report' value='0' <?php echo $filter_by=='0'||empty($filter_by)?'checked="checked"':''?>/>My Reports
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input type='radio' name='filter_report' value='1' <?php echo $filter_by=='1' && !empty($filter_by)?'checked="checked"':''?>/>Approved By Me
                              </label>
                            </div>
                          </div>
                      </div>
						<?php
						endif;
						?>
                      <div class='form-group'>
                        <label for="date_from" class="col-sm-3 control-label">Status</label>
                          <div class="col-sm-9">
                            <select class='form-control cbo-request-status-id' name='status' data-allow-clear='true' data-placeholder="Filter by Status" <?php echo !(empty($_GET))?"data-selected='".$_GET['status']."'":NULL ?> style='width:100%'>
                                <?php
                                if (!empty($status)) {
                                ?>
                                    <option value='<?php echo $status['id']; ?>'><?php echo $status['name']; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="date_from" class="col-sm-3 control-label">Date Start *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="date_from"  name='date_from' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_from']):''; ?>' required>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="date_to" class="col-sm-3 control-label">Date End *</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control date_picker" id="date_to"  name='date_to' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_to']):''; ?>' required>
                          </div>
                      </div>

		                    <div class="form-group">
		                      <div class="col-sm-9 col-md-offset-3 text-center">
		                        <button type='submit' class='btn btn-warning'>Filter </button>
                            <a href='adjustment_reports.php' class='btn btn-default'>Cancel</a>
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
                        <th class='text-center date-td'>Date Filed</th>
                        <th class='text-center date-time-td'>Original Time-in</th>
                        <th class='text-center date-time-td'>Original Time-out</th>
                        <th class='text-center date-time-td'>Adjusted Time-in</th>
                        <th class='text-center date-time-td'>Adjusted Time-out</th>
                        <th class='text-center'>Note</th>
                        <th class='text-center'>Status</th>
                        <th class='text-center'>Step</th>
                      </thead>
                      <tbody>
                        <?php
                          foreach ($data as $row):
                        ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['code']) ?></td>
                            <td><?php echo htmlspecialchars($row['employee_name']) ?></td>
														<td><?php echo htmlspecialchars($row['department']) ?></td>
                            <td><?php echo htmlspecialchars(date_format(date_create($row['date_filed']),DATE_FORMAT_PHP)) ?></td>
                            <td><?php echo htmlspecialchars(date_format(date_create($row['orig_in_time']),DATE_FORMAT_PHP." ".TIME_FORMAT_PHP)) ?></td>
                            <td><?php echo htmlspecialchars(date_format(date_create($row['orig_out_time']),DATE_FORMAT_PHP." ".TIME_FORMAT_PHP)) ?></td>
                            <td><?php echo htmlspecialchars(date_format(date_create($row['adj_in_time']),DATE_FORMAT_PHP." ".TIME_FORMAT_PHP)) ?></td>
                            <td><?php echo htmlspecialchars(date_format(date_create($row['adj_out_time']),DATE_FORMAT_PHP." ".TIME_FORMAT_PHP)) ?></td>
                            <td><?php echo htmlspecialchars($row['adjustment_reason']) ?></td>
                            <td><?php echo htmlspecialchars($row['status']) ?></td>
                            <td><?php echo htmlspecialchars($row['step_name']) ?></td>
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
    else if(Date.parse($("#date_from").val()) == Date.parse($("#date_to").val())){
      alert("Date End should be greater than Date Start.")
      return false;
    }

    return true;
  }
</script>
<?php
	makeFoot();
?>
