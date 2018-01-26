<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(empty($_GET['id']))
	{
		redirect('index.php');
		die;
	}else
	{	
		$total_tax = 0;

		$get_total_tax = $con->myQuery("SELECT id,tax_withheld FROM bir_1601_e_details WHERE bir_1601_e_master_id=?",array($_GET['id']));

		while ($row = $get_total_tax->fetch(PDO::FETCH_ASSOC)) 
		{
			$total_tax = floatval($row['tax_withheld']) + $total_tax;
		}

		$con->myQuery("UPDATE bir_1601_e_master SET is_processed=1, date_processed=CURDATE(), total_tax=? WHERE id=?",array($total_tax,$_GET['id']));
		Alert("Successfully Saved.","success");
		redirect("report_bir_1601_e_view.php?id=".$_GET['id']);
		die();
	}
?>