<?php
    require_once("support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}

if (!AllowUser(array(1,4))) {
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

    if (empty($inputs['holiday_category'])) {
            $errors.="<li>Select Holiday Category. </li>";
    }

    if ($errors!="") {
        Alert("You have the following errors: <br/><ul>".$errors."</ul>", "danger");
        if (empty($inputs['id'])) {
            redirect("frm_general_holiday.php");
        } else {
            redirect("frm_general_holiday.php?id=".urlencode($inputs['id']));
        }
        die;
    } else {
        //IF  id exists update ELSE insert
        try {
            $date=new DateTime($inputs['holiday_date']);
            $inputs['holiday_date']=$date->format("Y-m-d");
            if (empty($inputs['id'])) {
                //Insert
                unset($inputs['id']);
                
                $con->myQuery("INSERT INTO default_holidays(holiday_name,holiday_date,holiday_category) VALUES(:holiday_name,:holiday_date,:holiday_category)", $inputs);
                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "Created new general holiday.");
            } else {
                //Update
                
                $con->myQuery("UPDATE default_holidays SET holiday_name=:holiday_name,holiday_date=:holiday_date,holiday_category=:holiday_category WHERE id=:id", $inputs);
                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "Modified general holiday with an id of {$inputs['id']}.");
            }
            
            Alert("Save succesful", "success");
            redirect("general_holidays.php");
        } catch (Exception $e) {
            Alert("Save Failed", "danger");
            redirect("general_holidays.php");
        }
    }
    die;
} else {
        redirect('index.php');
        die();
}
    redirect('index.php');
