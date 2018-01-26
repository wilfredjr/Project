<?php
session_start();
date_default_timezone_set("Asia/Manila");
define("WEBAPP", 'SGTSI');
define("DATE_FORMAT_PHP", "m/d/Y");
define("DATE_FORMAT_SQL", "%m/%d/%Y");
define("TIME_FORMAT_SQL", "%h:%i %p");
define("TIME_FORMAT_PHP", "h:i A");
    //$_SESSION[WEBAPP]=array();
    // function __autoload($class)
    // {
    //  require_once 'class.'.$class.'.php';
    // }

# TRIXIA
function get_leave_available($id)
{
    global $con;
    $result = $con->myQuery("SELECT
                                eal.id,
                                eal.leave_id,
                                l.name AS leave_name,
                                eal.employee_id,
                                eal.balance_per_year,
                                eal.date_added,
                                eal.total_leave
                            FROM employees_available_leaves eal
                            INNER JOIN leaves l ON l.id=eal.leave_id
                            WHERE eal.employee_id=? AND eal.is_deleted=0 AND eal.is_cancelled=0 AND eal.is_converted=0 AND l.is_pay=1",array($id))->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}
# TRIXIA

function redirect($url)
{
    header("location:".$url);
}
function getFileExtension($filename)
{
    return substr($filename, strrpos($filename, "."));
}
// ENCRYPTOR
function encryptIt($q)
{
    $cryptKey  = 'JPB0rGtIn5UB1xG03efyCp';
    $qEncoded      = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), $q, MCRYPT_MODE_CBC, md5(md5($cryptKey))));
    return($qEncoded);
}
function decryptIt($q)
{
    $cryptKey  = 'JPB0rGtIn5UB1xG03efyCp';
    $qDecoded      = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($q), MCRYPT_MODE_CBC, md5(md5($cryptKey))), "\0");
    return($qDecoded);
}
//End Encryptor
/* User FUNCTIONS */
function isLoggedIn()
{
    if (empty($_SESSION[WEBAPP]['user'])) {
        return false;
    } else {
        return true;
    }
}
function toLogin($url=null)
{
    if (empty($url)) {
            //Alert('Please Log in to Continue');

        if(substr(getcwd(), strrpos(getcwd(),"\\")+1)=="payroll"){
            header("location: ../frmlogin.php");
        } else {
            header("location: frmlogin.php");
        }
    } else {
        header("location: ".$url);
    }
}
function Login($user)
{
    $_SESSION[WEBAPP]['user']=$user;
}
/* End User FUnctions */
//HTML Helpers
function makeHead($pageTitle=WEBAPP, $level=0)
{
    require_once str_repeat('../', $level).'template/head.php';
    unset($pageTitle);
}
function makeFoot($pageTitle=WEBAPP,$level=0)
{
    global $request_type;
    require_once str_repeat('../', $level).'template/foot.php';
    unset($pageTitle);

}

function makeOptions($array, $placeholder="", $val=null, $disable="", $checked_value=null)
{
    $options="";
        // if(!empty($placeholder)){
    $options.="<option value='{$val}'>{$placeholder}</option>";
        // }
    foreach ($array as $row) {
        list($value, $display) = array_values($row);
        if ($checked_value!=null && $checked_value==$value) {
            $options.="<option value='".htmlspecialchars($value)."' checked $disable>".htmlspecialchars($display)."</option>";
        } else {
            $options.="<option value='".htmlspecialchars($value)."' $disable>".htmlspecialchars($display)."</option>";
        }
    }
    return $options;
}

//END HTML Helpers
/* BOOTSTRAP Helpers */
function Modal($content=null, $title="Alert")
{
    if (!empty($content)) {
        $_SESSION[WEBAPP]['Modal']=array("Content"=>$content,"Title"=>$title);
    } else {
        if (!empty($_SESSION[WEBAPP]['Modal'])) {
            include_once 'template/modal.php';
            unset($_SESSION[WEBAPP]['Modal']);
        }
    }
}
function Alert($content=null, $type="info")
{
    if (!empty($content)) {
        $_SESSION[WEBAPP]['Alert']=array("Content"=>$content,"Type"=>$type);
    } else {
        if (!empty($_SESSION[WEBAPP]['Alert'])) {
            include_once (substr(getcwd(), strrpos(getcwd(),"\\")+1)=="payroll"?"..//":'').'template/alert.php';
            unset($_SESSION[WEBAPP]['Alert']);
        }
    }
}
function createAlert($content='', $type='info')
{
    echo "<div class='alert alert-{$type}' role='alert'>{$content}</div>";
}
/* End BOOTSTRAP Helpers */

/* SPECIFIC TO WEBAPP */
function getDepriciationDate($purchase_date, $terms)
{
    $purchase_date=new DateTime($purchase_date);
    $diff_terms=new DateInterval("P{$terms}M");
    return date_format(date_add($purchase_date, $diff_terms), 'Y-m-d');
}

function AllowUser($user_type_id)
{
    if (array_search($_SESSION[WEBAPP]['user']['user_type'], $user_type_id)!==false) {
        return true;
    }
    return false;
}

function insertAuditLog($user, $action)
{
    #user,action,date
    if (file_exists("./audit_log.txt")) {
        $user=htmlspecialchars($user);
        $action=htmlspecialchars($action);
        $new_input=json_encode(array($user,$action,date('Y-m-d H:i:s')), JSON_PRETTY_PRINT);
        $file = fopen("./audit_log.txt", "r+");
        fseek($file, -4, SEEK_END);
        fwrite($file, ",".$new_input."\n\t]\n}");
        fclose($file);
    } else {
        $file = fopen("./audit_log.txt", "w+");

        $data=json_encode(array("data"=>array(array("NONE","INITIAL START UP",date('Y-m-d H:i:s')))), JSON_PRETTY_PRINT);
        fwrite($file, $data);
        fclose($file);
    }
}


function emailer($username, $password, $from, $to, $subject, $body, $host='tls://smtp.gmail.com', $port=465) {
    require_once "Mail.php";
    $headers=array(
        'From'=>$from,
        'To'=>$to,
        'Subject'=>$subject,
        'MIME-Version' => 1,
        'Content-type' => 'text/html;charset=iso-8859-1'
        );

    $smtp=Mail::factory('smtp', array(
        'host' => $host,
        'port' => $port,
        'auth' => true,
        'username' => $username,
        'password' => $password
        ));
    //echo $to;
    $mail = $smtp->send($to, $headers, $body);
    if (PEAR::isError($mail)) {
        var_dump($mail->getMessage());
        return false;
    } else {
        return true;
    }
}
function emailer_attachment($username, $password, $from, $to, $subject, $body, $host='tls://smtp.gmail.com', $port=465)
{
    require_once "Mail.php";
    require_once "Mail/mime.php";
    $file='Employee Self Service Guide.pdf';
    $headers=array(
        'From'=>$from,
        'To'=>$to,
        'Subject'=>$subject,
        'MIME-Version' => 1,
        'Content-type' => 'text/html;charset=iso-8859-1'
        );

    $mime = new Mail_mime(array('eol' => "\n"));

    $mime->setTXTBody($body);
    $mime->setHTMLBody($body);
    $mime->addAttachment($file, 'application/pdf');

    $body = $mime->get();
    $hdrs = $mime->headers($headers);

    $smtp=Mail::factory('smtp', array(
        'host' => $host,
        'port' => $port,
        'auth' => true,
        'username' => $username,
        'password' => $password
        ));
    //echo $to;
    $mail = $smtp->send($to, $hdrs, $body);
    if (PEAR::isError($mail)) {
        var_dump($mail->getMessage());
        return false;
    } else {
        return true;
    }
    // $from = 'jpbalderas17@gmail.com';
    // $to = 'johnpaul.balderas@sparkglobaltech.com';
    // $subject = 'Hi!';
    // $body = "Hi,\n\nHow are you?";
    // $headers = array(
    //     'From' => $from,
    //     'To' => $to,
    //     'Subject' => $subject
    // );
    // $smtp = Mail::factory('smtp', array(
    //         'host' => 'tls://smtp.gmail.com',
    //         'port' => 465,
    //         'auth' => true,
    //         'username' => 'johnpaul.balderas@sparkglobaltech.com',
    //         'password' => 'Sp@rk1234'
    //     ));
    // $mail = $smtp->send($to, $headers, $body);
    // if (PEAR::isError($mail)) {
    //     echo('<p>' . $mail->getMessage() . '</p>');
    // } else {
    //     echo('<p>Message successfully sent!</p>');
    // }
}
function getEmpDetails($emp_id)
{
    global $con;
    return $con->myQuery("SELECT * FROM employees WHERE id=? LIMIT 1", array($emp_id))->fetch(PDO::FETCH_ASSOC);
}
function getEmailSettings()
{
    global $con;
    return $con->myQuery("SELECT email_username as username,email_password as password,email_host as host,email_port as port FROM settings  LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}
function email_template($header, $message)
{
    return <<<html
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Spark Global Tech Systems Inc. HRIS</title>


        <style type="text/css">
            img {
                max-width: 100%;
            }
            body {
                -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6em;
            }
            body {
                background-color: #f6f6f6;
            }
            @media only screen and (max-width: 640px) {
              body {
                padding: 0 !important;
            }
            h1 {
                font-weight: 800 !important; margin: 20px 0 5px !important;
            }
            h2 {
                font-weight: 800 !important; margin: 20px 0 5px !important;
            }
            h3 {
                font-weight: 800 !important; margin: 20px 0 5px !important;
            }
            h4 {
                font-weight: 800 !important; margin: 20px 0 5px !important;
            }
            h1 {
                font-size: 22px !important;
            }
            h2 {
                font-size: 18px !important;
            }
            h3 {
                font-size: 16px !important;
            }
            .container {
                padding: 0 !important; width: 100% !important;
            }
            .content {
                padding: 0 !important;
            }
            .content-wrap {
                padding: 10px !important;
            }
            .invoice {
                width: 100% !important;
            }
        }
    </style>
</head>

<body itemscope itemtype="http://schema.org/EmailMessage" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6em; background-color: #f6f6f6; margin: 0;" bgcolor="#f6f6f6">

    <table class="body-wrap" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: #f6f6f6; margin: 0;" bgcolor="#f6f6f6"><tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
        <td class="container" width="600" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;" valign="top">
            <div class="content" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; background-color: #fff; margin: 0; border: 1px solid #e9e9e9;" bgcolor="#fff"><tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td class="alert alert-warning" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 16px; vertical-align: top; color: #fff; font-weight: 500; text-align: center; border-radius: 3px 3px 0 0; background-color: #348EDA; margin: 0; padding: 20px;" align="center" bgcolor="#348EDA" valign="top">
                    {$header}
                </td>
            </tr><tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td class="content-wrap" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 20px;" valign="top">
            <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                    <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                        {$message}
                    </td>
                </tr><tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">

            </td>
        </tr>
        <tr style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"><td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
            Thank you for choosing Spark Global Tech HRIS.
        </td>
    </tr>
</table>
</td>
</tr></table><div class="footer" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
</td>
<td style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
</tr></table></body>
</html>
html;
}

function refresh_activity($user_id)
{
    global $con;
    $con->myQuery("UPDATE users SET last_activity=NOW() WHERE id=?", array($user_id));
}
function is_active($user_id)
{
    global $con;
    $last_activity=$con->myQuery("SELECT last_activity FROM users  WHERE id=?", array($user_id))->fetchColumn();
    $inactive_time=60*10;
    // echo strtotime($last_activity)."<br/>";
    // echo time();
    if (time()-strtotime($last_activity) > $inactive_time) {
        return false;
    }

    return true;
}

function user_is_active($user_id)
{
    global $con;
    $last_activity=$con->myQuery("SELECT is_active FROM users  WHERE id=?", array($user_id))->fetchColumn();
    if (!empty($last_activity)) {
        return true;
    } else {
        return false;
    }
}

function getTimeIn($half_day="")
{
    if ($half_day=="") {
        return "08:30:00";
    } elseif ($half_day=="AM") {
        return "13:00:00";
    }

    return "08:30:00";
}

function getTimeOut()
{
    return "17:30:00";
}

function DisplayDate($unformatted_date)
{
    return date("m/d/Y", strtotime($unformatted_date));
}

function SaveDate($formatted_date)
{
    return date_format(date_create($formatted_date), 'Y-m-d');
}
function getLate($time_in, $shifts_array, $half_day="", $for_download=0, $in_array_form=FALSE)
{
    $late="";

    if (empty($time_in) || $shifts_array['late_start']=="00:00:00") {
        return    "";
    }
    // var_dump($time_in);

    if ($for_download==0) {
        $start_date=new DateTime($time_in);
        $late_start=new DateTime($start_date->format("Y-m-d")." ".$shifts_array['late_start']);
    } else {
        $start_date=new DateTime();
        $start_date->setTimeStamp($time_in);

        $late_start=new DateTime($start_date->format("Y-m-d")." ".$shifts_array['late_start']);
    }
    if (!empty($shifts_array['grace_minutes'])) {
        // Has Grace period
        $late_start_with_grace_period=clone($late_start);
        $late_start_with_grace_period->add(new DateInterval('PT'.intval($shifts_array['grace_minutes']).'M'));
        if ($start_date->format("Y-m-d H:i:s") <= $late_start_with_grace_period->format("Y-m-d H:i:is")) {
            // Time in is less than late start with grace period
            return "";
        }
    }

    $interval=$late_start->diff($start_date);


    if (empty($interval->format("%r"))) {
        $hours=$interval->format("%h");
        $minutes=$interval->format("%i");

        if ($in_array_form===TRUE) {
            if (!empty($hours)) {
                $late['hours']=$hours;
            } else {
                $late['hours']=0;
            }

            if (!empty($minutes)) {
                $late['minutes']=$minutes;
            } else {
                $late['minutes']=0;
            }
        } else {

            if (!empty($hours)) {
                $late.=$hours." Hour/s ";
            }

            if (!empty($minutes)) {
                $late.=$minutes." Minute/s ";
            }
        }
    }

    return $late;
}
function convertBytes($value)
{
    if (is_numeric($value)) {
        return $value;
    } else {
        $value_length = strlen($value);
        $qty = substr($value, 0, $value_length - 1);
        $unit = strtolower(substr($value, $value_length - 1));
        switch ($unit) {
            case 'k':
            $qty *= 1024;
            break;
            case 'm':
            $qty *= 1048576;
            break;
            case 'g':
            $qty *= 1073741824;
            break;
        }
        return $qty;
    }
}
function getFlexiTimeInAndOut ($time_in, $time_out, $shifts_array) {
    if ($time_in == "" && $time_out =="") {
        return "";
    }
    

    $time_in = new DateTime($time_in);
    // $original_time_in = $time_in;
    if (empty($time_out)) {
        $today=new DateTime();
        if ($time_in->format("Y-m-d")!==$today->format("Y-m-d")) {
            return '';
        }
    }
    $time_out = new DateTime($time_out);
    $original_time_out = clone($time_out);
    $hours_worked="";
    // var_dump($time_in, $time_out, $shifts_array);
    if ($time_in->format("Y-m-d H:i:s") < $time_in->format("Y-m-d")." ".$shifts_array['time_in']) {
        //greater than time out of the shift
        $time_in = new DateTime($time_in->format("Y-m-d")." ".$shifts_array['time_in']);
    }

    // echo "<pre>";
    // print_r($time_in);
    // print_r($time_out);
    if ($time_out->format("Y-m-d H:i:s") >= $time_out->format("Y-m-d")." ".$shifts_array['time_out']) {
        //greater than time out of the shift
        $time_out = new DateTime($time_out->format("Y-m-d")." ".$shifts_array['time_out']);
    }
    $interval = $time_out->diff($time_in);
    $hours_to_deduct=0;
    if ($interval->format("%h") < 9) {
        $original_interval = $original_time_out->diff($time_in);
        if ($original_interval->format("%h") > 9) {
            $hours_to_deduct = $original_interval->format("%h")-9;
            
            $mintes_to_deduct = 0;
            if (!empty($original_interval->format("%i"))) {
                $mintes_to_deduct = $original_interval->format("%i");
            }
            // var_dump($original_time_out);
                $time_out = $original_time_out->sub(new DateInterval("PT{$hours_to_deduct}H{$mintes_to_deduct}M"));
            // var_dump($original_time_out);
        }
    } else {
        if ($interval->format("%h") > 9) {
            $hours_to_deduct = $interval->format("%h")-9;
            // var_dump($hours_to_deduct);
            $mintes_to_deduct = 0;
            if (!empty($interval->format("%i"))) {
                $mintes_to_deduct = $interval->format("%i");
            }
            // var_dump($time_out);
                $time_out = $time_out->sub(new DateInterval("PT{$hours_to_deduct}H{$mintes_to_deduct}M"));
            // var_dump($original_time_out);
        }
    }

    return array("time_in"=>$time_in->format(DATE_FORMAT_PHP." ".TIME_FORMAT_PHP), "time_out"=>$time_out->format(DATE_FORMAT_PHP." ".TIME_FORMAT_PHP));
}
function getHoursWorked($time_in, $time_out, $shifts_array, $in_array_form=FALSE)
{
    if ($time_in == "" && $time_out =="") {
        return "";
    }
    
    $time_in = new DateTime($time_in);
    // $original_time_in = $time_in;
    if (empty($time_out)) {
        $today=new DateTime();
        if ($time_in->format("Y-m-d")!==$today->format("Y-m-d")) {
            return '';
        }
    }
    $time_out = new DateTime($time_out);
    $original_time_out = clone($time_out);
    $hours_worked="";
    // var_dump($time_in, $time_out, $shifts_array);
    if ($time_in->format("Y-m-d H:i:s") < $time_in->format("Y-m-d")." ".$shifts_array['time_in']) {
        //greater than time out of the shift
        $time_in = new DateTime($time_in->format("Y-m-d")." ".$shifts_array['time_in']);
    }

    if ($time_out->format("Y-m-d H:i:s") >= $time_out->format("Y-m-d")." ".$shifts_array['time_out']) {
        //greater than time out of the shift
        $time_out = new DateTime($time_out->format("Y-m-d")." ".$shifts_array['time_out']);
    }

    $interval = $time_out->diff($time_in);
    // var_dump($interval);
    if ($interval->format("%h") < 9) {
        $original_interval = $original_time_out->diff($time_in);
        if ($original_interval->format("%h") > 9) {
            $hours_to_deduct = $original_interval->format("%h")-9;
            $mintes_to_deduct = 0;
            if (!empty($original_interval->format("%i"))) {
                $mintes_to_deduct = $original_interval->format("%i");
            }
            // var_dump($original_time_out);
                $original_time_out->sub(new DateInterval("PT{$hours_to_deduct}H{$mintes_to_deduct}M"));
            // var_dump($original_time_out);
        }
        $interval = $original_time_out->diff($time_in);
        // die;
    }
    if (!empty($interval->format("%h")) || !empty($interval->format("%i"))) {
        $hours=$interval->format("%h");
        if ($hours>=5) {
            $hours-=1;
        }
        $minutes=$interval->format("%i");
        if ($in_array_form===TRUE) {
            if (!empty($hours)) {
                $hours_worked['hours']=$hours;
            } else {
                $hours_worked['hours']=0;
            }

            if (!empty($minutes)) {
                $hours_worked['minutes']=$minutes;
            } else {
                $hours_worked['minutes']=0;
            }
        } else {

            if (!empty($hours)) {
                $hours_worked.=$hours." Hour/s ";
            }

            if (!empty($minutes)) {
                $hours_worked.=$minutes." Minute/s ";
            }
        }
    }
    // var_dump($hours_worked);
    return $hours_worked;
}
function getStartAndEndDate($week, $year) {
  $dto = new DateTime();
  $dto->setISODate($year, $week);

  $ret['week_start'] = $dto->format('Y-m-d');
  $dto->modify('+6 days');
  $ret['week_end'] = $dto->format('Y-m-d');
  return $ret;
}
function getHours($date_start, $date_end, $employee_id)
{
    global $con;
    $date_start=new DateTime($date_start);
    $date_end=new DateTime($date_end);
    $date_end->modify("+1 day");
    $period = new DatePeriod(
        $date_start,
        new DateInterval('P1D'),
        $date_end
    );
    $date_end->modify("-1 day");
    $work_hours=array("hours"=>0, "minutes"=>0);
    $late_array=array("hours"=>0, "minutes"=>0);
    $overtime=array("hours"=>0, "minutes"=>0);
    foreach ($period as $date) {
        // var_dump($date);
        $next_day=new DateTime($date->format("Y-m-d"));
        $next_day->modify("+1 day");
        $weekday=$date->format("w");
        $shift=getShift($employee_id, $date->format("Y-m-d"));
        // var_dump($shift);
        $time_inputs=array(
            "employee_id"=>$employee_id,
            "date_filter"=>$date->format("Y-m-d"),
            "shift_in"=>$shift['beginning_in'],
            "shift_out"=>$shift['ending_out']
            );
        // $data[$index]['shift_start']=$shift['time_in'];
        // $data[$index]['shift_end']=$shift['time_out'];

        $time_in_query="SELECT DATE_FORMAT(in_time,'".DATE_FORMAT_SQL." %H:%i:%s') as in_time,DATE_FORMAT(out_time,'".DATE_FORMAT_SQL." %H:%i:%s') as out_time,id,note FROM `attendance` WHERE employees_id=:employee_id ";
        $time_in_sql="";
        $time_out_sql="";

        // var_dump($date->format("Y-m-d"));
        $time_in_and_out=getTimeInAndOut($employee_id, $date->format(DATE_FORMAT_PHP));
        
        $time_ins['in_time'] = !empty($time_in_and_out['in_time'])?$time_in_and_out['in_time']:'';
        $time_outs['out_time'] = !empty($time_in_and_out['out_time'])?$time_in_and_out['out_time']:'';

        $data['in_time']='';
        $data['out_time']='';

        if (!empty($time_ins)) {
            $flexi_time=getFlexiTimeInAndOut ($time_ins['in_time'], $time_outs['out_time'], $shift);
            $data['in_time']=!empty($flexi_time['time_in'])?$flexi_time['time_in']:'';
            $data['out_time']=!empty($flexi_time['time_out'])?$flexi_time['time_out']:'';
            
        }

        $break_inputs['employee_id']=$employee_id;
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
        $data['break_excess']="";
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
                $data['break_excess'].=$break_excess['h']." hour/s ";
            }

            if (!empty($break_excess['i'])) {
                $data['break_excess'].=$break_excess['i']." minute/s ";
            }
        }
        $data['note']=(!empty($time_ins['note'])?"Time in: ".$time_ins['note']:''). (!empty($time_outs['note'])?" Time out: ".$time_outs['note']:'');

        $ob_date=$con->myQuery("SELECT DATE_FORMAT(ob_date,'".DATE_FORMAT_SQL."') as ob_date,time_from,time_to FROM employees_ob WHERE employees_id=? AND ob_date=? AND request_status_id=2 ORDER BY time_from ASC", array($employee_id,$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);
        // echo $date->format("Y-m-d")."<br>";
        // var_dump($time_ins['in_time']);
        // var_dump($ob_date);
        // echo "<br>";
        if (!empty($ob_date)) {
            if (date_format(date_create($time_ins['in_time']), DATE_FORMAT_PHP) <> date_format(date_create($ob_date['ob_date']), DATE_FORMAT_PHP)) {
                $data['in_time']=$ob_date['ob_date'].' '.$ob_date['time_from'];
                $data['out_time']=$ob_date['ob_date'].' '.$ob_date['time_to'];
            } else {
                if ($time_outs['out_time'] < date_format(date_create($ob_date['time_to']), $ob_date['ob_date'].' H:i:s')) {
                    $data['out_time']=$ob_date['ob_date'].' '.$ob_date['time_to'];
                }
                if ($time_ins['in_time'] > date_format(date_create($ob_date['time_from']), $ob_date['ob_date'].' H:i:s')) {
                    $data['in_time']=$ob_date['ob_date'].' '.$ob_date['time_from'];
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
            AND request_STATUS_id=2",
            array($employee_id,$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);
        if (!empty($offset_date)) {
            if (date_format(date_create($time_ins['in_time']), DATE_FORMAT_PHP) <> date_format(date_create($offset_date['start_datetime']), DATE_FORMAT_PHP)) {
                $data['in_time']=date_format(date_create($offset_date['start_datetime']), DATE_FORMAT_PHP.' H:i:s');
                $data['out_time']=date_format(date_create($offset_date['end_datetime']), DATE_FORMAT_PHP.' H:i:s');
            } else {
                if ($time_outs['out_time'] < date_format(date_create($offset_date['end_datetime']), DATE_FORMAT_PHP.' H:i:s')) {
                    $data['out_time']=date_format(date_create($offset_date['end_datetime']), DATE_FORMAT_PHP.' H:i:s');
                }
                if ($time_ins['in_time'] > date_format(date_create($offset_date['start_datetime']), $offset_date['start_datetime'].' H:i:s')) {
                    $data['in_time']=date_format(date_create($offset_date['start_datetime']), DATE_FORMAT_PHP.' H:i:s' );
                }
            }
        }

        $leaves=$con->myQuery("SELECT id,remark,comment FROM `employees_leaves` WHERE employee_id=? AND ? BETWEEN date_start AND date_end AND request_status_id=2", array($employee_id,$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);
        if (!empty($leaves)) {
            $data['status']=$leaves['remark']=="L"?"Leave":"Leave Without Pay";

            if ($leaves['comment']=="AM" || $leaves['comment']=="PM") {
                $late=getLate($data['in_time'], $shift, $leaves['comment'],0,TRUE);
                $undertime=getUndertime($data['out_time'],$shift, $leaves['comment']);

            } else {
                $late=getLate($data['in_time'], $shift);
                $undertime=getUndertime($data['out_time'],$shift, $leaves['comment']);

            }
        } else {
            $late=getLate($data['in_time'], $shift, "",0, TRUE);
                            $undertime=getUndertime($data['out_time'],$shift, $leaves['comment']);

        }
        // var_dump($late);
        // $data['lates']=$late;
        // $data['undertime']=$undertime;
        // $data['late_undertime']="";
       
        if(!empty($late['hours'])) {
            $late_array['hours']+=$late['hours'];
        }
        if (!empty($late['minutes'])) {
            $late_array['hours']+=$late['minutes']/60;
        }
        $data['hours_worked']=getHoursWorked($data['in_time'], $data['out_time'], $shift, TRUE);
        if(!empty($data['hours_worked']['hours'])){
            $work_hours['hours']+=intval($data['hours_worked']['hours']);
        }
        if (!empty($data['hours_worked']['minutes'])) {
            $work_hours['hours']+=floatval($data['hours_worked']['minutes']/60);
        }

        $ots=$con->myQuery("SELECT id,no_hours FROM employees_ot WHERE employees_id=? AND date(ot_date)=? AND request_status_id=2".(!empty($use_ot)?" AND id NOT IN (".implode(",", $use_ot) .")":''), array($employee_id,$date->format("Y-m-d")))->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ots as $key => $ot) {
            $overtime['hours']+=$ot['no_hours'];
            $use_ot[]=$ot['id'];
        }

    }

    return array("work_hours"=>$work_hours, "overtime"=>$overtime, "late_array"=>$late_array);
}
function getTimeInAndOut($employee_id, $date)
{
    global $con;
    $date=new DateTime($date);
    if (!empty($date)) {
        $next_day=clone($date);
        $next_day->modify("+1 day");
        // var_dump($date->format("Y-m-d"), $next_day->format("Y-m-d"));
        $shift = getShift($_SESSION[WEBAPP]["user"]["id"], $date->format("Y-m-d"));
        $data['shift']=$shift;
        $weekday=$date->format("w");
        $time_inputs=array(
            "employee_id"=>$employee_id,
            "date_filter"=>$date->format("Y-m-d"),
            // "shift_in"=>$shift['beginning_in'],
            // "shift_out"=>$shift['ending_out']
        );

        $time_in_query="SELECT DATE_FORMAT(in_time,'".DATE_FORMAT_SQL." %H:%i:%s') as in_time,DATE_FORMAT(out_time,'".DATE_FORMAT_SQL." %H:%i:%s') as out_time,id,note FROM `attendance` WHERE employees_id=:employee_id ";
        $time_in_sql="";
        $time_out_sql="";

        if ($shift['beginning_in']> $shift['ending_out']) {
            /*
            Time in exceeds a day. ex: 20:00 - 05:00
             */
            $time_inputs['next_day']=$next_day->format('Y-m-d');
            $time_in_query.=" AND DATE(in_time) BETWEEN :date_filter AND :next_day";
            // $time_in_sql.=" AND CAST(in_time as time) NOT BETWEEN :shift_out AND :shift_in ";
            // $time_out_sql.=" AND CAST(out_time as time) NOT BETWEEN :shift_out AND :shift_in ";
        } else {
            $time_in_query.=" AND DATE(in_time)=:date_filter ";
            // $time_in_sql.=" AND CAST(in_time as time) BETWEEN :shift_in AND :shift_out ";
            // $time_out_sql.=" AND CAST(out_time as time) BETWEEN :shift_in AND :shift_out ";
        }

        $time_ins=$con->myQuery($time_in_query.$time_in_sql." ORDER BY in_time ASC LIMIT 1", $time_inputs)->fetch(PDO::FETCH_ASSOC);
        $time_outs=$con->myQuery($time_in_query.$time_out_sql." ORDER BY out_time DESC LIMIT 1", $time_inputs)->fetch(PDO::FETCH_ASSOC);

        if (!empty($time_ins)) {
            if ($shift['beginning_in']> $shift['ending_out']) {
                /*
                Shifts covers two days
                 */
                $in_time=new DateTime($time_ins['in_time']);
                $out_time=new DateTime($time_outs['out_time']);

                if ($in_time->format("Y-m-d") > $date->format("Y-m-d")) {
                    /*
                in time greater than the current date and time is less than beginning in
                 */
                } else {
                    if ($in_time->format("H:i:s") >= $shift['beginning_in']) {
                        $data['in_time']=!empty($time_ins['in_time'])?$time_ins['in_time']:'';
                        $data['out_time']=!empty($time_outs['out_time']) && $time_outs['out_time']<>"0000-00-00 00:00:00"?$time_outs['out_time']:'';
                    }
                }
            } else {
                $data['in_time']=!empty($time_ins['in_time'])?$time_ins['in_time']:'';
                $data['out_time']=!empty($time_outs['out_time']) && $time_outs['out_time']<>"0000-00-00 00:00:00"?$time_outs['out_time']:'';
            }
        }

        $ob_date=$con->myQuery("SELECT DATE_FORMAT(ob_date,'".DATE_FORMAT_SQL."') as ob_date,time_from,time_to FROM employees_ob WHERE employees_id=? AND ob_date=? AND request_status_id=2 ORDER BY time_from ASC", array($employee_id,$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);
        if (!empty($ob_date)) {
            if (date_format(date_create($time_ins['in_time']), DATE_FORMAT_PHP) <> date_format(date_create($ob_date['ob_date']), DATE_FORMAT_PHP)) {
                $data['in_time']=$ob_date['ob_date'].' '.$ob_date['time_from'];
                $data['out_time']=$ob_date['ob_date'].' '.$ob_date['time_to'];
            } else {
                if ($time_outs['out_time'] < date_format(date_create($ob_date['time_to']), $ob_date['ob_date'].' H:i:s')) {
                    $data['out_time']=$ob_date['ob_date'].' '.$ob_date['time_to'];
                }
                if ($time_ins['in_time'] > date_format(date_create($ob_date['time_from']), $ob_date['ob_date'].' H:i:s')) {
                    $data['in_time']=$ob_date['ob_date'].' '.$ob_date['time_from'];
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
                array($employee_id,$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);

        if (!empty($offset_date)) {
            if (date_format(date_create($time_ins['in_time']), DATE_FORMAT_PHP) <> date_format(date_create($offset_date['start_datetime']), DATE_FORMAT_PHP)) {
                $data['in_time']=date_format(date_create($offset_date['start_datetime']), DATE_FORMAT_PHP.' H:i:s');
                $data['out_time']=date_format(date_create($offset_date['end_datetime']), DATE_FORMAT_PHP.' H:i:s');
            } else {
                if ($time_outs['out_time'] < date_format(date_create($offset_date['end_datetime']), DATE_FORMAT_PHP.' H:i:s')) {
                    $data['out_time']=date_format(date_create($offset_date['end_datetime']), DATE_FORMAT_PHP.' H:i:s');
                }
                if ($time_ins['in_time'] > date_format(date_create($offset_date['start_datetime']), $offset_date['start_datetime'].' H:i:s')) {
                    $data['in_time']=date_format(date_create($offset_date['start_datetime']), DATE_FORMAT_PHP.' H:i:s');
                }
            }
        }

        return $data;
    }
}

function getOvertimePerDay($employee_id, $date)
{
    global $con;
    $ots=$con->myQuery("SELECT IFNULL(SUM(no_hours),0) FROM employees_ot WHERE employees_id=? AND date(ot_date)=? AND request_status_id=2", array($employee_id,$date))->fetchColumn();

    return floatval($ots);
}

function canFileForEmployees($employee_id)
{
    global $con;
    return $con->myQuery("SELECT employee_id FROM projects_employees WHERE is_manager = 1 AND employee_id = :employee_id
                    UNION
                    SELECT employees.id AS employee_id FROM employees LEFT JOIN pay_grade ON pay_grade.id = employees.pay_grade_id WHERE (SELECT COUNT(id) FROM employees WHERE supervisor_id = :employee_id) > 0 AND employees.id =:employee_id", array("employee_id"=>$employee_id))->fetchColumn();

}
function canFileForEmployees1($employee_id)
{
    global $con;
    return $con->myQuery("SELECT employee_id FROM projects_employees WHERE (is_manager = 1 OR is_team_lead_ba=1 OR is_team_lead_dev=1) AND employee_id = :employee_id
                    UNION
                    SELECT employees.id AS employee_id FROM employees LEFT JOIN pay_grade ON pay_grade.id = employees.pay_grade_id WHERE (SELECT COUNT(id) FROM employees WHERE supervisor_id = :employee_id) > 0 AND employees.id =:employee_id", array("employee_id"=>$employee_id))->fetchColumn();

}
function getEmployeeDepartment($employee_id)
{
    global $con;
    return $con->myQuery("SELECT department_id FROM employees WHERE id = :employee_id", array("employee_id"=>$employee_id))->fetchColumn();
}
/* END SPECIFIC TO WEBAPP */
//-------------------------------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------------------------------//
                                                //PAYROLL GENERATION FUNCTIONS
//-------------------------------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------------------------------//

function get_sss_details($basic_salary)
{
    global $con;
    $sss= $con->myQuery("SELECT
        sss_code,
        sss_ee,
        sss_er,
        sss_ec
        FROM
        gd_sss
        WHERE sss_from_comp <= ? AND
        sss_to_comp >= ? AND
        is_deleted = 0",array($basic_salary,$basic_salary))->fetch(PDO::FETCH_ASSOC);

    return $sss;
}

function get_philhealth_details($basic_salary)
{
    global $con;
    $ph= $con->myQuery("SELECT
        ph_code,
        ph_ee,
        ph_er
        FROM
        gd_philhealth
        WHERE ph_from_comp <= ? AND
        ph_to_comp >= ? AND
        is_deleted = 0",array($basic_salary,$basic_salary))->fetch(PDO::FETCH_ASSOC);

    return $ph;
}

function get_salary_settings($payroll_group_id)
{
    global $con;
    $data=$con->myQuery("SELECT
        ps.pay_group_id,
        ps.salary_settings,
        ps.tax_settings,
        ps.government_settings,
        ps.company_settings,
        ps.days_per_month,
        pay_period.id as 'pay_period_id',
        ps.sss_deduction,
        ps.philhealth_deduction,
        ps.pagibig_deduction
        FROM
        payroll_settings AS ps
        INNER JOIN pay_period ON pay_period.id = ps.salary_settings
        WHERE ps.pay_group_id = ?",array($payroll_group_id))->fetch(PDO::FETCH_ASSOC);

    return $data;

}

function get_employee_govde_setting($employee_id)
{
    global $con;
    $data= $con->myQuery("SELECT w_sss,w_hdmf,w_philhealth FROM employees where id = ?",array($employee_id))->fetch(PDO::FETCH_ASSOC);

    return $data;
}

function get_hdmf_details($basic_salary)
{
    global $con;
    $hdmf= $con->myQuery("SELECT
        hdmf_code,
        hdmf_ee,
        hdmf_er
        FROM
        gd_hdmf
        WHERE hdmf_from_comp <= ? AND
        hdmf_to_comp >= ? AND
        is_deleted = 0",array($basic_salary,$basic_salary))->fetch(PDO::FETCH_ASSOC);

    return $hdmf;
}

function computeTimeDiff($time_one,$time_two)
{
    $min =(strtotime($time_one) - strtotime($time_two)) / 60;
    $zero    = new DateTime('@0');
    $offset  = new DateTime('@' . $min * 60);
    $diff    = $zero->diff($offset);
    $total_time = $diff->format('%H:%I');
    $hms = explode(":", $total_time);
    $decimalHours=($hms[0] + ($hms[1]/60));

    $result=number_format($decimalHours,'2','.','');
    return $result;
}

function checkHalfDay($type,$employee_id,$date)
{
    global $con;

    $data=$con->myQuery("SELECT * FROM employees_leaves WHERE employee_id=? AND leave_id<>0 AND comment=? AND request_status_id=2 AND date_start=?",array($employee_id,$type,$date))->fetch(PDO::FETCH_ASSOC);
    return $data;
}
function checkLeave($employee_id,$date)
{
    global $con;
    $data=$con->myQuery("SELECT * FROM employees_leaves WHERE employee_id=? AND leave_id<>0 AND comment='' AND request_status_id=2 AND date_start=?",array($employee_id,$date))->fetch(PDO::FETCH_ASSOC);
    return $data;
}
function get_employees_ob($emp_id, $ob_date)
{
    global $con;
    $ob= $con->myQuery("SELECT * FROM employees_ob WHERE request_status_id = '2' AND employees_id = ? AND ob_date = ?
        ",array($emp_id, $ob_date))->fetchAll(PDO::FETCH_ASSOC);
    return count($ob);
}
function get_employees_offset($emp_id, $of_date)
{
    global $con;
    $offset= $con->myQuery("SELECT * FROM employees_offset_request
        WHERE request_status_id = '2' AND
        employees_id = ? AND
        DATE_FORMAT(start_datetime, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(start_datetime, '%Y-%m-%d') >= ? AND
        DATE_FORMAT(end_datetime, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(end_datetime, '%Y-%m-%d') >= ?",array($emp_id,$of_date,$of_date,$of_date,$of_date))->fetchAll(PDO::FETCH_ASSOC);
    return count($offset);
}

function getNightShiftHours(DateTime $time_in, DateTime $time_out)
{
    $return=array();
    $nighshift_start_time="22:00:00";
    $nighshift_end_time="06:00:00";
    if ($time_out->format("Y-m-d") > $time_in->format("Y-m-d") && $time_out->format("H:i:s")!="00:00:00") {
      /*
      Time in and Time out covers two days
       */
        $return['current_date_hours']=getNightShiftHours($time_in, new DateTime($time_in->format("Y-m-d")." 24:00:00"))['current_date_hours'];
        $return['current_date']=$time_in->format("Y-m-d");
        $return['next_date_hours']=getNightShiftHours(new DateTime($time_in->format("Y-m-d")." 24:00:00"), $time_out)['current_date_hours'];
        $return['next_date']=$time_out->format("Y-m-d");

    } else {
        $nightshift_morning_start=new DateTime($time_in->format("Y-m-d")." 00:00:00");
        $nightshift_morning_end=new DateTime($time_in->format("Y-m-d")." {$nighshift_end_time}");

        $nightshift_evening_start=new DateTime($time_in->format("Y-m-d")." {$nighshift_start_time}");
        $nightshift_evening_end=new DateTime($time_in->format("Y-m-d")." 24:00:00");
        if (($time_in >= $nightshift_morning_start && $time_in <= $nightshift_morning_end) ||  ($time_in >= $nightshift_evening_start && $time_in <= $nightshift_evening_end) || ($time_out >= $nightshift_morning_start && $time_out <= $nightshift_morning_end) ||  ($time_out >= $nightshift_evening_start && $time_out <= $nightshift_evening_end)) {
            /*
            Between Night shift Range
             */
            $return['current_date_hours']=0;
            $return['current_date']=$time_in->format("Y-m-d");
            if (($time_in >= $nightshift_morning_start && $time_in <= $nightshift_morning_end)) {
                /*
                Time in is in morning night shift
                 */
                if ($time_out > $nightshift_morning_end) {
                    /*
                    Time out is greater than morning shift end
                     */
                    $difference=$time_in->diff($nightshift_morning_end);
                    if ($difference->h>0 || $difference->i>0) {
                        $return['current_date_hours']+=$difference->h + ($difference->i>0?$difference->i/60:0);
                    }
                } else {
                    $difference=$time_in->diff($time_out);
                    if ($difference->h>0 || $difference->i>0) {
                        $return['current_date_hours']+=$difference->h + ($difference->i>0?$difference->i/60:0);
                    }
                }
            }

            if (($time_out >= $nightshift_evening_start && $time_out <= $nightshift_evening_end)) {
                /*
                Time out is in evening night shift
                 */
                if ($time_in > $nightshift_evening_start) {
                    /*
                    Time in is greater than evening shift start
                     */
                    $difference=$time_in->diff($time_out);
                    if ($difference->h>0 || $difference->i>0) {
                        $return['current_date_hours']+=$difference->h + ($difference->i>0?$difference->i/60:0);
                    }
                } else {
                    $difference=$nightshift_evening_start->diff($time_out);
                    if ($difference->h>0 || $difference->i>0) {
                        $return['current_date_hours']+=$difference->h + ($difference->i>0?$difference->i/60:0);
                    }
                }
            }

        } else {
            return 0;
        }
    }
    return $return;
}



function check_loans($employee_id)
{
    global $con;

    $data=$con->myQuery("SELECT * FROM emp_loans WHERE status_id = 1 AND employee_id = ?",array($employee_id))->fetch(PDO::FETCH_ASSOC);
    return count($data['emp_loan_id']);
}

function check_loan_pass($employee_id,$date_from,$date_to)
{
    global $con;

    $data=$con->myQuery("SELECT * FROM emp_loan_pass elp
        INNER JOIN emp_loans el ON elp.emp_loan_id = el.emp_loan_id
        WHERE el.employee_id = ? AND elp.date_applied BETWEEN ? AND ?",array($employee_id,$date_from,$date_to))->fetch(PDO::FETCH_ASSOC);
    return count($data['emp_loan_id']);
}

function get_loan_details($employee_id)
{
    global $con;

    $data=$con->myQuery("SELECT
        emp_loan_id,
        cut_off_no,
        loan_amount,
        balance,
        remaining_cut_off_no
        FROM
        emp_loans
        WHERE status_id = 1 AND employee_id = ?",array($employee_id))->fetch(PDO::FETCH_ASSOC);
    return $data;
}


function get_sss($basic_salary)
{
    global $con;
    $sss= $con->myQuery("SELECT
        id,
        sss_ee,
        sss_er
        FROM
        gd_sss
        WHERE sss_from_comp <= ? AND
        sss_to_comp >= ? AND
        is_deleted = 0",array($basic_salary,$basic_salary))->fetch(PDO::FETCH_ASSOC);

    global $period_id;
    if ($period_id == 2){ // SEMI-MONTHLY
        return $sss = ($sss['sss_ee'] / 2);
    } else { //MONTHLY
        return $sss = ($sss['sss_ee']);
    }

}

function get_philhealth($basic_salary)
{
    global $con;
    $ph= $con->myQuery("SELECT
        id,
        ph_ee,
        ph_er
        FROM
        gd_philhealth
        WHERE ph_from_comp <= ? AND
        ph_to_comp >= ? AND
        is_deleted = 0",array($basic_salary,$basic_salary))->fetch(PDO::FETCH_ASSOC);

    global $period_id;
    if ($period_id == 2){ // SEMI-MONTHLY
        return $ph = ($ph['ph_ee'] / 2);
    } else { //MONTHLY
        return $ph = ($ph['ph_ee']);
    }
}

function get_hdmf($basic_salary)
{
    global $con;
    $hdmf= $con->myQuery("SELECT
        id,
        hdmf_ee,
        hdmf_er
        FROM
        gd_hdmf
        WHERE hdmf_from_comp <= ? AND
        hdmf_to_comp >= ? AND
        is_deleted = 0",array($basic_salary,$basic_salary))->fetch(PDO::FETCH_ASSOC);

    global $period_id;
    if ($period_id == 2){ // SEMI-MONTHLY
        return $hdmf = ($hdmf['hdmf_ee'] / 2);
    } else { //MONTHLY
        return $hdmf = ($hdmf['hdmf_ee']);
    }
}

function get_deminimis($emp_id)
{
    global $con;
    $deminimis= $con->myQuery("SELECT employees.`code`,
        de_minimis_benefits.dmb_code,
        de_minimis_benefits.dmb_desc,
        de_minimis_benefits.dmb_amount,
        de_minimis_benefits.dmb_type
        FROM employee_de_minimis_benefits
        INNER JOIN employees ON employee_de_minimis_benefits.emp_code = employees.`code`
        INNER JOIN de_minimis_benefits ON employee_de_minimis_benefits.dmb_code = de_minimis_benefits.dmb_code
        WHERE  employees.id = ?",array($emp_id))->fetch(PDO::FETCH_ASSOC);


    if(!empty($deminimis['dmb_amount'])){
        if ($deminimis['dmb_type'] = 2){ //SEMI-MONTHLY
            return $deminimis = ($deminimis['dmb_amount'] / 2);
        } else { //MONTHLY
            return $deminimis = $deminimis['dmb_amount'];
        }
    }

}

function get_receivablesdeduction($emp_id)
{
    global $con;
    $receivablesdeduction= $con->myQuery("SELECT employees.`code`,
        receivable_and_taxable_allowances.rta_code,
        receivable_and_taxable_allowances.rta_desc,
        receivable_and_taxable_allowances.rta_amount,
        receivable_and_taxable_allowances.rta_taxable,
        receivable_and_taxable_allowances.rta_type
        FROM employees
        INNER JOIN employee_receivable_and_taxable_allowances ON employee_receivable_and_taxable_allowances.emp_code = employees.`code`
        INNER JOIN receivable_and_taxable_allowances ON employee_receivable_and_taxable_allowances.rta_code = receivable_and_taxable_allowances.rta_code
        WHERE receivable_and_taxable_allowances.rta_taxable = '0' and employees.`code` = ?",array($emp_id))->fetch(PDO::FETCH_ASSOC);

    if(!empty($receivablesdeduction['rta_amount'])){
        if ($receivablesdeduction['rta_type'] = 2){ //SEMI-MONTHLY
            return $receivablesdeduction = ($receivablesdeduction['rta_amount'] / 2) ;
        } else { //MONTHLY
            return $receivablesdeduction = $receivablesdeduction['rta_amount'];
        }
    }

}

function get_company_deductions($emp_id)
{
    global $con;
    $company_deduction= $con->myQuery("SELECT cd.comde_code,
        ecd.emp_comde_amt,
        ecd.emp_comde_start_date,
        ecd.emp_comde_end_date,
        ecd.emp_deduct_type,
        e.code
        FROM ((employee_company_deductions ecd
        JOIN company_deductions cd on ((cd.comde_code = ecd.comde_code)))
        JOIN employees e on ((ecd.emp_code = e.code)))
        WHERE  e.code = ?",array($emp_id))->fetch(PDO::FETCH_ASSOC);

    if(!empty($company_deduction['emp_comde_amt'])){
        if ($company_deduction['emp_deduct_type'] = 2){ //SEMI-MONTHLY
            return $company_deduction = ($company_deduction['emp_comde_amt'] / 2) ;
        } else { //MONTHLY
            return $company_deduction = $company_deduction['emp_comde_amt'];
        }
    }

}



function get_taxablededuction($emp_id)
{
    global $con;
    $taxablededuction= $con->myQuery("SELECT employees.`code`,
        receivable_and_taxable_allowances.rta_code,
        receivable_and_taxable_allowances.rta_desc,
        receivable_and_taxable_allowances.rta_amount,
        receivable_and_taxable_allowances.rta_taxable,
        receivable_and_taxable_allowances.rta_type
        FROM employees
        INNER JOIN employee_receivable_and_taxable_allowances ON employee_receivable_and_taxable_allowances.emp_code = employees.`code`
        INNER JOIN receivable_and_taxable_allowances ON employee_receivable_and_taxable_allowances.rta_code = receivable_and_taxable_allowances.rta_code
        WHERE receivable_and_taxable_allowances.rta_taxable = '1' and employees.`code` = ?",array($emp_id))->fetch(PDO::FETCH_ASSOC);

    if(!empty($taxablededuction['rta_amount'])){
        if ($taxablededuction['rta_type'] = 2){ //SEMI-MONTHLY
            return $taxablededuction = ($taxablededuction['rta_amount'] / 2) ;
        } else { //MONTHLY
            return $taxablededuction = $taxablededuction['rta_amount'];
        }
    }

}

function get_payroll_adjustments($emp_id)
{
    global $con;
    $payroll_adjustments= $con->myQuery("SELECT employee_id,SUM(amount) as amount, adjustment_type
        FROM payroll_adjustments 
        WHERE 
        employee_id = ? AND
        status = '0'",array($emp_id))->fetch(PDO::FETCH_ASSOC);

    return $payroll_adjustments;
}


function get_employees_leaves_halfday_without_pay($emp_id, $leave_date) //half day without pay --> leave_id = 0 AND `comment` <> ''
{
    global $con;
    $leaves= $con->myQuery("SELECT * FROM employees_leaves
        WHERE request_status_id = '2' AND
        employee_id = ? AND
        DATE_FORMAT(date_start, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(date_start, '%Y-%m-%d') >= ? AND
        DATE_FORMAT(date_end, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(date_end, '%Y-%m-%d') >= ?AND
        leave_id = 0 AND `comment` <> ''",array($emp_id,$leave_date,$leave_date,$leave_date,$leave_date))->fetchAll(PDO::FETCH_ASSOC);
    return count($leaves);
}

function get_employees_leaves_wholeday_without_pay($emp_id, $leave_date) //whole day without pay --> leave_id = 0 AND `comment` = '
{
    global $con;
    $leaves= $con->myQuery("SELECT * FROM employees_leaves
        WHERE request_status_id = '2' AND
        employee_id = ? AND
        DATE_FORMAT(date_start, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(date_start, '%Y-%m-%d') >= ? AND
        DATE_FORMAT(date_end, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(date_end, '%Y-%m-%d') >= ?AND
        leave_id = 0 AND `comment` = ''",array($emp_id,$leave_date,$leave_date,$leave_date,$leave_date))->fetchAll(PDO::FETCH_ASSOC);
    return count($leaves);
}

function get_employees_leaves_halfday_with_pay($emp_id, $leave_date) //half day with pay --> leave_id > 0 AND `comment` <> ''
{
    global $con;
    $leaves= $con->myQuery("SELECT * FROM employees_leaves
        WHERE request_status_id = '2' AND
        employee_id = ? AND
        DATE_FORMAT(date_start, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(date_start, '%Y-%m-%d') >= ? AND
        DATE_FORMAT(date_end, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(date_end, '%Y-%m-%d') >= ?AND
        leave_id > 0 AND `comment` <> ''",array($emp_id,$leave_date,$leave_date,$leave_date,$leave_date))->fetchAll(PDO::FETCH_ASSOC);
    return count($leaves);
}

function get_employees_leaves_wholeday_with_pay($emp_id, $leave_date) //wholeday with pay --> leave_id > 0 AND `comment` = ''
{
    global $con;
    $leaves= $con->myQuery("SELECT * FROM employees_leaves
        WHERE request_status_id = '2' AND
        employee_id = ? AND
        DATE_FORMAT(date_start, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(date_start, '%Y-%m-%d') >= ? AND
        DATE_FORMAT(date_end, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(date_end, '%Y-%m-%d') >= ?AND
        leave_id > 0 AND `comment` = ''",array($emp_id,$leave_date,$leave_date,$leave_date,$leave_date))->fetchAll(PDO::FETCH_ASSOC);
    return count($leaves);
}

function get_employees_offset_no($emp_id, $of_date)
{
    global $con;
    $offset= $con->myQuery("SELECT no_hours FROM employees_offset_request
        WHERE request_status_id = '2' AND
        employees_id = ? AND
        DATE_FORMAT(start_datetime, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(start_datetime, '%Y-%m-%d') >= ? AND
        DATE_FORMAT(end_datetime, '%Y-%m-%d') <= ? AND
        DATE_FORMAT(end_datetime, '%Y-%m-%d') >= ?",array($emp_id,$of_date,$of_date,$of_date,$of_date))->fetch(PDO::FETCH_ASSOC);
    return $offset;
}
function get_employees_ob_data($emp_id, $ot_date)
{
    global $con;
    $ob= $con->myQuery("SELECT time_from,time_to FROM employees_ob WHERE request_status_id = '2' AND employees_id = ? AND ob_date = ?
        ",array($emp_id, $ot_date))->fetch(PDO::FETCH_ASSOC);
    return $ob;
}

function compute_tax($tax_earning, $tax_comp)
{
    global $con;

    $tax= $con->myQuery("SELECT tax_rate,tax_additional,tax_ceiling FROM taxes WHERE tax_amount_comp >= ? AND tax_ceiling <= ? AND tax_status = ? ORDER BY tax_amount_comp DESC",array(floatval($tax_earning),floatval($tax_earning),$tax_comp))->fetch(PDO::FETCH_ASSOC);
    return $tax;
}

function get_payroll_group_rates($payroll_group_id)
{
    global $con;
    $data=$con->myQuery("SELECT
        pgr.rd_rate,
        pgr.sh_rate,
        pgr.rd_sh_rate,
        pgr.rh_rate,
        pgr.rd_rh_rate,
        pgr.o_ot_rate,
        pgr.rd_ot_rate,
        pgr.sh_ot_rate,
        pgr.rd_sh_ot_rate,
        pgr.rh_ot_rate,
        pgr.rd_rh_ot_rate,
        pgr.n_rate
        FROM payroll_groups pg
        INNER JOIN payroll_group_rates pgr ON pg.payroll_group_id = pgr.payroll_group_id
        WHERE is_deleted = 0 AND pg.payroll_group_id = ?",array($payroll_group_id))->fetch(PDO::FETCH_ASSOC);
    return $data;
}

function get_working_days($shift_id)
{
    global $con;
    $data=$con->myQuery("SELECT monday,tuesday,wednesday,thursday,friday,saturday,sunday FROM employee_working_days WHERE is_deleted = 0 and shift_id = ?",array($shift_id))->fetch(PDO::FETCH_ASSOC);

    return $data;

}

function get_basic_salary($emp_id)
{
    global $con;
    $data=$con->myQuery("SELECT basic_salary FROM employees WHERE is_deleted = 0 AND id = ?",array($emp_id))->fetch(PDO::FETCH_ASSOC);

    return $data;

}

// function getDefaultShift($employee, $date)
// {
//     global $con;
//     $default_shift=$con->myQuery("SELECT id, employee_id, time_in, time_out, beginning_in, beginning_out, ending_in, ending_out, start_date, end_date, break_one_start, break_one_end, break_two_start, break_two_end, break_three_start, break_three_end, working_days FROM employees_default_shifts WHERE employee_id=:employee_id AND IF(:selected_date BETWEEN start_date AND end_date, :selected_date BETWEEN start_date AND end_date, :selected_date>=start_date AND ISNULL(end_date)) LIMIT 1", array("employee_id"=>$employee, "selected_date"=>$date))->fetch(PDO::FETCH_ASSOC);

//     return $default_shift;
// }
function getUndertime($time_in,$half_day="",$for_download=0){
    $late="";


    if(empty($time_in)){
        return  "";
    }



    if($for_download==0){
        $start_date=new DateTime($time_in);
        $late_start=new DateTime($start_date->format("Y-m-d")." ".$half_day['time_out']);
    }
    else{
        $start_date=new DateTime();
        $start_date->setTimeStamp($time_in);

        $late_start=new DateTime($start_date->format("Y-m-d")." ".$half_day['time_out']);
    }


    $interval=$start_date->diff($late_start);



    if(empty($interval->format("%r"))){
        $hours=$interval->format("%h");
        $minutes=$interval->format("%i");

        if(!empty($hours)){
            $late=$hours." Hour/s ";
        }

        $late.=$minutes." Minute/s";
    }
    // echo "<pre>";
    // print_r($late_start);
    // echo "</pre>";
    return $late;
}
function getDefaultShift($employee, $date)
{
    global $con;
    $default_shift=$con->myQuery("SELECT id, employee_id, time_in, time_out, beginning_in, beginning_out, ending_in, ending_out, start_date, end_date, break_one_start, break_one_end, break_two_start, break_two_end, break_three_start, break_three_end, working_days,late_start, grace_minutes FROM employees_default_shifts WHERE employee_id=:employee_id AND IF(:selected_date BETWEEN start_date AND end_date, :selected_date BETWEEN start_date AND end_date, :selected_date>=start_date AND ISNULL(end_date)) LIMIT 1", array("employee_id"=>$employee, "selected_date"=>$date))->fetch(PDO::FETCH_ASSOC);

    return $default_shift;
}
function getShift($employee, $date)
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
        s.working_days,
        s.late_start,
        s.grace_minutes

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
        date_filed AS `date_applied`,
        working_days,
        late_start,
        grace_minutes
        FROM employees_change_shift
        WHERE
        :selected_date BETWEEN date_from AND date_to
        AND :employee_id =employees_id
        AND request_status_id=2
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
function getHolidayOfDay($date, $payroll_group_id)
{
    global $con;
    $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE is_deleted = 0 AND holiday_date=? AND payroll_group_id=?", array($date, $payroll_group_id))->fetch(PDO::FETCH_ASSOC);
    return $holiday;
}

function get_employees_ot($emp_id, $ot_date)
{
    global $con;
    $ot= $con->myQuery("SELECT no_hours FROM employees_ot WHERE request_status_id = '2' AND employees_id = ? AND ot_date = ?
        ",array($emp_id, $ot_date))->fetch(PDO::FETCH_ASSOC);
    return $ot;
}

function get_pay_grade_level($employee_id)
{
    global $con;
    $data= $con->myQuery("SELECT pay_grade.allow_overtime FROM employees INNER JOIN pay_grade ON employees.pay_grade_id = pay_grade.id WHERE employees.id = ?",array($employee_id))->fetch(PDO::FETCH_ASSOC);

    return $data;
}

function getApprovalFlow($department_id) {
    global $con;
    $approval_steps=$con->myQuery("SELECT id,name,step_number FROM approval_steps WHERE is_deleted=0 AND department_id=? ORDER BY step_number", array($department_id))->fetchAll(PDO::FETCH_ASSOC);

    return $approval_steps;
}

function getEmployeesFromSteps($approval_step_id)
{
    global $con;

    return $con->myQuery("SELECT employee_id, CONCAT(last_name,', ',first_name,' ',middle_name)as `employee_name`,private_email,work_email FROM approval_steps_employees ase JOIN employees e ON e.id = ase.employee_id WHERE approval_step_id=?",array($approval_step_id))->fetchAll(PDO::FETCH_ASSOC);
}
function getNextStep($current_step,$request_id,$request_type)
{
    global $con;

    return $con->myQuery("SELECT * FROM request_steps WHERE request_id=:request_id AND step_number > (SELECT step_number FROM request_steps WHERE approval_step_id=:approval_step_id AND request_id =:request_id AND request_type = :request_type LIMIT 1) AND request_type = :request_type LIMIT 1 ", array("request_id"=>$request_id,"approval_step_id"=>$current_step, "request_type"=>$request_type))->fetch(PDO::FETCH_ASSOC);
}
function getPreviousStep($current_step,$request_id,$request_type)
{
    global $con;

    return $con->myQuery("SELECT * FROM request_steps WHERE request_id=:request_id AND step_number > (SELECT step_number FROM request_steps WHERE approval_step_id=:approval_step_id AND request_id =:request_id AND request_type = :request_type LIMIT 1) AND request_type = :request_type LIMIT 1 ", array("request_id"=>$request_id,"approval_step_id"=>$current_step, "request_type"=>$request_type))->fetch(PDO::FETCH_ASSOC);
}
function PHPemailer($username, $password, $from, $to, $subject, $body, $host='tls://smtp.gmail.com', $port=587) {
    require_once("support/PHPMailer/PHPMailerAutoload.php");
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->SMTPSecure = 'tls';
    $mail->Port = $port;

    $mail->setFrom($from);
    foreach ($to as $email) {
        $mail->AddBCC($email);
    }

    $mail->isHTML(true);

    $mail->Subject = $subject;
    $mail->Body    = $body;
    // var_dump($mail->ErrorInfo);
    return $mail->send();
}

function getOvertimePerDayForPayroll($employee_id, $ot_date)
{
    global $con;
    $ots=$con->myQuery("SELECT employees_id,ot_date,no_hours FROM employees_ot WHERE employees_id=? AND ot_date=? AND request_status_id='2'", array($employee_id,$ot_date))->fetch(PDO::FETCH_ASSOC);
    // echo '<pre>';
    // print_r($ots['no_hours']);
    // echo '</pre>';
    return $ots;
}

function AccessForProject($project_id, $employee_id)
{
    global $con;
    return $con->myQuery("SELECT is_team_lead_ba,is_team_lead_dev,is_manager FROM projects_employees WHERE employee_id=? AND project_id=?", array($employee_id,$project_id))->fetch(PDO::FETCH_ASSOC);
}

function getFiledOvertime($date, $time_from, $time_to, $employee_id)
{
    global $con;
    
    return $con->myQuery("SELECT
                    COUNT(id)
                    FROM employees_ot
                    WHERE employees_id = :employee_id
                    AND ot_date = :ot_date
                    AND ((:time_from BETWEEN time_from AND time_to) OR (:time_to BETWEEN time_from AND time_to))
                    AND request_status_id IN (1, 2, 3)", array("ot_date"=>$date, "time_from"=>$time_from, "time_to"=>$time_to, "employee_id"=> $employee_id))->fetchColumn();
}
//-------------------------------------------------------------------------------------------------------------------------------------------//
//-------------------------------------------------------------------------------------------------------------------------------------------//




require_once('class.myPDO.php');
$con=new myPDO('project_monitoring', 'root', '');

if (isLoggedIn()) {
    if (!user_is_active($_SESSION[WEBAPP]['user']['id'])) {
        refresh_activity($_SESSION[WEBAPP]['user']['id']);
        session_destroy();
        session_start();
        Alert("Your account has been deactivated.", "danger");
        redirect('frmlogin.php');
        die;
    }
    if (is_active($_SESSION[WEBAPP]['user']['id'])) {
        refresh_activity($_SESSION[WEBAPP]['user']['id']);
    } else {
            //echo 'You have been inactive.';
            // die;
        refresh_activity($_SESSION[WEBAPP]['user']['id']);
            // die;
            // $con->myQuery("UPDATE users SET is_login=0 WHERE id=?", array($_SESSION[WEBAPP]['user']['id']));
            // session_destroy();
            // session_start();
            // Alert("You have been inactive for 3 minutes and have been logged out.", "danger");
            // redirect('frmlogin.php');
            // die;
    }
}
