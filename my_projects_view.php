<?php
 require_once("support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("Project Schedule");
?>

<?php


  	if (!empty($_GET['id'])) {
  		
  		

		$project_details=$con->myQuery("SELECT p.id,p.name,p.date_filed,p.project_status_id,p.start_date,p.end_date,ps.id,ps.status_name,p.a_end_date,
		(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e JOIN projects_employees pe WHERE pe.is_manager=1 AND pe.project_id=p.id AND pe.employee_id=e.id) AS manager_id
		FROM projects p INNER JOIN project_status ps ON p.project_status_id = ps.id
	    WHERE p.id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
		$job_title=$con->myQuery("SELECT jb.id,jb.description AS name
		FROM job_title jb WHERE jb.is_deleted='0'")->fetchAll(PDO::FETCH_ASSOC);
		$project_id=$_GET['id'];
		$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
		$manage=AccessForProject($project_id, $employee_id);
		$validate_employee=$con->myQuery("SELECT pe.id as id
		FROM projects_employees pe INNER JOIN projects p ON p.id=pe.project_id
		WHERE pe.is_deleted=0 AND pe.employee_id=:employee_id AND p.id=:id",array("employee_id"=>$employee_id,"id"=>$_GET['id']))->fetchAll(PDO::FETCH_ASSOC);
	    $cur_phase=$con->myQuery("SELECT pp.phase_name AS cur_phase,p.id, pd.name AS cur_des, ppd.designation_id AS cur_des_id, ps.status_name AS cur_status, ppd.project_phase_id,ppd.designation_id,ppd.date_start,ppd.date_end,ppd.project_id FROM projects p
			JOIN project_phase_dates ppd ON p.id=ppd.project_id
		    JOIN project_phases pp ON ppd.project_phase_id=pp.id JOIN project_designation pd ON ppd.designation_id=pd.id
		    JOIN project_status ps ON ppd.status_id=ps.id
		    WHERE ppd.project_id=? AND ppd.project_phase_id=p.cur_phase",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
		//var_dump($validate_employee);
		$proj_def=$con->myQuery("SELECT SUM(done_days) AS done_days FROM project_deficit WHERE project_id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

															$addDay = 86400;	
														    $date_now1= new DateTime($project_details['end_date']);
                                                            $date_start1= new DateTime($project_details['start_date']);
                                                            $interval = $date_now1->diff($date_start1);
                                                            $days = $interval->days;
                                                            $period = new DatePeriod($date_start1, new DateInterval('P1D'), $date_now1);
                                                            foreach($period as $dt) {
                                                            $curr = $dt->format('D');
                                                            $holiday= $con->myQuery("SELECT holiday_date FROM holidays WHERE holiday_date=?",array($dt->format('Y-m-d')))->fetchAll(PDO::FETCH_ASSOC);
                                                            // substract if Saturday or Sunday
                                                            if ($curr == 'Sat' || $curr == 'Sun') {
                                                                $days--;
                                                                }
                                                            // (optional) for the updated question
                                                            elseif (!empty($holiday)) {
                                                                $days--;
                                                                }
                                                            }
                                                            if($days=='0'){$days='1';}else{$days=$days+1;}


														    $date_now1= new DateTime($project_details['a_end_date']);
                                                            $date_start1= new DateTime($project_details['start_date']);
                                                            $interval = $date_now1->diff($date_start1);
                                                            $days1 = $interval->days;
                                                            $period = new DatePeriod($date_start1, new DateInterval('P1D'), $date_now1);
                                                            foreach($period as $dt) {
                                                            $curr = $dt->format('D');
                                                            $holiday= $con->myQuery("SELECT holiday_date FROM holidays WHERE holiday_date=?",array($dt->format('Y-m-d')))->fetchAll(PDO::FETCH_ASSOC);
                                                            // substract if Saturday or Sunday
                                                            if ($curr == 'Sat' || $curr == 'Sun') {
                                                                $days1--;
                                                                }
                                                            // (optional) for the updated question
                                                            elseif (!empty($holiday)) {
                                                                $days1--;
                                                                }
                                                            }
                                                            if($days1=='0'){$days1='1';}else{$days1=$days1+1;}
		if (empty($validate_employee)) {
			 redirect("my_projects.php");
			//die;
		}

  	} else {
  		redirect("my_projects.php");
  	}
  	
    if (empty($_GET['tab'])) {
          
        redirect("my_projects_view.php?id=".$_GET['id']."&tab=1");

    } elseif($_GET['tab'] < 1 || $_GET['tab'] > 5) {
		redirect("my_projects_view.php?id=".$_GET['id']."&tab=1");
    }
  require_once("template/header.php");
  require_once("template/sidebar.php");

    
?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section>
		<div class="content-header">
			<h1 class="page-header text-center">Project Details</h1>
		</div>
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<?php
			Alert();
			Modal();
			?>

		</div>
		<!-- End of Adding -->
		<br>
		<div class='panel panel-default'>
			<div class='panel-body ' >
				<a href="my_projects.php" class="btn btn-default">
				<span class="glyphicon glyphicon-arrow-left"></span>
				My Projects
				</a><br><br>			
				<table>
						<tr>
									<td><b>Project Name: </b><?php echo !empty($project_details['id'])?htmlspecialchars($project_details['name']):''?></td>
									<td><b>Status:</b> <?php echo !empty($project_details['id'])?htmlspecialchars($project_details['status_name']):''?></td>
								</tr>
								<tr>
									<td><b>Date Start:</b> <?php echo !empty($project_details['id'])?htmlspecialchars($project_details['start_date']):''?></td>
									<td><b>Date End:</b> <?php if (!empty($project_details['id'])) {
										if (!empty(strtotime($project_details['end_date']))){
											echo $project_details['end_date'];
										} else {
											echo "------";
										}
									}
									?>
									</td>
								</tr>
								<tr>
									<td><b>Project Manager:</b> <?php echo !empty($project_details['id'])?htmlspecialchars($project_details['manager_id']):''?></td>
									<?php if($project_details['project_status_id']==2){?>
									<td><b>Actual Date End:</b> <?php echo !empty($project_details['id'])?htmlspecialchars($project_details['a_end_date']):''?></td>
									<?php } ?>
								</tr>
								<?php if($project_details['project_status_id']==2){?>
								<tr>
									<td><b>Planned No. of Days:</b> <?php echo !empty($project_details['id'])?htmlspecialchars($days):''?></td>
									<td><b>Actual No. of Days:</b> <?php echo !empty($project_details['id'])?htmlspecialchars($days1):''?></td>
								</tr>
								<tr>
									<td><b>No. of Days (Delayed):</b> <?php if($proj_def['done_days']==''){echo "0";}else{echo $proj_def['done_days'];}?></td>
								</tr>
								<?php } ?>
				</table><hr>
				      <table><tr>
				      <td><b> Current Project Phase: </b><?php echo $cur_phase['cur_phase'];?></td><td><b>Team Assigned: </b><?php echo $cur_phase['cur_des'];?></td></tr>
				      <tr><td><b>Phase Start Date: </b><?php echo $cur_phase['date_start'];?></td><td><b>Phase End Date: </b><?php echo $cur_phase['date_end'];?></td></tr>
				      <tr><td><b>Phase Status: </b><?php echo $cur_phase['cur_status'];?> </td><td>
				      <td></td></tr>
				    </table><br><br>
				<div class="row">
		            <div class='col-md-12'>
		              <div class="nav-tabs-custom">
		                <ul class="nav nav-tabs">
		                	<li <?php if ($_GET['tab'] == 1) {echo "class='active'";} echo "><a href='my_projects_view.php?id=".$_GET['id']."&tab=1'"; ?> >Project Timeline</a>
		                    </li>

		                    <li <?php if ($_GET['tab'] == 2) {echo "class='active'";} echo "><a href='my_projects_view.php?id=".$_GET['id']."&tab=2'"; ?> >Project Member/s</a>
		                    </li>
		                   	<?php  	

							if($manage['is_team_lead_ba']=='1' || $manage['is_manager'] || $manage['is_team_lead_dev'] =='1'){
							?>
  
			                     <li <?php if ($_GET['tab'] == 3) {echo "class='active'";} echo "><a href='my_projects_view.php?id=".$_GET['id']."&tab=3'"; ?> > Employee Request</a>
			                    </li>
		                   
		                    <?php

								}
								
							?>
							<?php if($cur_phase['project_phase_id']=='3'){?>
							<li <?php if ($_GET['tab'] == 4) {echo "class='active'";} echo "><a href='my_projects_view.php?id=".$_GET['id']."&tab=4'"; ?> >Project Development</a>
		                    </li>
		                    <?php } ?>
		                     <li <?php if ($_GET['tab'] == 5) {echo "class='active'";} echo "><a href='my_projects_view.php?id=".$_GET['id']."&tab=5'"; ?> >Project Files</a>
		                    </li>
		                </ul>
		              </div>
		            </div>
		         </div>
				<div class="tab-content">
	            <div class="active tab-pane" >
	                    <?php
	                        switch ($_GET['tab']) {
	                        	case '1':
	                                #Project Details
	                               

	                                $form='my_projects_timeline.php';
	                            break;
	                            case '2':
	                                #Project Details
	                               

	                                $form='project_details.php';
	                            break;
	                            case '3':
	                                #Project Details
	                               

	                                $form='project_add_employee.php';
	                            break;
	                            case '4':
	                                #Project Details
	                               

	                                $form='project_development.php';
	                            break;
	                            case '5':
	                                #Project Details
	                               

	                                $form='project_files.php';
	                            break;


	                        }
	                        require_once($form);
	                    ?>
	            </div>
</div>
<?php
makeFoot(WEBAPP);
?>