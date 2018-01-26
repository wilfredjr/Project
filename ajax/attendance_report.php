<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
         toLogin();
         die();
}

error_reporting(E_ALL);
$employees=array();
$employees_query="SELECT employees.id,code,CONCAT(last_name,', ',first_name,' ',middle_name) as employee, employees.payroll_group_id,payroll_groups.name,departments.name as payroll_group FROM employees JOIN payroll_groups ON employees.payroll_group_id=payroll_groups.payroll_group_id JOIN departments ON employees.department_id=departments.id WHERE employees.is_deleted=0 AND is_terminated=0 ";
$employee_inputs=array();

if(!empty($_GET['payroll_group_id']) && is_numeric($_GET['payroll_group_id'])){
    $employee_inputs['payroll_group_id']=$_GET['payroll_group_id'];
    $employees_query.=" AND payroll_groups.payroll_group_id=:payroll_group_id";
}

if (!empty($_GET['employees_id'])) {
    if ($_GET['employees_id']=='NULL' && AllowUser(array(1,4))) {
        $employees=$con->myQuery("$employees_query")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        if (AllowUser(array(1,4))) {
            if (is_numeric($_GET['employees_id'])) {
                $employee_inputs['employee_id']=$_GET['employees_id'];
                $employees=$con->myQuery("$employees_query AND employees.id=:employee_id", $employee_inputs)->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            $employee_inputs['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            $employees=$con->myQuery("$employees_query AND id=:employee_id", $employee_inputs)->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} elseif (!empty($_GET['department_id'])) {
    if (AllowUser(array(1,4))) {
        if (is_numeric($_GET['department_id'])) {
            $employee_inputs['department_id']=$_GET['department_id'];
            $employees=$con->myQuery("$employees_query AND department_id=:department_id", $employee_inputs)->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $employee_inputs['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            $employees=$con->myQuery("$employees_query AND employees.id=:employee_id", $employee_inputs)->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $employee_inputs['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
        $employees=$con->myQuery("$employees_query AND employees.id=:employee_id", $employee_inputs)->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    if (AllowUser(array(1,4))) {
        $employees=$con->myQuery("$employees_query", $employee_inputs)->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $employee_inputs['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
        $employees=$con->myQuery("$employees_query AND employees.id=:employee_id", $employee_inputs)->fetchAll(PDO::FETCH_ASSOC);
    }
}
function getDefaultShift_bu($employee, $date)
{
    global $con;
    $default_shift=$con->myQuery("SELECT id, employee_id, time_in, time_out, beginning_in, beginning_out, ending_in, ending_out, start_date, end_date, break_one_start, break_one_end, break_two_start, break_two_end, break_three_start, break_three_end, working_days FROM employees_default_shifts WHERE employee_id=:employee_id AND IF(:selected_date BETWEEN start_date AND end_date, :selected_date BETWEEN start_date AND end_date, :selected_date>=start_date AND ISNULL(end_date)) LIMIT 1", array("employee_id"=>$employee, "selected_date"=>$date))->fetch(PDO::FETCH_ASSOC);

    return $default_shift;
}
function getLateUndertime($time_in, $time_out)
{
    $late_undertime="";
    $date_time_in=new DateTime($time_in);
    $date_time_out= new DateTime($time_out);
    $interval=$date_time_in->diff($date_time_out);
    if ($interval->h < 9 && $interval->d==0 ) {
        // var_dump($index, $date_time_in, $date_time_out, $interval);
        if ($interval->h < 8) {
            $late_undertime.=(8-$interval->h)." Hour/s ";
        }
        if ($interval->i > 0) {
            $late_undertime.=(60-$interval->i)." Minute/s ";
        }
    }
    return $late_undertime;
}
//$limit=$_GET['length'];
try {
    $date_start=new DateTime($_GET['date_from']);
    $date_end=new DateTime($_GET['date_to']);
    $period = new DatePeriod(
        $date_start,
        new DateInterval('P1D'),
        $date_end->modify("+1 day")
    );

    $data=array();
    $index=count($data);


    foreach ($employees as $employee) {
        $use_ot=array();
        foreach ($period as $key => $date) {
            $late="";
            $data[$index]['code']=$employee['code'];
            $data[$index]['payroll_group']=$employee['payroll_group'];
            $data[$index]['employee']=$employee['employee'];
            $data[$index]['department_name']=$employee['name'];
            $data[$index]['ot']=0;
            $data[$index]['date']=$date->format(DATE_FORMAT_PHP);
            $next_day=new DateTime($date->format("Y-m-d"));
            $next_day->modify("+1 day");
            $weekday=$date->format("w");


            /*
            Get Shifts To check for time in
             */
            /*
            Get approved change shifts for the date
             */
            /*
            Get Latest applied shift of the employee by the HR
             */
            /*
            Get active default shits
             */
            // $default_shift=getDefaultShift($employee['id'], $date->format("Y-m-d"));

            $shift=getShift($employee['id'], $date->format("Y-m-d"));

            /*
            Get if date is holiday based on employee payroll group
             */
            $holiday=getHolidayOfDay($date->format("Y-m-d"), $employee['id']);
            if (!empty($holiday)) {
                $data[$index]['status']=$holiday['holiday_name'];
            } else {
                $in_working_days=false;
                switch ($weekday) {
                    case '0':
                        /*
                        Sunday
                         */
                        $in_working_days=in_array("SU", explode(",", $shift['working_days']));
                        break;
                    case '1':
                        /*
                        Monday
                         */
                        $in_working_days=in_array("M", explode(",", $shift['working_days']));
                        break;
                    case '2':
                        /*
                        Tuesday
                         */
                        $in_working_days=in_array("T", explode(",", $shift['working_days']));
                        break;
                    case '3':
                        /*
                        Wednesday
                         */
                        $in_working_days=in_array("W", explode(",", $shift['working_days']));
                        break;
                    case '4':
                        /*
                        Thursday
                         */
                        $in_working_days=in_array("TH", explode(",", $shift['working_days']));
                        break;
                    case '5':
                        /*
                        Friday
                         */
                        $in_working_days=in_array("F", explode(",", $shift['working_days']));
                        break;
                    case '6':
                        /*
                        Saturday
                         */
                        $in_working_days=in_array("SA", explode(",", $shift['working_days']));
                        break;
                }
                if ($in_working_days) {
                    $data[$index]['status']='Working Day';
                } else {
                    $data[$index]['status']='Rest Day';
                }
            }

            $time_inputs=array(
                "employee_id"=>$employee['id'],
                "date_filter"=>$date->format("Y-m-d"),
                "shift_in"=>$shift['beginning_in'],
                "shift_out"=>$shift['ending_out']
                );
            $data[$index]['shift_start']=$shift['time_in'];
            $data[$index]['shift_end']=$shift['time_out'];

            $time_in_query="SELECT DATE_FORMAT(in_time,'".DATE_FORMAT_SQL." %H:%i:%s') as in_time,DATE_FORMAT(out_time,'".DATE_FORMAT_SQL." %H:%i:%s') as out_time,id,note FROM `attendance` WHERE employees_id=:employee_id ";
            $time_in_sql="";
            $time_out_sql="";

            // var_dump($date->format("Y-m-d"));

            // if ($shift['beginning_in']> $shift['ending_out']) {
            //     /*
            //     Time in exceeds a day. ex: 20:00 - 05:00
            //      */
            //     $time_inputs['next_day']=$next_day->format('Y-m-d');
            //     $time_in_query.=" AND DATE(in_time) BETWEEN :date_filter AND :next_day";
            //     $time_in_sql.=" AND CAST(in_time as time) NOT BETWEEN :shift_out AND :shift_in ";
            //     // $time_out_sql.=" AND CAST(out_time as time) NOT BETWEEN :shift_out AND :shift_in ";
            // } else {
            //     $time_in_query.=" AND DATE(in_time)=:date_filter ";
            //     $time_in_sql.=" AND CAST(in_time as time) BETWEEN :shift_in AND :shift_out ";
            //     // $time_out_sql.=" AND CAST(out_time as time) BETWEEN :shift_in AND :shift_out ";
            // }

            // $time_ins=$con->myQuery($time_in_query.$time_in_sql." ORDER BY in_time ASC LIMIT 1", $time_inputs)->fetch(PDO::FETCH_ASSOC);
            // unset($time_inputs['shift_in']);
            // unset($time_inputs['shift_out']);

            // $time_outs=$con->myQuery($time_in_query.$time_out_sql." ORDER BY out_time DESC LIMIT 1", $time_inputs)->fetch(PDO::FETCH_ASSOC);
            $time_in_and_out=getTimeInAndOut($employee['id'], $date->format(DATE_FORMAT_PHP));
            // echo "<pre>";
            // print_r($time_in_and_out);
            // echo "</pre>";
            
            $time_ins['in_time'] = !empty($time_in_and_out['in_time'])?$time_in_and_out['in_time']:'';
            $time_outs['out_time'] = !empty($time_in_and_out['out_time'])?$time_in_and_out['out_time']:'';
            $data[$index]['in_time']='';
            $data[$index]['out_time']='';
            // var_dump($time_ins, $time_outs);
            if (!empty($time_ins)) {
                $flexi_time=getFlexiTimeInAndOut ($time_ins['in_time'], $time_outs['out_time'], $shift);
                $data[$index]['in_time']=!empty($flexi_time['time_in'])?$flexi_time['time_in']:'';
                $data[$index]['out_time']=!empty($flexi_time['time_out'])?$flexi_time['time_out']:'';
                
                // if ($shift['beginning_in']> $shift['ending_out']) {
                //     /*
                //     Shifts covers two days
                //      */
                //     $in_time=new DateTime($time_ins['in_time']);
                //     $out_time=new DateTime($time_outs['out_time']);

                //     if ($in_time->format("Y-m-d") > $date->format("Y-m-d")) {
                //     /*
                //     in time greater than the current date and time is less than beginning in
                //      */
                //     } else {
                //         if ($in_time->format("H:i:s") >= $shift['beginning_in']) {
                //             $data[$index]['in_time']=!empty($time_ins['in_time'])?$time_ins['in_time']:'';
                //             $data[$index]['out_time']=!empty($time_outs['out_time']) && $time_outs['out_time']<>"0000-00-00 00:00:00"?$time_outs['out_time']:'';
                //         }
                //     }
                // } else {
                //     $data[$index]['in_time']=!empty($time_ins['in_time'])?$time_ins['in_time']:'';
                //     $data[$index]['out_time']=!empty($time_outs['out_time']) && $time_outs['out_time']<>"0000-00-00 00:00:00"?$time_outs['out_time']:'';
                // }
            }
    
            $break_inputs['employee_id']=$employee['id'];
            $break_query="";

            if (($shift['break_one_start']!="00:00:00" && !empty($shift['break_one_start'])) || ($shift['break_one_end']!="00:00:00" && !empty($shift['break_one_end']))) {
                /*
                has break
                 */
                $break_query[]="(SELECT out_time FROM attendance
                WHERE
                employees_id=:employee_id
                AND (
                out_time < :break_one_start
                AND DATE(out_time) = DATE(:break_one_start)
                )
                ORDER BY in_time DESC LIMIT 1) AS b1_early_out,
                (
                SELECT in_time FROM attendance
                WHERE
                employees_id=:employee_id
                AND (
                in_time> :break_one_end
                AND DATE(in_time) = DATE(:break_one_end)
                )
                ORDER BY in_time DESC LIMIT 1) AS b1_late_in";
                if ($shift['break_one_start'] > $shift['break_one_end']) {
                    /*
                    Greater than a day
                     */
                    $break_inputs['break_one_start']=$date->format("Y-m-d")." ".$shift['break_one_start'];
                    $break_inputs['break_one_end']=$next_day->format("Y-m-d")." ".$shift['break_one_end'];
                } else {
                    $break_inputs['break_one_start']=$date->format("Y-m-d")." ".$shift['break_one_start'];
                    $break_inputs['break_one_end']=$date->format("Y-m-d")." ".$shift['break_one_end'];
                }
            }

            if ((!empty($shift['break_two_start']) && $shift['break_two_start']!="00:00:00") || (!empty($shift['break_two_start']) && $shift['break_two_end']!="00:00:00")) {
                /*
                has break
                 */

                $break_query[]=" (SELECT out_time FROM attendance
                WHERE
                employees_id=:employee_id
                AND (
                out_time < :break_two_start
                AND DATE(out_time) = DATE(:break_two_start)
                )
                ORDER BY in_time DESC LIMIT 1) AS b2_early_out,
                (
                SELECT in_time FROM attendance
                WHERE
                employees_id=:employee_id
                AND (
                in_time> :break_two_end
                AND DATE(in_time) = DATE(:break_two_end)
                )
                ORDER BY in_time DESC LIMIT 1) AS b2_late_in";
                if ($shift['break_two_start'] > $shift['break_two_end']) {
                    /*
                    Greater than a day
                     */
                    $break_inputs['break_two_start']=$date->format("Y-m-d")." ".$shift['break_two_start'];
                    $break_inputs['break_two_end']=$next_day->format("Y-m-d")." ".$shift['break_two_end'];
                } else {
                    $break_inputs['break_two_start']=$date->format("Y-m-d")." ".$shift['break_two_start'];
                    $break_inputs['break_two_end']=$date->format("Y-m-d")." ".$shift['break_two_end'];
                }
            }

            if ((!empty($shift['break_three_start']) && $shift['break_three_start']!="00:00:00") || (!empty($shift['break_three_end']) && $shift['break_three_end']!="00:00:00")) {
                /*
                has break
                 */
                $break_query[]="(SELECT out_time FROM attendance
                WHERE
                employees_id=:employee_id
                AND (
                out_time < :break_three_start
                AND DATE(out_time) = DATE(:break_three_start)
                )
                ORDER BY in_time DESC LIMIT 1) AS b3_early_out,
                (
                SELECT in_time FROM attendance
                WHERE
                employees_id=:employee_id
                AND (
                in_time> :break_three_end
                AND DATE(in_time) = DATE(:break_three_end)
                )
                ORDER BY in_time DESC LIMIT 1) AS b3_late_in";
                if ($shift['break_three_start'] > $shift['break_three_end']) {
                    /*
                    Greater than a day
                     */
                    $break_inputs['break_three_start']=$date->format("Y-m-d")." ".$shift['break_three_start'];
                    $break_inputs['break_three_end']=$next_day->format("Y-m-d")." ".$shift['break_three_end'];
                } else {
                    $break_inputs['break_three_start']=$date->format("Y-m-d")." ".$shift['break_three_start'];
                    $break_inputs['break_three_end']=$date->format("Y-m-d")." ".$shift['break_three_end'];
                }
            }
            $break_excess=array('h'=>0, 'i'=>0);
            if (!empty($break_query)) {

                $breaks=$con->myQuery("SELECT ". implode(",", $break_query), $break_inputs)->fetch(PDO::FETCH_ASSOC);
                if (!empty($breaks)) {
                    $break_loop=array(
                        "b1_early_out"=>"break_one_start",
                        "b1_late_in"=>"break_one_end",
                        "b2_early_out"=>"break_two_start",
                        "b2_late_in"=>"break_two_end",
                        "b3_early_out"=>"break_three_start",
                        "b3_late_in"=>"break_three_end"
                        );
                    // var_dump($breaks);
                    foreach ($break_loop as $break_key => $break_value) {
                        if(isset($breaks[$break_key]) && !empty($breaks[$break_key]) && $breaks[$break_key]!="0000-00-00 00:00:00") {
                            $difference=date_diff(date_create($break_inputs[$break_value]), date_create($breaks[$break_key]));
                            // var_dump($break_inputs[$break_value], $breaks[$break_key]);
                            $break_excess['h']+=$difference->h;
                            $break_excess['i']+=$difference->i;
                        }
                    }
                }
            }
            $data[$index]['break_excess']="";
            if (!empty($break_excess['h']) || !empty($break_excess['i'])) {
                if ($break_excess['i']>=60 ){
                    /*
                    add to hours and deduct from minutes
                     */
                    $additional_hours=floor($break_excess['i']/60);
                    $break_excess['h']+=$additional_hours;

                    $break_excess['i']-=$additional_hours*60;
                }

                if (!empty($break_excess['h'])) {
                    $data[$index]['break_excess'].=$break_excess['h']." hour/s ";
                }

                if (!empty($break_excess['i'])) {
                    $data[$index]['break_excess'].=$break_excess['i']." minute/s ";
                }
            }
            $data[$index]['note']=(!empty($time_ins['note'])?"Time in: ".$time_ins['note']:''). (!empty($time_outs['note'])?" Time out: ".$time_outs['note']:'');

            $ob_date=$con->myQuery("SELECT DATE_FORMAT(ob_date,'".DATE_FORMAT_SQL."') as ob_date,time_from,time_to FROM employees_ob WHERE employees_id=? AND ob_date=? AND request_status_id=2 ORDER BY time_from ASC", array($employee['id'],$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);
            // echo $date->format("Y-m-d")."<br>";
            // var_dump($time_ins['in_time']);
            // var_dump($ob_date);
            // echo "<br>";
            if (!empty($ob_date)) {
                if (date_format(date_create($time_ins['in_time']), DATE_FORMAT_PHP) <> date_format(date_create($ob_date['ob_date']), DATE_FORMAT_PHP)) {
                    $data[$index]['in_time']=$ob_date['ob_date'].' '.$ob_date['time_from'];
                    $data[$index]['out_time']=$ob_date['ob_date'].' '.$ob_date['time_to'];
                } else {
                    if ($time_outs['out_time'] < date_format(date_create($ob_date['time_to']), $ob_date['ob_date'].' H:i:s')) {
                        $data[$index]['out_time']=$ob_date['ob_date'].' '.$ob_date['time_to'];
                    }
                    if ($time_ins['in_time'] > date_format(date_create($ob_date['time_from']), $ob_date['ob_date'].' H:i:s')) {
                        $data[$index]['in_time']=$ob_date['ob_date'].' '.$ob_date['time_from'];
                    }
                }
            }


            $offset_date=$con->myQuery(
                "SELECT
                start_datetime,end_datetime
                FROM employees_offset_request
                WHERE employees_id=?
                AND DATE(start_datetime)=?
                AND request_type_id=2
                AND request_status_id=2",
                array($employee['id'],$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);

            if (!empty($offset_date)) {
                if (date_format(date_create($time_ins['in_time']), DATE_FORMAT_PHP) <> date_format(date_create($offset_date['start_datetime']), DATE_FORMAT_PHP)) {
                    $data[$index]['in_time']=date_format(date_create($offset_date['start_datetime']), DATE_FORMAT_PHP.' H:i:s');
                    $data[$index]['out_time']=date_format(date_create($offset_date['end_datetime']), DATE_FORMAT_PHP.' H:i:s');
                } else {
                    if ($time_outs['out_time'] < date_format(date_create($offset_date['end_datetime']), DATE_FORMAT_PHP.' H:i:s')) {
                        $data[$index]['out_time']=date_format(date_create($offset_date['end_datetime']), DATE_FORMAT_PHP.' H:i:s');
                    }
                    if ($time_ins['in_time'] > date_format(date_create($offset_date['start_datetime']), $offset_date['start_datetime'].' H:i:s')) {
                        $data[$index]['in_time']=date_format(date_create($offset_date['start_datetime']), DATE_FORMAT_PHP.' H:i:s' );
                    }
                }
            }

            $leaves=$con->myQuery("SELECT id,remark,comment FROM `employees_leaves` WHERE employee_id=? AND ? BETWEEN date_start AND date_end AND request_status_id=2", array($employee['id'],$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);
            if (!empty($leaves)) {
                $data[$index]['status']=$leaves['remark']=="L"?"Leave":"Leave Without Pay";

                if ($leaves['comment']=="AM" || $leaves['comment']=="PM") {
                    $late=getLate($data[$index]['in_time'], $shift, $leaves['comment']);
                    $undertime=getUndertime($data[$index]['out_time'],$shift, $leaves['comment']);

                } else {
                    $late=getLate($data[$index]['in_time'], $shift);
                    $undertime=getUndertime($data[$index]['out_time'],$shift, $leaves['comment']);

                }
            } else {
                $late=getLate($data[$index]['in_time'], $shift);
                                $undertime=getUndertime($data[$index]['out_time'],$shift, $leaves['comment']);

            }

            $data[$index]['lates']=$late;
            $data[$index]['undertime']=$undertime;
            $data[$index]['late_undertime']="";
            // if (!empty($data[$index]['out_time']) && !empty($data[$index]['in_time'])) {

            //     $out_time=new DateTime($data[$index]['out_time']);
            //     $in_time=new DateTime($data[$index]['in_time']);
            //     // var_dump($out_time->format("Y-m-d H:i:s"), $in_time->format("Y-m-d")." ".$shift['time_out']);
            //     if ($out_time->format("Y-m-d H:i:s") > $in_time->format("Y-m-d")." ".$shift['time_out'] && !empty($in_time)) {
            //         $data[$index]['out_time']=$in_time->format(DATE_FORMAT_PHP)." ".$shift['time_out'];
            //     }
            //     // unset($out_time);
            //     // unset($in_time);
            // }
            // echo "<pre>";
            //     print_r($data[$index]['out_time']);
            //     echo "</pre>";
            $data[$index]['hours_worked']=getHoursWorked($data[$index]['in_time'], $data[$index]['out_time'], $shift);
            if (!empty($data[$index]['in_time']) && !empty($data[$index]['out_time'])) {
                // $date_time_in=new DateTime($data[$index]['in_time']);
                // $date_time_out= new DateTime($data[$index]['out_time']);
                // $interval=$date_time_in->diff($date_time_out);
                // if ($interval->h < 9 && $interval->d==0 ) {
                //     // var_dump($index, $date_time_in, $date_time_out, $interval);
                //     if ($interval->h < 8) {
                //         $data[$index]['late_undertime'].=(8-$interval->h)." Hour/s ";
                //     }
                //     if ($interval->i > 0) {
                //         $data[$index]['late_undertime'].=(60-$interval->i)." Minute/s ";
                //     }
                // }
                $data[$index]['late_undertime']=getLateUndertime($data[$index]['in_time'], $data[$index]['out_time']);
            }

            $ots=$con->myQuery("SELECT id,no_hours FROM employees_ot WHERE employees_id=? AND date(ot_date)=? AND request_status_id=2".(!empty($use_ot)?" AND id NOT IN (".implode(",", $use_ot) .")":''), array($employee['id'],$date->format("Y-m-d")))->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ots as $key => $ot) {
                $data[$index]['ot']+=$ot['no_hours'];
                $use_ot[]=$ot['id'];
            }

            $obs=$con->myQuery("SELECT COUNT(id) FROM employees_ob WHERE employees_id=? AND date(ob_date)=?  AND request_status_id=2", array($employee['id'],$date->format("Y-m-d")))->fetchColumn();
            if (!empty($obs)) {
                $data[$index]['status']='Official Business';
            }


            /*
            Data formatting
            */
              $data[$index]['shift_start']=date_format(date_create($data[$index]['shift_start']),TIME_FORMAT_PHP);
              $data[$index]['shift_end']=date_format(date_create($data[$index]['shift_end']),TIME_FORMAT_PHP);
              if ($data[$index]['time_in']=""){
                $data[$index]['time_in']=="";
              }
              else{
              $data[$index]['time_in']=date_format(date_create($data[$index]['time_in']),TIME_FORMAT_PHP);}
              if ($data[$index]['time_out']=""){
                $data[$index]['time_out']=="";
              }
              else{
              $data[$index]['time_out']=date_format(date_create($data[$index]['time_in']),TIME_FORMAT_PHP);}
            //echo $date->format("Y-m-d");
            $index++;
        }
    }
} catch (Exception $e) {
    //echo $e;
    $data=array();
}
echo json_encode($data);
die;
