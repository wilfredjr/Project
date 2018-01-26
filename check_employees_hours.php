<?php
require_once("./support/config.php");
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
function getShift_bu($employee, $date)
{
    global $con;
    /*
    Hierarchy is based on date applied/ approved
     */
    $shift=$con->myQuery(
        "SELECT
        s.time_in,
        s.time_out,
        s.beginning_time_in AS beginning_in,
        s.beginning_time_out AS beginning_out,
        s.ending_time_in AS ending_in,
        s.ending_time_out AS ending_out,
        s.break_one_start,
        s.break_one_end,
        s.break_two_start,
        s.break_two_end,
        s.break_three_start,
        s.break_three_end,
        esm.date_from,
        esm.date_to,
        esm.date_applied,
        s.working_days
        FROM employees_shift_master esm
        JOIN shifts s ON s.id=esm.shift_id
        WHERE
        :selected_date BETWEEN esm.date_from AND esm.date_to AND
        :employee_id IN (
        SELECT employee_id FROM employees_shift_details esd
        WHERE employee_shift_master_id=esm.id AND esd.is_deleted=0)
        AND esm.is_deleted=0
        AND s.is_deleted=0
        UNION
        SELECT
        adj_in_time AS time_in,
        adj_out_time AS time_out,
        beginning_in,
        beginning_out,
        ending_in,
        ending_out,
        break_one_start,
        break_one_end,
        break_two_start,
        break_two_end,
        break_three_start,
        break_three_end,
        date_from,
        date_to,
        final_approver_date_action AS `date_applied`,
        working_days
        FROM employees_change_shift
        WHERE
        :selected_date BETWEEN date_from AND date_to
        AND :employee_id =employees_id
        AND status='Approved'
        ORDER BY date_applied DESC LIMIT 1",
        array(
            "selected_date"=>$date,
            "employee_id"=>$employee
            )
    )->fetch(PDO::FETCH_ASSOC);
    if (empty($shift)) {
        $shift=getDefaultShift($employee, $date);
    } else {
    }

    return $shift;
}
function getHolidayOfDay_bu($date, $payroll_group_id)
{
    global $con;
    $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=? AND payroll_group_id=?", array($date, $payroll_group_id))->fetch(PDO::FETCH_ASSOC);
    return $holiday;
}
//$limit=$_GET['length'];
try {
    $date_start=new DateTime(date("Y/m/d",strtotime("last Monday")));
    $date_end=new DateTime(date("Y/m/d"));

    $period = new DatePeriod(
        $date_start,
        new DateInterval('P1W'),
        $date_end->modify("+1 day")
    );
    // var_dump($period);
    // die;
    $date_end->modify("-1 day");
    $data=array();
    $index=0;
    $recipients = array();

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
            // var_dump($employee1['supervisor_id']);
            // die;
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
           // var_dump($work_hours);
           // die;
            // $receipients=array(
            //     1=>array(
            //       "employee_name"=>'asdas',
            //       "employee_list"=>array(
            //         2=>array(
            //           "employee_name"
            //           )
            //         )
            //       ),
            //     3=>array(
            //       "employee_name"=>'asdas',
            //       "employee_list"=>array(
            //         2=>array(
            //           "employee_name"
            //           )
            //         )
            //       )
            //   );
            // echo "<pre>";
            // var_dump($receipients);
            // if(array_key_exists(1, $receipients)==true) {
            //   var_dump(array_key_exists(3, $receipients[1]['employee_list']));
            // }
            // die;
            if($work_hours>=40){
                 $employee1=getEmpDetails($employee['id']);
                 $supervisor=getEmpDetails($employee1['supervisor_id']);
                      /*
                        QUery SUpervisor,Team lead, Managers of projects employee is included.
                      // */
                      //   $receipients[1]['employee_list']=array();
                      /*
                      if supervisor,team lead, maangers are already included in the array
                        add employee under the array of the to be emailed
                      */
                      /*
                      else add new record to supervisor list array and check employee
                      */

                      if(array_key_exists($employee1['supervisor_id'], $recipients)==true) {
                        if(array_key_exists($employee1['id'], $recipients[$employee1['supervisor_id']]['employee_list'])==false){
                          $recipients[$employee1['supervisor_id']]['employee_list'][$employee1['id']]=$employee1['first_name']." ".$employee1['last_name'];
                          $recipients[$employee1['supervisor_id']]['hours'][$employee1['id']]=$work_hours;
                        }
                      }
                      else{
                        $recipients[$employee1['supervisor_id']]=array('id'=>$employee1['supervisor_id'],'name'=>$supervisor['first_name'],'private_email'=>$supervisor['private_email'],'work_email'=>$supervisor['work_email'],
                          'employee_list'=>array(),'hours'=>array());
                        $recipients[$employee1['supervisor_id']]['employee_list'][$employee1['id']]=$employee1['first_name']." ".$employee1['last_name'];
                        $recipients[$employee1['supervisor_id']]['hours'][$employee1['id']]=$work_hours;
                        
                      }
                      



                     $project_id=$con->myQuery("SELECT project_id FROM projects_employees WHERE employee_id={$employee['id']}")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($project_id as $inputs){

                    $team_lead=$con->myQuery("SELECT employee_id FROM projects_employees WHERE project_id=? AND is_team_lead=1",array($inputs['project_id']))->fetchAll(PDO::FETCH_ASSOC);
                    $manager=$con->myQuery("SELECT employee_id FROM projects_employees WHERE project_id=? AND is_manager=1",array($inputs['project_id']))->fetchAll(PDO::FETCH_ASSOC);

                    foreach($team_lead as $inputs2){
                    $team_lead_id=getEmpDetails($inputs2['employee_id'])['id'];
                    $email=getEmpDetails($team_lead_id);

                      if(array_key_exists($team_lead_id, $recipients)==true) {
                        if(array_key_exists($employee1['id'], $recipients[$team_lead_id])==false){
                          $recipients[$team_lead_id]['employee_list'][$employee1['id']]=$employee1['first_name']." ".$employee1['last_name'];
                          $recipients[$team_lead_id]['hours'][$employee1['id']]=$work_hours;
                        }
                      }
                      else{
                        $recipients[$email['id']]=array('id'=>$email['id'],'name'=>$email['first_name'],'private_email'=>$email['private_email'],'work_email'=>$email['work_email'],
                          'employee_list'=>array(),'employee_list'=>array(),'hours'=>array());
                        $recipients[$team_lead_id]['employee_list'][$employee1['id']]=$employee1['first_name']." ".$employee1['last_name'];
                        $recipients[$team_lead_id]['hours'][$employee1['id']]=$work_hours;
                      }
                                                }

                    foreach($manager as $inputs3){
                    $manager_id=getEmpDetails($inputs3['employee_id'])['id'];
                    $email=getEmpDetails($manager_id);
                      if(array_key_exists($manager_id, $recipients)==true) {
                        if(array_key_exists($employee1['id'], $recipients[$manager_id])==false){
                          $recipients[$manager_id]['employee_list'][$employee1['id']]=$employee1['first_name']." ".$employee1['last_name'];
                          $recipients[$manager_id]['hours'][$employee1['id']]=$work_hours;
                        }
                      }
                      else{
                       $recipients[$email['id']]=array('id'=>$email['id'],'name'=>$email['first_name'],'private_email'=>$email['private_email'],'work_email'=>$email['work_email'],
                           'employee_list'=>array(),'employee_list'=>array(),'hours'=>array());
                        $recipients[$manager_id]['employee_list'][$employee1['id']]=$employee1['first_name']." ".$employee1['last_name'];
                        $recipients[$manager_id]['hours'][$employee1['id']]=$work_hours;
                      }

                                             } 
                                       
                                                }
                                          }
                               

                             }

                      }

                    $email_settings=getEmailSettings();
                    foreach ($recipients as $key => $receiver){

                      $private_email=$receiver['private_email'];
                      $work_email=$receiver['work_email'];
                       
                    if ((!empty($private_email) || !empty($work_email)) && !empty($email_settings)) {
                    $emp="";
                    $header="Employee Working Hours Update";
                    $message="Hi {$receiver['name']},<br/> The following employees has already accumulated more than 40 working hours: <br/><br> ";
                      foreach($receiver['employee_list'] as $key => $lapitna){
                        echo $emp=$emp."<li> {$lapitna} ( ".$receiver['hours'][$key]." hours )<br>";
                      }
                    $last="<br/> For more details please login to the Secret 6 HRIS.";
                    $message=email_template($header, $message.$emp.$last);
                     //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                    PHPemailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com",array($private_email,$work_email), "Employee Working Hours Update", $message, $email_settings['host'], $email_settings['port']);
                                                                                                                                }
                                                                                                                              }
                                                                                                                              // echo "<pre>";
                                                                                                                              // var_dump($recipients);
                                                                                                                              // die;
                  
                     
    
    echo json_encode($data);
    die;
} catch (Exception $e) {
    $data=array();
}

echo json_encode($data);
die;
