<?php
	require_once '../support/config.php';

	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	if(!empty($_POST))
	{
		$inputs 				= $_POST;
		$inputs['shift_name'] 	= trim($inputs['shift_name']);
		$errors 				= "";

		if (empty($inputs['shift_name'])) 
		{
			$errors .= " Enter Shift Name. <br>";
		}

		if($errors!="")
		{
			Alert($errors,"danger");
			if(empty($inputs['shift_id']))
			{
				redirect("frm_shift.php");
			}else
			{
				redirect("frm_shift.php?id=".urlencode($inputs['shift_id']));
			}	
			die;
		}

		if(empty($inputs['shift_id']))
		{			
			$inputs['days']=implode(",", $inputs['days']);
			$working_days = $inputs['days'];
		
			$params=array(
					'shift_name' 			=> $inputs['shift_name'],
					'time_in' 				=> $inputs['time_in'],
					'time_out' 				=> $inputs['time_out'],
					'working_days' 			=> $working_days,
					'late_start' 			=> $inputs['late_start'],
					'grace_minutes' 		=> $inputs['grace_minutes']
				);

			$con->myQuery("INSERT INTO shifts(shift_name,
				time_in,
				time_out,
				working_days,
				late_start,
				grace_minutes
				) 
				VALUES (:shift_name,
				:time_in,
				:time_out,
				:working_days,
				:late_start,
				:grace_minutes)",$params);

			Alert("Shift successfully created","success");

		}else
		{
			$inputs['days']=implode(",", $inputs['days']);
			$working_days = $inputs['days'];
		
			$params=array(
					'shift_id' 				=> $inputs['shift_id'],
					'shift_name' 			=> $inputs['shift_name'],
					'time_in' 				=> $inputs['time_in'],
					'time_out' 				=> $inputs['time_out'],
					'working_days' 			=> $working_days,
					'late_start' 			=> $inputs['late_start'],
					'grace_minutes' 		=> $inputs['grace_minutes']
				);

			$con->myQuery("UPDATE shifts SET 
				shift_name 			= :shift_name ,
				time_in 			= :time_in ,
				time_out 			= :time_out ,
				working_days 		= :working_days,
				late_start 			= :late_start,
				grace_minutes 		= :grace_minutes
				WHERE id = :shift_id ",$params);
			
			Alert("Shift successfully updated","success");
		}

		redirect("../payroll/view_shift.php");
		die();
	}

?>