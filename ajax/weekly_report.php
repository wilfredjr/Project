<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
         toLogin();
         die();
}

$employees=array();
$employees_query="SELECT employees.id,code,CONCAT(last_name,', ',first_name,' ',middle_name) as employee,departments.name, employees.payroll_group_id,payroll_groups.name as payroll_group FROM employees JOIN departments ON employees.department_id=departments.id JOIN payroll_groups ON employees.payroll_group_id=payroll_groups.payroll_group_id WHERE employees.is_deleted=0 AND is_terminated=0";
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
        new DateInterval('P1W'),
        $date_end->modify("+1 day")
    );

    $date_end->modify("-1 day");
    $data=array();
    $index=0;
     // var_dump($employees);
     //        die;
    foreach ($employees as $employee) {

        foreach ($period as $key => $date) {
            $week_array=getStartAndEndDate($date->format("W"), $date->format("Y"));


            if ($week_array['week_start'] < $date_start->format("Y-m-d")) {
                $week_array['week_start'] = $date_start->format("Y-m-d");
            }
            if ($week_array['week_end'] > $date_end->format("Y-m-d")) {
                $week_array['week_end'] = $date_end->format("Y-m-d");
            }

            $week_array['week_start'] = new DateTime($week_array['week_start']);
            $week_array['week_end'] = new DateTime($week_array['week_end']);
            $data_per_week=getHours($week_array['week_start']->format("Y-m-d"), $week_array['week_end']->format("Y-m-d"), $employee['id']);
            // var_dump($data_per_week);
            $overtime=0;
            $extended_hours=0;
            $work_hours=$data_per_week['work_hours']['hours']+$data_per_week['overtime']['hours'];

            if ($work_hours > 48) {
                $overtime=$work_hours-48;
            }
            if ($work_hours > 40) {
                $extended_hours=$work_hours-40>8?8:$work_hours-40;
            }
            $data[]=array(
                "code"=>$employee['code'],
                "employee"=>$employee['employee'],
                "payroll_group"=>$employee['payroll_group'],
                "week"=>$week_array['week_start']->format(DATE_FORMAT_PHP). " - ". $week_array['week_end']->format(DATE_FORMAT_PHP),
                "department_name"=>$employee['name'],
                "overtime"=>number_format($overtime,2),
                "extended_hours"=>number_format($extended_hours,2),
                "worked_hours"=>number_format($data_per_week['work_hours']['hours']>40?40:$data_per_week['work_hours']['hours'],2),

                "late"=>number_format($data_per_week['late_array']['hours'],2),
                );
            // $index++;
            // var_dump($week_array["week_end"]);
            // die;
        }
    }
    echo json_encode($data);
    die;
} catch (Exception $e) {
    $data=array();
}

echo json_encode($data);
die;
