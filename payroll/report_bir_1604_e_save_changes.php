<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}


	if(empty($_POST))
	{
		redirect('index.php');
		die;
	}else
	{	
		$inputs = $_POST;
		// $inputs = array_map("trim", $);
		$errors = "";

		for ($i=0; $i < count($inputs['1604E_sched_id']); $i++) 
		{
			$schedule_1 = $con->myQuery("SELECT id,tax_withheld FROM bir_1604_e_schedule_1 WHERE id=?",array($inputs['1604E_sched_id'][$i]))->fetch(PDO::FETCH_ASSOC);
			$id = $schedule_1['id'];

			$tax_withheld 	= floatval($schedule_1['tax_withheld']);
			$penalty 		= floatval($inputs['penalties'][$id]);
			$total_tax 		= $tax_withheld+$penalty;

			$params = array(
					"id" 					=> $id,
					"ror_details" 			=> $inputs['ror_details'][$id],
					"penalties" 			=> $inputs['penalties'][$id],
					"total_amount_remitted" => $total_tax
				);

			$con->myQuery("UPDATE bir_1604_e_schedule_1 SET ror_details=:ror_details, penalties=:penalties, total_amount_remitted=:total_amount_remitted WHERE id=:id",$params);
		}
		
		Alert("Successfully Saved.","success");
		redirect("report_bir_1604_e_view.php?id=".$inputs['sched_id']);
		die();
	}
?>