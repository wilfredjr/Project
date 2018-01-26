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
	// die;
	$errors="";
			//IF id exists update ELSE insert
		if(empty($inputs['loan_id'])){
				//Insert
			//$inputs=$_POST;
			unset($inputs['loan_id']);
			$lname=$con->myQuery("SELECT loan_id,lcase(loan_name) as `loan_name` FROM loans WHERE is_deleted=0 and loan_name=? ",array(strtolower($inputs['loan_name'])))->fetch(PDO::FETCH_ASSOC);
			if(!empty($lname)){
				if($lname['loan_name'] == strtolower($inputs['loan_name'])){
					$errors.="Loan Name " .$inputs['loan_name']. " is exist already.";

				}
			}
			if($errors!=""){

				Alert("You have the following errors: <br/>".$errors,"danger");
				if(empty($inputs['loan_id'])){
					redirect("frm_loan_list.php");
				}
				else{
					redirect("frm_loan_list.php?loan_id=".urlencode($inputs['loan_id']));
				}
				die;
			}
			// $date = strtotime($inputs['birthdate']);
			// $newformat = date('m-d-Y',$date);
			// $inputs['birthdate'] = $newformat;


			// $date = strtotime($inputs['joined_date']);
			// $newformat = date('m-d-Y',$date);
			// $inputs['joined_date'] = $newformat;

			// $inputs['basic_salary'] = encryptIt($inputs['basic_salary']);
			
			
				//$userid=$_SESSION[WEBAPP]['user']['id'];
				// var_dump($inputs);
				// die;
			$con->myQuery("INSERT INTO loans (loan_name) VALUES (:loan_name)", $inputs);	

				// var_dump($con);
				// die;	
				// insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']," Add Company named " . $inputs['company_name']. ".");	
			
			Alert("Save successful.","success");
			

		}
		else{
			// $date = strtotime($inputs['birthdate']);
			// $newformat = date('m-d-Y',$date);
			// $inputs['birthdate'] = $newformat;


			// $date = strtotime($inputs['joined_date']);
			// $newformat = date('m-d-Y',$date);
			// $inputs['joined_date'] = $newformat;

			// $inputs['basic_salary'] = encryptIt($inputs['basic_salary']);
			// // var_dump($inputs);
			// // 	die;
			$lname=$con->myQuery("SELECT loan_id,lcase(loan_name) as `loan_name` FROM loans WHERE is_deleted=0 and loan_name=? and loan_id <> ?",array(strtolower($inputs['loan_name']),$inputs['loan_name']))->fetch(PDO::FETCH_ASSOC);
		
				if(!empty($lname)){
					if($lname['loan_name'] == strtolower($inputs['loan_name'])){
						$errors.="Loan Name " .$inputs['loan_name']. " is exist already.";
					}
				}
				if($errors!=""){

				Alert("You have the following errors: <br/>".$errors,"danger");
				if(empty($inputs['loan_id'])){
					redirect("frm_loan_list.php");
				}
				else{
					redirect("frm_loan_list.php?loan_id=".urlencode($inputs['loan_id']));
				}
				die;
			}
			$con->myQuery("UPDATE loans SET loan_name=:loan_name WHERE loan_id=:loan_id",$inputs);

			// insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']," Update Company ID " . $inputs['company_id']. ".");
	//die; ".");
			
			Alert("Update successful.","success");
		}
		
		redirect("view_loan_list.php");
	//}
	die();
	
}
else{
	redirect('index.php');
	die();
}
redirect('index.php');
?>