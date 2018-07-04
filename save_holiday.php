<?php
    require_once("support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}

if (!AllowUser(array(4))) {
         redirect("index.php");
}


if (!empty($_POST)) {
        //Validate form inputs
        $inputs=$_POST;
        $inputs=array_map("trim",$inputs);
        $errors="";
    if (empty($inputs['holiday_name'])) {
            $errors.="<li>Enter Holiday Name. </li>";
    }

    if (empty($inputs['holiday_date'])) {
            $errors.="<li>Enter Holiday Date. </li>";
    }

    if ($errors!="") {
        Alert("You have the following errors: <br/><ul>".$errors."</ul>", "danger");
        if (empty($inputs['id'])) {
            redirect("frm_holiday.php");
        } else {
            redirect("frm_holiday.php?id=".urlencode($inputs['id']));
        }
        die;
    } else {
        //IF  id exists update ELSE insert
        try {
            $date=new DateTime($inputs['holiday_date']);
            $inputs['holiday_day']=$date->format("l");
            $inputs['holiday_date']=$date->format("Y-m-d");
            var_dump($_POST);
            if (empty($inputs['id'])) {
                //Insert
                unset($inputs['id']);
                
                $con->myQuery("INSERT INTO holidays(holiday_name,holiday_date,holiday_day) VALUES(:holiday_name,:holiday_date,:holiday_day)", $inputs);
                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "Created new holiday.");
            } else {
                //Update
                
                $con->myQuery("UPDATE holidays SET holiday_name=:holiday_name,holiday_date=:holiday_date,holiday_day=:holiday_day WHERE id=:id", $inputs);
                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "Modified holiday with an id of {$inputs['id']}.");
            }
            Alert("Save succesful", "success");
            redirect("holidays.php");
        } catch (Exception $e) {
            Alert("Save Failed", "danger");
            redirect("holidays.php");
        }
    }
    die;
} else {
        redirect('index.php');
        die();
}
    redirect('index.php');
