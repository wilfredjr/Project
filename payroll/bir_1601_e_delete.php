<?php
	require_once("../support/config.php");

	if (!empty($_GET['id'])) 
	{
		$con->myQuery("UPDATE bir_1601_e_reference SET is_deleted=1 WHERE id=?",array($_GET['id']));

		Alert("Successfully Deleted","success");
		redirect("bir_1601_e_reference.php");
	}else
	{
		redirect("index.php");
	}
	die();
?>