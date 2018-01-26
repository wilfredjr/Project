<?php
    require_once 'support/config.php';
    
    if(!isLoggedIn()){
        toLogin();
        die();
    }
   if(!empty($_POST['id'])) {
    $department_id=$con->myQuery("SELECT approval_step_id FROM approval_steps_employees WHERE id=?", array($_POST['id']))->fetchColumn();
    if (!empty($department_id)) {
        $con->myQuery("DELETE FROM approval_steps_employees WHERE id=?", array($_POST['id']));
        
        Alert("Delete Successful.","success");
        redirect("approval_flow_employees.php?id=".$department_id);
        die;
    } else {
        redirect("departments.php");
        die;
    }
   } else {
    redirect("departments.php");
    die;
   }
?>