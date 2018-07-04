<?php
	require_once("support/config.php");
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}
    $usertype=$con->myQuery("SELECT user_type_id FROM users WHERE employee_id=:employee_id",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
    // if ($usertype!=5) {
    //     redirect("index.php");
    // }
    // if(!AllowUser(array(1,2)))
    // {
    //     redirect("index.php");
    // }
	$data=""; 
    $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
	if(!empty($_GET['id']))
    {
  		$data=$con->myQuery("SELECT pbl.id,pbl.bug_phase_id,pbl.bug_app_id,pbl.name as bug_name,pbl.employee_id,pbl.description,p.name AS project_name, pbl.project_id,pbl.project_status_id,pbp.name as phase_name, ps.status_name, pbl.date_filed,pbl.date_start,pbl.date_end,pbr.desc as bug_rate,pbl.manager_id,pbl.team_lead_ba,pbl.team_lead_dev,pbl.ba_test,pbl.dev_control,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pbl.manager_id) AS manager,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pbl.team_lead_ba) AS lead_ba,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pbl.team_lead_dev) AS lead_dev,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pbl.employee_id) AS account_manager
FROM project_bug_list pbl JOIN project_status ps ON pbl.project_status_id=ps.id JOIN projects p ON pbl.project_id=p.id
JOIN project_bug_phase pbp ON pbl.bug_phase_id=pbp.id JOIN project_bug_rate pbr ON pbr.id=pbl.bug_rate_id WHERE (pbl.manager_id='$employee_id' OR pbl.team_lead_ba='$employee_id' OR pbl.team_lead_dev='$employee_id' OR pbl.ba_test='$employee_id' OR pbl.dev_control='$employee_id' OR pbl.employee_id='$employee_id' OR pbl.admin_id='$employee_id') AND pbl.id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
  		if(empty($data))
        {
  			Modal("Invalid Record Selected");
  			redirect("bug_management.php");
  			die;
  		}
	}
  $dates=$con->myQuery("SELECT pbpd.date_start,pbpd.date_end,pbp.name FROM project_bug_phase_dates pbpd JOIN project_bug_phase pbp ON pbp.id=pbpd.bug_phase_id WHERE project_id=? AND bug_list_id=? AND bug_phase_id='1'",array($data['project_id'],$data['id']))->fetch(PDO::FETCH_ASSOC);
    $dates1=$con->myQuery("SELECT pbpd.date_start,pbpd.date_end,pbp.name FROM project_bug_phase_dates pbpd JOIN project_bug_phase pbp ON pbp.id=pbpd.bug_phase_id WHERE project_id=? AND bug_list_id=? AND bug_phase_id='2'",array($data['project_id'],$data['id']))->fetch(PDO::FETCH_ASSOC);
    $project_status=$con->myQuery("SELECT status_name FROM project_status WHERE id=?",array($data['project_status_id']))->fetch(PDO::FETCH_ASSOC);
    $bug_rates=$con->myQuery("SELECT id,name FROM project_bug_rate")->fetchAll(PDO::FETCH_ASSOC);
    $current=$con->myQuery("SELECT bf.id FROM  bug_files bf JOIN project_bug_list pbl ON pbl.bug_app_id=bf.bug_list_id  WHERE bf.bug_list_id=? AND bf.is_deleted=0 AND pbl.project_id=? AND bf.employee_id=?",array($data['bug_app_id'],$data['project_id'],$data['employee_id']))->fetch(PDO::FETCH_ASSOC);
    $dev_control=$con->myQuery("SELECT employee_id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name)  FROM projects_employees JOIN employees e ON e.id=employee_id WHERE project_id=? AND (designation_id=1 OR designation_id=3)",array($data['project_id']))->fetchAll(PDO::FETCH_ASSOC);
    $ba_test=$con->myQuery("SELECT employee_id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name)  FROM projects_employees JOIN employees e ON e.id=employee_id WHERE project_id=? AND (designation_id=2 OR designation_id=3)",array($data['project_id']))->fetchAll(PDO::FETCH_ASSOC);

	makeHead("Bug Form");
?>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 <div class="content-wrapper">
    <section class="content-header text-center">
        <h1>
             Bug Form
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-10 col-md-offset-1'>
				<?php	Alert();
          Modal();?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class='col-md-12'>
                                <div class='col-md-9  col-md-offset-1' >
                                       <div class='form-group'>
                                      <br><table>
                                      <tr><td><b>Bug Name: </b><?php echo $data['bug_name'];?> </td><td><b> Project Name: </b><?php echo $data['project_name'];?></td></tr>
                                      <tr><td><b> Current Phase: </b><?php echo $data['phase_name'];?></td><td><b>Bug Status: </b><?php echo $data['status_name'];?> </td></tr>
                                      <tr><td><b><?php echo $dates['name'];?>: </b><?php echo $dates['date_start']." to ".$dates['date_end'];?><b></td><td><b><?php echo $dates1['name'];?>: </b> <?php echo $dates1['date_start']." to ".$dates1['date_end'];?></td></tr>
                                      <tr><td><b>Account Manager: </b><?php echo $data['account_manager'];?> </td>
                                        <?php if(!empty($current['id'])){?>
                                        <td><b>Account Manager File: </b><a href="download_file.php?id=<?php echo $current['id']."&type=bf";?>" class='btn btn-default'><span class='fa fa-download'></span></a> </td> <?php } ?>
                                      </tr>
                                      <tr><td><b>Description: </b><?php echo nl2br($data['description']);?> </td></tr>
                                    </table>
                                    </div>
                                    <div>
                                      <div class='form-group'>
                                            <?php 
                                            if(($data['bug_phase_id']=='1')AND($data['dev_control']==$employee_id)){
                                            echo "<div class='col-md-offset-5'>";
                                            echo "<button type='button' style='display: inline;' class='btn btn-warning' onclick='submit(\"{$data['id']}\")'>Endorse as Fixed</button>";}
                                            ?>
                                            <?php
                                            if(($data['bug_phase_id']=='2')AND($data['ba_test']==$employee_id)AND($data['project_status_id']!=2)){
                                            echo "<div class='col-md-offset-3'>";
                                            echo "<button type='button' style='display: inline;' class='btn btn-warning' onclick='submit(\"{$data['id']}\")'>Endorse as Fixed</button> "; 
                                            echo "<button type='button' style='display: inline;' class='btn btn-danger' onclick='reject(\"{$data['id']}\")'>Endorse Errors</button>";}?>
                                        </div>
                                    </div>
                                <form class='form-horizontal disable-submit' action='save_bug_employee.php' method="POST">
                                    <input type='hidden' name='id' value='<?php echo !empty($data)?$data['id']:''; ?>'>
                                    <input type='hidden' name='project_id' value='<?php echo !empty($data)?$data['project_id']:''; ?>'>
                                    <input type='hidden' name='manager_id' value='<?php echo !empty($data)?$data['manager_id']:''; ?>'>
                                    <div class='form-group'>
                                        <label for='dev' class='col-sm-2 control-label'>Developer: </label>
                                          <div class='col-sm-9'>
                                            <select class='form-control cbo' name='developer' data-allow-clear='True' data-placeholder='Select Developer (Bug Control)'  <?php echo !(empty($data))?"data-selected='".$data['dev_control']."'":NULL ?> required <?php if($data['team_lead_dev']==$employee_id){}else{?> disabled <?php } if(!empty($data['dev_control'])){?> disabled <?php } ?> >
                                               <?php echo makeOptions($dev_control); ?>   
                                          </select>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <label for='dev' class='col-sm-2 control-label'>Tester: </label>
                                          <div class='col-sm-9'>
                                            <select class='form-control cbo' name='tester' data-allow-clear='True' data-placeholder='Select BA (Bug Assessment)'  <?php echo !(empty($data))?"data-selected='".$data['ba_test']."'":NULL ?> required<?php if($data['team_lead_ba']==$employee_id){}else{?> disabled <?php }if(!empty($data['ba_test'])){?> disabled <?php } ?> >
                                               <?php echo makeOptions($ba_test); ?>   
                                          </select>
                                        </div>
                                    </div>
                                    <?php if((($data['team_lead_ba']==$employee_id)AND(empty($data['ba_test'])))OR(($data['team_lead_dev']==$employee_id)AND(empty($data['dev_control'])))) {?>
                                    <div class="form-group">
                                        <div class="text-center">
                                            <button type='submit' class='btn btn-warning'>Save </button>
                                            <a href='bug_management_project.php?id=<?php echo $_GET['id'];?>' class='btn btn-default' onclick="return confirm('Are you sure you want to cancel this application?')">Cancel</a>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </form>	
                            </div>
                        </div>
                            <div class='panel-default'>
                                                          <div class='panel-body ' >
                    <!-- <table class='table table-bordered table-condensed table-hover display select' id='ResultTable'> -->

                                        <table class='table table-bordered table-condensed table-hover' id='ResultTable3'>
                                            <thead>
                                                <tr>
                                                    <th class='text-center'>Date Filed</th>
                                                    <th class='text-center'>Phase Name</th>
                                                    <th class='text-center'>Type</th>
                                                    <th class='text-center'>Current Approver</th>
                                                    <th class='text-center'>Comment</th>
                                                    <th class='text-center'>Employee</th>
                                                    <th class='text-center'>Status</th>
                                                    <th class='text-center'>Reason</th>
                                                    <th class='text-center' width="11%">Actions</th>
                                                </tr>
                                            </thead>
                                            </table>
                              </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php 
    require_once("include/modal_bug_submit.php");
    require_once("include/modal_bug_reject.php");
?>
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

$(function () {
  data_table=$('#ResultTable3').DataTable({
    "processing": true,
    "scrollX":true,
    "searching": false,
    "serverSide": true,
    "ajax":{
      "url":"ajax/bugs_view.php?id=<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>",
      "data":function(d){
             d.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
             d.employee_id=$("select[name='employee_name']").val();
              d.department_id=$("select[name='department_id']").val();
             d.job_id=$("select[name='job_id']").val();
             d.req_type=$("select[name='req_type']").val();
      }
    },
    "columnDefs": [{ "orderable": false, "targets": -1 },
    {"sClass": "text-center", "aTargets": [ -1 ]}],
          "order": [[ 0, "desc" ]]
    
  });
});
</script>
<?php
    makeFoot();
?>