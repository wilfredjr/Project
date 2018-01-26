<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}
    
    // var_dump($_GET);
if (!empty($_GET['term'])) {
        $term=$_GET['term']."%";
        $count=$con->myQuery("SELECT p.id,p.name as description FROM projects p WHERE  is_deleted=0 AND (manager_id = :employee_id OR p.id IN (SELECT project_id FROM projects_employees WHERE (is_manager= 1 AND projects_employees.employee_id=:employee_id) OR projects_employees.employee_id=:employee_id)) AND p.name LIKE :term ORDER BY p.name DESC", array("term"=>$term, "employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
} else {
        $count=$con->myQuery("SELECT p.id,p.name as description FROM projects p WHERE  is_deleted=0 AND (manager_id = :employee_id OR p.id IN (SELECT project_id FROM projects_employees WHERE (is_manager= 1 AND projects_employees.employee_id=:employee_id) OR projects_employees.employee_id=:employee_id)) ORDER BY p.name DESC", array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($count);
