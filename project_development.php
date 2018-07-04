<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }

    $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
    $can_upload=$con->myQuery("SELECT id FROM projects_employees WHERE designation_id=? AND project_id=? AND employee_id=?",array($cur_phase['designation_id'],$project_id,$employee_id))->fetchAll(PDO::FETCH_ASSOC);
    $employees=$con->myQuery("SELECT pe.employee_id,(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) From employees e WHERE e.id=pe.employee_id) as employee FROM projects_employees pe WHERE pe.project_id=? AND pe.designation_id='1' AND pe.is_team_lead_dev=0",array($cur_phase['project_id']))->fetchAll(PDO::FETCH_ASSOC);
        $project_phase=$con->myQuery("SELECT id,phase_name  FROM project_phases")->fetchAll(PDO::FETCH_ASSOC);
    $des=$con->myQuery("SELECT designation_id FROM projects_employees WHERE employee_id=? and project_id=?",array($employee_id,$project_id))->fetch(PDO::FETCH_ASSOC);
    $project=$con->myQuery("SELECT ui,dbase,coding,man_hours FROM projects WHERE id=?",array($project_id))->fetch(PDO::FETCH_ASSOC);
    $data1=$con->myQuery("SELECT worked_done,status_id,hours,(SELECT CONCAT(e.first_name,' ',e.last_name) FROM employees e WHERE e.id=employee_id) as employee_name FROM project_task_list WHERE project_id=? AND project_phase_id=3 AND worked_done='UI'",array($cur_phase['project_id']))->fetchAll(PDO::FETCH_ASSOC);
    $data2=$con->myQuery("SELECT worked_done,status_id,hours,(SELECT CONCAT(e.first_name,' ',e.last_name) FROM employees e WHERE e.id=employee_id) as employee_name FROM project_task_list WHERE project_id=? AND project_phase_id=3 AND worked_done='Database Design'",array($cur_phase['project_id']))->fetchAll(PDO::FETCH_ASSOC);
    $data3=$con->myQuery("SELECT worked_done,status_id,hours,(SELECT CONCAT(e.first_name,' ',e.last_name) FROM employees e WHERE e.id=employee_id) as employee_name FROM project_task_list WHERE project_id=? AND project_phase_id=3 AND worked_done='Coding'",array($cur_phase['project_id']))->fetchAll(PDO::FETCH_ASSOC);
    $done1=$con->myQuery("SELECT SUM(hours) as hours FROM project_task_list WHERE project_id=? AND project_phase_id=3 AND worked_done='UI' AND status_id=2",array($cur_phase['project_id']))->fetch(PDO::FETCH_ASSOC);
    $done2=$con->myQuery("SELECT SUM(hours) as hours FROM project_task_list WHERE project_id=? AND project_phase_id=3 AND worked_done='Database Design' AND status_id=2",array($cur_phase['project_id']))->fetch(PDO::FETCH_ASSOC);
    $done3=$con->myQuery("SELECT SUM(hours) as hours FROM project_task_list WHERE project_id=? AND project_phase_id=3 AND worked_done='Coding' AND status_id=2",array($cur_phase['project_id']))->fetch(PDO::FETCH_ASSOC);
    $done1_hours=$done1['hours'];
    $left1_hours=$project['ui']-$done1_hours;
    $left1_days=$left1_hours/8;
    $done2_hours=$done2['hours'];
    $left2_hours=$project['dbase']-$done2_hours;
    $left2_days=$left2_hours/8;
    $done3_hours=$done3['hours'];
    $left3_hours=$project['coding']-$done3_hours;
    $left3_days=$left3_hours/8;
    $tot_per=(($done1_hours+$done2_hours+$done3_hours)/$project['man_hours'])*100;
    // var_dump($cur_phase);
    // die;
	makeHead("Project Files");
?>
       <div class="row">
            <div class='col-md-12'>
              <?php 
                Alert();
              ?>
            
              <div class="box box-warning">
                <div class="box-body"><br>
                  <div class="row">
                    <div class="col-sm-12">
                     <table>
                             <tr><td><b>Phase Start Date: </b><?php echo $cur_phase['date_start'];?></td><td><b>Phase End Date: </b><?php echo $cur_phase['date_end'];?></td></tr>
                             <tr><td><b>Total Man Hours: </b><?php echo $project['man_hours'];?></td><td><b>Percentage Done: </b><?php echo number_format($tot_per,2);?> %</td></tr>
                     </table><br>
                     <hr>
                     <!--timeline-->
            <div class='blah'>
            <ul class="timeline" id="timeline">
            <!--p1-->
            <div style="margin-top: 45px;">
              <h4>User Interface &nbsp;&nbsp;&nbsp;&nbsp;</h4>
            </div>  
            <?php foreach($data1 as $row){ ?>
            <li>
            <?php  if($row['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($row['status_id']=='2'){echo '<li class="li complete">';}
                elseif($row['status_id']=='3'){echo '<li class="li">';}
                elseif($row['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
             <div class="timestamp">
                  <span class="author"><b><?php echo $row['employee_name'];?></b></span>
                  <span class="date"><?php echo number_format($row['hours'],2); ?> Hours</span>
                </div>
                <div class="status">
<!--                   <h4> <?php echo $p1['name']; ?> </h4> -->
                </div>
<!--                 <div>
                  <table class="text-center">
                    <tr><th>Planned</th><th>Delay</th><th>Actual</th></tr>
                    <tr><td><?php echo $p1['p_days'];?> Day/s</td><td><?php if($p1_def['done_days']==''){echo "-";}else{echo $p1_def['done_days']." Day/s";}?></td><td><?php if($p1['a_days']==0){echo "-";}else{echo $p1['a_days']." Day/s";}?></td></tr>
                  </table>
                </div> -->
              </li>
              <?php } ?>
             </ul>
             <div>
              <table  class="text-center">
                <tr><th>Total</th><th>Done</th><th>Remaining</th><th>Percentage</th><tr>
                  <tr >
                    <td><?php echo $project['ui'];?> Hours <br> or <br> <?php echo number_format($project['ui']/8,2);?> Days</td>
                    <td><?php if(is_numeric($done1['hours'])){echo number_format($done1['hours'],2);}else{echo number_format($done1['hours']);} ?> Hours <br> or <br> <?php if(is_numeric($done1['hours'])){echo number_format($done1['hours']/8,2);}else{echo number_format($done1['hours']/8);} ?> Days</td>
                    <td><?php if(is_numeric($left1_hours)){echo number_format($left1_hours,2);}else{echo number_format($left1_hours);}?> Hours <br> or <br> <?php if(is_numeric($left1_days)){echo number_format($left1_days,2);}else{echo number_format($left1_days);}?> Days</td>
                     <td><?php echo number_format(($done1_hours/$project['ui'])*100,2) ;?> %</td>
                  </tr>
              </table>
            </div><br><br>
            <ul class="timeline" id="timeline">
            <!--p1-->
            <div class="text-center" style="margin-top: 40px;"><h4>Database Design</h4></div>  
            <?php foreach($data2 as $row){ ?>
            <li>
            <?php  if($row['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($row['status_id']=='2'){echo '<li class="li complete">';}
                elseif($row['status_id']=='3'){echo '<li class="li">';}
                elseif($row['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
             <div class="timestamp">
                  <span class="author"><b><?php echo $row['employee_name'];?></b></span>
                  <span class="date"><?php echo number_format($row['hours'],2); ?> Hours</span>
                </div>
                <div class="status">
<!--                   <h4> <?php echo $p1['name']; ?> </h4> -->
                </div>
<!--                 <div>
                  <table class="text-center">
                    <tr><th>Planned</th><th>Delay</th><th>Actual</th></tr>
                    <tr><td><?php echo $p1['p_days'];?> Day/s</td><td><?php if($p1_def['done_days']==''){echo "-";}else{echo $p1_def['done_days']." Day/s";}?></td><td><?php if($p1['a_days']==0){echo "-";}else{echo $p1['a_days']." Day/s";}?></td></tr>
                  </table>
                </div> -->
              </li>
              <?php } ?>
             </ul>
              <div>
              <table  class="text-center">
                <tr><th>Total</th><th>Done</th><th>Remaining</th><th>Percentage</th><tr>
                  <tr >
                    <td><?php echo $project['dbase'];?> Hours <br> or <br> <?php echo number_format($project['dbase']/8,2);?> Days</td>
                    <td><?php if(is_numeric($done2['hours'])){echo number_format($done2['hours'],2);}else{echo number_format($done2['hours']);} ?> Hours <br> or <br> <?php if(is_numeric($done2['hours'])){echo number_format($done2['hours']/8,2);}else{echo number_format($done2['hours']/8);} ?> Days</td>
                    <td><?php if(is_numeric($left2_hours)){echo number_format($left2_hours,2);}else{echo number_format($left2_hours);}?> Hours <br> or <br> <?php if(is_numeric($left2_days)){echo number_format($left2_days,2);}else{echo number_format($left2_days);}?> Days</td>
                   <td><?php echo number_format(($done2_hours/$project['dbase'])*100,2) ;?> %</td>
                  </tr>
              </table>
            </div><br><br>
              <ul class="timeline" id="timeline">
            <!--p1-->
            <div class="text-center" style="margin-top: 40px;"><h4>Coding <?php echo str_repeat('&nbsp', 15);?></h4></div>  
            <?php foreach($data3 as $row){ ?>
            <li>
            <?php  if($row['status_id']=='1'){echo '<li class="li ongoing">';}
                elseif($row['status_id']=='2'){echo '<li class="li complete">';}
                elseif($row['status_id']=='3'){echo '<li class="li">';}
                elseif($row['status_id']=='4'){echo '<li class="li delayed">';}
            ?>
             <div class="timestamp">
                  <span class="author"><b><?php echo $row['employee_name'];?></b></span>
                  <span class="date"><?php echo number_format($row['hours'],2); ?> Hours</span>
                </div>
                <div class="status">
<!--                   <h4> <?php echo $p1['name']; ?> </h4> -->
                </div>
<!--                 <div>
                  <table class="text-center">
                    <tr><th>Planned</th><th>Delay</th><th>Actual</th></tr>
                    <tr><td><?php echo $p1['p_days'];?> Day/s</td><td><?php if($p1_def['done_days']==''){echo "-";}else{echo $p1_def['done_days']." Day/s";}?></td><td><?php if($p1['a_days']==0){echo "-";}else{echo $p1['a_days']." Day/s";}?></td></tr>
                  </table>
                </div> -->
              </li>
              <?php } ?>
             </ul>
              <div>
              <table  class="text-center">
                <tr><th>Total</th><th>Done</th><th>Remaining</th><th>Percentage</th><tr>
                  <tr >
                    <td><?php echo $project['coding'];?> Hours <br> or <br> <?php echo number_format($project['coding']/8,2);?> Days</td>
                    <td><?php if(is_numeric($done3['hours'])){echo number_format($done3['hours'],2);}else{echo number_format($done3['hours']);} ?> Hours <br> or <br> <?php if(is_numeric($done3['hours'])){echo number_format($done3['hours']/8,2);}else{echo number_format($done3['hours']/8);} ?> Days</td>
                    <td><?php if(is_numeric($left3_hours)){echo number_format($left3_hours,2);}else{echo number_format($left3_hours);}?> Hours <br> or <br> <?php if(is_numeric($left3_days)){echo number_format($left3_days,2);}else{echo number_format($left3_days);}?> Days</td>
                   <td><?php echo number_format(($done3_hours/$project['coding'])*100,2) ;?> %</td>
                  </tr>
              </table>
            </div><br><br>
          </div><br>
             <!--timeline end-->  
                            <div class='panel-body ' >
                    <!-- <table class='table table-bordered table-condensed table-hover display select' id='ResultTable'> -->

                                        <table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
                                            <thead>
                                                <tr>
                                                    <th class='text-center'>Employee Name</th>
                                                    <th class='text-center'>Work To Do</th>
                                                    <th class='text-center'>Work Done</th>
                                                    <th class='text-center'>Status</th>
                                                    <th class='text-center'>Date Finished</th>
                                                    <th class='text-center'>Actions</th>
                                                </tr>
                                            </thead>
                                            </table>
                            </div>
                    </div><!-- /.col -->
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
          </div><!-- /.row -->

<?php
    $request_type="task_completion_approval";
    $request_type1="task_completion_submit";
    $request_from="submit";
    $redirect_page="my_tasks.php";
    require_once("include/modal_submit_emp.php");
?>
<script type="text/javascript">
  // $(function () {
  //       $('#ResultTable').DataTable(<?php if(AllowUser(array(1,2,3,4,5))):?>{
  //              dom: 'Bfrtip',
  //                   buttons: [
  //                       // {
  //                       //     extend:"excel",
  //                       //     text:"<span class='fa fa-download'></span> Download as Excel File "
  //                       // }
  //                       ]
  //       }<?php endif;?>);
  //     });

$(function () {
  data_table=$('#ResultTable').DataTable({
    "processing": true,
    "scrollX":true,
    "searching": false,

    "ajax":{
      "url":"ajax/project_development.php?id=<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>",
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
          "order": [[ 3, "desc" ]]
    
  });
});
</script>

<?php
    Modal();
	makeFoot();
?>