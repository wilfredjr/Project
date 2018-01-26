<?php
 require_once("support/config.php");
if(!isLoggedIn()){
  toLogin();
  die();
}
  	if (!empty($_GET['id'])) {
  	$project_id=$_GET['id'];
    $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
    $manage=AccessForProject($project_id, $employee_id);
		$project_details=$con->myQuery("SELECT p.employee_id,p.id,p.name,p.date_filed,p.project_status_id,p.start_date,p.end_date,ps.id,ps.status_name,p.manager_id AS manager,
		(SELECT CONCAT(e.last_name,', ',e.first_name) FROM employees e JOIN projects_employees pe WHERE pe.is_manager=1 AND pe.project_id=p.id AND pe.employee_id=e.id) AS manager_id
		FROM projects p INNER JOIN project_status ps ON p.project_status_id = ps.id
	    WHERE p.id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
    $emp_des=$con->myQuery("SELECT designation_id FROM projects_employees WHERE project_id=? AND employee_id=?",array($project_id,$employee_id))->fetch(PDO::FETCH_ASSOC);
	     $p1=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=1 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $p2=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=2 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $p3=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=3 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $p4=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=4 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $p5=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=5 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $p6=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=6 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $p7=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=7 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $p8=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=8 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
      }
      $manager_id=$con->myQuery("SELECT id, manager_id FROM projects WHERE id = ".$_GET['id'])->fetch(PDO::FETCH_ASSOC);
?>
<div class="box box-warning">
<div class="box-body"><br>
<div class='row'>
<!--timeline-->
            <div class='blah'>
            <ul class="timeline" id="timeline">
            <!--p1-->
            <?php  if($p1['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($p1['status_id']=='2'){echo '<li class="li complete">';}
                elseif($p1['status_id']=='3'){echo '<li class="li">';}
                elseif($p1['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
                <div class="timestamp">
                  <span class="author"><b>Business Analyst</b></span>
                  <span class="date"><?php echo $p1['date']; ?><span>
                </div>
                <div class="status">
                  <h4> <?php echo $p1['name']; ?> </h4>
                </div>
              </li>
             <!--p2-->
            <?php if($p2['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($p2['status_id']=='2'){echo '<li class="li complete">';}
                elseif($p2['status_id']=='3'){echo '<li class="li">';}
                elseif($p2['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
                <div class="timestamp">
                  <span class="author"><b>Business Analyst</b></span>
                  <span class="date"><?php echo $p2['date']; ?><span>
                </div>
                <div class="status">
                  <h4> <?php echo $p2['name']; ?> </h4>
                </div>
              </li>
               <!--p3-->
            <?php if($p3['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($p3['status_id']=='2'){echo '<li class="li complete">';}
                elseif($p3['status_id']=='3'){echo '<li class="li">';}
                elseif($p3['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
                <div class="timestamp">
                  <span class="author"><b>Developer</b></span>
                  <span class="date"><?php echo $p3['date']; ?><span>
                </div>
                <div class="status">
                  <h4> <?php echo $p3['name']; ?> </h4>
                </div>
              </li>
              <!--p4-->
            <?php if($p4['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($p4['status_id']=='2'){echo '<li class="li complete">';}
                elseif($p4['status_id']=='3'){echo '<li class="li">';}
                elseif($p4['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
                <div class="timestamp">
                  <span class="author"><b>Business Analyst</b></span>
                  <span class="date"><?php echo $p4['date']; ?><span>
                </div>
                <div class="status">
                  <h4> <?php echo $p4['name']; ?> </h4>
                </div>
              </li>
              <!--p4-->
            <?php if($p5['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($p5['status_id']=='2'){echo '<li class="li complete">';}
                elseif($p5['status_id']=='3'){echo '<li class="li">';}
                elseif($p5['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
                <div class="timestamp">
                  <span class="author"><b>Business Analyst</b></span>
                  <span class="date"><?php echo $p5['date']; ?><span>
                </div>
                <div class="status">
                  <h4> <?php echo $p5['name']; ?> </h4>
                </div>
              </li>
              <!--p4-->
            <?php if($p6['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($p6['status_id']=='2'){echo '<li class="li complete">';}
                elseif($p6['status_id']=='3'){echo '<li class="li">';}
                elseif($p6['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
                <div class="timestamp">
                  <span class="author"><b>Business Analyst</b></span>
                  <span class="date"><?php echo $p6['date']; ?><span>
                </div>
                <div class="status">
                  <h4> <?php echo $p6['name']; ?> </h4>
                </div>
              </li>
              <!--p4-->
            <?php if($p7['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($p7['status_id']=='2'){echo '<li class="li complete">';}
                elseif($p7['status_id']=='3'){echo '<li class="li">';}
                elseif($p7['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
                <div class="timestamp">
                  <span class="author"><b>Business Analyst</b></span>
                  <span class="date"><?php echo $p7['date']; ?><span>
                </div>
                <div class="status">
                  <h4> <?php echo $p7['name']; ?> </h4>
                </div>
              </li> 
                <!--p4-->
            <?php if($p8['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($p8['status_id']=='2'){echo '<li class="li complete">';}
                elseif($p8['status_id']=='3'){echo '<li class="li">';}
                elseif($p8['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
                <div class="timestamp">
                  <span class="author"><b>Business Analyst</b></span>
                  <span class="date"><?php echo $p8['date']; ?><span>
                </div>
                <div class="status">
                  <h4> <?php echo $p8['name']; ?> </h4>
                </div>
              </li>
             </ul>
             </div>
             <!--timeline end-->  
        </h4><br>
      </div><br>
      <table><tr>
      <td><b> Current Project Phase: </b><?php echo $cur_phase['cur_phase'];?></td><td><b>Team Assigned: </b><?php echo $cur_phase['cur_des'];?></td></tr>
      <tr><td><b>Phase Start Date: </b><?php echo $cur_phase['date_start'];?></td><td><b>Phase End Date: </b><?php echo $cur_phase['date_end'];?></td></tr>
      <tr><td><b>Phase Status: </b><?php echo $cur_phase['cur_status'];?> </td><td>
      <td></td></tr>
    </table>
    <?php if(($manage['is_team_lead_ba']=='1')||($manage['is_team_lead_dev']=='1')){ ?>
    <div class='form-group'>
     <div class='col-md-5 col-md-offset-3 text-right'><br>
        <?php if($cur_phase['cur_des_id']=='1'){
                if($emp_des['designation_id']=='2'){}else{
                     if($cur_phase['project_phase_id']<'8'){
              echo "<button type='button' style='display: inline;' class='btn btn-warning' onclick='submit(\"{$_GET['id']}\")'>Submit Phase Completion</button> ";
        }
        if($cur_phase['project_phase_id']>'1'){
        echo "<button type='button' style='display: inline;' class='btn btn-danger' onclick='reject(\"{$_GET['id']}\")'>Revert to Previous Phase</button>";
        }
      }}elseif($cur_phase['cur_des_id']=='2'){ 
        if($cur_phase['cur_status']=='Done'){}else{
        if($emp_des['designation_id']=='1'){}else{
          if($cur_phase['project_phase_id']<'8'){ 
             echo "<button type='button' style='display: inline;' class='btn btn-warning' onclick='submit(\"{$_GET['id']}\")'>Submit Phase Completion</button> ";
        }
        if($cur_phase['project_phase_id']>'1'){
        echo "<button type='button' style='display: inline;' class='btn btn-danger' onclick='reject(\"{$_GET['id']}\")'>Revert to Previous Phase</button>";
        }
      }}}?>
    </div>
    </div><br><br><br>
    <div class='panel-body ' >
                    <!-- <table class='table table-bordered table-condensed table-hover display select' id='ResultTable'> -->

                                        <table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
                                            <thead>
                                                <tr>
                                                    <th class='text-center'>Date Filed</th>
                                                    <th class='text-center'>Phase Name</th>
                                                    <th class='text-center'>Employee Name</th>
                                                    <th class='text-center'>Current Approver</th>
                                                    <th class='text-center'>Type</th>
                                                    <th class='text-center'>Hours</th>
                                                    <th class='text-center'>Status</th>
                                                    <th class='text-center'>Comment</th>
                                                    <th class='text-center'>Reason</th>
                                                    <th class='text-center'>Actions</th>
                                                </tr>
                                            </thead>
                                            </table>
            </div>
    <?php
    $request_type="project_approval_phase";
    $redirect_page="my_projects_view.php?id={$_GET['id']}&tab=1";
    require_once("include/modal_revertion.php");
    require_once("include/modal_phase_submit.php");
    require_once("include/modal_query.php");
    }
    ?>
      </div>
    </div>
  </section>
</div>
<script type="text/javascript">

function filter_search() 
        {
            //table.draw();
            data_table.ajax.reload();

        }

$(function () {
  data_table=$('#ResultTable').DataTable({
    "processing": true,
    "scrollX":true,
    "searching": false,

    "ajax":{
      "url":"ajax/project_phase_status.php?id=<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>",
      "data":function(d){
             d.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
             d.employee_id=$("select[name='employee_name']").val();
              d.department_id=$("select[name='department_id']").val();
             d.job_id=$("select[name='job_id']").val();
             d.req_type=$("select[name='req_type']").val();
      }
    },
    "columnDefs": [{ "orderable": false, "targets": -1 },
     { "width": "10%", "targets": -1 },
    {"sClass": "text-center", "aTargets": [ -1 ]}],
          "order": [[ 0, "desc" ]]
    
  });
});

</script>