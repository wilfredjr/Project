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

		if(empty($inputs['a_id']))
		{
			#INSERT INTO
			$params=array(
					"adjustment_type"=>$inputs['adjustment_type'],
					"13th_month_details_id"=>$inputs['id'],
					"amount"=>$inputs['amount'],
					"remarks"=>$inputs['remarks']
				);

			$con->myQuery("INSERT INTO 13th_month_adjust(adjustment_type,13th_month_details_id,amount,remarks) VALUES(:adjustment_type,:13th_month_details_id,:amount,:remarks)",$params);
			
			Alert("Successfully Saved!","success");
			redirect("13th_month_adjust.php?id=".$inputs['id']);
		}else
		{
			#UPDATE
			$params2=array(
					"adjustment_type"=>$inputs['adjustment_type'],
					"id"=>$inputs['a_id'],
					"amount"=>$inputs['amount'],
					"remarks"=>$inputs['remarks']
				);

			$con->myQuery("UPDATE 13th_month_adjust SET adjustment_type=:adjustment_type, amount=:amount, remarks=:remarks WHERE id=:id",$params2);
	
			
			Alert("Successfully Saved!","success");
			redirect("13th_month_adjust.php?id=".$inputs['id']);
		}

		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>