<?php
require_once("support/config.php");
if (!AllowUser(array(1,4))) {
    redirect("index.php");
}

if (!empty($_POST['id'])) {
    $employee=$con->myQuery("SELECT * FROM employees WHERE id=? LIMIT 1", array($_POST['id']))->fetch(PDO::FETCH_ASSOC);
    if (!empty($employee)) {
        $con->myQuery("UPDATE employees SET is_regular=1, regularization_date=NOW() WHERE id = ?", array($employee['id']));
        Alert("Save Successful.", "success");
        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Regularized ({$employee['last_name']}, {$employee['first_name']} {$employee['middle_name']}) .");
        redirect("for_regularization.php");
        die;
    } else {
        Alert("Invalid Employee", "danger");
        redirect("for_regularization.php");
        die;
    }
}
