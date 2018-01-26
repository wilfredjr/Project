<?php
require_once '../support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}



if(!empty($_POST)){
		
	$inputs=$_POST;
	$errors="";


	// echo "<pre>";
	// print_r($inputs);
	// echo "</pre>";
	// die;
	


	if(empty($inputs['uid'])){
		
		$emp_id = $inputs['emp_id'];
		$date = new DateTime();
		$date_o=new DateTime($inputs['dt_occur']);
		$date_occur=$date_o->format("Y-m-d");
		$date_created=date_format($date, 'Y-m-d');
		$amount = $inputs['amount'];
		$reason = $inputs['reason'];
		$status = '0';
		$type = $inputs['type'];

		$param=array(
			'emp_id'=>$emp_id,
			'date_created'=>$date_created,
			'date_occur'=>$date_occur,
			'amount'=>$amount,
			'reason'=>$reason,
			'status'=>$status,
			'a_type'=>$type
			);

		$con->myQuery("INSERT INTO payroll_adjustments (employee_id,date_created,date_occur,amount,reason,status,adjustment_type) VALUES (:emp_id,:date_created,:date_occur,:amount,:reason,:status,:a_type)",$param);

		Alert("Payroll adjustment of employee successfully created","success");
	}else{
		
		$id = $inputs['uid'];
		$date = new DateTime();
		$date_o=new DateTime($inputs['udt_occur']);
		$date_occur=$date_o->format("Y-m-d");
		$amount = $inputs['uamount'];
		$reason = $inputs['ureason'];
		$type = $inputs['utype'];

		$param=array(
			'id'=>$id,
			'date_occur'=>$date_occur,
			'amount'=>$amount,
			'reason'=>$reason,
			'a_type'=>$type
			);
			
		$con->myQuery("UPDATE payroll_adjustments SET date_occur=:date_occur ,amount=:amount ,reason=:reason ,adjustment_type=:a_type WHERE id=:id",$param);
	

		Alert("Payroll adjustment of employee successfully updated","success");

	}



	redirect("../payroll/frm_payroll_adjustment.php");
	die;

}