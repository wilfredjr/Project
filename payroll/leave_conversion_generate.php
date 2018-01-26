<?php
require_once("../support/config.php");

if(!isLoggedIn())
{
	toLogin();
	die();
}
if(!empty($_POST))
{
	# VALIDATE INPUTS
	$inputs=$_POST;			
	$inputs=array_map('trim', $inputs);
	$errors="";

# ---- FOR LEAVE CONVERSION MAIN TABLE -----
	$for_insert_master = array(
			'transaction_code' 	=> 'L'.$inputs['pay_group_id'].$inputs['leave_year'],
			'for_year' 			=> $inputs['leave_year'],
			'pay_group_id' 		=> $inputs['pay_group_id']
		);
# -----

# ---- GET EMPLOYEES IN THE SELECTED PAYROLL GROUP ----
	$employees=$con->myQuery("SELECT e.id, e.code AS employee_code, CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name  FROM employees e WHERE e.payroll_group_id=? AND e.is_deleted=0 AND e.is_terminated=0",array($inputs['pay_group_id']));
# -----

	$x = 0;
	while($data=$employees->fetch(PDO::FETCH_ASSOC))
	{
		# GET LEAVES OF EMPLOYEES
		$get_leave_available = get_leave_available($data['id']);

		$days_per_month = get_salary_settings($inputs['pay_group_id'])['days_per_month'];
		$basic_salary 	= get_basic_salary($data['id'])['basic_salary'];
		$daily_rate 	= (intval($basic_salary) / intval($days_per_month));

		if (!empty($get_leave_available)) 
		{
			for ($i=0; $i < count($get_leave_available); $i++) 
			{ 
				$params[$x] = array(
						'employee_id' 		=> $data['id'],
						'leave_id' 			=> $get_leave_available[$i]['leave_id'],
						'remaining_leave' 	=> $get_leave_available[$i]['balance_per_year'],
						'rate_per_day' 		=> $daily_rate,
						'amount' 			=> ($daily_rate * $get_leave_available[$i]['balance_per_year'])
					);
				$x++;
			}
		}
	}

	
	$check_data=$con->myQuery("SELECT pay_group_id,for_year FROM leave_conversion WHERE is_deleted = 0 AND pay_group_id = '{$inputs['pay_group_id']}' AND for_year = '{$inputs['leave_year']}'")->fetchAll(PDO::FETCH_ASSOC);

	if (!empty($check_data)) 
	{
		Alert("Selected Pay group and year already exists", "danger");
		redirect("leave_conversion.php");
		die;
	}

	$con->beginTransaction();

		$con->myQuery("INSERT INTO leave_conversion(transaction_code,date_generated,for_year,pay_group_id) 
			VALUES(:transaction_code,CURDATE(),:for_year,:pay_group_id)",$for_insert_master);			

		$last_id=$con->lastInsertId();

		for ($j=0; $j < count($params); $j++) 
		{ 
			$params[$j]['leave_conversion_id'] = $last_id;
			$con->myQuery("INSERT INTO leave_conversion_details(leave_conversion_id,employee_id,leave_id,remaining_leave,rate_per_day,amount) 
									 				VALUES(:leave_conversion_id,:employee_id,:leave_id,:remaining_leave,:rate_per_day,:amount)",$params[$j]);
		}

	$con->commit();

	Alert("Temporarily Saved!","warning");
	redirect("leave_conversion_view.php?id=".$last_id);
	die();
}
redirect('index.php');
?>