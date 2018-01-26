<?php
    require_once 'support/config.php';
    
if (!isLoggedIn()) {
    toLogin();
    die();
}
if (!AllowUser(array(1,4))) {
    redirect("index.php");
}
if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
    redirect('index.php');
    die;
} else {
    $selected_shift=$con->myQuery("SELECT id,employee_id, time_in, time_out, beginning_in, beginning_out, ending_in, ending_out, start_date, end_date FROM employees_default_shifts WHERE id=?", array($_POST['id']))->fetch(PDO::FETCH_ASSOC);
    if (empty($selected_shift)) {
        Alert("Invalid Record Selected.<br/>");
        redirect("frm_employee.php");
        die;
    }
    
    /*
    Get Next and previous rows of the shift
     */
    $previous_record=$con->myQuery("(SELECT * FROM employees_default_shifts WHERE start_date < :start_date AND employee_id=:employee_id ORDER BY start_date DESC LIMIT 1)", array("start_date"=>$selected_shift['start_date'], "employee_id"=>$selected_shift['employee_id']))->fetch(PDO::FETCH_ASSOC);
    $next_record=$con->myQuery("(SELECT * FROM employees_default_shifts WHERE start_date > :start_date AND employee_id=:employee_id LIMIT 1)", array("start_date"=>$selected_shift['start_date'], "employee_id"=>$selected_shift['employee_id']))->fetch(PDO::FETCH_ASSOC);
    /*
        Check if the shift is active, the first default shift, or middle
     */
    if (empty($selected_shift['end_date'])) {
        /*
        Currently Active
         */
        /*
        set previous record to active by set end_date to NULL
         */
        $con->myQuery("UPDATE employees_default_shifts SET end_date=NULL WHERE id=?", array($previous_record['id']));
    } else {
        if (empty($previous_record)) {
            /*
            The first default shift
             */
            /*
            Set start_date of next shift to the start_date of the selected date
             */
            $con->myQuery("UPDATE employees_default_shifts SET start_date=? WHERE id=?", array($selected_shift['start_date'], $next_record['id']));
        } elseif (!empty($previous_record) && !empty($next_record)) {
            /*
            middle has previous and next
             */
            /*
            Set the previous record's end date to the start date of the next record -1 day
             */
            $new_end_date=new DateTime($next_record['start_date']);
            $new_end_date->modify('-1 day');
            $con->myQuery("UPDATE employees_default_shifts SET end_date=? WHERE id=?", array($new_end_date->format("Y-m-d"), $previous_record['id']));
        }
    }

     $con->myQuery("DELETE FROM employees_default_shifts WHERE id=?", array($selected_shift['id']));
     $employee=getEmpDetails($selected_shift['employee_id']);
    $audit_message="Deleted a default shift of {$employee['last_name']}, {$employee['first_name']} {$employee['middle_name']}";
    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], $audit_message);
    Alert("Delete Successful.", "success");
    redirect("frm_employee.php?id={$_POST['employee_id']}&tab=13");

    die();
}
