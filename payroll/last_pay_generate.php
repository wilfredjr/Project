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
	$employee_id 			= $inputs['employee_id'];

	$employee_details = $con->myQuery("SELECT * FROM employees WHERE id=?",array($employee_id))->fetch(PDO::FETCH_ASSOC);

	// echo "<pre>";
	// print_r($inputs);
	// echo "</pre>";
	// die();

	$for_dtrcompute = array();


# ---- FOR DTR COMPUTE TABLE -----


	if(!empty($employee_details))
	{
		$pay_group_id 	= $employee_details['payroll_group_id'];
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



	$start_date = new DateTime($inputs['date_start']);
	$end_date 	= new DateTime($inputs['date_end']);
	$datediff	= $start_date->diff($end_date);
	$days_count	= $datediff->format('%r%a');

	$base_date=$inputs['date_start'];

	$x = 0;
	for($i=0; $i<=$days_count; $i++)
	{
	    # GET SHIFT (Time in and Time Out)
		$get_default_shift	= getShift($employee_id,$base_date);
		# -----	

		if (!empty($get_default_shift)) 
		{

    		# GET FIRST TIME IN AND OUT
				$get_first_time_in	= $con->myQuery("SELECT id,in_time FROM attendance WHERE DATE_FORMAT(in_time,'%Y-%m-%d')=? AND employees_id=? ORDER BY id ASC LIMIT 1",array($base_date,$employee_id))->fetch(PDO::FETCH_ASSOC);
				$get_last_time_out	= $con->myQuery("SELECT id,out_time FROM attendance WHERE DATE_FORMAT(in_time,'%Y-%m-%d')=? AND employees_id=? ORDER BY id ASC LIMIT 10",array($base_date,$employee_id))->fetch(PDO::FETCH_ASSOC);
			# ------

			$for_dtrcompute[$x]['official_date']		= $base_date;	
			$for_dtrcompute[$x]['official_time_in']		= $get_first_time_in['in_time'];
			$for_dtrcompute[$x]['official_time_out']	= $get_last_time_out['out_time'];
			$date_in = substr($get_first_time_in['in_time'],0,10);


			# COMPENSATION
				$basic_salary 	= get_basic_salary($employee_id)['basic_salary'];
				$dailyrate 		= (intval($basic_salary) / intval($days_per_month));
				$hourlyrate 	= intval($dailyrate / 8);

				$for_dtrcompute[$x]['daily_rate']		= $dailyrate;
				$for_dtrcompute[$x]['hourly_rate']		= $hourlyrate;
				$for_dtrcompute[$x]['night_rate']		= $night_differential_rate;
			# -----


		    # ABSENT/LEAVE WITHOUT PAY (CHECK OB, LEAVE, AND OFFSET) COMPUTATION
		        $working_days 		= getShift($employee_id,$base_date)['working_days'];
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
			            $in_working_days=in_array("Th", explode(",", $working_days));
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
		        	$check_ob 			= get_employees_ob($employee_id,$base_date); 		# start and end time of OB is excluded 
		        	$check_offset 		= get_employees_offset($employee_id,$base_date); 	# start and end time of OFFSET is excluded
		        	$check_leave		= checkLeave($employee_id,$base_date);

		        	$late_amount 		= '0.00';
		        	$undertime_amount 	= '0.00';
		        	$total_late			= '0.00';
	        		
					if ((!empty($check_ob) && $check_ob !== 0) || (!empty($check_offset) && $check_offset !== 0) || (!empty($check_leave) && $check_leave !== 0)) # check ob and offset and leave
					{
						$absent_amount = "0.00";
					}else
					{
						if (!$in_working_days) 
				        {
				        	$absent_amount = '0.00';
				        }else 
				        {
							$absent_amount = $dailyrate;
				        }
					}		        		
	        	}else # if with time in and out, check lates and undertime
	        	{
	        		$absent_amount = "0.00";

	        		# LATE AND UDERTIME COMPUTATION
				    # LATE
	        		$global_time_in1 	= new DateTime($base_date.' '.$get_default_shift['time_in']);
	        		$in_time 			= new DateTime($get_first_time_in['in_time']);

	        		if ($global_time_in1 < $in_time)
	        		{
	        			$checkAM=checkHalfDay('AM',$employee_id,$base_date);
	        			if (empty($checkAM)) 
	        			{
	        				$late_mins 		= $global_time_in1->diff($in_time);
	        				$late_mins->i;	           
	        				$late_amount 	= ($hourlyrate / 60) * $late_mins->i;
	        			}
	        		}else
	        		{
	        			$late_amount 	= '0.00';
	        		}
				    
				    # UNDERTIME 
	        		$global_time_out3 = substr($get_last_time_out['out_time'],0,10).' '.$get_default_shift['time_out'];

	        		if ($global_time_out3 >= $get_last_time_out['out_time'])
	        		{
	        			$undertime_mins = computeTimeDiff($get_last_time_out['out_time'],$global_time_out3);

	        			$checkPM=checkHalfDay('PM',$employee_id,substr($get_last_time_out['out_time'],0,10));
	        			if(empty($checkPM)) 
	        			{
	        				$undertime_amount = ($hourlyrate * $undertime_mins);
	        			}else
	        			{
	        				$undertime_amount = "0.00";
	        			}
	        		}else
	        		{
	        			$undertime_amount = '0.00';
	        		}
				    # -----

	        		if (!$in_working_days) 
			        {
			        	$total_late = '0.00';
			        }else 
			        {
						$total_late = $late_amount+$undertime_amount;
			        }
	        	}

	        	$for_dtrcompute[$x]['late']		= $total_late;


	        	$for_dtrcompute[$x]['absent']	= $absent_amount;			
		    # -----

		     # OVERTIME COMPUTATION
	        	$ot_hours 					= get_employees_ot($employee_id,$base_date)['no_hours'];
	        	$overtime_amount 			= (($overtime_rate * $hourlyrate) * $ot_hours);

	        	$for_dtrcompute[$x]['overtime']	= $overtime_amount;
		    # -----

		    # OVERTIME SPECIAL HOLIDAY COMPUTATION
	        	$holiday_day = getHolidayOfDay($base_date,$pay_group_id)['holiday_category'];
	        	if ($holiday_day == 'Special Holiday' and $ot_hours <> 0)
	        	{
	        		$special_holiday_ot_amount = (($special_holiday_overtime_rate * $hourlyrate) * $ot_hours);
	        	}else
	        	{	       
	        		$special_holiday_ot_amount = '0.00';
	        	}

	        	$for_dtrcompute[$x]['overtime_special_holiday']	= $special_holiday_ot_amount;
		    # -----

		    # LEGAL HOLIDAY OVERTIME COMPUTATION
	        	if ($holiday_day == 'Legal Holiday' and $ot_hours <> 0)
	        	{
	        		$legal_holiday_ot_amount = (($regular_holiday_overtime_rate * $hourlyrate) * $ot_hours);
	        	}else
	        	{
	        		$legal_holiday_ot_amount = '0.00';
	        	}

	        	$for_dtrcompute[$x]['overtime_legal_holiday'] = $legal_holiday_ot_amount;
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
	        	if ($holiday_day == 'Special Holiday')
		        { #SPECIAL HOLIDAY COMPUTATION
		        	$special_holiday_amount = (($special_holiday_rate * $hourlyrate) * $no_of_work_hours);
		        }else
		        {
		        	$special_holiday_amount = '0.00';
		        }
		        if ($holiday_day == 'Legal Holiday') 
		        { #LEGAL HOLIDAY COMPUTATION
		        	$legal_holiday_amount = (($regular_holiday_rate * $hourlyrate) * $no_of_work_hours);
		        }else 
		        {
		        	$legal_holiday_amount = '0.00';
		        }

		        $for_dtrcompute[$x]['special_holiday'] 	= $special_holiday_amount;
		        $for_dtrcompute[$x]['legal_holiday'] 	= $legal_holiday_amount;
		    # -----

		   	# GET WORKING DAYS
		        $working_days 		= getShift($employee_id,$get_first_time_in['in_time'])['working_days'];
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
		            $in_working_days=in_array("Th", explode(",", $working_days));
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
		        	$rest_day_amount = '0.00';
		        }

		        $for_dtrcompute[$x]['rest_day'] = $rest_day_amount;
		    # -----

		    # REST DAY SPECIAL HOLIDAY
		        if (!$in_working_days) 
		        {
		        	$rest_day_special_holiday_amount = '0.00';
		        	if ($holiday_day == 'Special Holiday') 
		        	{
		        		$rest_day_special_holiday_amount = (($rest_day_special_holiday_rate * $hourlyrate) * $no_of_work_hours);
		        	}
		        }else 
		        {
		        	$rest_day_special_holiday_amount = '0.00';
		        }

		        $for_dtrcompute[$x]['rest_day_special_holiday'] = $rest_day_special_holiday_amount;
		    # -----

		    # REST DAY LEGAL HOLIDAY
		        if (!$in_working_days) 
		        {
		        	$rest_day_legal_holiday_amount = '0.00';
		        	if ($holiday_day == 'Legal Holiday') 
		        	{
		        		$rest_day_legal_holiday_amount = (($rest_day_special_holiday_rate * $hourlyrate) * $no_of_work_hours);
		        	}
		        } else 
		        {
		        	$rest_day_legal_holiday_amount = '0.00';
		        }

		        $for_dtrcompute[$x]['rest_day_legal_holiday'] = $rest_day_legal_holiday_amount;
		    # -----

			
			# FOR NIGHT SHIFTS
		        $nd_rest_day_night_shift_amount = '0.00';
		        $f_rest_day_night_shift_amount = '0.00';
		        $nd_regular_holiday_night_shift_amount = '0.00';
		        $f_regular_holiday_night_shift_amount = '0.00';
		        $nd_special_holiday_night_shift_amount= '0.00';
		        $f_special_holiday_night_shift_amount = '0.00';
		        $nd_ordinary_day_ns_amount  = '0.00';
		        $f_ordinary_day_ns_amount  = '0.00';
		        $nd_special_holiday_rd_night_shift_amount  = '0.00';
		        $f_legal_holiday_rd_night_shift_amount = '0.00';
		        $f_special_holiday_rd_night_shift_amount = '0.00';

		        $nd_time_in=new DateTime($get_first_time_in['in_time']);
		        $nd_time_out=new DateTime($get_last_time_out['out_time']);

		        
		        //FIRST DAY
		        if(!empty(getNightShiftHours($nd_time_in,$nd_time_out)))
		        {

		        	$current_date_hours = getNightShiftHours($nd_time_in,$nd_time_out)['current_date_hours'];
		        	$current_date 		= getNightShiftHours($nd_time_in,$nd_time_out)['current_date'];

		        	$working_days_i		= getShift($employee_id,$current_date)['working_days'];
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
			            $in_working_days=in_array("Th", explode(",", $working_days_i));
			            break;
			            case '5': #friday
			            $in_working_days=in_array("F", explode(",", $working_days_i));
			            break;
			            case '6': #saturday
			            $in_working_days=in_array("Sa", explode(",", $working_days_i));
			            break;
			        }

			        if (!empty($holiday_day)) {
			        	switch ($holiday_day) {
			        		case 'Legal Holiday':
			        		$f_regular_holiday_night_shift_amount = (($hourlyrate * $regular_holiday_rate * $night_differential_rate) * $current_date_hours);
			        		break;
			        		case 'Special Holiday':
			        		$f_special_holiday_night_shift_amount = (($hourlyrate * $special_holiday_rate * $night_differential_rate) * $current_date_hours);
			        		break;			        		
			        	}

			        } else {
			        	if(!$in_working_days){
			        		#REST DAY
			        		$f_rest_day_night_shift_amount = (($hourlyrate * $rest_day_rate * $night_differential_rate) * $current_date_hours);
			        	}
			        }

			        if($holiday_day = 'Legal Holiday' && !$in_working_days){
			        	$f_legal_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_regular_holiday_rate * $rest_day_rate) * $current_date_hours);
			        } elseif ($holiday_day = 'Special Holiday' && !$in_working_days) {
			        	$f_special_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_special_holiday_rate * $rest_day_rate) * $current_date_hours);
			        } else{
			        	$f_ordinary_day_ns_amount = (($hourlyrate * $night_differential_rate) * $current_date_hours);		
			        }
			    }

		    	//NEXT DAY
			    if (!empty(getNightShiftHours($nd_time_in,$nd_time_out))) 
			    {
		        	#Next Day
			    	$next_date_hours 	= getNightShiftHours($nd_time_in,$nd_time_out)['next_date_hours'];
			    	$next_date 			= getNightShiftHours($nd_time_in,$nd_time_out)['next_date'];

			    	$working_days_o		= getShift($employee_id,$next_date)['working_days'];
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
			            $out_working_days=in_array("Th", explode(",", $working_days_o));
			            break;
			            case '5': #friday
			            $out_working_days=in_array("F", explode(",", $working_days_o));
			            break;
			            case '6': #saturday
			            $out_working_days=in_array("Sa", explode(",", $working_days_o));
			            break;
			        }



			        $next_day_holiday=getHolidayOfDay($employee_id, $next_date);

			        if (!empty($next_day_holiday)) {
			        	switch ($next_day_holiday) {
			        		case 'Legal Holiday':
			        		$nd_regular_holiday_night_shift_amount = (($hourlyrate * $regular_holiday_rate * $night_differential_rate) * $next_date_hours);
			        		break;
			        		case 'Special Holiday':
			        		$nd_special_holiday_night_shift_amount = (($hourlyrate * $special_holiday_rate * $night_differential_rate) * $next_date_hours);
			        		break;			        		
			        	}
			        } else {
			        	if(!$out_working_days){
				        	#REST DAY
			        		$nd_rest_day_night_shift_amount = (($hourlyrate * $rest_day_rate * $night_differential_rate) * $next_date_hours);
			        	}
			        }

			        if($holiday_day = 'Legal Holiday' && !$out_working_days){
			        	$nd_legal_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_regular_holiday_rate * $rest_day_rate) * $next_date_hours);
			        } elseif ($holiday_day = 'Special Holiday' && !$out_working_days) {
			        	$nd_special_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_special_holiday_rate * $rest_day_rate) * $next_date_hours);
			        } else{
			        	$nd_ordinary_day_ns_amount = (($hourlyrate * $night_differential_rate) * $next_date_hours);		
			        }
			    }

			    $ordinary_day_night_shift_amount = ($f_ordinary_day_ns_amount + $nd_ordinary_day_ns_amount);
			    $rest_day_night_shift_amount = ($f_rest_day_night_shift_amount + $nd_rest_day_night_shift_amount);
			    $special_holiday_night_shift_amount = ($f_special_holiday_night_shift_amount + $nd_special_holiday_night_shift_amount);
			    $regular_holiday_night_shift_amount = ($f_regular_holiday_night_shift_amount + $nd_regular_holiday_night_shift_amount);
			    $legal_holiday_rd_amount = ($f_legal_holiday_rd_night_shift_amount + $nd_special_holiday_rd_night_shift_amount);
			    $special_holiday_rd_amount = ($f_special_holiday_rd_night_shift_amount + $nd_special_holiday_rd_night_shift_amount);
			    

			    $for_dtrcompute[$x]['ordinary_day_night_shift']				= $ordinary_day_night_shift_amount;
			    $for_dtrcompute[$x]['rest_day_night_shift']					= $rest_day_night_shift_amount;
			    $for_dtrcompute[$x]['special_holiday_night_shift']			= $special_holiday_night_shift_amount;
			    $for_dtrcompute[$x]['legal_holiday_night_shift']			= $regular_holiday_night_shift_amount;
			    $for_dtrcompute[$x]['special_holiday_rest_day_night_shift']	= $legal_holiday_rd_amount;
			    $for_dtrcompute[$x]['legal_holiday_rest_day_night_shift']	= $special_holiday_rd_amount;
			# -----


		    $x++;

			$stop_date = new DateTime($base_date.' 20:24:00');
			$stop_date->modify('+1 day');
			$base_date=$stop_date->format('Y-m-d');
		}
	}

// echo "<pre>";
// print_r($for_dtrcompute);
// echo "</pre>";
// die();

	if (empty($for_dtrcompute)) 
	{
		$for_lastsalary['net_pay'] == "0.00";
	}else
	{
		$total_late 		= 0;
		$total_absent 		= 0;
		$total_overtime 	= 0;
		$total_ot_sp_hol 	= 0;
		$total_ot_lg_hol 	= 0;
		$total_sp_hol 		= 0;
		$total_lg_hol 		= 0;
		$total_restday 		= 0;
		$total_rd_sp_hol 	= 0;
		$total_rd_lg_hol 	= 0;
		$total_nightshift	= 0;
		$total_night_rd		= 0;
		$total_night_sp		= 0;
		$total_night_lg		= 0;
		$total_night_rd_sp	= 0;
		$total_night_rd_lg	= 0;

		for($y=0; $y<$days_count; $y++)
		{
			$total_late 		= $total_late + $for_dtrcompute[$y]['late'];
			$total_absent 		= $total_absent + $for_dtrcompute[$y]['absent'];
			$total_overtime 	= $total_overtime + $for_dtrcompute[$y]['overtime'];
			$total_ot_sp_hol 	= $total_ot_sp_hol + $for_dtrcompute[$y]['overtime_special_holiday'];
			$total_ot_lg_hol 	= $total_ot_lg_hol + $for_dtrcompute[$y]['overtime_legal_holiday'];
			$total_sp_hol 		= $total_sp_hol + $for_dtrcompute[$y]['special_holiday'];
			$total_lg_hol 		= $total_lg_hol + $for_dtrcompute[$y]['legal_holiday'];
			$total_restday 		= $total_restday + $for_dtrcompute[$y]['rest_day'];
			$total_rd_sp_hol 	= $total_rd_sp_hol + $for_dtrcompute[$y]['rest_day_special_holiday'];
			$total_rd_lg_hol 	= $total_rd_lg_hol + $for_dtrcompute[$y]['rest_day_legal_holiday'];
			$total_nightshift 	= $total_nightshift + $for_dtrcompute[$y]['ordinary_day_night_shift'];
			$total_night_rd 	= $total_night_rd + $for_dtrcompute[$y]['rest_day_night_shift'];
			$total_night_sp 	= $total_night_sp + $for_dtrcompute[$y]['special_holiday_night_shift'];
			$total_night_lg 	= $total_night_lg + $for_dtrcompute[$y]['legal_holiday_night_shift'];
			$total_night_rd_sp 	= $total_night_rd_sp + $for_dtrcompute[$y]['special_holiday_rest_day_night_shift'];
			$total_night_rd_lg 	= $total_night_rd_lg + $for_dtrcompute[$y]['legal_holiday_rest_day_night_shift'];
		  
		  	$daily_rate 		= $for_dtrcompute[$y]['daily_rate'];
		  	$hourly_rate 		= $for_dtrcompute[$y]['hourly_rate'];	
		}

	  	# GET BASIC SALARY
		    $period_id 		= get_salary_settings($pay_group_id)['pay_period_id'];
		    $basic_salary 	= get_basic_salary($employee_id)['basic_salary'];
		       
		    if ($period_id == 2)
		    { # semi-monthly
		        $basic_salary = ($basic_salary / 2);
		    } else 
		    { # monthly
		        $basic_salary = $basic_salary; 
		    }
	    # -----

	    $for_lastsalary['basic_salary'] 							= $basic_salary;
		$for_lastsalary['late'] 									= $total_late;
		$for_lastsalary['absent'] 									= $total_absent;
		$for_lastsalary['overtime'] 								= $total_overtime + $total_ot_sp_hol + $total_ot_lg_hol;

		# TAXABLE AMOUNT
		    $taxable_amount = get_taxablededuction($employee_id);
		    if(!empty($taxable_amount))
		    {
		        $for_lastsalary['tax_allowance'] = $taxable_amount;
		    }else
		    {
		        $for_lastsalary['tax_allowance'] = '0.00';
		    }
		# -----

		# RECEIVABLE AMOUNT
	        $receivables_amount = get_receivablesdeduction($employee_id);
	        if(!empty($receivables_amount))
	        {
	            $for_lastsalary['receivable'] = $receivables_amount;
	        }else
	        {
	            $for_lastsalary['receivable'] = '0.00';
	        }
	    # -----

	    # DE MINIMIS AMOUNT
	        $deminimis_amount = get_deminimis($employee_id);  
	        if(!empty($deminimis_amount))
	        {
	            $for_lastsalary['de_minimis'] = $deminimis_amount;
	        }else
	        {
	            $for_lastsalary['de_minimis'] = '0.00';
	        }
	    # -----

	    # COMPANY DEDUCTIONS
	        $company_deduction_amount = get_company_deductions($employee_id);
	        if(!empty($company_deduction_amount))
	        {
	            $for_lastsalary['company_deduction'] = $company_deduction_amount;
	        }else
	        {
	            $for_lastsalary['company_deduction'] = '0.00';
	        }
	    # -----

	    # PAYROLL ADJUSTMENT
	        $payroll_adjustments_type = get_payroll_adjustments($employee_id,$date_start,$date_end)['adjustment_type']; 

	        if ($payroll_adjustments_type == 0) 
	        { # minus
	            $payroll_adjustments_amount_minus = get_payroll_adjustments($employee_id,$date_start,$date_end)['amount'];
	        }else
	        {
	            $payroll_adjustments_amount_minus = '0.00';
	        }  
	        
	        if ($payroll_adjustments_type <> 0) 
	        { # plus
	            $payroll_adjustments_amount_plus = get_payroll_adjustments($employee_id,$date_start,$date_end)['amount'];
	        }else
	        {
	            $payroll_adjustments_amount_plus = '0.00';
	        }  
	    # -----

	    # LEAVES
	        $check_leaves_whole_withoutpay   = get_employees_leaves_wholeday_without_pay($employee_id,$date_start,$date_end); 
	        $check_leaves_halfday_withoutpay = get_employees_leaves_halfday_without_pay($employee_id,$date_start,$date_end); 
	        $check_leaves_whole_withpay      = get_employees_leaves_wholeday_with_pay($employee_id,$date_start,$date_end); 
	        $check_leaves_halfday_withpay    = get_employees_leaves_halfday_with_pay($employee_id,$date_start,$date_end); 
	       

	        if ($check_leaves_whole_withoutpay > 0)
	        {
	            $leave_without_pay = $daily_rate;
	        }else
	        {
	            $leave_without_pay ='0.00';
	        }

	        if ($check_leaves_halfday_withoutpay > 0)
	        {
	            $leave_without_pay = ($daily_rate / 2);
	        }else
	        {
	            $leave_without_pay ='0.00';
	        }

	        if ($check_leaves_whole_withpay > 0)
	        {
	            $leave_with_pay = ($daily_rate / 2);
	        }else
	        {
	            $leave_with_pay ='0.00';
	        }

	        if ($check_leaves_halfday_withpay > 0)
	        {
	            $leave_with_pay = ($daily_rate / 2);
	        }else
	        {
	            $leave_with_pay ='0.00';
	        }
	    # -----

	    # OFF-SET
	        $offset_hours  = get_employees_offset_no($employee_id,$date_start,$date_end)['no_hours'];
	        $offset_amount = ($hourlyrate * $offset_hours);
	    # -----

	    # OFFICIAL BUSINESS  
	        $ob_time_from = new DateTime(get_employees_ob_data($employee_id,$date_start,$date_end)['time_from']);
	        $ob_time_to   = new DateTime(get_employees_ob_data($employee_id,$date_start,$date_end)['time_to']);

	        $ob_mins = $ob_time_from->diff($ob_time_to);
	        $ob_mins->i;

	        $ob_amount = ($hourlyrate * $ob_mins->i);
	    # -----


	    $basic_salary_with_deductions = ($basic_salary - ($total_late + $payroll_adjustments_amount_minus + $leave_without_pay + $total_absent + $company_deduction_amount));

	    $addto = (	$total_overtime + 
	    			$payroll_adjustments_amount_plus + 
	    			$leave_with_pay + 
	    			$ob_amount + 
	    			$total_sp_hol + 
	    			$total_lg_hol + 
	    			$total_restday + 
	    			$total_rd_sp_hol + 
	    			$total_rd_lg_hol + 
	    			$total_nightshift + 
	    			$total_night_rd + 
	    			$total_night_sp + 
	    			$total_night_lg + 
	    			$total_night_rd_sp + 
	    			$total_night_rd_lg + 
	    			$deminimis_amount + 
	    			$receivables_amount +  
	    			$taxable_amount );


	    # GOVERNMENT DEDUCTION
		    $sss_amount 	= get_sss($basic_salary); 
		    $ph_amount 		= get_philhealth($basic_salary);
		    $hdmf_amount 	= get_hdmf($basic_salary);

		    $total_government_deduction_amount 		= ($sss_amount + $ph_amount + $hdmf_amount);
	        
	        $for_lastsalary['government_deduction'] = $total_government_deduction_amount;
	    # -----

	    # TAX EARNINGS
	        $tax_earning    = ($basic_salary_with_deductions + $addto);
	        $for_lastsalary['tax_earning'] = $tax_earning;

	        $tax_detaiks 	= $con->myQuery("SELECT ts.code AS tax_compensation FROM employees e INNER JOIN tax_status ts ON ts.id=e.tax_status_id WHERE e.id=?",array($employee_id))->fetch(PDO::FETCH_ASSOC);

	        $tax_comp       = $tax_detaiks['tax_compensation'];
	        $tax_rate       = compute_tax(floatval($tax_earning),$tax_comp)['tax_rate'];
	        $tax_additional = compute_tax($tax_earning,$tax_comp)['tax_additional'];
	        $tax_ceiling    = compute_tax($tax_earning,$tax_comp)['tax_ceiling'];

	        $tax = ($tax_additional + (($tax_earning - $tax_ceiling) * $tax_rate));
	        $for_lastsalary['withholding_tax'] = $tax;

	        $total_deduction = ($tax + $total_government_deduction_amount + $company_deduction_amount);
	        $for_lastsalary['total_deduction'] = $total_deduction;
	    # -----

		# PAYROLL ADJUSTMENT     
	        if ($payroll_adjustments_type == 0) 
	        { # minus
	            $for_lastsalary['payroll_adjustment_m'] = $payroll_adjustments_amount_minus;
	        }else
	        {
	            $for_lastsalary['payroll_adjustment_m'] = '0.00';
	        }  
	        
	        if ($payroll_adjustments_type <> 0) 
	        { # plus    
	            $for_lastsalary['payroll_adjustment_p'] = $payroll_adjustments_amount_plus;
	        }else
	        {
	            $for_lastsalary['payroll_adjustment_p'] = '0.00';
	        }  

	        $for_lastsalary['payroll_year'] = date("Y");

	        $thirteen_month = (($basic_salary - $total_late) / 12);

	        $for_lastsalary['thirteen_month'] = $thirteen_month;
	    # -----

	    # LOANS
	        $check_loans = check_loans($employee_id);

	        if($check_loans > 0)
	        { # with loan
	            $check_loan_pass = check_loan_pass($employee_id,$date_start,$date_end);
	           
	            # loan pass checking
	            if ($check_loan_pass > 0 )
	            {
	                $loan_amount = '0.00';
	            }else
	            {
	                $emp_loan_id            = get_loan_details($employee_id)['emp_loan_id'];
	                $loan_cut_off_no        = get_loan_details($employee_id)['cut_off_no'];
	                $loan_emp_amount        = get_loan_details($employee_id)['loan_amount'];
	                $loan_balance           = get_loan_details($employee_id)['balance'];
	                $loan_remaining_cut_off = get_loan_details($employee_id)['remaining_cut_off_no'];

	                # check kung may remaining cut off
	                if(!empty($loan_remaining_cut_off))
	                {
	                    $loan_amount = ($loan_balance / $loan_remaining_cut_off);

	                    $loan_new_cut_off_no         = ($loan_remaining_cut_off - 1);
	                    $loan_new_remaining_balance = ($loan_balance - $loan_amount);

	                    if($loan_new_cut_off_no == 0 && $loan_new_remaianing_balance == 0)
	                    {
	                        $con->myQuery("UPDATE emp_loans SET status_id=2,balance = {$loan_new_remaining_balance}, remaining_cut_off_no = {$loan_new_cut_off_no} WHERE employee_id=?",array($employee_id));
	                    }else
	                    {
	                        $con->myQuery("UPDATE emp_loans SET balance = {$loan_new_remaining_balance}, remaining_cut_off_no = {$loan_new_cut_off_no} WHERE employee_id = ?",array($employee_id));
	                    }
	                }else
	                {
	                    $loan_amount = ($loan_emp_amount / $loan_cut_off_no);

	                    $loan_new_cut_off_no         	= ($loan_cut_off_no - 1);
	                    $loan_new_remaining_balance 	= ($loan_emp_amount - $loan_amount);

	                    $con->myQuery("UPDATE emp_loans SET balance = {$loan_new_remaining_balance}, remaining_cut_off_no = {$loan_new_cut_off_no} WHERE employee_id = ?",array($employee_id));
	                }

	                $params3=array(
	                        'emp_loan_id1'=>$emp_loan_id,
	                        'loan_amount1'=>$loan_amount
	                        );
	                
	                $con->myQuery("INSERT INTO emp_loans_det(emp_loan_id,amount_paid,date_deducted) VALUES (:emp_loan_id1,:loan_amount1,CURDATE())",$params3);  
	            }
	        }else
	        {
	            # no loan
	            $loan_amount = '0.00';
	        }
	    # -----


	    $net_pay 						= ($tax_earning - ($tax + $total_government_deduction_amount));
	    $for_lastsalary['loan_amount'] 	= $loan_amount;
	    $for_lastsalary['net_pay'] 		= ($net_pay - $loan_amount);

	}

// echo "<pre>";
// print_r($for_lastsalary);
// echo "</pre>";
# ***** NO SAVING TO DATABASE FOR LAST SALARY (for_lastsalary) *****


	# GET PRORATED 13TH MONTH
	$get_13th_month = $con->myQuery("SELECT SUM(13th_month) AS total_13th_month FROM payroll_details INNER JOIN payroll ON payroll.id=payroll_details.payroll_id WHERE employee_id=? AND done_13th_month=0 AND payroll.is_deleted=0 AND payroll.is_processed=1",array($employee_id))->fetch(PDO::FETCH_ASSOC);
	
	$total_13th_month = intval($get_13th_month['total_13th_month'] + $for_lastsalary['thirteen_month']);
	# -----

# ***** NO SAVING TO DATABASE FOR 13TH MONTH *****



	# GET SL/VL CONVERSION	
# ***** PARK SL/VL CONVERSION



	# GATHER LAST PAY MASTER INPUTS
	$for_last_pay['last_pay_code'] 			= "LP".$employee_details['code'];
	$for_last_pay['employee_id'] 			= $employee_id;
	$for_last_pay['date_start'] 			= $date_start;
	$for_last_pay['date_end'] 				= $date_end;
	$for_last_pay['last_salary'] 			= $for_lastsalary['net_pay'];
	$for_last_pay['13th_month'] 			= $total_13th_month;


# SAVING MODE
	$con->beginTransaction();

	$con->myQuery("INSERT INTO last_pay(last_pay_code,
										employee_id,
										date_start,
										date_end,
										last_salary,
										13th_month,
										date_generated)
					VALUES(	:last_pay_code,
							:employee_id,
							:date_start,
							:date_end,
							:last_salary,
							:13th_month,
							CURDATE())",$for_last_pay);
	
	$last_id=$con->lastInsertId();

	$con->commit();
# END SAVING


	Alert("Temporarily Saved!","warning");
	redirect("last_pay_view.php?id=".$last_id);
	die();
}
redirect('index.php');
?>