<?php
	require_once 'support/config.php';
	
	if(!isLoggedIn()){
		toLogin();
		die();
	}
	if(!AllowUser(array(1,4))){
        redirect("index.php");
    }
	if(empty($_POST['id']) || !is_numeric($_POST['id'])){
		redirect('index.php');
		die;
	}
	else
	{

		$table="holidays";

        $audit_details=$con->myQuery("SELECT a.holiday_name FROM {$table} a  WHERE a.id=?",array($_POST['id']))->fetch(PDO::FETCH_ASSOC);
        $audit_message="Deleted Holiday {$audit_details['holiday_name']}.";

		
		$con->myQuery("UPDATE {$table} SET is_deleted=1 WHERE id=?",array($_POST['id']));

		insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],$audit_message);
		Alert("Delete Successful.","success");
		redirect("holidays.php");

		die();

	}
?>