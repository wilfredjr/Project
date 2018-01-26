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

		$table="company_deductions";
		$employee=getEmpDetails($_POST['employee_id']);
        $audit_details=$con->myQuery("SELECT a.comde_desc FROM {$table} a  WHERE a.id=?",array($_POST['id']))->fetch(PDO::FETCH_ASSOC);
        $audit_message="Deleted Company Deductions {$audit_details['comde_desc']} from {$employee['last_name']}, {$employee['first_name']}.";

		$con->myQuery("UPDATE employee_company_deductions SET is_deleted=1 WHERE id=?",array($_POST['id']));
		

		insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],$audit_message);
		Alert("Delete Successful.","success");
		redirect("frm_employee.php?id={$_POST['employee_id']}&tab=12");

		die();

	}
?>