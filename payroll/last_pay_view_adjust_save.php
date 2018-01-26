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

		if ($inputs['lp_type'] == "Tax Refund" || $inputs['lp_type'] == "Other Benefits") 
		{
			$inputs['operation'] = "Add";
		}
		if ($inputs['lp_type'] == "Other Deductions") 
		{
			$inputs['operation'] = "Minus";
		}

		if(empty($inputs['last_pay_adjust_id']))
		{
			#INSERT INTO
			$params=array(
					"last_pay_id" 		=> $inputs['last_pay_id'],
					"adjustment_type" 	=> $inputs['lp_type'],
					"operation" 		=> $inputs['operation'],
					"amount" 			=> $inputs['lp_amount'],
					"remarks" 			=> $inputs['lp_remarks']
				);
			
			$con->myQuery("INSERT INTO last_pay_adjustments(last_pay_id,adjustment_type,operation,amount,remarks) VALUES(:last_pay_id,:adjustment_type,:operation,:amount,:remarks)",$params);
			
			Alert("Successfully Saved!","success");
		}else
		{
			#UPDATE
			$params2=array(
					"adjustment_type" 	=> $inputs['lp_type'],
					"operation" 		=> $inputs['operation'],
					"amount" 			=> $inputs['lp_amount'],
					"remarks" 			=> $inputs['lp_remarks'],
					"id" 				=> $inputs['last_pay_adjust_id']
				);

			$con->myQuery("UPDATE last_pay_adjustments SET adjustment_type=:adjustment_type, operation=:operation, amount=:amount, remarks=:remarks WHERE id=:id",$params2);
	
			Alert("Successfully Updated!","success");
		}

		redirect("last_pay_view.php?id=".$inputs['last_pay_id']);
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>