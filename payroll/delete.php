<?php
require_once '../support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}



if(!empty($_GET)){

	
	switch ($_GET['tb']) {
		case 's':
		$page="../payroll/view_sss.php";
		$con->myQuery("UPDATE gd_sss SET is_deleted=1 WHERE sss_code=? ",array($_GET['cd']));
			break;

			case 'h':
		$page="../payroll/view_housing.php";
		$con->myQuery("UPDATE gd_hdmf SET is_deleted=1 WHERE hdmf_code=? ",array($_GET['cd']));
			break;

			case 'p':
		$page="../payroll/view_phealth.php";
		$con->myQuery("UPDATE gd_philhealth SET is_deleted=1 WHERE ph_code=? ",array($_GET['cd']));
			break;

			case 't':
		$page="../payroll/view_tax.php";
		$con->myQuery("UPDATE taxes SET is_deleted=1 WHERE tax_code=? ",array($_GET['cd']));
			break;
		
		default:
			redirect("../payroll/index.php");
			break;
	}
Alert("Entry Deleted","danger");
redirect($page);

}

?>