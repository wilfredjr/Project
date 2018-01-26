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
		$inputs=array_map('trim', $inputs);

	#UPDATE 13TH MONTH TABLE FROM UNPROCESSED TO PROCESSED
		$con->myQuery("UPDATE 13th_month SET is_processed=1, date_processed=CURDATE() WHERE id=?",array($inputs['p_id']));

	#UPDATE PAYROLL DETAILS 
		$get_id=$con->myQuery("SELECT payroll_details_id FROM 13th_month_payroll_details WHERE 13th_month_id=?",array($inputs['p_id']));

		while($row=$get_id->fetch(PDO::FETCH_ASSOC))
		{
			$con->myQuery("UPDATE payroll_details SET done_13th_month=1 WHERE id=?",array($row['payroll_details_id']));
		}
		// die();
		Alert("Successfully Processed!","success");
		redirect("13th_month_view.php?id=".$inputs['p_id']);
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>