<?php
require_once("../support/config.php");
if(!isLoggedIn())
{
	toLogin();
	die();
}
if(empty($_POST['paymain_id']))
{
	redirect('index.php');
	die;
}else
{	
	$user_id=$_SESSION[WEBAPP]['user']['id'];
	$input_password=$_POST['admin_password'];

	$get_password=$con->myQuery("SELECT password FROM users WHERE is_deleted = '0' and id = ?",array($user_id))->fetch(PDO::FETCH_ASSOC);

	if(encryptIt($input_password) == $get_password['password']){
		$con->myQuery("UPDATE payroll SET is_deleted=1 WHERE id=?",array($_POST['paymain_id']));
		$con->myQuery("DELETE FROM dtr_compute WHERE payroll_id=?",array($_POST['paymain_id']));
		Alert("Delete Successful.","success");
	}else{
		Alert("Wrong Password.","danger");
	}

	redirect("view_payroll_maintenance.php");
	die;


}
?>