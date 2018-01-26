<?php
	require_once("../support/config.php");
	
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if(!empty($_POST))
	{
	#VALIDATE INPUTS
		$inputs=$_POST;			
		// $inputs=array_map('trim', $inputs);

	#UPDATE LEAVE CONVERSION TABLE FROM UNPROCESSED TO PROCESSED
		$con->myQuery("UPDATE leave_conversion SET is_processed=1, date_processed=CURDATE() WHERE id=?",array($inputs['p_id']));

	#UPDATE LEAVE CONVERSION DETAILS 
		$get_details = $con->myQuery("SELECT * FROM leave_conversion_details WHERE leave_conversion_id=?",array($inputs['p_id']));

		while($row=$get_details->fetch(PDO::FETCH_ASSOC))
		{
			$con->myQuery("UPDATE employees_available_leaves SET is_converted=1, is_cancelled=1, date_cancelled=NOW() WHERE employee_id=? AND leave_id=? AND is_cancelled=0",array($row['employee_id'],$row['leave_id']));
		}
		// die();
		Alert("Successfully Processed!","success");
		redirect("leave_conversion_view.php?id=".$inputs['p_id']);
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>