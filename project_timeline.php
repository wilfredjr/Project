<?php
 require_once("support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}
makeHead("Project Timeline");

		$p1=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=1 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
	    $p2=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=2 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
	    $p3=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=3 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
	    $p4=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=4 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
	    $p5=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=5 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
	    $p6=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=6 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
	    $p7=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=7 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
	    $p8=$con->myQuery("SELECT pp.phase_name AS name ,ppd.date_end AS date,ppd.status_id,ps.status_name AS status FROM project_phase_dates ppd JOIN project_phases pp ON pp.id=ppd.project_phase_id JOIN project_status ps ON ppd.status_id=ps.id WHERE project_phase_id=8 AND project_id=? ",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
	    ?>
<!--timeline-->
						<div class="blah">
						<ul class="timeline" id="timeline">
						<!--p1-->
						<?php if($p1['status_id']=='1'){echo '<li class="li ongoing">';}
								elseif($p1['status_id'=='2']){echo '<li class="li complete">';}
								elseif($p1['status_id'=='3']){echo '<li class="li">';}
								elseif($p1['status_id'=='4']){echo '<li class="li delayed">';}
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
						      <span class="author"><b>Business Analyst</b></span>
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
						 <!--timeline end-->	
						 </div>
<?php
makeFoot(WEBAPP);
?>