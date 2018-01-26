<?php
require_once '../support/config.php';
// if(!hasAccess(21)){
//   redirect("index.php");
// }
if(!isLoggedIn()){
	toLogin();
	die();
}
// if(!AllowUser(array(1,2))){
//         redirect("index.php");
//  }
if(!empty($_POST)){
		//Validate form inputs
	$inputs=$_POST;
	// var_dump($inputs);
	//  die;
	$errors="";
	
	unset($inputs['emp_loan_id']);
	unset($inputs['status_id']);	
	// unset($inputs['balance']);	
	$con->myQuery("INSERT INTO emp_loans (cut_off_no, loan_amount, balance, loan_id, employee_id, remaining_cut_off_no) VALUES (:cut_off_no, :loan_amount, :loan_amount, :loan_id, :emp_name, :cut_off_no)", $inputs);	
				// var_dump($con);
				// die;	
		
	Alert("Save successful.","success");
	}
	
	redirect("view_loan.php");
	die();
	
// }
// else{
// 	redirect('index.php');
// 	die();
// }
redirect('index.php');
?>