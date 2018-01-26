<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}
    
    // var_dump($_GET);
if (!empty($_GET['term'])) {
        $term=$_GET['term']."%";
        $count=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name) AS description FROM employees WHERE (first_name LIKE :term OR middle_name LIKE :term OR last_name LIKE :term)  AND is_deleted=0 AND is_terminated=0 AND (supervisor_id=:employee_id OR id =:employee_id) ORDER BY last_name", array("term"=>$term, "employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
} else {
        $count=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name) AS description FROM employees WHERE is_deleted=0 AND is_terminated=0 AND (supervisor_id=:employee_id OR id =:employee_id) ORDER BY last_name", array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($count);
