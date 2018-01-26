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


	$check_start=$con->myQuery("SELECT date_from FROM payroll WHERE is_deleted = 0 AND (date_from BETWEEN '{$date_start}' AND '{$date_end}') AND pay_group_id = '{$inputs['pay_group']}'")->fetchAll(PDO::FETCH_ASSOC);
	$check_end=$con->myQuery("SELECT date_to FROM payroll WHERE is_deleted = 0 AND (date_to BETWEEN '{$date_start}' AND '{$date_end}') AND pay_group_id = '{$inputs['pay_group']}'")->fetchAll(PDO::FETCH_ASSOC);

	
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

	$x=1;
	$last_employee_id='';
	while ($data1=$employees->fetch(PDO::FETCH_ASSOC)) 
	{

		$start_date = new DateTime($inputs['date_start']);
		$end_date 	= new DateTime($inputs['date_end']);
		$datediff	= $start_date->diff($end_date);
		$days_count	= $datediff->format('%r%a');

		$base_date=$inputs['date_start'];

		//------------------------------------------------------------------------------------------------------------------

		$date_start1=$dateS;
		$date_end1=$dateE;

		$period = new DatePeriod(
			$date_start1,
			new DateInterval('P1W'),
			$date_end1->modify("+1 day")
			);

		$date_end1->modify("-1 day");
		$data=array();
		$index=0;
		$abc = 0;
		
		foreach ($period as $key => $date) {
			$week_array=getStartAndEndDate($date->format("W"), $date->format("Y"));


			if ($week_array['week_start'] < $date_start1->format("Y-m-d")) {
				$week_array['week_start'] = $date_start1->format("Y-m-d");
			}
			if ($week_array['week_end'] > $date_end1->format("Y-m-d")) {
				$week_array['week_end'] = $date_end1->format("Y-m-d");
			}

			$week_array['week_start'] = new DateTime($week_array['week_start']);
			$week_array['week_end'] = new DateTime($week_array['week_end']);
			$data_per_week=getHours($week_array['week_start']->format("Y-m-d"), $week_array['week_end']->format("Y-m-d"), $data1['id']);

			$week_start_shit = $week_array['week_start']->format("Y-m-d");
			$week_end_shit = $week_array['week_end']->format("Y-m-d");

			$ot_shit = $data_per_week['overtime']['hours'];
			$wh_shit = number_format($data_per_week['work_hours']['hours'],2);
			$total_shit= $ot_shit + $wh_shit;

			$test_lang[$abc] = array(
				'abc_id' 	=> $data1['id'],
				'abc_start'	=> $week_start_shit,
				'abc_end'	=> $week_end_shit,
				'abc_total'	=> $total_shit
				);
			$abc++;

		}


		//------------------------------------------------------------------------------------------------------------------

		$last_in_time_value = '';
		$last_out_time_value = '';
		$ot_work_hours = 0;
		$normal_work_hours =0;
		$what=0;
		for($i=0; $i<=$days_count; $i++)
		{
		    	# GET SHIFT (Time in and Time Out)
			$get_default_shift	= getShift($data1['id'],$base_date);
				# -----

			if (!empty($get_default_shift)) 
			{
				$for_insert[$x]['employee_id']	= $data1['id'];

	    		# GET FIRST TIME IN AND OUT
				$get_first_time_in	=$con->myQuery("SELECT id,in_time FROM attendance WHERE DATE_FORMAT(in_time,'%Y-%m-%d')=? AND employees_id=? ORDER BY id ASC LIMIT 1",array($base_date,$data1['id']))->fetch(PDO::FETCH_ASSOC);
				$get_last_time_out	=$con->myQuery("SELECT id,out_time FROM attendance WHERE DATE_FORMAT(in_time,'%Y-%m-%d')=? AND employees_id=? ORDER BY id ASC LIMIT 10",array($base_date,$data1['id']))->fetch(PDO::FETCH_ASSOC);
				# ------


				$empty_in =false;
				$empty_out =false;

				if(empty($get_first_time_in['in_time'])){
					if($last_employee_id <> $data1['id']){
						$get_first_time_in['in_time'] = $date_start;

					}else{
						$date=new DateTime(substr($last_in_time_value, 0,10));
						$get_first_time_in['in_time'] = $date->modify('+1 day')->format('Y-m-d H:i:s');

					}

					$empty_in = true;

				}

				if(empty($get_last_time_out['out_time'])){
					if($last_employee_id <> $data1['id']){
						$get_last_time_out['out_time'] = $date_start;

					}else{
						$date1=new DateTime(substr($last_out_time_value, 0,10));
						$get_last_time_out['out_time'] = $date1->modify('+1 day')->format('Y-m-d H:i:s');
					}
					$empty_out = true;

				}

				$last_employee_id = $data1['id'];
				$last_in_time_value = $get_first_time_in['in_time'];
				$last_out_time_value = $get_last_time_out['out_time'];


				$for_insert[$x]['official_time_in']		= $get_first_time_in['in_time'];
				$for_insert[$x]['official_time_out']	= $get_last_time_out['out_time'];


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


		        		$late_mins = 0;
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
		        				$late_amount = ($hourlyrate /60)*$late_mins;
		        			}
		        		}else
		        		{
		        			$late_amount 	= 0;
		        		}

		        		$undertime_mins = 0;
					        # UNDERTIME 
		        		$global_time_out3 = substr($get_last_time_out['out_time'],0,10).' '.$get_default_shift['time_out'];

		        		
		        		if ($global_time_out3 >= $get_last_time_out['out_time'])
		        		{
		        			if(!empty($empty_out == false)){


		        				$undertime_mins = computeTimeDiff($get_last_time_out['out_time'],$global_time_out3);

		        				$checkPM=checkHalfDay('PM',$data1['id'],substr($get_last_time_out['out_time'],0,10));
		        				if(empty($checkPM)) 
		        				{
		        					$undertime_amount = ($hourlyrate * $undertime_mins);
		        				}else
		        				{
		        					$undertime_amount = 0;
		        				}
		        			}else{
		        				$undertime_amount = 0;
		        			}
		        		}else
		        		{
		        			$undertime_amount = 0;
		        		}
					    # -----
		        	}


		        	//echo $data1['id'] . ' - ORIG OUT : '.$global_time_out3 . ' - ACTUAL OUT : ' . $get_last_time_out['out_time'] . ' = L='. $late_mins  . ' U='. $undertime_mins. '<br>';
		        	

		        	$for_insert[$x]['late']		= $late_amount+$undertime_amount;
		        	$for_insert[$x]['absent']	= $absent_amount;		
		        	
		        	$worked_hours = getHoursWorked($get_first_time_in['in_time'],$get_last_time_out['out_time'],$get_default_shift,TRUE);
		        	
		        	if(!empty($worked_hours['hours'])){
		        		$for_insert[$x]['worked_hours'] = floatval($worked_hours['hours']); 
		        		
		        		if($empty_in || $empty_out){
		        			$for_insert[$x]['worked_hours'] = 0;
		        			
		        		}

		        	}else{
		        		$for_insert[$x]['worked_hours'] = 0;
		        		
		        	}	

			    # -----

			     # OVERTIME COMPUTATION
		        	$for_OT_validate = 0;
		        	for($t=0; $t < count($test_lang); $t++)
		        	{
		        		if($base_date >= $test_lang[$t]['abc_start'] && $base_date <= $test_lang[$t]['abc_end'])
		        		{
		        			if($test_lang[$t]['abc_total'] >= 48)
		        			{
		        				$for_OT_validate = 1;
		        			}
		        		}
		        	}


		        	$holiday_day = getHolidayOfDay($base_date,$pay_group_id)['holiday_category'];
		        	$no_of_work_hoursx = getOvertimePerDayForPayroll($data1['id'],$base_date)['no_hours'];

		        	if($for_OT_validate == 0){
		        		if($holiday_day != 'Special Holiday' && $holiday_day != 'Legal Holiday' && $no_of_work_hoursx >= 0){
		        			$overtime_amount = (($overtime_rate * $hourlyrate) * $no_of_work_hoursx);
		        		}else{
		        			$overtime_amount = 0;
		        		}
		        	}else{
		        		$overtime_amount = 0;
		        	}

		        	if(!empty($no_of_work_hoursx)){
		        		$for_insert[$x]['no_of_work_hours_regular'] = $no_of_work_hoursx;
		        		$normal_work_hours += $no_of_work_hoursx;
		        	}else{
		        		$for_insert[$x]['no_of_work_hours_regular'] = 0;
		        		$normal_work_hours += 0;
		        	}
		        	
		        	$for_insert[$x]['overtime'] = $overtime_amount;


		        	

		        	// $for_insert[$x]['no_of_work_hours_premium'] =0;
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

			    # OVERTIME SPECIAL HOLIDAY COMPUTATION
			        if($for_OT_validate == 1){
			        	if ($holiday_day == 'Special Holiday')
			        	{
			        		$special_holiday_ot_amount = (($special_holiday_overtime_rate * $hourlyrate) * $no_of_work_hoursx);
			        	}else
			        	{	       
			        		$special_holiday_ot_amount = 0;
			        	}

			        }else{
			        	$special_holiday_ot_amount = 0;
			        }


			        $for_insert[$x]['overtime_special_holiday']	= $special_holiday_ot_amount;
			    # -----

			    # LEGAL HOLIDAY OVERTIME COMPUTATION
		        	# if no of work hours is greater than 48 hours then $legal_holiday_ot_amount with pay
		        		# else then $legal_holiday_ot_amount = 0 and legal_holiday with pay
			        if($for_OT_validate == 1){
			        	if ($holiday_day == 'Legal Holiday')
			        	{
			        		$legal_holiday_ot_amount = (($regular_holiday_overtime_rate * $hourlyrate) * $no_of_work_hoursx);
			        	}else
			        	{
			        		$legal_holiday_ot_amount = 0;
			        	}
			        }else{
			        	$legal_holiday_ot_amount = 0;
			        }

			        $for_insert[$x]['overtime_legal_holiday'] = $legal_holiday_ot_amount;
			    # -----

			    # SPECIAL AND LEGAL HOLIDAY COMPUTATION
			        $nd_time_in=new DateTime($get_first_time_in['in_time']);
			        $nd_time_out=new DateTime($get_last_time_out['out_time']);

			        $out_time 			= new DateTime($get_first_time_in['in_time']); 
			        $in_time 			= new DateTime($get_last_time_out['out_time']); 
			        $no_of_work_hours1 	= $out_time->diff($in_time);
			        $no_of_work_hours2 	= $no_of_work_hours1->h;
			        $no_of_work_mins	= ($no_of_work_hours1->i / 60);
			        $no_of_work_hours 	= ($no_of_work_hours2 + $no_of_work_mins);


			        if ($no_of_work_hours > 8) 
			        {
			        	$no_of_work_hours = 8;
			        }

			        if ($in_working_days) 
			        {
			        	if($for_OT_validate == 0){
			        		if ($holiday_day == 'Special Holiday')
					        { #SPECIAL HOLIDAY COMPUTATION
					        	$special_holiday_amount = (($special_holiday_rate * $hourlyrate) * $no_of_work_hours);
					        }else{
					        	$special_holiday_amount = 0;
					        }
					    }else{
					    	$special_holiday_amount = 0;
					    }
					}else{
						$special_holiday_amount = 0;
					}

					if ($in_working_days && empty(getNightShiftHours($nd_time_in,$nd_time_out))) 
					{
						if($for_OT_validate == 0){
							if ($holiday_day == 'Legal Holiday') 
					        { #LEGAL HOLIDAY COMPUTATION
					        	$legal_holiday_amount = (($regular_holiday_rate * $hourlyrate) * $no_of_work_hours);
					        }else 
					        {
					        	$legal_holiday_amount = 0;
					        }
					    }else{
					    	$legal_holiday_amount = 0;
					    }
					}else{
						$legal_holiday_amount = 0;
					}
					$for_insert[$x]['special_holiday'] 	= $special_holiday_amount;
					$for_insert[$x]['legal_holiday'] 	= $legal_holiday_amount;
			    # -----



			    # REST DAY COMPUTATION 

					
					if (empty(getNightShiftHours($nd_time_in,$nd_time_out))) {
						if (!$in_working_days && !$holiday_day == 'Special Holiday' && !$holiday_day == 'Legal Holiday') 
						{
							$rest_day_amount = (($hourlyrate * $rest_day_overtime_rate) * $no_of_work_hours);
						}else 
						{
							$rest_day_amount = 0;
						}
					}else{
						$rest_day_amount = 0;
					}

			          // echo $data1['id'] .' - '. $get_first_time_in['in_time'] .' - ' .$get_last_time_out['out_time'].'(' . $hourlyrate .'*'. $rest_day_overtime_rate .')'.'*' .$no_of_work_hours.') = ' . $rest_day_amount .'<br>';

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
					$next_date_hours=0;
					$current_date_hours=0;


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
				        	if($in_working_days){
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
				        // var_dump($holiday_day == 'Legal Holiday' && !($holiday_day == 'Special Holiday') && !$in_working_days);

				        if($holiday_day == 'Legal Holiday' && !($holiday_day == 'Special Holiday') && !$in_working_days){
				        	$f_legal_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_regular_holiday_rate * $rest_day_rate) * $current_date_hours);
				        } elseif ($holiday_day == 'Special Holiday' && !($holiday_day == 'Legal Holiday') && !$in_working_days) {
				        	$f_special_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_special_holiday_rate * $rest_day_rate) * $current_date_hours);
				        } elseif($in_working_days && !($holiday_day == 'Legal Holiday') && !($holiday_day == 'Special Holiday') && $for_OT_validate == 0){
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
				        	if($out_working_days){
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

				        if($holiday_day == 'Legal Holiday' && !($holiday_day == 'Special Holiday') && !$out_working_days){
				        	$nd_legal_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_regular_holiday_rate * $rest_day_rate) * $next_date_hours);
				        } elseif ($holiday_day == 'Special Holiday' && !($holiday_day == 'Legal Holiday') && !$out_working_days) {
				        	$nd_special_holiday_rd_night_shift_amount = (($hourlyrate * $rest_day_special_holiday_rate * $rest_day_rate) * $next_date_hours);
				        } elseif($out_working_days && !($holiday_day == 'Legal Holiday') && !($holiday_day == 'Special Holiday') && $for_OT_validate == 0){
				        	$nd_ordinary_day_ns_amount = (($hourlyrate *  $night_differential_rate) * $next_date_hours);	

				        }
				        //echo 'next: '.$next_date_hours.'</br>';

				    }

				    // ---------------------------------------------------------------------------------------------------------------------------
				    $premium_night_shift_OThours=0;
				    if($for_OT_validate == 1 && $in_working_days){
				    	$premium_night_shift_OThours = $current_date_hours + $next_date_hours;

				    	$premium_night_shift_OTamount = ((($hourlyrate * $overtime_rate) * $night_differential_rate) * $premium_night_shift_OThours);	
				    }else{
				    	$premium_night_shift_OTamount = 0;
				    }
				    

				    $premium_night_shift_RDhours=0;
				    if($for_OT_validate == 1 && !$in_working_days){
				    	$premium_night_shift_RDhours = $current_date_hours + $next_date_hours;

				    	$premium_night_shift_RDamount = ((($hourlyrate * $rest_day_overtime_rate) * $night_differential_rate) * $premium_night_shift_RDhours);	
				    }else{
				    	$premium_night_shift_RDamount = 0;
				    }

				    // ---------------------------------------------------------------------------------------------------------------------------


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
				    $for_insert[$x]['special_holiday_rest_day_night_shift']	= $special_holiday_rd_amount;
				    $for_insert[$x]['legal_holiday_rest_day_night_shift']	= $legal_holiday_rd_amount;
				    $for_insert[$x]['night_diff_ordinary_ot']				= $premium_night_shift_OTamount;
				    $for_insert[$x]['night_diff_restday_ot']				= $premium_night_shift_RDamount;

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
				legal_holiday_rest_day_night_shift,
				night_diff_ordinary_ot,
				night_diff_restday_ot
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
				:legal_holiday_rest_day_night_shift,
				:night_diff_ordinary_ot,
				:night_diff_restday_ot
				)",$for_insert[$i]);


		}

		// die;
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