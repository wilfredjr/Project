<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}



  $employees=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name,' (',code,')') as employee_name FROM employees WHERE is_deleted=0 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);
  $departments=$con->myQuery("SELECT id,name FROM departments WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
  if(!empty($_GET['date_from']) && !empty($_GET['date_to'])){
    $date_from=date_create($_GET['date_from']);
    $date_to=date_create($_GET['date_to']);
    $inputs['date_from']=date_format($date_from,'Y-m-d')." 00:00:00";
    $inputs['date_to']=date_format($date_to,'Y-m-d')." 23:59:59";
    $date_from=date_format($date_from,DATE_FORMAT_PHP);
    $date_to=date_format($date_to,DATE_FORMAT_PHP);
    // $date_start=date_create($_GET['date_start']);

    $query="SELECT e.code,CONCAT(e.last_name,', ',e.first_name,' ',middle_name)as employee_name,departments.name,DATE_FORMAT(in_time,'".DATE_FORMAT_SQL." %H:%i:%s') as in_time,DATE_FORMAT(out_time,'".DATE_FORMAT_SQL." %H:%i:%s') as out_time,note FROM attendance a JOIN employees e ON e.id=a.employees_id JOIN departments ON e.department_id=departments.id WHERE in_time BETWEEN :date_from AND :date_to";
    if(AllowUser(array(1,4))){

      if(!empty($_GET['employees_id']) && $_GET['employees_id']!='NULL' ){

        $query.=" AND employees_id=:employees_id";
        $inputs['employees_id']=$_GET['employees_id'];
      }
      elseif (!empty($_GET['department_id']) && $_GET['employees_id']!='NULL') {
        $query.=" AND department_id=:department_id";
        $inputs['department_id']=$_GET['department_id'];
      }
    }
    else{
        $query.=" AND employees_id=:employees_id";
        $inputs['employees_id']=$_SESSION[WEBAPP]['user']['employee_id'];
    }


    $data=$con->myQuery($query,$inputs)->fetchAll(PDO::FETCH_ASSOC);

  }

  makeHead("DTR Report");
?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");

?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            DTR Report
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
                      <?php
                        if(AllowUser(array(1,4))):
                      ?>
                      <div class="form-group">
                          <label for="department_id" class="col-sm-3 control-label">Department *</label>
                          <div class="col-sm-9">
                            <select class='form-control cbo' name='department_id' onchange='getUsers()' id='department_id' data-placeholder="Select Department" data-allow-clear="true" <?php echo !(empty($_GET))?"data-selected='".$_GET['department_id']."'":NULL ?> style='width:100%'>
                            <?php
                              echo makeOptions($departments,"Select Department");
                            ?>
                            </select>
                          </div>
                      </div>
		              		<div class="form-group">
		                      <label for="employees_id" class="col-sm-3 control-label">Employee *</label>
		                      <div class="col-sm-9">
                            <select class='form-control cbo' name='employees_id' data-allow-clear="true" data-placeholder="All Employees" <?php echo !(empty($_GET))?"data-selected='".$_GET['employees_id']."'":NULL ?> style='width:100%'>
                            <?php
                              echo makeOptions($employees,"All Employees");
                            ?>
                            </select>
		                      </div>
		                  </div>
                      <?php
                        endif;
                      ?>
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
                            <a href='dtr_report.php' class='btn btn-default'>Cancel</a>
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
                        <th class='text-center date-time-td'>In Time</th>
                        <th class='text-center  date-time-td'>Out Time</th>
                        <th class='text-center'>Note</th>
                      </thead>
                      <tbody>
                        <?php
                          foreach ($data as $row):
                        ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['code']) ?></td>
                            <td><?php echo htmlspecialchars($row['employee_name']) ?></td>
														<td><?php echo htmlspecialchars($row['name']) ?></td>
                            <td><?php echo htmlspecialchars(date_format(date_create($row['in_time']),DATE_FORMAT_PHP." ".TIME_FORMAT_PHP)) ?></td>
                            <td><?php echo $row['out_time']!=="00/00/0000 00:00:00"?htmlspecialchars(date_format(date_create($row['out_time']),DATE_FORMAT_PHP." ".TIME_FORMAT_PHP)):"" ?></td>
                            <td><?php echo htmlspecialchars($row['note']) ?></td>
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
