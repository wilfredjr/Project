<?php
	require_once '../support/config.php';
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(!empty($_POST))
	{
		//Validate form inputs
		$inputs = $_POST;
		$inputs = array_map("trim",$inputs);
		$errors = "";
		
		if (empty($inputs['payroll_group_name'])) 
		{
			$errors .= " Enter Payroll Group Name. <br>";
		}
		if (empty($inputs['address'])) 
		{
			$errors .= " Enter Address. <br>";
		}
		if (empty($inputs['email'])) 
		{
			$errors .= " Enter Email. <br>";
		}
		if (empty($inputs['website'])) 
		{
			$errors .= " Enter Website. <br>";
		}
		if (empty($inputs['mobile_no'])) 
		{
			$errors .= " Enter Mobile Number. <br>";
		}
		if (empty($inputs['bank_account_number'])) 
		{
			$errors .= " Enter Bank Account Number. <br>";
		}


		if($errors!="")
		{
			Alert($errors,"danger");
			if(empty($inputs['payroll_group_id']))
			{
				redirect("frm_payrollgroup.php");
			}else
			{
				redirect("frm_payrollgroup.php?pg_id=".urlencode($inputs['payroll_group_id']));
			}	
			die;
		}


		if(empty($inputs['payroll_group_id']))
		{
			try 
			{
				$con->beginTransaction();
				
				unset($inputs['payroll_group_id']);
				
				$con->myQuery("INSERT INTO payroll_groups(name,address,email,website,mobile_no,telephone_no,fax_no,bank_account_number) VALUES (:payroll_group_name,:address,:email,:website,:mobile_no,:telephone_no,:fax_no,:bank_account_number)", $inputs);	
			
				$con->commit();

				Alert("Payroll group saved successfully","success");
				redirect("view_payrollgroups.php");
			} catch (Exception $e) 
			{
				$con->rollBack();
				Alert("Save failed. Please try again.","danger");
				redirect("frm_payrollgroup.php");
			}
		}else
		{
			try 
			{
				$con->beginTransaction();

				$con->myQuery("UPDATE payroll_groups SET name=:payroll_group_name , address=:address, email=:email, website=:website, mobile_no=:mobile_no, telephone_no=:telephone_no, fax_no =:fax_no, bank_account_number=:bank_account_number WHERE payroll_group_id=:payroll_group_id",$inputs);
			
				$con->commit();
				
				Alert("Payroll group updated successfully","success");
				redirect("view_payrollgroups.php");
			} catch (Exception $e) 
			{
				$con->rollBack();
				Alert("Save failed. Please try again.","danger");
				redirect("frm_payrollgroup.php?pg_id=".urlencode($_POST['payroll_group_id']));
			}
		}
		die();	
	}else
	{
		redirect('index.php');	
	}

?>