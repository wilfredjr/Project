<?php
	require_once("support/config.php");
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}
        $canFileForEmployees=canFileForEmployees1($_SESSION[WEBAPP]['user']['employee_id']);
    if (empty($canFileForEmployees)) {
        redirect("index.php");
    }

    // if(!AllowUser(array(1,2)))
    // {
    //     redirect("index.php");
    // }
	$data=""; 

	if(!empty($_GET['id']))
    {
  		$data=$con->myQuery("SELECT id,name,cur_phase,project_status_id,manager_id,employee_id,team_lead_ba,team_lead_dev FROM projects WHERE id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
  		if(empty($data) OR ($data['project_status_id']==2))
        {
  			Modal("Invalid Record Selected");
  			redirect("task_management.php");
  			die;
  		}
	}
    $emp_id=$_SESSION[WEBAPP]['user']['employee_id'];
    $cur_phase=$con->myQuery("SELECT phase_name FROM project_phases WHERE id=?",array($data['cur_phase']))->fetch(PDO::FETCH_ASSOC);
    $dev_phase=$con->myQuery("SELECT id FROM project_phase_dates WHERE project_id=? AND project_phase_id='3'",array($data['id']))->fetch(PDO::FETCH_ASSOC);
    $project_status=$con->myQuery("SELECT status_name FROM project_status WHERE id=?",array($data['project_status_id']))->fetch(PDO::FETCH_ASSOC);
    $emp_des=$con->myQuery("SELECT designation_id FROM projects_employees WHERE project_id=? AND employee_id=?",array($data['id'],$emp_id))->fetch(PDO::FETCH_ASSOC);
    if(($emp_des['designation_id']==3) OR ($emp_des['designation_id']==0)){
          if(empty($dev_phase)){
        $phases=$con->myQuery("SELECT id,phase_name FROM project_phases WHERE id<='2' AND designation_id='2'")->fetchAll(PDO::FETCH_ASSOC);
      }else{
      $phases=$con->myQuery("SELECT id,phase_name FROM project_phases WHERE id>=?",array($data['cur_phase']))->fetchAll(PDO::FETCH_ASSOC);}
    }else{
      if(empty($dev_phase)){
        $phases=$con->myQuery("SELECT id,phase_name FROM project_phases WHERE id<='2' AND designation_id=?",array($emp_des['designation_id']))->fetchAll(PDO::FETCH_ASSOC);
      }else{
        $phases=$con->myQuery("SELECT id,phase_name FROM project_phases WHERE id>=? AND designation_id=?",array($data['cur_phase'],$emp_des['designation_id']))->fetchAll(PDO::FETCH_ASSOC);}
    }
    $project_employee=$con->myQuery("SELECT employee_id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name)  FROM projects_employees JOIN employees e ON e.id=employee_id WHERE project_id=".$_GET['id'])->fetchAll(PDO::FETCH_ASSOC);
	makeHead("Application for Task Form");
?>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 <div class="content-wrapper">
    <section class="content-header text-center">
        <h1>
             Task Management Form
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-10 col-md-offset-1'>
				<?php	Alert();
          Modal();	?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class='col-md-12'>
                                <div class='col-md-12  col-md-offset-2' >
                                      <table><tr>
                                      <td><b> Project Name: </b><?php echo $data['name'];?></td><td><b>Current Phase: </b><?php echo $cur_phase['phase_name'];?></td></tr>
                                      <tr><td><b>Project Status: </b><?php echo $project_status['status_name'];?> </td>
                                      <td></td></tr>
                                    </table>
                                    </div>
                                <div class='text-center'>
                                <button class='btn btn-success' data-toggle="modal" data-target="#alertModal">Project Phase Dates</button>
                                </div><br>
                                <form class='form-horizontal disable-submit' action='save_task_management.php' method="POST">
                                    <input type='hidden' name='get_id' value='<?php echo !empty($get_id)?$get_id:''; ?>'> 
                                    <input type='hidden' name='id' value='<?php echo !empty($data)?$data['id']:''; ?>'>
                                    <input type='hidden' name='admin_id' value='<?php echo !empty($data)?$data['employee_id']:''; ?>'>
                                    <input type='hidden' name='manager_id' value='<?php echo !empty($data)?$data['manager_id']:''; ?>'>
                                   <div class='form-group'>
                                      <label for="ot_date" class="col-sm-2 control-label">Project Phase: </label>
                                        <div class="col-sm-9">
                                          <select class='form-control cbo' name='phase_id' id='phase_id' style='width:100%' data-allow-clear='true' <?php if (!empty($canFileForEmployees)) {?> onchange="getProjectEmployees(this)" <?php } ?> required="" data-placeholder="Select Project Phase">
                                               <?php echo makeOptions($phases);?>
                                          </select>
                                        </div>
                                    </div>
                                      <div class='form-group'>
                                      <label for="ot_date" class="col-sm-2 control-label">Employees: </label>
                                        <div class="col-sm-9">
                                          <select class='form-control' name='employees_id[]' id='employees_id' style='width:100%' disabled="" required="" multiple="multiple"></select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_start" class="col-md-2 control-label">Start Date: </label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control date_picker" id="date_start" name='date_start' required>
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label for="date_end" class="col-md-2 control-label">End Date: </label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control date_picker" id="date_end" name='date_end' required>
                                        </div>
                                    </div> 
                                     <div class="form-group">
                                        <label for="worked_done" class="col-sm-2 control-label">Work to be done: </label>
                                        <div class="col-sm-9">
                                          <textarea class='form-control' id='worked_done' name='worked_done' rows='5' value='<?php echo !empty($data)?$data['worked_done']:''; ?>' required=""></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="text-center">
                                            <button type='submit' class='btn btn-warning'>Save </button>
                                            <a href='task_management_project.php?id=<?php echo $_GET['id'];?>' class='btn btn-default' onclick="return confirm('Are you sure you want to cancel this application?')">Cancel</a>
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
<div class="modal fade" id="alertModal" name="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title text-center" id="alertModalLabel">
                Project Phase Dates
                </h4>
            </div>
            <div class="modal-body">
                                                                <table id='ResultTable1' class='table table-bordered table-striped' width="100%">
                                                                  <thead>
                                                                    <tr>
                                                                      <!-- <th class='text-center'>Type of OT</th> -->
                                                                      <th class='text-center'></th>
                                                                      <th class='text-center'>Phase Name</th>
                                                                      <th class='text-center'>Date Start</th>
                                                                      <th class='text-center'>Date End</th>
                                                                      <th class='text-center'>Status</th>
                                                                     <!--  <th class='text-center'>Deficit Days</th> -->
                                                                    </tr>
                                                                  </thead>
                                                                  <tbody>
                                                                  </tbody>
                                                                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<script type="text/javascript">
    var employees_id="";

  $(function () {
        $('#ResultTable').DataTable();
        <?php
        if (!empty($canFileForEmployees)) {
        ?>
        employees_id=$("select[name='employees_id[]']").select2();
        <?php
        }
        ?>
      });
    function getEmployeeLeaves(emp_select) {
        leave_select=$("select[name='leave_id']");
        leave_select.select2("val","");
        leave_select.select2("enable",false);
        leave_select.select2({
          ajax: {
            url:'./ajax/cbo_employee_leaves.php?emp_id='+emp_select.value,
            dataType: "json",
            data: function (params) {

                var queryParameters = {
                    term: params.term
                }
                return queryParameters;
            },
            processResults: function (data) {
                  return {
                      results: $.map(data, function (item) {
                          // console.log(item);
                          return {
                              text: item.description,
                              id: item.id
                          }
                      })
                  };
            }
          }
        });
        leave_select.removeAttr('disabled')
        leave_select.select2("enable", true);
    }
    function getProjectEmployees(project_select) {
    project_id=$("input[name='id']").val();
    if(project_select.value==3){
        des=1;
    }else{
        des=2;
    }
    employees_id.select2("val", "");
    employees_id.select2("enable", false);
    employees_id.select2({
      ajax: {
        url:'./ajax/cbo_project_employees1.php?project_id='+project_id+'&des_id='+des,
        dataType: "json",
        data: function (params) {

            var queryParameters = {
                term: params.term
            }
            return queryParameters;
        },
        processResults: function (data) {
              return {
                  results: $.map(data, function (item) {
                      // console.log(item);
                      return {
                          text: item.description,
                          id: item.id
                      }
                  })
              };
        }
      }
    });
    employees_id.removeAttr('disabled')
    employees_id.select2("enable", true);
    // $.get( "./ajax/cbo_project_employees.php", { project_id: project_select.value } )
    // .done(function( data ) {
      
    // });
  }
  var dttable="";
$(document).ready(function ()
{
    dttable=$('#ResultTable1').DataTable({
        "scrollX": false,
        "processing": true,
        "serverSide": true,
        "searching": false,
        "ajax":
              {    
                "url":"ajax/project_timeline_dates.php",
                "data":function(d)
                {
                     d.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
                }
     
            
            },
        "order": [[ 0, "asc" ]]
    });
});

</script>
<?php
    makeFoot();
?>