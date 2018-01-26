<?php
	require_once("../support/config.php");
    // require_once("../support/PHPExcel.php"); 

	if(!isLoggedIn())
    {
		toLogin();
		die();
	}

    $payroll_master         = $con->myQuery("SELECT id,pay_group_id,date_gen FROM payroll WHERE id=?",array($_POST['frm_payroll_code']))->fetch(PDO::FETCH_ASSOC);
    $payroll_group_details  = $con->myQuery("SELECT * FROM payroll_groups WHERE payroll_group_id=?",array($payroll_master['pay_group_id']))->fetch(PDO::FETCH_ASSOC);
    $payroll_details        = $con->myQuery("SELECT * FROM payroll_details WHERE payroll_id=?",array($payroll_master['id']));
    $total_net_pay          = $con->myQuery("SELECT SUM(net_pay) FROM payroll_details WHERE payroll_id=?",array($payroll_master['id']))->fetchColumn();

    $string     = new DateTime($payroll_master['date_gen']);
    $d          = $string->format("d");

    if (($d >= 10 && $d <=20)) #15 first cutoff
    {
        $payroll_code = "1";
    }else #30 second cutoff
    {
        $payroll_code = "2";
    }

    $total_net_pay = str_replace('.', '', $total_net_pay);
    $date_today = date("Ymd");
    echo "000".$payroll_group_details['bank_account_number']."000000000".$payroll_code."000000".$total_net_pay.$date_today;
    
    while ($data = $payroll_details->fetch(PDO::FETCH_ASSOC)) 
    {
        $get_card_number    = $con->myQuery("SELECT card_number FROM employees WHERE id=?",array($data['employee_id']))->fetch(PDO::FETCH_ASSOC);
        $net_pay            = str_replace('.', '', $data['net_pay']);   
        echo "\r\n000".$payroll_group_details['bank_account_number'].$get_card_number['card_number']."000000000".$net_pay.$date_today;
    }
    
    header('Content-type: text/plain');
    header("Content-Disposition: attachment; filename='".$payroll_group_details['name']." - ".$date_today."'.txt'");
    die();
?>