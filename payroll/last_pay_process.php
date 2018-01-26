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
		$inputs = $_POST;			
		$inputs = array_map('trim', $inputs);
		$id 	= $inputs['lp_id'];

		$total_last_pay = 0;

		$from_master 		= $con->myQuery("SELECT id,last_salary,13th_month FROM last_pay WHERE id=?",array($id))->fetch(PDO::FETCH_ASSOC);
		$from_adjustment 	= $con->myQuery("SELECT id,operation,amount FROM last_pay_adjustments WHERE last_pay_id=? AND is_deleted=0",array($id))->fetchAll(PDO::FETCH_ASSOC);

		$last_salary  		= $from_master['last_salary'];
		$thirteenth_month 	= $from_master['13th_month'];

		$total_last_pay 	= $last_salary+$thirteenth_month;

		for($i=0; $i<count($from_adjustment); $i++)
		{
			if ($from_adjustment[$i]['operation'] == "Add") 
			{
				$total_last_pay = $total_last_pay + $from_adjustment[$i]['amount'];
			}

			if ($from_adjustment[$i]['operation'] == "Minus") 
			{
				$total_last_pay = $total_last_pay - $from_adjustment[$i]['amount'];
			}		
		}


		$con->myQuery("UPDATE last_pay SET total_last_pay=?, is_processed=1, date_processed=CURDATE() WHERE id=?",array($total_last_pay,$id));


		Alert("Successfully Processed!","success");
		redirect("last_pay.php");
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>