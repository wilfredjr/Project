<aside class="main-sidebar bg-LightGray "> 
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar" >
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">         
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="index.php"?"active":"";?>">
              <a href="index.php">
              
                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
              </a>
            </li>
     <!--        <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="time_management.php"?"active":"";?>">
              <a href="time_management.php">
                <i class="fa fa-clock-o"></i> <span>My Attendance</span>
              </a>
            </li>
      -->       
            <li class='header'>MAIN NAVIGATION</li>
            <?php
            $usertype=$con->myQuery("SELECT user_type_id FROM users WHERE employee_id=:employee_id",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $project_app_query=$con->myQuery("SELECT COUNT(id) FROM `project_application` WHERE employee_id=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $total=intval($project_app_query);
            if ($usertype=='3'){
            ?>
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="project_management.php"?"active":"";?>">
              <a href="project_management.php">
                <i class="fa fa-folder-o"></i> <span>Project Management</span> &nbsp;
                  <?php echo empty($total)?'':"<small class='label bg-primary'>{$total}</small>";?>
              </a>
            </li>
            <?php
            }if($usertype=='5'){
            ?>
             <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="bug_management.php"?"active":"";?>">
              <a href="bug_management.php">
                <i class="fa fa-tasks"></i> <span>Bug Management</span>
              </a>
            </li>
            <?php
            }
             $tasker=$con->myQuery("SELECT id FROM projects WHERE ((manager_id=:employee_id)OR(team_lead_ba=:employee_id)OR(team_lead_dev=:employee_id))",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchAll();
             if(!empty($tasker)){
             ?>
             <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="task_management.php"?"active":"";?>">
              <a href="task_management.php">
                <i class="fa fa-tasks"></i> <span>Task Management</span>
              </a>
            </li>
            <?php 
            }if($usertype!=5){
            ?>
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="my_projects.php"?"active":"";?>">
              <a href="my_projects.php">
                <i class="fa fa-folder-open-o"></i> <span>My Projects</span>
              </a>
            </li>
            <?php 
            }
            $task=$con->myQuery("SELECT count(id) FROM project_task_list WHERE employee_id=:employee_id",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            if(!empty($task)){ 
              $my_task_query=$con->myQuery("SELECT COUNT(id) FROM `project_task_list` WHERE employee_id=? AND status_id!=2",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
              $my_task_query=intval($my_task_query);
              ?>
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="my_tasks.php"?"active":"";?>">
              <a href="my_tasks.php">
                <i class="fa fa-hourglass-half"></i> <span>My Tasks </span><?php echo empty($my_task_query)?'':"<small class='label bg-primary'>{$my_task_query}</small>";?>
              </a>
            </li>
            <?php }
            $bugs=$con->myQuery("SELECT count(id) FROM project_bug_list WHERE (employee_id=:employee_id OR manager_id=:employee_id OR team_lead_ba=:employee_id OR team_lead_dev=:employee_id OR ba_test=:employee_id OR dev_control=:employee_id OR admin_id=:employee_id) AND project_status_id!=2",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $bugs=intval($bugs); ?>
            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="bug_list.php"?"active":"";?>">
              <a href="bug_list.php">
                <i class="fa fa-bug"></i> <span>Bugs and Errors </span><?php echo empty($bugs)?'':"<small class='label bg-primary'>{$bugs}</small>";?>
              </a>
            </li>
<!--FOR APPROVAL MENU-->

               <?php
                  $overtime_count=$con->myQuery("SELECT 
                      COUNT(id)
                      FROM vw_employees_ot
                      WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $ot_adjustment_count = $con->myQuery("SELECT
                        COUNT(id)
                    FROM vw_employees_ot_adjustments WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $ot_count=intval($overtime_count);
                  $leave_count=$con->myQuery("SELECT
                      COUNT(el.id)
                      FROM vw_employees_leave el
                      WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1",
                      array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $ob_count=$con->myQuery("SELECT 
                      COUNT(id)
                      FROM vw_employees_ob
                      WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $shift_count=$con->myQuery("SELECT 
                      COUNT(id)
                      FROM vw_employees_change_shift
                      WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $adj_count=$con->myQuery("SELECT 
                      COUNT(id)
                      FROM vw_employees_adjustments
                      WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $adj_count=$con->myQuery("SELECT 
                      COUNT(id)
                      FROM vw_employees_adjustments
                      WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $offset_count=$con->myQuery("SELECT 
                      COUNT(id)
                      FROM vw_employees_offset
                      WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $allowance_count=$con->myQuery("SELECT 
                      COUNT(id)
                      FROM vw_employees_allowances
                      WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $project_employee_approval_count= $con->myQuery("SELECT COUNT(pr.id)
                      FROM project_requests pr JOIN employees e ON e.id=pr.employee_id JOIN projects p ON pr.project_id=p.id
                      JOIN request_status rs ON rs.id=pr.status_id WHERE pr.is_deleted=0 AND status_id=1 AND (SELECT   
                         CASE   
                            WHEN pr.step_id=2 THEN pr.manager_id 
                            WHEN pr.step_id=3 THEN pr.admin_id
                         END=:employee_id)",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                 $project_phase_approval_count=$con->myQuery("SELECT COUNT(ppr.id) FROM project_phase_request ppr WHERE ppr.request_status_id=1 AND (SELECT   
                         CASE   
                            WHEN ppr.step_id=2 THEN ppr.manager_id 
                            WHEN ppr.step_id=3 THEN ppr.admin_id
                         END=:employee_id)",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                  $project_dev_approval_count=$con->myQuery("SELECT COUNT(pd.id) FROM project_development pd WHERE pd.request_status_id=1 AND (SELECT   
                         CASE   
                            WHEN pd.step_id=2 THEN pd.manager_id 
                            WHEN pd.step_id=3 THEN pd.admin_id
                            WHEN pd.step_id=0 THEN pd.team_lead_id
                         END=:employee_id)",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                 $task_management_approval_count=$con->myQuery("SELECT COUNT(id) FROM project_task WHERE request_status_id=1 AND (SELECT   
                         CASE   
                            WHEN step_id=2 THEN manager_id 
                            WHEN step_id=3 THEN admin_id
                         END=:employee_id)",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                 $task_completion_approval_count=$con->myQuery("SELECT COUNT(id) FROM project_task_completion WHERE request_status_id=1 AND (SELECT   
                         CASE   
                            WHEN step_id=2 THEN manager_id 
                            WHEN step_id=3 THEN admin_id
                            WHEN step_id=1 THEN team_lead_id
                         END=:employee_id)",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                 $bug_employee_approval_count=$con->myQuery("SELECT COUNT(id) FROM project_bug_employee WHERE request_status_id=1 AND (SELECT   
                         CASE   
                            WHEN step_id=2 THEN manager_id 
                            WHEN step_id=3 THEN admin_id
                         END=:employee_id)",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                 $bug_phase_approval_count=$con->myQuery("SELECT COUNT(id) FROM project_bug_request WHERE request_status_id=1 AND (SELECT   
                         CASE   
                            WHEN step_id=2 THEN manager_id 
                            WHEN step_id=3 THEN admin_id
                            WHEN step_id=1 THEN team_lead_id
                         END=:employee_id)",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                 if($usertype=='4'){
                 $project_application_approval_count=$con->myQuery("SELECT COUNT(id) FROM project_application WHERE request_status_id=1")->fetchColumn();
                 $bug_application_approval_count=$con->myQuery("SELECT COUNT(id) FROM project_bug_application WHERE request_status_id=1 AND admin_id=:employee_id",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
                 }else{$project_application_approval_count="";
                 $bug_application_approval_count="";}

                  $total_for_approval=intval($ot_count) + intval($leave_count) + intval($ob_count) + intval($shift_count) + intval($adj_count) + intval($offset_count)+intval($allowance_count)+intval($ot_adjustment_count) + intval($project_employee_approval_count) + intval($project_phase_approval_count) + intval($task_management_approval_count) + intval($task_completion_approval_count)  + intval($project_application_approval_count) + intval($project_dev_approval_count) + intval($bug_application_approval_count) + intval($bug_employee_approval_count) + intval($bug_phase_approval_count);
                  if(!empty($ot_count) || !empty($leave_count) || !empty($ob_count) || !empty($ot_count) || !empty($shift_count) || !empty($adj_count) || !empty($allowance_count) || !empty($offset_count) || !empty($project_employee_approval_count) || !empty($project_phase_approval_count) || !empty($task_management_approval_count) || !empty($task_completion_approval_count) || !empty($project_application_approval_count) || !empty($project_dev_approval_count) || !empty($bug_application_approval_count) || !empty($bug_employee_approval_count) || !empty($bug_phase_approval_count)){
              ?>

              <li class='header'>REQUEST APPROVAL MENU </li>
              <li class='treeview <?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("overtime_approval.php","leave_approval.php","ob_approval.php","shift_approval.php","adjustments_approval.php","offset_approval.php", "allowance_approval.php","ot_adjustments_approval.php","project_employee_approval.php","project_phase_approval.php","task_management_approval.php","task_completion_approval.php","project_application_approval.php","project_development_approval.php","bug_application_approval.php","bug_employee_approval.php","bug_phase_approval.php")))?"active":"";?>'>
                  <a href="#">
                      <i class="fa fa-file-text"></i>
                      <span>For Approval</span> &nbsp;
                      <?php echo empty($total_for_approval)?'':"<small class='label bg-primary text-left'>{$total_for_approval}</small>";?>
                      <i class="fa fa-angle-down pull-right"></i>
                      
                  </a>
                  <ul class='treeview-menu'>                
                      <!-- <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="overtime_approval.php"?"active":"";?>'>
                      <a href='overtime_approval.php'><i class="fa fa-file-text"></i><span>Overtime </span> <?php echo empty($ot_count)?'':"<small class='label pull-right bg-primary'>{$ot_count}</small>";?></a>  </li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="ot_adjustments_approval.php"?"active":"";?>'>
                      <a href='ot_adjustments_approval.php'><i class="fa fa-file-text"></i><span>Overtime Adjustments</span> <?php echo empty($ot_adjustment_count)?'':"<small class='label pull-right bg-primary'>{$ot_adjustment_count}</small>";?></a>  </li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="leave_approval.php"?"active":"";?>'>
                      <a href='leave_approval.php'><i class="fa fa-file-text"></i><span>Leave</span> <?php echo empty($leave_count)?'':"<small class='label pull-right bg-primary'>{$leave_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="ob_approval.php"?"active":"";?>'>
                      <a href='ob_approval.php'><i class="fa fa-file-text"></i><span>Official Business</span> <?php echo empty($ob_count)?'':"<small class='label pull-right bg-primary'>{$ob_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="shift_approval.php"?"active":"";?>'>
                      <a href='shift_approval.php'><i class="fa fa-file-text"></i><span>Change Shift</span> <?php echo empty($shift_count)?'':"<small class='label pull-right bg-primary'>{$shift_count}</small>";?></a></li>          
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="adjustments_approval.php"?"active":"";?>'>
                      <a href='adjustments_approval.php'><i class="fa fa-file-text"></i><span>Attendance Adjustment </span> <?php echo empty($adj_count)?'':"<small class='label pull-right bg-primary'>{$adj_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="offset_approval.php"?"active":"";?>'>
                      <a href='offset_approval.php'><i class="fa fa-file-text"></i><span>Offset </span> <?php echo empty($offset_count)?'':"<small class='label pull-right bg-primary'>{$offset_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="allowance_approval.php"?"active":"";?>'>
                      <a href='allowance_approval.php'><i class="fa fa-file-text"></i><span>Allowance </span> <?php echo empty($allowance_count)?'':"<small class='label pull-right bg-primary'>{$allowance_count}</small>";?></a></li> -->
                      <?php if($usertype=='4'){ ?>
                       <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="project_application_approval.php"?"active":"";?>'>
                      <a href='project_application_approval.php'><i class="fa fa-circle-o"></i><span>Project Application</span> <?php echo empty($project_application_approval_count)?'':"<small class='label pull-right bg-primary'>{$project_application_approval_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="bug_application_approval.php"?"active":"";?>'>
                      <a href='bug_application_approval.php'><i class="fa fa-circle-o"></i><span>Bug Application</span> <?php echo empty($bug_application_approval_count)?'':"<small class='label pull-right bg-primary'>{$bug_application_approval_count}</small>";?></a></li>
                      <?php } if(($usertype=='5')OR($usertype=='2')){}else{ ?>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="project_development_approval.php"?"active":"";?>'>
                      <a href='project_development_approval.php'><i class="fa fa-circle-o"></i><span>Project Development</span> <?php echo empty($project_dev_approval_count)?'':"<small class='label pull-right bg-primary'>{$project_dev_approval_count}</small>";?></a></li>
                      <?php } ?>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="task_management_approval.php"?"active":"";?>'>
                      <a href='task_management_approval.php'><i class="fa fa-circle-o"></i><span>Task Assignment</span> <?php echo empty($task_management_approval_count)?'':"<small class='label pull-right bg-primary'>{$task_management_approval_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="task_completion_approval.php"?"active":"";?>'>
                      <a href='task_completion_approval.php'><i class="fa fa-circle-o"></i><span>Task Completion</span> <?php echo empty($task_completion_approval_count)?'':"<small class='label pull-right bg-primary'>{$task_completion_approval_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="project_phase_approval.php"?"active":"";?>'>
                      <a href='project_phase_approval.php'><i class="fa fa-circle-o"></i><span>Project Phase Status</span> <?php echo empty($project_phase_approval_count)?'':"<small class='label pull-right bg-primary'>{$project_phase_approval_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="bug_phase_approval.php"?"active":"";?>'>
                      <a href='bug_phase_approval.php'><i class="fa fa-circle-o"></i><span>Bug Phase Status</span> <?php echo empty($bug_phase_approval_count)?'':"<small class='label pull-right bg-primary'>{$bug_phase_approval_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="project_employee_approval.php"?"active":"";?>'>
                      <a href='project_employee_approval.php'><i class="fa fa-circle-o"></i><span>Project Employees</span> <?php echo empty($project_employee_approval_count)?'':"<small class='label pull-right bg-primary'>{$project_employee_approval_count}</small>";?></a></li>
                      <li class='<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="bug_employee_approval.php"?"active":"";?>'>
                      <a href='bug_employee_approval.php'><i class="fa fa-circle-o"></i><span>Bug Employees</span> <?php echo empty($bug_employee_approval_count)?'':"<small class='label pull-right bg-primary'>{$bug_employee_approval_count}</small>";?></a></li>
                  </ul>
              </li>            

<!--END OF FOR APPROVAL MENU-->

<!--EMPLOYEE SELF SERVICE MENU-->
           <?php
              }
              ?>
            <?php 
            $ot_query=$con->myQuery("SELECT COUNT(id) FROM `employees_ot` where (employees_id=:employee_id OR requestor_id=:employee_id) AND request_status_id = 3",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $ot_adjustment_query=$con->myQuery("SELECT COUNT(id) FROM `employees_ot_adjustments` where (employees_id=:employee_id OR requestor_id=:employee_id) AND request_status_id = 3",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $ot_return_count=intval($ot_query) +intval($ot_adjustment_query);

            $leave_query=$con->myQuery("SELECT COUNT(id) FROM `employees_leaves` where (employee_id=:employee_id OR requestor_id=:employee_id) AND request_status_id = 3",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $adj_query=$con->myQuery("SELECT COUNT(id) FROM `employees_adjustments` where employees_id=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $shift_query=$con->myQuery("SELECT COUNT(id) FROM `employees_change_shift` where (employees_id=:employee_id OR requestor_id=:employee_id) AND request_status_id = 3",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $ob_query=$con->myQuery("SELECT COUNT(id) FROM `employees_ob` where employees_id=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $offset_query=$con->myQuery("SELECT COUNT(id) FROM `employees_offset_request` where (employees_id=:employee_id OR requestor_id=:employee_id) AND request_status_id = 3",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $allowance_query=$con->myQuery("SELECT COUNT(id) FROM `employees_allowances` where employees_id=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $project_employee_query=$con->myQuery("SELECT COUNT(id) FROM `project_requests` WHERE employee_id=? AND status_id = 3 AND is_deleted=0",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $project_phase_query=$con->myQuery("SELECT COUNT(id) FROM `project_phase_request` WHERE employee_id=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $task_management_query=$con->myQuery("SELECT COUNT(id) FROM `project_task` WHERE employee_id=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $project_development_query=$con->myQuery("SELECT COUNT(id) FROM `project_development` WHERE employee_id=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $bug_application_query=$con->myQuery("SELECT COUNT(id) FROM `project_bug_application` WHERE employee_id=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $bug_employee_query=$con->myQuery("SELECT COUNT(id) FROM `project_bug_employee` WHERE requested_by=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $bug_phase_query=$con->myQuery("SELECT COUNT(id) FROM `project_bug_request` WHERE employee_id=? AND request_status_id = 3",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
            $total_return=intval($ot_return_count) + intval($leave_query) + intval($adj_query) + intval($shift_query) + intval($ob_query)+intval($allowance_query)+intval($project_employee_query)+intval($project_phase_query)+intval($task_management_query)+intval($project_development_query)+intval($bug_application_query)+intval($bug_employee_query) +intval($bug_phase_query);
            // if(!empty($ot_query) || !empty($ot_adjustment_query) || !empty($ot_return_count) || !empty($adj_query) || !empty( $shift_query) || !empty($ob_query) || !empty($offset_query) || !empty($allowance_query) || !empty($project_employee_query) ){
          ?>

            <li class='header'>EMPLOYEE SELF SERVICE MENU</li>
            <li class='treeview <?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("overtime.php","employee_leave_request.php","adjustment_request.php","shift_request.php","ob_request.php","offset.php", "allowance_request.php","project_employee_request.php","project_phase_request.php","task_management_request.php","project_development_request.php","bug_application_request.php","bug_employee_request.php","bug_phase_request.php")))?"active":"";?>'>
                <a href="#">
                    <i class="fa fa-file-text"></i>
                    <span>My Requests</span> &nbsp;
                    <?php echo empty($total_return)?'':"<small class='label bg-primary'>{$total_return}</small>";?>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class='treeview-menu'>
                   <!-- <?php
                    if (!empty($_SESSION[WEBAPP]['user']['allow_overtime'])) {
                    ?>
                   <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="overtime.php"?"active":"";?>">
                      <a href="overtime.php"><i class="fa fa-circle-o"></i> <span>Overtime</span> <?php echo empty($ot_return_count)?'':"<small class='label pull-right bg-primary'>{$ot_return_count}</small>";?></a>
                    </li>
                    <?php
                    }
                    ?>

                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="employee_leave_request.php"?"active":"";?>">
                      <a href="employee_leave_request.php"><i class="fa fa-circle-o"></i> <span>Leave</span> <?php echo empty($leave_query)?'':"<small class='label pull-right bg-primary'>{$leave_query}</small>";?></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="adjustment_request.php"?"active":"";?>">
                      <a href="adjustment_request.php"><i class="fa fa-circle-o"></i> <span>Attendance Adjustments</span> <?php echo empty($adj_query)?'':"<small class='label pull-right bg-primary'>{$adj_query}</small>";?></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="shift_request.php"?"active":"";?>">
                      <a href="shift_request.php"><i class="fa fa-circle-o"></i> <span>Change Shift</span> <?php echo empty($shift_query)?'':"<small class='label pull-right bg-primary'>{$shift_query}</small>";?></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="ob_request.php"?"active":"";?>">
                      <a href="ob_request.php"><i class="fa fa-circle-o"></i> <span>Official Business</span> <?php echo empty($ob_query)?'':"<small class='label pull-right bg-primary'>{$ob_query}</small>";?></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="offset.php"?"active":"";?>">
                      <a href="offset.php"><i class="fa fa-circle-o"></i> <span>Offset</span> <?php echo empty($offset_query)?'':"<small class='label pull-right bg-primary'>{$offset_query}</small>";?></a>
                    </li>
                    <?php
                    if (!empty($_SESSION[WEBAPP]["user"]["can_apply_for_meal_transpo"])) {
                    ?>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="allowance_request.php"?"active":"";?>">
                      <a href="allowance_request.php"><i class="fa fa-circle-o"></i> <span>Allowance</span> <?php echo empty($allowance_query)?'':"<small class='label pull-right bg-primary'>{$allowance_query}</small>";?></a>
                    </li>
                    <?php
                    }
                    ?> -->
                    <?php if(($usertype=='5')OR($usertype=='2')){}else{?>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="project_development_request.php"?"active":"";?>">
                      <a href="project_development_request.php"><i class="fa fa-circle-o"></i> <span>Project Development</span> <?php echo empty($project_development_query)?'':"<small class='label pull-right bg-primary'>{$project_development_query}</small>";?></a>
                    </li>
                    <?php } if($usertype=='5'){?>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="bug_application_request.php"?"active":"";?>">
                      <a href="bug_application_request.php"><i class="fa fa-circle-o"></i> <span>Bug Application</span> <?php echo empty($bug_application_query)?'':"<small class='label pull-right bg-primary'>{$bug_application_query}</small>";?></a>
                    </li>
                    <?php } if($usertype!='5'){?>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="task_management_request.php"?"active":"";?>">
                      <a href="task_management_request.php"><i class="fa fa-circle-o"></i> <span>Task Assignment</span> <?php echo empty($task_management_query)?'':"<small class='label pull-right bg-primary'>{$task_management_query}</small>";?></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="project_phase_request.php"?"active":"";?>">
                      <a href="project_phase_request.php"><i class="fa fa-circle-o"></i> <span>Project Phase Status</span> <?php echo empty($project_phase_query)?'':"<small class='label pull-right bg-primary'>{$project_phase_query}</small>";?></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="bug_phase_request.php"?"active":"";?>">
                      <a href="bug_phase_request.php"><i class="fa fa-circle-o"></i> <span>Bug Phase Status</span> <?php echo empty($bug_phase_query)?'':"<small class='label pull-right bg-primary'>{$bug_phase_query}</small>";?></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="project_employee_request.php"?"active":"";?>">
                      <a href="project_employee_request.php"><i class="fa fa-circle-o"></i> <span>Project Employees</span> <?php echo empty($project_employee_query)?'':"<small class='label pull-right bg-primary'>{$project_employee_query}</small>";?></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="bug_employee_request.php"?"active":"";?>">
                      <a href="bug_employee_request.php"><i class="fa fa-circle-o"></i> <span>Bug Employees</span> <?php echo empty($bug_employee_query)?'':"<small class='label pull-right bg-primary'>{$bug_employee_query}</small>";?></a>
                    </li>
                    <?php }?>
                </ul>
            </li>
             <li class='treeview <?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("my_project_files.php","my_bug_files.php")))?"active":"";?>'>
              <a href="#">
                <i class="fa fa-file"></i>
                <span>Document Management</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class='treeview-menu'>
              <?php if($usertype!=5){ ?>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="my_project_files.php"?"active":"";?>">
                  <a href="my_project_files.php"><i class="fa fa-circle-o"></i> <span>Project Files</span></a>
                </li>
     <!--            <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="my_files.php"?"active":"";?>">
                  <a href="my_files.php"><i class="fa fa-circle-o"></i> <span>My Files</span></a>
                </li> -->
                <?php } ?>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="my_bug_files.php"?"active":"";?>">
                  <a href="my_bug_files.php"><i class="fa fa-circle-o"></i> <span>Bug Files</span></a>
                </li>
              </ul>
            </li>
      <li class='treeview <?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("attendance_report.php","dtr_report.php","leave_reports.php","leave_entitlement_reports.php","employee_details_report.php","adjustment_reports.php","change_shift_reports.php","ob_reports.php","dtr_report.php","overtime_report.php","weekly_report.php", "pay_slip.php","calendar_employee_leaves.php","overtime_adjustment_reports.php","for_regularization.php","task_reports.php","project_phase_reports.php","bug_reports.php")))?"active":"";?>'>
              <a href="#">
                <i class="fa fa-file-text"></i>
                <span>Reports</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class='treeview-menu'>
<!--                 <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="attendance_report.php"?"active":"";?>">
                  <a href="attendance_report.php"><i class="fa fa-circle-o"></i> <span>Attendance Report</span></a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="weekly_report.php"?"active":"";?>">
                  <a href="weekly_report.php"><i class="fa fa-circle-o"></i> <span>Weekly Report</span></a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="dtr_report.php"?"active":"";?>">
                  <a href="dtr_report.php"><i class="fa fa-circle-o"></i> <span>DTR Report</span></a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="leave_reports.php"?"active":"";?>">
                  <a href="leave_reports.php"><i class="fa fa-circle-o"></i> <span>Leaves Report</span></a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="leave_entitlement_reports.php"?"active":"";?>">
                  <a href="leave_entitlement_reports.php"><i class="fa fa-circle-o"></i> <span>Leaves Entitlement Report</span></a>
                </li>
                <?php
                if (!empty($_SESSION[WEBAPP]["user"]["view_employee_leave_calendar"])) {
                ?>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="calendar_employee_leaves.php"?"active":"";?>">
                  <a href="calendar_employee_leaves.php"><i class="fa fa-circle-o"></i> <span>Calendar of Leaves</span></a>
                </li>
                <?php
                }
                ?>

            <?php
              if(AllowUser(array(1,2,3,4,5))):
            ?>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="employee_details_report.php"?"active":"";?>">
                  <a href="employee_details_report.php"><i class="fa fa-circle-o"></i> <span>Employee Details</span></a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="for_regularization.php"?"active":"";?>">
                  <a href="for_regularization.php"><i class="fa fa-circle-o"></i> <span>For Regularization</span></a>
                </li>
            <?php
              endif;
            ?>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="adjustment_reports.php"?"active":"";?>">
                  <a href="adjustment_reports.php">
                    <i class="fa fa-circle-o"></i> <span>Attendance Adjustments</span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="change_shift_reports.php"?"active":"";?>">
                  <a href="change_shift_reports.php">
                    <i class="fa fa-circle-o"></i> <span>Shift Change
                    </span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="ob_reports.php"?"active":"";?>">
                  <a href="ob_reports.php">
                    <i class="fa fa-circle-o"></i> <span>Official Business</span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="overtime_report.php"?"active":"";?>">
                  <a href="overtime_report.php">
                    <i class="fa fa-circle-o"></i> <span>Overtime</span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="overtime_adjustment_reports.php"?"active":"";?>">
                  <a href="overtime_adjustment_reports.php">
                    <i class="fa fa-circle-o"></i> <span>Overtime Adjustments</span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="allowance_report.php"?"active":"";?>">
                  <a href="allowance_report.php">
                    <i class="fa fa-circle-o"></i> <span>Allowance</span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="pay_slip.php"?"active":"";?>">
                  <a href="pay_slip.php">
                    <i class="fa fa-circle-o"></i> <span>Payslip</span>
                  </a>
                </li> -->
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="project_phase_reports.php"?"active":"";?>">
                  <a href="project_phase_reports.php">
                    <i class="fa fa-circle-o"></i> <span>Project</span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="task_reports.php"?"active":"";?>">
                  <a href="task_reports.php">
                    <i class="fa fa-circle-o"></i> <span>Employee Tasks</span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="bug_reports.php"?"active":"";?>">
                  <a href="bug_reports.php">
                    <i class="fa fa-circle-o"></i> <span>Bug Lists</span>
                  </a>
                </li>
              </ul>
            </li>
<!--END OF EMPLOYEE SELF SERVICE MENU-->

            <?php
              if(AllowUser(array(1,2,3,4,5))):
            ?>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="frm_change_pass.php"?"active":"";?>">
                  <a href="frm_change_pass.php"><i class="fa fa-unlock"></i> <span>Change Password</span></a>
                </li>

            <?php
              endif;
            ?>


            <?php
              if(AllowUser(array(1,4))):
            ?>
            <li class='header'>ADMINISTRATOR MENU</li>
            <li class='treeview <?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("audit_log.php","employees.php","terminated_employees.php","users.php","education_level.php","skills.php","trainings.php","certifications.php","job_title.php","leave_type.php","departments.php","monitor_attendance.php","company_files.php","employee_files.php","tax_status.php","pay_grade.php","employment_status.php","approval_matrix.php","settings.php","company_settings.php","holidays.php")))?"active":"";?>'>
              <a href=''><i class="fa fa-user-secret"></i><span>Administrator</span><i class="fa fa-angle-left pull-right"></i></a>
              <ul class='treeview-menu'>
                <!-- <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="audit_log.php"?"active":"";?>">
                  <a href="audit_log.php">
                    <i class="fa fa-list"></i> <span>Audit Log</span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="employees.php"?"active":"";?>">
                  <a href="employees.php">
                    <i class="fa fa-users"></i> <span>Employees</span>
                  </a>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="terminated_employees.php"?"active":"";?>">
                  <a href="terminated_employees.php">
                    <i class="fa fa-users"></i> <span>Terminated Employees</span>
                  </a>
                </li> -->
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="users.php"?"active":"";?>">
                  <a href="users.php">
                    <i class="fa fa-users"></i> <span>Users</span>
                  </a>
                </li>
                <!-- <li class='<?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("education_level.php","skills.php","trainings.php","certifications.php","job_title.php","holidays.php")))?"active":"";?>'>
                  <a href="#">
                    <i class="fa fa-check-square-o"></i>
                    <span>Job Details</span>
                    <i class="fa fa-angle-left pull-right"></i>
                  </a>
                  <ul class='treeview-menu'>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="education_level.php"?"active":"";?>">
                      <a href="education_level.php"><i class="fa fa-circle-o"></i> <span>Education Levels</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="skills.php"?"active":"";?>">
                      <a href="skills.php"><i class="fa fa-circle-o"></i> <span>Skills</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="trainings.php"?"active":"";?>">
                      <a href="trainings.php"><i class="fa fa-circle-o"></i> <span>Trainings</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="certifications.php"?"active":"";?>">
                      <a href="certifications.php"><i class="fa fa-circle-o"></i> <span>Certifications</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="job_title.php"?"active":"";?>">
                      <a href="job_title.php"><i class="fa fa-circle-o"></i> <span>Job Titles</span></a>
                    </li>
                  </ul>
                </li> -->

                <!-- <li class='<?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("company_deductions.php","deminimis.php","taxable_allowances.php")))?"active":"";?>'>
                    <a href="#">
                        <i class="fa fa-gears"></i>
                        <span>Payroll Settings</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class='treeview-menu'>
                        <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="company_deductions.php"?"active":"";?>">
                            <a href="company_deductions.php"> <i class="fa fa-minus-square"></i> <span>Company Deductions</span> </a>
                        </li>
                        <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="deminimis.php"?"active":"";?>">
                            <a href="deminimis.php"> <i class="fa fa-plus-square"></i> <span>De Minimis/Non Taxable</span> </a>
                        </li>
                        <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="taxable_allowances.php"?"active":"";?>">
                            <a href="taxable_allowances.php"> <i class="fa fa-plus-square"></i> <span>Receivable/Taxable</span> </a>
                        </li>
                    </ul>
                </li>

                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="leave_type.php"?"active":"";?>">
                  <a href="leave_type.php">
                    <i class="fa fa-building"></i> <span>Leaves</span>
                  </a>
                </li> -->
       <!--          <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="departments.php"?"active":"";?>">
                  <a href="departments.php">
                    <i class="fa fa-building"></i> <span>Departments</span>
                  </a>
                </li> -->
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="holidays.php"?"active":"";?>">
                    <a href="holidays.php">
                        <i class="fa fa-calendar-times-o"></i> <span>Holidays</span>
                    </a>
                </li>
   <!--              <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="monitor_attendance.php"?"active":"";?>">
                  <a href="monitor_attendance.php">
                    <i class="fa fa-clock-o"></i> <span>Monitor Attendance</span>
                  </a>
                </li>
                <li class='<?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("employees_file.php")))?"active":"";?>'>
                  <a href="#">
                    <i class="fa fa-check-square-o"></i>
                    <span>Document Management</span>
                    <i class="fa fa-angle-left pull-right"></i>
                  </a> -->
                  <!-- <ul class='treeview-menu'> -->
                    <!-- <li class="<?php //echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="company_files.php"?"active":"";?>">
                      <a href="company_files.php"><i class="fa fa-circle-o"></i> <span>Company Files</span></a>
                    </li> -->
                    <!-- <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="employees_file.php"?"active":"";?>">
                      <a href="employees_file.php"><i class="fa fa-circle-o"></i> <span>Employee Files</span></a>
                    </li>
                  </ul>
                </li>
                <li class='treeview <?php echo (in_array(substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1), array("tax_status.php","pay_grade.php","employment_status.php","approval_matrix.php","settings.php")))?"active":"";?>'>
                  <a href="#">
                    <i class="fa fa-sort-alpha-asc"></i>
                    <span>Metadata</span>
                    <i class="fa fa-angle-left pull-right"></i>
                  </a>
                  <ul class='treeview-menu'>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="tax_status.php"?"active":"";?>">
                      <a href="tax_status.php"><i class="fa fa-circle-o"></i> <span>Tax Status</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="pay_grade.php"?"active":"";?>">
                      <a href="pay_grade.php"><i class="fa fa-circle-o"></i> <span>Pay Grades</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="employment_status.php"?"active":"";?>">
                      <a href="employment_status.php"><i class="fa fa-circle-o"></i> <span>Employment Status</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="approval_matrix.php"?"active":"";?>">
                      <a href="approval_matrix.php"><i class="fa fa-circle-o"></i> <span>Approval Matrix</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="default_pass.php"?"active":"";?>">
                      <a href="default_pass.php"><i class="fa fa-circle-o"></i> <span>Default Password</span></a>
                    </li>
                    <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="settings.php"?"active":"";?>">
                      <a href="settings.php"><i class="fa fa-circle-o"></i> <span>System Settings</span></a>
                    </li>
                  </ul>
                </li>
                <li class="<?php echo (substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'], "/")+1))=="company_settings.php"?"active":"";?>">
                  <a href="company_settings.php">
                    <i class="fa fa-building"></i> <span>Company Profile</span>
                  </a>
                </li> -->
              </ul>
            </li>
            <?php
              endif;
            ?>
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>