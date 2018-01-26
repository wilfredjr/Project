<?php
	require_once("../support/config.php");
	
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}
	if(!empty($_POST))
	{
	#VALIDATE INPUTS
		$inputs=$_POST;			
		$inputs=array_map('trim', $inputs);
		$errors="";
		
		$dateS=new datetime($inputs['date_start']);
		$dateE=new datetime($inputs['date_end']);


	#CHECK 13TH MONTH CUT-OFF DUPLICATION 
		$inputs['date_start']=$dateS->format('Y-m-d');
		$inputs['date_end']=$dateE->format('Y-m-d');

		$check_start=$con->myQuery("SELECT id FROM 13th_month WHERE is_deleted=0 AND payroll_group_id=:payroll_group AND (date_start BETWEEN :date_start AND :date_end)",$inputs)->fetchAll(PDO::FETCH_ASSOC);
		$check_end=$con->myQuery("SELECT id FROM 13th_month WHERE is_deleted=0 AND payroll_group_id=:payroll_group AND (date_end BETWEEN :date_start AND :date_end)",$inputs)->fetchAll(PDO::FETCH_ASSOC);

		if(!empty($check_start) || !empty($check_end)) 
		{
			$errors.=" Selected date already exists. <br/> ";
		}


	
		if ($errors!="") 
		{
			Alert("You have the following errors: <br/><ul>".$errors."<ul>", "danger");
			redirect("13th_month.php");
			die;
        }else
        {	
    #GATHER INPUTS
        #MAKE TRASACTION NUMBER
			$get_payroll_group=$con->myQuery("SELECT name FROM payroll_groups WHERE payroll_group_id=?",array($inputs['payroll_group']))->fetch(PDO::FETCH_ASSOC);
			$first_letter=strtoupper(substr($get_payroll_group['name'],0,1));
			$date_s=$dateS->format('Ymd');
			$date_e=$dateE->format('Ymd');
			
			$transaction_number=$first_letter.$inputs['payroll_group'].$date_s.$date_e;

		#GET EMPLOYEE AND AMOUNT FROM PAYROLL DETAILS
			$get_amount=$con->myQuery("SELECT pd.id, pd.payroll_code, pd.employee_id, e.code AS employee_code, SUM(pd.13th_month) AS amount FROM payroll_details pd INNER JOIN payroll p ON p.payroll_code=pd.payroll_code INNER JOIN employees e ON e.id=pd.employee_id WHERE (date_gen BETWEEN ? AND ?) AND pd.done_13th_month=0 AND e.is_deleted=0 AND e.is_terminated=0 GROUP BY employee_id",
					array($inputs['date_start'],$inputs['date_end']));

		#GET PAYROLL DETAILS ID
			$get_p_id=$con->myQuery("SELECT pd.id FROM payroll_details pd INNER JOIN payroll p ON p.payroll_code=pd.payroll_code INNER JOIN employees e ON e.id=pd.employee_id WHERE (date_gen BETWEEN ? AND ?) AND pd.done_13th_month=0 AND e.is_deleted=0 AND e.is_terminated=0",
					array($inputs['date_start'],$inputs['date_end']));


	#SAVING
		#SAVE 13TH MONTH TABLE
			$params=array(
					"transaction_number"=>$transaction_number,
					"payroll_group"=>$inputs['payroll_group'],
					"date_start"=>$inputs['date_start'],
					"date_end"=>$inputs['date_end']
				);
			$con->myQuery("INSERT INTO 13th_month(transaction_number,payroll_group_id,date_start,date_end,date_generated,is_processed)
								VALUES(:transaction_number,:payroll_group,:date_start,:date_end,CURDATE(),0)",$params);
		
			$last_id=$con->lastInsertId();

		#SAVE 13TH MONTH DETAILS TABLE
			while($row=$get_amount->fetch(PDO::FETCH_ASSOC)) 
			{
				$params2=array(
						"id"=>$last_id,
						"employee_id"=>$row['employee_id'],
						"amount"=>$row['amount']
					);

				$con->myQuery("INSERT INTO 13th_month_details(13th_month_id,employee_id,amount)
												VALUES(:id,:employee_id,:amount)",$params2);
			}

		#SAVE 13TH MONTH PAYROLL DETAILS TABLE
			while ($row2=$get_p_id->fetch(PDO::FETCH_ASSOC)) 
			{
				$con->myQuery("INSERT INTO 13th_month_payroll_details(13th_month_id,payroll_details_id) VALUES(?,?)",array($last_id,$row2['id']));
			}
		// die();
			Alert("Temporarily Saved!","warning");
			redirect("13th_month_view.php?id=".$last_id);
		}
		die;
	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>