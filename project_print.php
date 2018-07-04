<?php
	require_once("support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

$inputs=$_POST;
	if(!empty($inputs['project_id']))
	{
		$data=$con->myQuery("SELECT pp.id as phase_id,phase_name,date_end,status_name,date_start,project_id,p_days,a_days FROM project_phase_dates pjd JOIN project_phases pp ON pp.id=pjd.project_phase_id JOIN project_status ps ON ps.id=pjd.status_id WHERE project_id=? ORDER BY pjd.project_phase_id",array($inputs['project_id']))->fetchAll(PDO::FETCH_ASSOC);
		$proj=$con->myQuery("SELECT p.name,p.description,p.start_date,p.end_date,p.a_end_date,ps.status_name,p.cur_phase,pp.phase_name,
			(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e WHERE e.id=p.manager_id) as manager,
			(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e WHERE e.id=p.team_lead_ba) as ba,
			(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e WHERE e.id=p.team_lead_dev) as dev
			FROM projects p JOIN project_status ps ON ps.id=p.project_status_id JOIN project_phases pp ON pp.id=p.cur_phase WHERE p.id=?",array($inputs['project_id']))->fetch(PDO::FETCH_ASSOC);
		$def=$con->myQuery("SELECT pd.date_start,pd.date_end,pd.done_days,pp.phase_name FROM project_deficit pd JOIN project_phases pp ON pp.id=pd.project_phase_id WHERE pd.project_id=?",array($inputs['project_id']))->fetchAll(PDO::FETCH_ASSOC);
		$tot=$con->myQuery("SELECT SUM(done_days) as totdays FROM project_deficit WHERE project_id=?",array($inputs['project_id']))->fetch(PDO::FETCH_ASSOC);

	}else{
		redirect("project_phase_reports.php");
	}

	makeHead("Project Information");
?>
<div class='col-md-12 no-print' align='right'>
	<br>
	<a href='project_phase_reports.php' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Back</a>
	<button onclick='window.print()' class='btn btn-brand no-print'>Print &nbsp;<span class='fa fa-print'></span></button>  
</div>
<div class='page'>
	<div class="row">
		<br><br>
		<div align="center">
				<img src='dist/img/bg1.png' />
		</div>
		<h5 align="center" > 1805A East Tower, Philippine Stock Exchange Centre, Exchange Rd, Ortigas, Pasig City </h5>
		<br>
		<div class="col-md-12" style="padding-left: 50px" >
		<p align="left"  >Date Printed: <?php echo date("m/d/Y") ?></p>
		</div>
	</div>
	<hr>
	<div>
		<table>
			<tr><td><b> Project Name</b></td><td>: <?php echo $proj['name'];?> </td><td><b> Description</b></td><td>: <?php echo $proj['description'];?> </td></tr>
			<tr><td><b> Current Phase</b></td><td>: <?php echo $proj['phase_name'];?> </td><td><b> Project Status</b></td><td>: <?php echo $proj['status_name'];?> </td></tr>
			<tr><td><b> Date Start</b></td><td>: <?php echo $proj['start_date'];?> </td><td><b> Date End</b> </td><td>: <?php echo $proj['end_date'];?> </td></tr>
			<tr><td><b> Project Manager</b></td><td>: <?php echo $proj['manager'];?> </td><td><b> Actual Date End</b></td><td>: <?php echo $proj['a_end_date'];?> </td></tr>
			<tr><td><b> Team Lead BA</b></td><td>: <?php echo $proj['ba'];?> </td><td><b> Team Lead Dev</b> </td><td>: <?php echo $proj['dev'];?> </td></tr>
			<tr><td><b> No. of Days Delayed</b></td><td>: <?php if(empty($tot['totdays'])){echo "0";}else{echo $tot['totdays'];}?> </td></tr>
		</table>
	</div>
	<div class="row col-md-12">
		<br>
		<br><div>
			<h4> Project Phases </h4>
		                  <div class='col-md-12'>
                    <table >
                      <thead>
                        <th class='text-center'></th>
                        <th class='text-center'>Phase Name</th>
                        <th class='text-center'>Date Start</th>
                        <th class='text-center'>Date End</th>
                        <th class='text-center'>Planned No. of Days</th>
                        <th class='text-center'>Actual No. of Days</th>
                        <th class='text-center'>Status</th>
                        
                      </thead>
                      <tbody>
                        <?php
                          foreach ($data as $row):
                        ?>
                          <tr class="text-center">
                            <td><?php echo htmlspecialchars($row['phase_id']) ?></td>
							<td><?php echo htmlspecialchars($row['phase_name']) ?></td>
                            <td><?php echo htmlspecialchars($row['date_start']) ?></td>
                            <td><?php echo htmlspecialchars($row['date_end']) ?></td>
                            <td><?php echo htmlspecialchars($row['p_days']) ?></td>
                            <td><?php echo htmlspecialchars($row['a_days']) ?></td>
                            <td><?php echo htmlspecialchars($row['status_name']) ?></td>
                          </tr>
                        <?php
                          endforeach;
                        ?>
                      </tbody>
                    </table>
                  </div></div><br><br>
                  <?php if(!empty($def)){?>
                  <div>
                  	<h4> Project Deficit </h4>
                  <div class='col-md-12'>
                    <table >
                      <thead>
                        <th class='text-center'>Phase Name</th>
                        <th class='text-center'>Date Start</th>
                        <th class='text-center'>Date End</th>
                        <th class='text-center'>No. of Days</th>         
                      </thead>
                      <tbody>
                        <?php
                          foreach ($def as $row):
                        ?>
                          <tr class="text-center">
							<td><?php echo htmlspecialchars($row['phase_name']) ?></td>
                            <td><?php echo htmlspecialchars($row['date_start']) ?></td>
                            <td><?php echo htmlspecialchars($row['date_end']) ?></td>
                            <td><?php echo htmlspecialchars($row['done_days']) ?></td>
                          </tr>
                        <?php
                          endforeach;
                        ?>
                      </tbody>
                    </table>
                  </div></div>
                  <?php } ?>
	</div>
</div>


