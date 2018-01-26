<?php
require_once '../support/config.php';
//$con->myQuery("SELECT FROM comments c WHERE ");
$empty_message="No data available in table.";


$a = substr($_GET['id'], 0, 1);
if ($a == "o")
{
	$_GET['request_type'] = "overtime";
	$_GET['id'] = ltrim($_GET['id'],'o');
}
if ($a == "p")
{
	$_GET['request_type'] = "pre_overtime";
	$_GET['id'] = ltrim($_GET['id'],'p');
}

if(!empty($_GET['id']) && !empty($_GET['request_type'])){
	if(in_array($_GET['request_type'], array("leave","overtime","official_business","shift","adjustment","pre_overtime","offset","allowance","ot_adjustment","project_approval_emp","project_approval_phase","task_management_approval","task_completion_approval","project_application_approval","project_development_approval"))){
		$messages=$con->myQuery("SELECT message,
								(SELECT CONCAT(last_name,', ',first_name,' ',middle_name) FROM employees e WHERE e.id=sender_id) as sender,
								(SELECT CONCAT(last_name,', ',first_name,' ',middle_name) FROM employees e WHERE e.id=receiver_id) as receiver,
								date_sent,
								sender_id 
								FROM comments 
								WHERE request_type=? and request_id=? ORDER BY date_sent DESC",array($_GET['request_type'],$_GET['id']))->fetchAll(PDO::FETCH_ASSOC);

		//echo $_GET['request_type']."<br>".$_GET['id'];
		// var_dump($messages);
		// die();

		if(empty($messages)){
			echo $empty_message;
		}
		else{
			//echo "<ul class='timeline'>";
			echo "<div class='direct-chat-messages direct-chat-primary'>";
			foreach ($messages as $row):
			?>
				<div class='direct-chat-msg <?php echo $row['sender_id']==$_SESSION[WEBAPP]['user']['employee_id']?'right':''?>'>
					
				<div class='direct-chat-info clearfix'>
					<span class='direct-chat-name pull-left'><?php echo htmlspecialchars($row['sender']) ?></span>
					<span class='direct-chat-timestamp pull-right'><?php echo htmlspecialchars($row['date_sent']) ?></span>
				</div>
				<div class='direct-chat-text'>
					<?php echo htmlspecialchars($row['message'])?>
				</div>
				</div>
			<!-- <li>
			<div class='timeline-item'>
				<span class='time'>
					<i class='fa fa-clock-o'></i>
					<?php //echo htmlspecialchars($row['date_sent'])?>
				</span>
				<div class='timeline-header'>
					<a><?php //echo htmlspecialchars($row['sender'])?></a>
				</div>
				<div class='timeline-body'>
					<?php //echo htmlspecialchars($row['message'])?>
				</div>
			</div>
			</li> -->
			<?php

			endforeach;
			echo "</div>";
			//echo "</ul>";
		}


	}
	else{
		//var_dump($_GET['request_type']);
		//var_dump(in_array("leave",  array("leave","overtime","official_business","change_shift","attendance_adjustment")));
		//	var_dump(in_array($_GET['request_type'], array("leave","overtime","official_business","change_shift","attendance_adjustment")));

		echo $empty_message;
	}
}
else{
	echo $empty_message;
}
?>