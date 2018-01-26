<?php
require_once("../support/config.php");

if(!isLoggedIn())
{
    toLogin();
    die();
}


if(!empty($_POST)){

        # VALIDATE INPUTS
    $inputs=$_POST;         
    $errors="";

    $payroll_id  =$inputs['payroll_id'];
    $pay_group   =$inputs['pay_group'];
    $dateS       =new DateTime($inputs['date_start']);
    $dateE       =new DateTime($inputs['date_end']);
    $date_start  =$dateS->format('Y-m-d');
    $date_end    =$dateE->format('Y-m-d');



    if(!empty($pay_group)){
        $con->beginTransaction();

        $dtr_details=$con->myQuery("SELECT
            dtr.id,
            dtr.employee_id,
            e.code as 'employee_code',
            CONCAT(e.last_name,', ',e.last_name,' ',e.middle_name) as 'employee_name',
            e.basic_salary,
            dtr.daily_rate,
            dtr.hourly_rate,
            ts.id as 'tax_compensation',
            SUM(dtr.late) as 'late',
            SUM(dtr.absent) as 'absent',
            SUM(dtr.overtime) as 'overtime',
            SUM(dtr.overtime_special_holiday) as 'overtime_special_holiday',
            SUM(dtr.overtime_legal_holiday) as 'overtime_legal_holiday',
            SUM(dtr.special_holiday) as 'special_holiday',
            SUM(dtr.legal_holiday) as 'legal_holiday',
            SUM(dtr.rest_day) as 'rest_day',
            SUM(dtr.rest_day_special_holiday) as 'rest_day_special_holiday',
            SUM(dtr.rest_day_legal_holiday) as 'rest_day_legal_holiday',
            SUM(dtr.ordinary_day_night_shift) as 'ordinary_day_night_shift',
            SUM(dtr.rest_day_night_shift) as 'rest_day_night_shift',
            SUM(dtr.special_holiday_night_shift) as 'special_holiday_night_shift',
            SUM(dtr.legal_holiday_night_shift) as 'legal_holiday_night_shift',
            SUM(dtr.special_holiday_rest_day_night_shift) as 'special_holiday_rest_day_night_shift',
            SUM(dtr.legal_holiday_rest_day_night_shift) as 'legal_holiday_rest_day_night_shift',
            SUM(dtr.night_diff_ordinary_ot) as 'night_diff_ordinary_ot',
            SUM(dtr.night_diff_restday_ot) as 'night_diff_restday_ot',
            p.date_from,
            p.date_to
            FROM dtr_compute dtr 
            INNER JOIN employees e ON dtr.employee_id = e.id 
            INNER JOIN tax_status ts ON e.tax_status_id = ts.id
            INNER JOIN payroll p ON dtr.payroll_id = p.id
            WHERE dtr.payroll_id = {$payroll_id} GROUP BY dtr.employee_id");

        $get_payroll_code = $con->myQuery("SELECT payroll_code FROM payroll WHERE id = ?",array($payroll_id))->fetch(PDO::FETCH_ASSOC);

        $x=1;

        while ($data=$dtr_details->fetch(PDO::FETCH_ASSOC))
        {
            //------------------------------------------------------------------------------------
            // BASIC SALARY
            if(!empty($inputs['pay_group'])){
                $pay_group_id = $inputs['pay_group'];

                $period_id          = get_salary_settings($pay_group_id)['pay_period_id'];
                $government_setting = get_salary_settings($pay_group_id)['government_settings'];
                $tax_setting        = get_salary_settings($pay_group_id)['tax_settings'];
                $basic_salary       = get_basic_salary($data['employee_id'])['basic_salary'];
                $basic_salary_gov   = get_basic_salary($data['employee_id'])['basic_salary'];

                    if ($period_id == 2){ // SEMI-MONTHLY
                        $basic_salary = ($basic_salary / 2);
                    } else { //MONTHLY
                        $basic_salary = $basic_salary; 
                    }
                }

            //------------------------------------------------------------------------------------


                $for_insert[$x]['payroll_id']        =$payroll_id;
                $for_insert[$x]['payroll_code']      =$get_payroll_code['payroll_code'];
                $for_insert[$x]['employee_id']       =$data['employee_id'];
                $for_insert[$x]['tax_compensation']  =$data['tax_compensation'];
                $for_insert[$x]['basic_salary']      =$basic_salary;
                $for_insert[$x]['late']              =$data['late'];
                $for_insert[$x]['absent']            =number_format($data['absent'], 2, '.', '');

                $employee_id                         =$data['employee_id'];
                $absent_amount                       =number_format($data['absent'], 2, '.', '');
                $late                                =$data['late'];
                $hourlyrate                          =$data['hourly_rate'];
                $date_from                           =$data['date_from'];
                $date_to                             =$data['date_to'];
                $taxex                               =$data['tax_compensation'];


            //------------------------------------------------------------------------------------
            // GOVERNMENT DEDUCTION
                $with_sss   = get_employee_govde_setting($employee_id)['w_sss'];
                $with_ph    = get_employee_govde_setting($employee_id)['w_philhealth'];
                $with_hdmf  = get_employee_govde_setting($employee_id)['w_hdmf'];

                $sss_ded    = get_salary_settings($pay_group_id)['sss_deduction'];
                $ph_ded     = get_salary_settings($pay_group_id)['philhealth_deduction'];
                $hdmf_ded   = get_salary_settings($pay_group_id)['pagibig_deduction'];

                $date_s     = $dateS->format('d');
                $date_e     = $dateE->format('d');



                if ($with_sss == 1)
                {
                if(intval($date_s) >= 26 && intval($date_e) <= 10){ //1st cut-off
                    if($sss_ded == 1){
                        $sss_code = get_sss_details($basic_salary_gov)['sss_code'];
                        $sss_ee   = get_sss_details($basic_salary_gov)['sss_ee'];
                        $sss_er   = get_sss_details($basic_salary_gov)['sss_er'];
                        $sss_ec   = get_sss_details($basic_salary_gov)['sss_ec'];

                        $sss_amount = get_sss($basic_salary_gov);
                    }else{
                        $sss_code   = '0.00';
                        $sss_ee     = '0.00';
                        $sss_er     = '0.00';
                        $sss_ec     = '0.00';

                        $sss_amount = '0.00';
                    }
                }elseif(intval($date_s) >=11 && intval($date_e) <= 25){ //2nd cut-off
                    if($sss_ded == 2){
                        $sss_code = get_sss_details($basic_salary_gov)['sss_code'];
                        $sss_ee   = get_sss_details($basic_salary_gov)['sss_ee'];
                        $sss_er   = get_sss_details($basic_salary_gov)['sss_er'];
                        $sss_ec   = get_sss_details($basic_salary_gov)['sss_ec'];

                        $sss_amount = get_sss($basic_salary_gov);
                    }else{
                        $sss_code   = '0.00';
                        $sss_ee     = '0.00';
                        $sss_er     = '0.00';
                        $sss_ec     = '0.00';

                        $sss_amount = '0.00';
                    }
                }else{
                   $sss_code   = '0.00';
                   $sss_ee     = '0.00';
                   $sss_er     = '0.00';
                   $sss_ec     = '0.00';

                   $sss_amount = '0.00';
               }

               $paramSSS=array(
                'payroll_code' =>$get_payroll_code['payroll_code'],
                'employee_id'  =>$employee_id,
                'sss_code'     =>$sss_code,
                'sss_ee'       =>$sss_ee,
                'sss_er'       =>$sss_er,
                'sss_ec'       =>$sss_ec,
                'sss_desc'     =>'SSS'
                );

               $con->myQuery("INSERT INTO payroll_govde(payroll_code,employee_id,govde_code,govde_eeshare,govde_ershare,gov_desc,gov_ec) VALUES (:payroll_code,:employee_id,:sss_code,:sss_ee,:sss_er,:sss_desc,:sss_ec)",$paramSSS);
           }else{
            $sss_amount = '0.00';
        }

        if ($with_ph == 1)
        {

                if(intval($date_s) >= 26 && intval($date_e) <= 10){ //1st cut-off
                    if($ph_ded == 1){
                        $ph_code = get_philhealth_details($basic_salary_gov)['ph_code'];
                        $ph_ee   = get_philhealth_details($basic_salary_gov)['ph_ee'];
                        $ph_er   = get_philhealth_details($basic_salary_gov)['ph_er'];

                        $ph_amount = get_philhealth($basic_salary_gov);
                    }else{
                        $ph_code = '0.00';
                        $ph_ee   = '0.00';
                        $ph_er   = '0.00';

                        $ph_amount = '0.00';
                    }

                }elseif(intval($date_s) >= 11 && intval($date_e) <= 25){ //2nd cut-off
                    if($ph_ded == 2){
                        $ph_code = get_philhealth_details($basic_salary_gov)['ph_code'];
                        $ph_ee   = get_philhealth_details($basic_salary_gov)['ph_ee'];
                        $ph_er   = get_philhealTh_details($basic_salary_gov)['ph_er'];

                        $ph_amount = get_philhealth($basic_salary_gov);
                    }else{
                        $ph_code = '0.00';
                        $ph_ee   = '0.00';
                        $ph_er   = '0.00';

                        $ph_amount = '0.00';
                    }
                }  else{
                    $ph_code = '0.00';
                    $ph_ee   = '0.00';
                    $ph_er   = '0.00';

                    $ph_amount = '0.00';
                }

                $paramPH=array(
                    'payroll_code' =>$get_payroll_code['payroll_code'],
                    'employee_id'  =>$employee_id,
                    'ph_code'      =>$ph_code,
                    'ph_ee'        =>$ph_ee,
                    'ph_er'        =>$ph_er,
                    'ph_desc'      =>'PhilHealth'
                    );

            
                $con->myQuery("INSERT INTO payroll_govde(payroll_code,employee_id,govde_code,govde_eeshare,govde_ershare,gov_desc) VALUES (:payroll_code,:employee_id,:ph_code,:ph_ee,:ph_er,:ph_desc)",$paramPH);


            }else{
                $ph_amount = '0.00';
            }

            if ($with_hdmf == 1)
            {

                if(intval($date_s) >= 26 && intval($date_e) <= 10){ //1st cut-off
                    if($hdmf_ded == 1){
                        $hdmf_code = get_hdmf_details($basic_salary_gov)['hdmf_code'];
                        $hdmf_ee   = get_hdmf_details($basic_salary_gov)['hdmf_ee'];
                        $hdmf_er   = get_hdmf_details($basic_salary_gov)['hdmf_er'];

                        $hdmf_amount = get_hdmf($basic_salary_gov);
                        // $hdmf_er   = $basic_salary_gov * (get_hdmf_details($basic_salary_gov)['hdmf_er'] / 100);

                        // $hdmf_amount = $basic_salary_gov * (get_hdmf($basic_salary_gov) / 100);
                    }else{
                        $hdmf_code = '0.00';
                        $hdmf_ee   = '0.00';
                        $hdmf_er   = '0.00';

                        $hdmf_amount = '0.00';
                    }
                }elseif(intval($date_s) >= 11 && intval($date_e) <= 25){ //2nd cut-off
                    if($hdmf_ded == 2){
                        $hdmf_code = get_hdmf_details($basic_salary_gov)['hdmf_code'];
                        $hdmf_ee   = get_hdmf_details($basic_salary_gov)['hdmf_ee']; 
                        $hdmf_er   = get_hdmf_details($basic_salary_gov)['hdmf_er'];

                        $hdmf_amount = get_hdmf($basic_salary_gov);
                        //NEW COMPUTATION
                        // $hdmf_er   = $basic_salary_gov * (get_hdmf_details($basic_salary_gov)['hdmf_er'] / 100);

                        // $hdmf_amount = $basic_salary_gov * (get_hdmf($basic_salary_gov) / 100);
                    }else{
                        $hdmf_code = '0.00';
                        $hdmf_ee   = '0.00';
                        $hdmf_er   = '0.00';

                        $hdmf_amount = '0.00';
                    }
                } else{
                    $hdmf_code = '0.00';
                    $hdmf_ee   = '0.00';
                    $hdmf_er   = '0.00';

                    $hdmf_amount = '0.00';
                } 

                $paramHDMF=array(
                    'payroll_code'   =>$get_payroll_code['payroll_code'],
                    'employee_id'    =>$employee_id,
                    'hdmf_code'      =>$hdmf_code,
                    'hdmf_ee'        =>$hdmf_ee,
                    'hdmf_er'        =>$hdmf_er,
                    'hdmf_desc'      =>'HDMF'
                    );


                $con->myQuery("INSERT INTO payroll_govde(payroll_code,employee_id,govde_code,govde_eeshare,govde_ershare,gov_desc) VALUES (:payroll_code,:employee_id,:hdmf_code,:hdmf_ee,:hdmf_er,:hdmf_desc)",$paramHDMF);

            }else{
                $hdmf_amount = '0.00';

            }

            // echo 'BAS'. ' - ' . $basic_salary_gov .'<br>' ;
            // echo 'SSS'. ' - ' . $sss_amount .'<br>' ;
            // echo 'PHI'. ' - ' . $ph_amount .'<br>' ;
            // echo 'PAG'. ' - ' . $hdmf_amount .'<br>' ;


            $total_government_deduction_amount = ($sss_amount + $ph_amount + $hdmf_amount);

            //------------------------------------------------------------------------------------

            //------------------------------------------------------------------------------------
            // OVERTIME

            $period = new DatePeriod($dateS,new DateInterval('P1W'),$dateE->modify("+1 day"));

            $dateE->modify("-1 day");
            $data=array();
            $index=0;

            $totalOvertime=0;
            $ordinary_ot_hrs=0;
            $ot_special_holiday=0;
            $ordinary_ot=0;
            $overtime_rate=0;
            $ot_legal_holiday=0;
            $total_ot=0;
            $wtf=0;
            $night_diff_ordinary_ot=0;
            foreach ($period as $key => $date) {

                $week_array=getStartAndEndDate($date->format("W"), $date->format("Y"));

                if ($week_array['week_start'] < $dateS->format("Y-m-d")) {
                    $week_array['week_start'] = $dateS->format("Y-m-d");
                }
                if ($week_array['week_end'] > $dateE->format("Y-m-d")) {
                    $week_array['week_end'] = $dateE->format("Y-m-d");

                }

                $week_array['week_start'] = new DateTime($week_array['week_start']);
                $week_array['week_end'] = new DateTime($week_array['week_end']);
                $data_per_week=getHours($week_array['week_start']->format("Y-m-d"), $week_array['week_end']->format("Y-m-d"), $employee_id);

                $work_hours = $data_per_week['work_hours']['hours'];
                $overtime   = $data_per_week['overtime']['hours'];
                $total_ot   = $work_hours + $overtime;


                if($total_ot > 48){
                //var_dump($total_ot);
                    $ordinary_ot_hrs = ($total_ot - 48);

                    $overtime_rate   = get_payroll_group_rates($pay_group_id)['o_ot_rate'];
                    $ordinary_ot     = ($overtime_rate * $hourlyrate) * $ordinary_ot_hrs;

                    if(!empty($data['overtime_special_holiday'])){
                        $ot_special_holiday1 = $data['overtime_special_holiday'];
                    }else{
                        $ot_special_holiday1 =0;
                    }

                    if(!empty($data['overtime_legal_holiday'])){
                        $ot_legal_holiday1 = $data['overtime_legal_holiday'];
                    }else{
                        $ot_legal_holiday1 = 0;
                    }

                    if(!empty($data['night_diff_ordinary_ot'])){
                        $night_diff_ordinary_ot1 = $data['night_diff_ordinary_ot'];
                    }else{
                        $night_diff_ordinary_ot1 = 0;
                    }

                    $ot_special_holiday = $ot_special_holiday1;
                    $ot_legal_holiday   = $ot_legal_holiday1;
                    $night_diff_ordinary_ot = $night_diff_ordinary_ot1;

                    $totalOvertime      += ($ordinary_ot + $ot_special_holiday + $ot_legal_holiday + $night_diff_ordinary_ot);
                }else{
                    $totalOvertime      += 0;;

                }

                $wtf++;

            }

            $for_insert[$x]['overtime']= $totalOvertime;


            $receivables_amount = get_receivablesdeduction($employee_id);
            if(!empty($receivables_amount)){
                $for_insert[$x]['receivable']=number_format($receivables_amount, 2, '.', '');
            }else{
                $for_insert[$x]['receivable']= '0.00';
            }

            $deminimis_amount = get_deminimis($employee_id);  
            if(!empty($deminimis_amount)){
                $for_insert[$x]['de_minimis']=number_format($deminimis_amount, 2, '.', '');
            }else{
                $for_insert[$x]['de_minimis']= '0.00';
            }

            $company_deduction_amount = get_company_deductions($employee_id);
            if(!empty($company_deduction_amount)){
                $for_insert[$x]['company_deduction']=number_format($company_deduction_amount, 2, '.', '');
            }else{
                $for_insert[$x]['company_deduction']= '0.00';
            }

            //------------------------------------------------------------------------------------
            // PAYROLL ADJUSTMENT
            $payroll_adjustments_amount_minus=0;
            $payroll_adjustments_amount_plus=0;
            $payroll_adjustments_type = get_payroll_adjustments($employee_id)['adjustment_type']; 


            if ($payroll_adjustments_type == 0) { //MINUS
                $payroll_adjustments_amount_minus = get_payroll_adjustments($employee_id)['amount'];
            }else{
                $payroll_adjustments_amount_minus = '0.00';
            }  
            if ($payroll_adjustments_type <> 0) { //PLUS    
                $payroll_adjustments_amount_plus = get_payroll_adjustments($employee_id)['amount'];
            }else{
                $payroll_adjustments_amount_plus = '0.00';
            }  


            //------------------------------------------------------------------------------------

            //------------------------------------------------------------------------------------
            // LEAVES
            $check_leaves_whole_withoutpay   = get_employees_leaves_wholeday_without_pay($employee_id,$date_from,$date_to); 
            $check_leaves_halfday_withoutpay = get_employees_leaves_halfday_without_pay($employee_id,$date_from,$date_to); 
            $check_leaves_whole_withpay      = get_employees_leaves_wholeday_with_pay($employee_id,$date_from,$date_to); 
            $check_leaves_halfday_withpay    = get_employees_leaves_halfday_with_pay($employee_id,$date_from,$date_to); 

            if(!empty($data['daily_rate'])){
                $daily_rate1 = $data['daily_rate'];
            }else{
                $daily_rate1 = '0.00';
            }

            $daily_rate = $daily_rate1;

            if ($check_leaves_whole_withoutpay > 0){
                $leave_without_pay = $daily_rate;
            }else{
                $leave_without_pay ='0.00';
            }

            if ($check_leaves_halfday_withoutpay > 0){
                $leave_without_pay = ($daily_rate / 2);
            }else{
                $leave_without_pay ='0.00';
            }

            if ($check_leaves_whole_withpay > 0){
                $leave_with_pay = ($daily_rate / 2);
            }else{
                $leave_with_pay ='0.00';
            }

            if ($check_leaves_halfday_withpay > 0){
                $leave_with_pay = ($daily_rate / 2);
            }else{
                $leave_with_pay ='0.00';
            }
            //------------------------------------------------------------------------------------

            //------------------------------------------------------------------------------------
            // OFF-SET
            $offset_hours  = get_employees_offset_no($employee_id,$date_from,$date_to)['no_hours'];
            $offset_amount = ($hourlyrate * $offset_hours);


            //var_dump($hourlyrate .'-'. $offset_hours);
            //------------------------------------------------------------------------------------

            //------------------------------------------------------------------------------------
            // OFFICIAL BUSINESS  
            $ob_time_from = new DateTime(get_employees_ob_data($employee_id,$date_from,$date_to)['time_from']);
            $ob_time_to   = new DateTime(get_employees_ob_data($employee_id,$date_from,$date_to)['time_to']);

            $ob_mins = $ob_time_from->diff($ob_time_to);
            $ob_mins->i;

            $ob_amount = ($hourlyrate * $ob_mins->i);
            //------------------------------------------------------------------------------------
            if(!empty($data['special_holiday'])){
                $special_holiday = $data['special_holiday'];
            }else{
             $special_holiday = '0.00';
         }

         if(!empty($data['legal_holiday'])){
            $legal_holiday = $data['legal_holiday'];
        }else{
         $legal_holiday = '0.00';
     }

     if(!empty($data['rest_day'])){
        $rest_day = $data['rest_day'];
    }else{
        $rest_day = '0.00';
    }

    if(!empty($data['rest_day_special_holiday'])){
        $rest_day_special_holiday = $data['rest_day_special_holiday'];
    }else{
        $rest_day_special_holiday = '0.00';
    }

    if(!empty($data['rest_day_legal_holiday'])){
        $rest_day_legal_holiday = $data['rest_day_legal_holiday'];
    }else{
        $rest_day_legal_holiday = '0.00';
    }

    if(!empty($data['ordinary_day_night_shift'])){
        $ordinary_day_night_shift = $data['ordinary_day_night_shift'];
    }else{
        $ordinary_day_night_shift = '0.00';
    }

    if(!empty($data['rest_day_night_shift'])){
        $rest_day_night_shift = $data['rest_day_night_shift'];
    }else{
        $rest_day_night_shift = '0.00';
    }

    if(!empty($data['special_holiday_night_shift'])){
        $special_holiday_night_shift = $data['special_holiday_night_shift'];
    }else{
        $special_holiday_night_shift = '0.00';
    }

    if(!empty($data['legal_holiday_night_shift'])){
        $legal_holiday_night_shift = $data['legal_holiday_night_shift'];
    }else{
        $legal_holiday_night_shift = '0.00';
    }

    if(!empty($data['special_holiday_rest_day_night_shift'])){
        $special_holiday_rest_day_night_shift = $data['special_holiday_rest_day_night_shift'];
    }else{
        $special_holiday_rest_day_night_shift = '0.00';
    }

    if(!empty($data['legal_holiday_rest_day_night_shift'])){
        $legal_holiday_rest_day_night_shift = $data['legal_holiday_rest_day_night_shift'];
    }else{
        $legal_holiday_rest_day_night_shift = '0.00';
    }

    if(!empty($data['night_diff_restday_ot'])){
        $night_diff_restday_ot = $data['night_diff_restday_ot'];
    }else{
        $night_diff_restday_ot = '0.00';
    }

            //$rest_day                             = $data['rest_day']; 
            //$rest_day_special_holiday             = $data['rest_day_special_holiday'];
            //$rest_day_legal_holiday               = $data['rest_day_legal_holiday'];
            //$ordinary_day_night_shift             = $data['ordinary_day_night_shift'];
            //$rest_day_night_shift                 = $data['rest_day_night_shift'];
            //$special_holiday_night_shift          = $data['special_holiday_night_shift'];
            //$legal_holiday_night_shift            = $data['legal_holiday_night_shift'];
            //$special_holiday_rest_day_night_shift = $data['special_holiday_rest_day_night_shift'];
           // $legal_holiday_rest_day_night_shift   = $data['legal_holiday_rest_day_night_shift'];

    $basic_salary_with_deductions = ($basic_salary - ($total_government_deduction_amount + $late + $payroll_adjustments_amount_minus + $leave_without_pay + $absent_amount + $company_deduction_amount));

    $addto = ($totalOvertime + $payroll_adjustments_amount_plus + $leave_with_pay + $ob_amount + $offset_amount +$special_holiday + $legal_holiday + $rest_day + $night_diff_restday_ot + $rest_day_special_holiday + $rest_day_legal_holiday + $ordinary_day_night_shift + $rest_day_night_shift + $special_holiday_night_shift + $legal_holiday_night_shift + $special_holiday_rest_day_night_shift + $legal_holiday_rest_day_night_shift + $deminimis_amount + $receivables_amount);

    $for_insert[$x]['government_deduction'] = number_format($total_government_deduction_amount, 2, '.', '');

    $tax_earning    = ($basic_salary_with_deductions + $addto);


    $for_insert[$x]['tax_earning'] = number_format($tax_earning, 2, '.', '');


    $tax_comp = $taxex;
    $tax_rate       = compute_tax($tax_earning,$tax_comp)['tax_rate'];
    $tax_additional = compute_tax($tax_earning,$tax_comp)['tax_additional'];
    $tax_ceiling    = compute_tax($tax_earning,$tax_comp)['tax_ceiling'];

            // echo 'te'. ' - '. $tax_earning . '<br>';
            // echo 'tc'. ' - '. $tax_comp . '<br>';


            // echo 'tr'. ' - '. $tax_rate . '<br>';
            // echo 'ta'. ' - '. $tax_additional . '<br>';
            // echo 'txc'. ' - '. $tax_ceiling . '<br>';


    $tax = ($tax_additional + (($tax_earning - $tax_ceiling) * $tax_rate));
    $for_insert[$x]['withholding_tax'] = number_format($tax, 2, '.', '');

            // var_dump( $tax);

    $total_deduction = ($total_government_deduction_amount + $company_deduction_amount +  $absent_amount + $tax);
    $for_insert[$x]['total_deduction'] = number_format($total_deduction, 2, '.', '');

            // if ($payroll_adjustments_type == 0) { //MINUS
            //     $for_insert[$x]['payroll_adjustment_m'] = $payroll_adjustments_amount_minus;
            // }else{
            //     $for_insert[$x]['payroll_adjustment_m'] = '0.00';
            // }  
            // if ($payroll_adjustments_type <> 0) { //PLUS    
            //     $for_insert[$x]['payroll_adjustment_p'] = $payroll_adjustments_amount_plus;
            // }else{
            //     $for_insert[$x]['payroll_adjustment_p'] = '0.00';
            // }  

    $for_insert[$x]['payroll_adjustment_m'] = $payroll_adjustments_amount_minus;
    $for_insert[$x]['payroll_adjustment_p'] = $payroll_adjustments_amount_plus;


    $for_insert[$x]['payroll_year'] = date("Y");

    $thirteen_month = (($basic_salary - $late) / 12);

    $for_insert[$x]['thirteen_month'] = number_format($thirteen_month, 2, '.', '');



            //------------------------------------------------------------------------------------
            // LOANS
    $check_loans = check_loans($employee_id);

    if($check_loans > 0){
                //WITH LOAN
        $check_loan_pass = check_loan_pass($employee_id,$date_from,$date_to);

                //Check kung may loan pass

        if ($check_loan_pass > 0 ){
            $loan_amount = '0.00';
        }else{
            $emp_loan_id            = get_loan_details($employee_id)['emp_loan_id'];
            $loan_cut_off_no        = get_loan_details($employee_id)['cut_off_no'];
            $loan_emp_amount        = get_loan_details($employee_id)['loan_amount'];
            $loan_balance           = get_loan_details($employee_id)['balance'];
            $loan_remaining_cut_off = get_loan_details($employee_id)['remaining_cut_off_no'];

                    //check kung may remaining cut off
            if(!empty($loan_remaining_cut_off))
            {
                $loan_amount = ($loan_balance / $loan_remaining_cut_off);

                $loan_new_cut_off_no         = ($loan_remaining_cut_off - 1);
                $loan_new_remaining_balance = ($loan_balance - $loan_amount);

                if($loan_new_cut_off_no == 0 && $loan_new_remaining_balance == 0){
                    $con->myQuery("UPDATE emp_loans SET status_id=2,balance = {$loan_new_remaining_balance}, remaining_cut_off_no = {$loan_new_cut_off_no} WHERE employee_id=?",array($employee_id));
                }else{
                    $con->myQuery("UPDATE emp_loans SET balance = {$loan_new_remaining_balance}, remaining_cut_off_no = {$loan_new_cut_off_no} WHERE employee_id = ?",array($employee_id));
                }
            }else
            {
                $loan_amount = ($loan_emp_amount / $loan_cut_off_no);

                $loan_new_cut_off_no         = ($loan_cut_off_no - 1);
                $loan_new_remaining_balance = ($loan_emp_amount - $loan_amount);

                $con->myQuery("UPDATE emp_loans SET balance = {$loan_new_remaining_balance}, remaining_cut_off_no = {$loan_new_cut_off_no} WHERE employee_id = ?",array($employee_id));

            }
            $params3=array(
                'emp_loan_id1'=>$emp_loan_id,
                'loan_amount1'=>$loan_amount
                );

            $con->myQuery("INSERT INTO emp_loans_det(emp_loan_id,amount_paid,date_deducted) VALUES (:emp_loan_id1,:loan_amount1,CURDATE())",$params3);  
        }

    }else{
                //WALANG LOAN
        $loan_amount = '0.00';
    }
            //------------------------------------------------------------------------------------
    $net_pay = ($tax_earning - $tax);
    $for_insert[$x]['net_pay'] = number_format(($net_pay - $loan_amount), 2, '.', '');


    $for_insert[$x]['loan_amount'] =  number_format($loan_amount, 2, '.', '');

    $x++;


}

        //die;

for($i = 1; $i < $x; $i++)
{
    $con->myQuery("INSERT INTO payroll_details(
        payroll_id,
        payroll_code,
        employee_id,
        tax_compensation,
        basic_salary,
        late,
        absent,
        overtime,
        receivable,
        de_minimis,
        company_deduction,
        government_deduction,
        tax_earning,
        withholding_tax,
        total_deduction,
        payroll_adjustment_minus,
        payroll_adjustment_plus,
        payroll_year,
        13th_month,
        done_13th_month,
        net_pay,
        loan
        )VALUES(
        :payroll_id,
        :payroll_code,
        :employee_id,
        :tax_compensation,
        :basic_salary,
        :late,
        :absent,
        :overtime,
        :receivable,
        :de_minimis,
        :company_deduction,
        :government_deduction,
        :tax_earning,
        :withholding_tax,
        :total_deduction,
        :payroll_adjustment_m,
        :payroll_adjustment_p,
        :payroll_year,
        :thirteen_month,
        '0',
        :net_pay,
        :loan_amount
        )",$for_insert[$i]);

    $params_pa = array(
        'payroll_code'   => $get_payroll_code['payroll_code'],
        'status'         => '1'
                // 'employee_id'    => $for_insert[$i]['employee_id'],
                // 'date_from'      => $date_from,
                // 'date_to'        => $date_to
        );

            //$con->myQuery("UPDATE payroll_adjustments SET payroll_code=:payroll_code, status=:status WHERE employee_id=:employee_id AND date_occur BETWEEN :date_from AND :date_to",$params_pa);

    $con->myQuery("UPDATE payroll_adjustments SET payroll_code=:payroll_code, status=:status WHERE status='0'",$params_pa);


}


$date = new DateTime();
$date_process=date_format($date, 'Y-m-d');

$con->myQuery("UPDATE payroll SET is_processed=1,date_process = ? WHERE id=?",array($date_process,$payroll_id));

$con->commit();

Alert("Payroll sucessfully processed!","success");
redirect("frm_generate_payroll.php?id=".$payroll_id);
die();
}

}else{
    $con->rollback();
}
?>