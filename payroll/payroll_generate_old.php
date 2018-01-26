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

	$dateS 					= new datetime($inputs['date_start']);
	$dateE 					= new datetime($inputs['date_end']);
	$inputs['date_start']	= $dateS->format('Y-m-d');
	$inputs['date_end']		= $dateE->format('Y-m-d');
	$date_start 			= $inputs['date_start'];
	$date_end 				= $inputs['date_end'];




# ---- FOR PAYROLL TABLE -----

	$a=$dateS->format('Ymd');
	$b=$dateE->format('Ymd');
	$for_insert_master['payroll_code'] 	= "P".$a.$b;
	$for_insert_master['date_from']		= $inputs['date_start'];
	$for_insert_master['date_to']		= $inputs['date_end'];
	$for_insert_master['pay_group']		= $inputs['pay_group'];


	$check_start=$con->myQuery("SELECT date_from FROM payroll WHERE is_deleted = 0 AND (date_from BETWEEN '{$date_start}' AND '{$date_end}')")->fetchAll(PDO::FETCH_ASSOC);;
	$check_end=$con->myQuery("SELECT date_to FROM payroll WHERE is_deleted = 0 AND (date_to BETWEEN '{$date_start}' AND '{$date_end}')")->fetchAll(PDO::FETCH_ASSOC);;

	
	if (!empty($check_start) || !empty($check_end)) 
	{
		Alert("Selected date already exists", "danger");
		redirect("view_payroll_maintenance.php");
		die;
	}

# ---- END PAYROLL TABLE -----


# ---- FOR DTR COMPUTE TABLE -----

	if(!empty($inputs['pay_group']))
	{
		$pay_group_id 	= $inputs['pay_group'];
		$period_id 		= get_salary_settings($pay_group_id)['pay_period_id'];
		$days_per_month = get_salary_settings($pay_group_id)['days_per_month'];

		# PAYROLL GROUP RATE
		$rest_day_rate 								= get_payroll_group_rates($pay_group_id)['rd_rate'];
		$special_holiday_rate 						= get_payroll_group_rates($pay_group_id)['sh_rate'];
		$rest_day_special_holiday_rate 				= get_payroll_group_rates($pay_group_id)['rd_sh_rate'];
		$regular_holiday_rate 						= get_payroll_group_rates($pay_group_id)['rh_rate'];
		$rest_day_regular_holiday_rate 				= get_payroll_group_rates($pay_group_id)['rd_rh_rate'];
		$overtime_rate 								= get_payroll_group_rates($pay_group_id)['o_ot_rate'];
		$rest_day_overtime_rate 					= get_payroll_group_rates($pay_group_id)['rd_ot_rate'];
		$special_holiday_overtime_rate 				= get_payroll_group_rates($pay_group_id)['sh_ot_rate'];
		$rest_day_special_holiday_overtime_rate 	= get_payroll_group_rates($pay_group_id)['rd_ot_rate'];
		$regular_holiday_overtime_rate 				= get_payroll_group_rates($pay_group_id)['rh_ot_rate'];
		$rest_day_regulary_holiday_overtime_rate 	= get_payroll_group_rates($pay_group_id)['rd_rh_ot_rate'];
		$night_differential_rate 					= get_payroll_group_rates($pay_group_id)['n_rate'];
	}

	if (empty($rest_day_rate)) 
	{
		Alert("Payroll group has not set yet.","danger");
		redirect("view_payroll_maintenance.php");
		die();
	}


	    # EMPLOYEES IN THIS PAYGROUP AND ATTENDANCE

	$employees=$con->myQuery("SELECT 
		e.id,
		e.code AS employee_code,
		CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as employee_name
		FROM employees e
		WHERE e.payroll_group_id=?",array($inputs['pay_group']));

// echo "<pre>";
// print_r($employees->fetchAll(PDO::FETCH_ASSOC));
// echo "</pre>";
// die();

	$x=1;
	while ($data1=$employees->fetch(PDO::FETCH_ASSOC)) 
	{

		$start_date = new DateTime($inputs['date_start']);
		$end_date 	= new DateTime($inputs['date_end']);
		$datediff	= $start_date->diff($end_date);
		$days_count	= $datediff->format('%r%a');

		$base_date=$inputs['date_start'];

		

		for($i=0; $i<=$days_count; $i++)
		{
		    	# GET SHIFT (Time in and Time Out)
			$get_default_shift	= getShift($data1['id'],$base_date);
				# -----

			if (!empty($get_default_shift)) 
			{
				$for_insert[$x]['employee_id']	= $data1['id'];
				// $for_insert[$x]['payroll_id']	= $last_id;

	    		# GET FIRST TIME IN AND OUT
				$get_first_time_in	=$con->myQuery("SELECT id,in_time FROM attendance WHERE DATE_FORMAT(in_time,'%Y-%m-%d')=? AND employees_id=? ORDER BY id ASC LIMIT 1",array($base_date,$data1['id']))->fetch(PDO::FETCH_ASSOC);
				$get_last_time_out	=$con->myQuery("SELECT id,out_time FROM attendance WHERE DATE_FORMAT(in_time,'%Y-%m-%d')=? AND employees_id=? ORDER BY id ASC LIMIT 10",array($base_date,$data1['id']))->fetch(PDO::FETCH_ASSOC);
				# ------

				$for_insert[$x]['official_time_in']		= $get_first_time_in['in_time'];
				$for_insert[$x]['official_time_out']	= $get_last_time_out['out_time'];
				$date_in = substr($get_first_time_in['in_time'],0,10);

				# COMPENSATION
				$basic_salary 	= get_basic_salary($data1['id'])['basic_salary'];
				$dailyrate 		= ($basic_salary / $days_per_month);
				$hourlyrate 	= ($dailyrate / 8);

				$for_insert[$x]['daily_rate']		= number_format($dailyrate, 2, '.', '');
				$for_insert[$x]['hourly_rate']		= number_format($hourlyrate, 2, '.', '');
				$for_insert[$x]['night_rate']		= $night_differential_rate;
				# -----

			    # ABSENT/LEAVE WITHOUT PAY (CHECK OB, LEAVE, AND OFFSET) COMPUTATION
				$late_amount 		= 0;
				$undertime_amount 	= 0;
				$working_days 		= getShift($data1['id'],$base_date)['working_days'];
				$in_time_formatted 	= date_create($base_date);
				$weekday 			= date_format($in_time_formatted,'w');
				$in_working_days 	= false;
				switch ($weekday) 
				{
			            case '0': #sunday
			            $in_working_days=in_array("Su", explode(",", $working_days));
			            break;
			            case '1': #monday
			            $in_working_days=in_array("M", explode(",", $working_days));
			            break;
			            case '2': #tuesday
			            $in_working_days=in_array("T", explode(",", $working_days));
			            break;
			            case '3': #wednesday
			            $in_working_days=in_array("W", explode(",", $working_days));
			            break;
			            case '4': #thursday
			            $in_working_days=in_array("TH", explode(",", $working_days));
			            break;
			            case '5': #friday
			            $in_working_days=in_array("F", explode(",", $working_days));
			            break;
			            case '6': #saturday
			            $in_working_days=in_array("Sa", explode(",", $working_days));
			            break;
			        }
		        	if(empty($get_first_time_in['in_time'])) # if no time in and out
		        	{
			        	$check_ob 			= get_employees_ob($data1['id'],$base_date); 		# start and end time of OB is excluded 
			        	$check_offset 		= get_employees_offset($data1['id'],$base_date); 	# start and end time of OFFSET is excluded
			        	$check_leave		= checkLeave($data1['id'],$base_date);
			        	$late_amount 		= 0;
			        	$undertime_amount 	= 0;
		        		// echo $x.' - '.$check_ob.' , '.$check_offset.' , '.$check_leave."<br>";
						if ((!empty($check_ob) && $check_ob !== 0) || (!empty($check_offset) && $check_offset !== 0) || (!empty($check_leave) && $check_leave !== 0)) # check ob and offset and leave
						{
							$absent_amount = 0;
						}else
						{
							if (!$in_working_days) 
							{
								$absent_amount = 0;
							}else 
							{
								$absent_amount = number_format($dailyrate, 2, '.', '');
							}
						}		        		
		        	}else # if with time in and out, check lates and undertime
		        	{
		        		$absent_amount = 0;

		        		# LATE AND UDERTIME COMPUTATION
					        # LATE
		        		$global_time_in1 	= new DateTime($base_date.' '.$get_default_shift['time_in']);
		        		$in_time 			= new DateTime($get_first_time_in['in_time']);


		        		if ($global_time_in1 < $in_time)
		        		{
		        			$checkAM=checkHalfDay('AM',$data1['id'],$base_date);
		        			if (empty($checkAM)) 
		        			{
		        				$orig_in = new DateTime($base_date.' '.$get_default_shift['time_in']);
		        				$actual_in =new DateTime($get_first_time_in['in_time']);
		        				$interval = $orig_in->diff($actual_in);
		        				$hours   = $interval->format('%h'); 
		        				$minutes = $interval->format('%i');
		        				$late_mins = ($hours * 60 + $minutes);
		        				$late_amount = number_format(($hourlyrate /60)*$late_mins,2);

		        			}
		        		}else
		        		{
		        			$late_amount 	= 0;
		        		}
					        # UNDERTIME 
		        		$global_time_out3 = substr($get_last_time_out['out_time'],0,10).' '.$get_default_shift['time_out'];

		        		if ($global_time_out3 >= $get_last_time_out['out_time'])
		        		{
		        			$undertime_mins = computeTimeDiff($get_last_time_out['out_time'],$global_time_out3);

		        			$checkPM=checkHalfDay('PM',$data1['id'],substr($get_last_time_out['out_time'],0,10));
		        			if(empty($checkPM)) 
		        			{
		        				$undertime_amount = number_format(($hourlyrate * $undertime_mins),2);
		        			}else
		        			{
		        				$undertime_amount = 0;
		        			}
		        		}else
		        		{
		        			$undertime_amount = 0;
		        		}
					    # -----
		        	}


		        	$for_insert[$x]['late']		= $late_amount+$undertime_amount;
		        	$for_insert[$x]['absent']	= $absent_amount;		

		        	$worked_hours = getHoursWorked($get_first_time_in['in_time'],$get_last_time_out['out_time'],$get_default_shift,TRUE);
		       

		        	if(!empty($worked_hours['hours'])){
		        		$for_insert[$x]['worked_hours'] = $worked_hours['hours']; 		
		        	}else{
		        		$for_insert[$x]['worked_hours'] = 0;
		        	}	

		        
			    # -----

			     # OVERTIME COMPUTATION

		        	$holiday_day = getHolidayOfDay($base_date,$pay_group_id)['holiday_category'];
		        	$no_of_work_hoursx = getOvertimePerDayForPayroll($data1['id'],$base_date)['no_hours'];

		        	if($holiday_day != 'Special Holiday' AND $holiday_day != 'Legal Holiday' AND $no_of_work_hoursx >= 0){
		        		$overtime_amount = (($overtime_rate * $hourlyrate) * $no_of_work_hoursx);
		        	}else{
		        		$overtime_amount = 0;
		        	}

		        	if(!empty($no_of_work_hoursx)){
		        		$for_insert[$x]['no_of_work_hours_regular'] = $no_of_work_hoursx;
		        	}else{
		        		$for_insert[$x]['no_of_work_hours_regular'] = 0;
		        	}
		        	
		        	$for_insert[$x]['overtime'] = $overtime_amount;

		        	
		        	$premium_hours = ($worked_hours['hours'] + $no_of_work_hoursx);

		        	echo $data1['id'].' - '.$premium_hours.'<br>';

		        	// if(!empty($premium_hours)){
		        	// 	if($premium_hours > 48){
		        	// 		$excess_premium_hours = ($premium_hours - 48);
		        	// 	}else{
		        	// 		$excess_premium_hours = 0;
		        	// 	}
		        	// }

		        	// $for_insert[$x]['no_of_work_hours_premium'] = $excess_premium_hours;
			    # -----

			    # OVERTIME SPECIAL HOLIDAY COMPUTATION
		        	

		        	if ($holiday_day == 'Special Holiday' and $no_of_work_hoursx <> 0)
		        	{
		        		$special_holiday_ot_amount = (($special_holiday_overtime_rate * $hourlyrate) * $no_of_work_hoursx);
		        	}else
		        	{	       
		        		$special_holiday_ot_amount = 0;
		        	}

		        	$for_insert[$x]['overtime_special_holiday']	= $special_holiday_ot_amount;
			    # -----

			    # LEGAL HOLIDAY OVERTIME COMPUTATION
		        	if ($holiday_day == 'Legal Holiday' and $no_of_work_hoursx <> 0)
		        	{
		        		$legal_holiday_ot_amount = (($regular_holiday_overtime_rate * $hourlyrate) * $no_of_work_hoursx);
		        	}else
		        	{
		        		$legal_holiday_ot_amount = 0;
		        	}

		        	$for_insert[$x]['overtime_legal_holiday'] = $legal_holiday_ot_amount;
			    # -----

			    # SPECIAL AND LEGAL HOLIDAY COMPUTATION
		        	$out_time 			= new DateTime($get_first_time_in['in_time']); 
		        	$in_time 			= new DateTime($get_last_time_out['out_time']); 
		        	$no_of_work_hours 	= $out_time->diff($in_time);
		        	$no_of_work_hours 	= $no_of_work_hours->h;
		        	if ($no_of_work_hours > 8) 
		        	{
		        		$no_of_work_hours = 8;
		        	}
		        	if ($holiday_day == 'Special Holiday' and $no_of_work_hoursx <= 0)
			        { #SPECIAL HOLIDAY COMPUTATION
			        	$special_holiday_amount = (($special_holiday_rate * $hourlyrate) * $no_of_work_hours);
			        }else
			        {
			        	$special_holiday_amount = 0;
			        }
			        if ($holiday_day == 'Legal Holiday' and $no_of_work_hoursx <= 0) 
			        { #LEGAL HOLIDAY COMPUTATION
			        	$legal_holiday_amount = (($regular_holiday_rate * $hourlyrate) * $no_of_work_hours);
			        }else 
			        {
			        	$legal_holiday_amount = 0;
			        }

			        $for_insert[$x]['special_holiday'] 	= $special_holiday_amount;
			        $for_insert[$x]['legal_holiday'] 	= $legal_holiday_amount;
			    # -----

			    # GET WORKING DAYS
			        $working_days 		= getShift($data1['id'],$get_first_time_in['in_time'])['working_days'];
			        $in_time_formatted 	= date_create($base_date);
			        $weekday 			= date_format($in_time_formatted,'w');
			        $in_working_days=false;
			        switch ($weekday) 
			        {
			            case '0': #sunday
			            $in_working_days=in_array("Su", explode(",", $working_days));
			            break;
			            case '1': #monday
			            $in_working_days=in_array("M", explode(",", $working_days));
			            break;
			            case '2': #tuesday
			            $in_working_days=in_array("T", explode(",", $working_days));
			            break;
			            case '3': #wednesday
			            $in_working_days=in_array("W", explode(",", $working_days));
			            break;
			            case '4': #thursday
			            $in_working_days=in_array("TH", explode(",", $working_days));
			            break;
			            case '5': #friday
			            $in_working_days=in_array("F", explode(",", $working_days));
			            break;
			            case '6': #saturday
			            $in_working_days=in_array("Sa", explode(",", $working_days));
			            break;
			        }

			    # -----

			    # REST DAY COMPUTATION
			        if (!$in_working_days) 
			        {
			        	$rest_day_amount = (($rest_day_rate * $hourlyrate) * $no_of_work_hours);
			        }else 
			        {
			        	$rest_day_amount = 0;
			        }

			        $for_insert[$x]['rest_day'] = $rest_day_amount;
			    # -----

			    # REST DAY SPECIAL HOLIDAY
			        if (!$in_working_days) 
			        {
			        	$rest_day_special_holiday_amount = 0;
			        	if ($holiday_day == 'Special Holiday') 
			        	{
			        		$rest_day_special_holiday_amount = (($rest_day_special_holiday_rate * $hourlyrate) * $no_of_work_hours);
			        	}
			        }else 
			        {
			        	$rest_day_special_holiday_amount = 0;
			        }

			        $for_insert[$x]['rest_day_special_holiday'] = $rest_day_special_holiday_amount;
			    # -----

			    # REST DAY LEGAL HOLIDAY
			        if (!$in_working_days) 
			        {
			        	$rest_day_legal_holiday_amount = 0;
			        	if ($holiday_day == 'Legal Holiday') 
			        	{
			        		$rest_day_legal_holiday_amount = (($rest_day_special_holiday_rate * $hourlyrate) * $no_of_work_hours);
			        	}
			        } else 
			        {
			        	$rest_day_legal_holiday_amount = 0;
			        }

			        $for_insert[$x]['rest_day_legal_holiday'] = $rest_day_legal_holiday_amount;
			    # -----

			    # FOR NIGHT SHIFTS
			        $nd_rest_day_night_shift_amount = 0;
			        $f_rest_day_night_shift_amount = 0;
			        $nd_regular_holiday_night_shift_amount = 0;
			        $f_regular_holiday_night_shift_amount = 0;
			        $nd_special_holiday_night_shift_amount= 0;
			        $f_special_holiday_night_shift_amount = 0;
			        $nd_ordinary_day_ns_amount  = 0;
			        $f_ordinary_day_ns_amount  = 0;
			        $nd_special_holiday_rd_night_shift_amount  = 0;
			        $f_legal_holiday_rd_night_shift_amount = 0;
			        $f_special_holiday_rd_night_shift_amount = 0;

			        $nd_time_in=new DateTime($get_first_time_in['in_time']);
			        $nd_time_out=new DateTime($get_last_time_out['out_time']);

			        
			        //FIRST DAY
			        if(!empty(getNightShiftHours($nd_time_in,$nd_time_out))){

			        	$current_date_hours = getNightShiftHours($nd_time_in,$nd_time_out)['current_date_hours'];
			        	$current_date 		= getNightShiftHours($nd_time_in,$nd_time_out)['current_date'];

			        	$working_days_i		= getShift($data1['id'],$current_date)['working_days'];
			        	$out_time_formatted = date_create($base_date);
			        	$weekday_i 			= date_format($out_time_formatted,'w');
			        	$in_working_days	= false;
			        	switch ($weekday_i) 
			        	{
				            case '0': #sunday
				            $in_working_days=in_array("Su", explode(",", $working_days_i));
				            break;
				            case '1': #monday
				            $in_working_days=in_array("M", explode(",", $working_days_i));
				            break;
				            case '2': #tuesday
				            $in_working_days=in_array("T", explode(",", $working_days_i));
				            break;
				            case '3': #wednesday
				            $in_working_days=in_array("W", explode(",", $working_days_i));
				            break;
				            case '4': #thursday
				            $in_working_days=in_array("TH", explode(",", $working_days_i));
				            break;
				            case '5': #friday
				            $in_working_days=in_array("F", explode(",", $working_days_i));
				            break;
				            case '6': #saturday
				            $in_working_days=in_array("Sa", explode(",", $working_days_i));
				            break;
				        }

				        if (!empty($holiday_day)) {
				        	if(!$in_working_days){
				        		switch ($holiday_day) {
				        			case 'Legal Holiday':
				        			$f_regular_holiday_night_shift_amount = (($hourlyrate * $regular_holiday_rate * $night_differential_rate) * $current_date_hours);
				        			break;
				        			case 'Special Holiday':
				        			$f_special_holiday_night_shift_amount = (($hourlyrate * $special_holiday_rate * $night_differential_rate) * $current_date_hours);
				        			break;			        		
				        		}
				        	}

				        } else {
				        	if(!$in_working_days){
				        		#REST DAY
				        		$f_rest_day_night_shift_amount = (($hourlyrate * $rest_day_rate * $night_differential_rate) * $current_date_hours);
				        	}
				        }

				        // if (!empty($holiday_day)) {
				        // 	if(!$in_working_days){
				        // 		switch ($holiday_day) {
				        // 			case 'Legal Holiday':
				        // 			$f_legal_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_regular_holiday_rate * $rest_day_rate) * $current_date_hours);
				        // 			break;
				        // 			case 'Special Holiday':
				        // 			$f_special_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_special_holiday_rate * $rest_day_rate) * $current_date_hours);
				        // 			break;			        		
				        // 		}
				        // 	}

				        // } else {
				        // 	if($in_working_days){
				        // 		#REST DAY
				        // 		$f_ordinary_day_ns_amount = (($hourlyrate * $night_differential_rate) * $current_date_hours);
				        // 	}
				        // }

				       // echo $current_date_hours . '<br>';

				        if($holiday_day = 'Legal Holiday' && !$in_working_days){
				        	$f_legal_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_regular_holiday_rate * $rest_day_rate) * $current_date_hours);
				        } elseif ($holiday_day = 'Special Holiday' && !$in_working_days) {
				        	$f_special_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_special_holiday_rate * $rest_day_rate) * $current_date_hours);
				        } else{
				        	$f_ordinary_day_ns_amount = (($hourlyrate * $night_differential_rate) * $current_date_hours);

				        }

				        //echo 'current: '.$current_date_hours.'</br>';


				    }

				    // echo '<pre>';
				    // print_r(getNightShiftHours($nd_time_in,$nd_time_out));
				    // echo '</pre>';

			    	//NEXT DAY
				    if (!empty(getNightShiftHours($nd_time_in,$nd_time_out))) {

			        	#Next Day
				    	$next_date_hours 	= !empty(getNightShiftHours($nd_time_in,$nd_time_out)['next_date_hours'])?getNightShiftHours($nd_time_in,$nd_time_out)['next_date_hours']:0;
				    	$next_date 			= !empty(getNightShiftHours($nd_time_in,$nd_time_out)['next_date'])?getNightShiftHours($nd_time_in,$nd_time_out)['next_date']:0;

				    	// $next_date_hours 	= getNightShiftHours($nd_time_in,$nd_time_out)['next_date_hours'];
				    	// $next_date 			= getNightShiftHours($nd_time_in,$nd_time_out)['next_date'];

				    	$working_days_o		= getShift($data1['id'],$next_date)['working_days'];
				    	$out_time_formatted = date_create($base_date);
				    	$weekday_o 			= date_format($out_time_formatted,'w');
				    	$out_working_days	= false;
				    	switch ($weekday_o) 
				    	{
				            case '0': #sunday
				            $out_working_days=in_array("Su", explode(",", $working_days_o));
				            break;
				            case '1': #monday
				            $out_working_days=in_array("M", explode(",", $working_days_o));
				            break;
				            case '2': #tuesday
				            $out_working_days=in_array("T", explode(",", $working_days_o));
				            break;
				            case '3': #wednesday
				            $out_working_days=in_array("W", explode(",", $working_days_o));
				            break;
				            case '4': #thursday
				            $out_working_days=in_array("TH", explode(",", $working_days_o));
				            break;
				            case '5': #friday
				            $out_working_days=in_array("F", explode(",", $working_days_o));
				            break;
				            case '6': #saturday
				            $out_working_days=in_array("Sa", explode(",", $working_days_o));
				            break;
				        }



				        $next_day_holiday=getHolidayOfDay($data1['id'], $next_date);

				        if (!empty($next_day_holiday)) {
				        	if(!$in_working_days){
				        		switch ($next_day_holiday) {
				        			case 'Legal Holiday':
				        			$nd_regular_holiday_night_shift_amount = (($hourlyrate * $regular_holiday_rate * $night_differential_rate) * $next_date_hours);
				        			break;
				        			case 'Special Holiday':
				        			$nd_special_holiday_night_shift_amount = (($hourlyrate * $special_holiday_rate * $night_differential_rate) * $next_date_hours);
				        			break;			        		
				        		}
				        	}
				        } else {
				        	if(!$out_working_days){
					        	#REST DAY
				        		$nd_rest_day_night_shift_amount = (($hourlyrate * $rest_day_rate * $night_differential_rate) * $next_date_hours);
				        	}
				        }

				        // if (!empty($holiday_day)) {
				        // 	if(!$in_working_days){
				        // 		switch ($holiday_day) {
				        // 			case 'Legal Holiday':
				        // 			$nd_legal_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_regular_holiday_rate * $rest_day_rate) * $next_date_hours);
				        // 			break;
				        // 			case 'Special Holiday':
				        // 			$nd_special_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_special_holiday_rate * $rest_day_rate) * $next_date_hours);
				        // 			break;			        		
				        // 		}
				        // 	}
				        // } else {
				        // 	if($out_working_days){
					       //  	#REST DAY
				        // 		$nd_ordinary_day_ns_amount = (($hourlyrate * $night_differential_rate) * $next_date_hours);	
				        // 	}
				        // }

				        //var_dump($next_date_hours);

				        if($holiday_day = 'Legal Holiday' && !$out_working_days){
				        	$nd_legal_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_regular_holiday_rate * $rest_day_rate) * $next_date_hours);
				        } elseif ($holiday_day = 'Special Holiday' && !$out_working_days) {
				        	$nd_special_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_special_holiday_rate * $rest_day_rate) * $next_date_hours);
				        } else{
				        	$nd_ordinary_day_ns_amount = (($hourlyrate * $night_differential_rate) * $next_date_hours);	

				        }
				        //echo 'next: '.$next_date_hours.'</br>';

				    }




				    $ordinary_day_night_shift_amount = ($f_ordinary_day_ns_amount + $nd_ordinary_day_ns_amount);
				    $rest_day_night_shift_amount = ($f_rest_day_night_shift_amount + $nd_rest_day_night_shift_amount);
				    $special_holiday_night_shift_amount = ($f_special_holiday_night_shift_amount + $nd_special_holiday_night_shift_amount);
				    $regular_holiday_night_shift_amount = ($f_regular_holiday_night_shift_amount + $nd_regular_holiday_night_shift_amount);
				    $legal_holiday_rd_amount = ($f_legal_holiday_rd_night_shift_amount + $nd_special_holiday_rd_night_shift_amount);
				    $special_holiday_rd_amount = ($f_special_holiday_rd_night_shift_amount + $nd_special_holiday_rd_night_shift_amount);
				    

				    $for_insert[$x]['ordinary_day_night_shift']				= $ordinary_day_night_shift_amount;
				    $for_insert[$x]['rest_day_night_shift']					= $rest_day_night_shift_amount;
				    $for_insert[$x]['special_holiday_night_shift']			= $special_holiday_night_shift_amount;
				    $for_insert[$x]['legal_holiday_night_shift']			= $regular_holiday_night_shift_amount;
				    $for_insert[$x]['special_holiday_rest_day_night_shift']	= $legal_holiday_rd_amount;
				    $for_insert[$x]['legal_holiday_rest_day_night_shift']	= $special_holiday_rd_amount;

				    $x++;


				}

				$stop_date = new DateTime($base_date.' 20:24:00');
				$stop_date->modify('+1 day');
				$base_date=$stop_date->format('Y-m-d');
			}
		}



		$con->beginTransaction();

		# PAYROLL MASTER TABLE
		$con->myQuery("INSERT INTO payroll(payroll_code,date_gen,date_from,date_to,pay_group_id) 
			VALUES(:payroll_code,CURDATE(),:date_from,:date_to,:pay_group)",$for_insert_master);			

		$last_id=$con->lastInsertId();

		for($i = 1; $i <= $x; $i++)
		{
			$for_insert[$i]['payroll_id'] = $last_id;

			$con->myQuery("INSERT INTO dtr_compute(
				employee_id,
				payroll_id,
				time_in,
				time_out,
				daily_rate,
				hourly_rate,
				night_rate,
				late,
				absent,
				worked_hours,
				no_of_work_hours_regular,
				overtime,
				no_of_work_hours_premium,
				overtime_special_holiday,
				overtime_legal_holiday,
				special_holiday,
				legal_holiday,
				rest_day,
				rest_day_special_holiday,
				rest_day_legal_holiday,
				ordinary_day_night_shift,
				rest_day_night_shift,
				special_holiday_night_shift,
				legal_holiday_night_shift,
				special_holiday_rest_day_night_shift,
				legal_holiday_rest_day_night_shift
				)VALUES(
				:employee_id,
				:payroll_id,
				:official_time_in,
				:official_time_out,
				:daily_rate,
				:hourly_rate,
				:night_rate,
				:late,
				:absent,
				:worked_hours,
				:no_of_work_hours_regular,
				:overtime,
				:no_of_work_hours_premium,
				:overtime_special_holiday,
				:overtime_legal_holiday,
				:special_holiday,
				:legal_holiday,
				:rest_day,
				:rest_day_special_holiday,
				:rest_day_legal_holiday,
				:ordinary_day_night_shift,
				:rest_day_night_shift,
				:special_holiday_night_shift,
				:legal_holiday_night_shift,
				:special_holiday_rest_day_night_shift,
				:legal_holiday_rest_day_night_shift
				)",$for_insert[$i]);


		}

		die;
		$con->commit();

# ---- END DTR COMPUTE TABLE -----

		Alert("Temporarily Saved!","warning");
		redirect("frm_generate_payroll.php?id=".$last_id."&date_start=".$date_start."&date_end=".$date_end."&pay_group=".$pay_group_id);
		// redirect("frm_generate_payroll.php?id=".$last_id);
		die();

	}else
	{
		redirect('index.php');
		die();
	}
	redirect('index.php');
	?>