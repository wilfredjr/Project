<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }
	 
    $time_in_module=$con->myQuery("SELECT time_in_module FROM settings LIMIT 1")->fetchColumn();
    if(empty($time_in_module)){
    	redirect("index.php");
		//die();
    }

	$has_record=$con->myQuery("SELECT id FROM attendance WHERE employees_id=? AND out_time='0000-00-00 00:00:00' LIMIT 1",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
	if(empty($has_record)){
		$con->myQuery("INSERT attendance(in_time,employees_id,note,out_time) VALUES(NOW(),?,?,'0000-00-00 00:00:00') ",array($_SESSION[WEBAPP]['user']['employee_id'],$_POST['note']));
		$message="Punch In Successful";
	}else{
		$con->myQuery("UPDATE attendance SET out_time=NOW(),note=? WHERE id=?",array($_POST['note'],$has_record));
		$message="Punch Out Successful";
	}
	//die;
		Alert($message,"success");
		redirect("time_management.php");
	die;
?>