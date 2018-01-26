<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}

    

    // $count=$con->myQuery("SELECT e.id,CONCAT(last_name,', ',first_name,' ',middle_name) AS description FROM employees e WHERE (first_name LIKE :term OR middle_name LIKE :term OR last_name LIKE :term)  AND is_deleted=0 AND is_terminated=0 WHERE e.id IN (SELECT employee_id FROM projects_employees WHERE project_id = :project_id)", array("project_id"=>$_GET['project_id'], "term"=>$term))->fetchAll(PDO::FETCH_ASSOC);
    // echo json_encode($count);

    // die;
if (!empty($_GET['term'])) {
        $term=$_GET['term']."%";
        $count=$con->myQuery("SELECT e.id,CONCAT(last_name,', ',first_name,' ',middle_name) AS description FROM employees e WHERE (first_name LIKE :term OR middle_name LIKE :term OR last_name LIKE :term)  AND is_deleted=0 AND is_terminated=0 AND e.id IN (SELECT employee_id FROM projects_employees WHERE project_id = :project_id)", array("project_id"=>$_GET['project_id'], "term"=>$term))->fetchAll(PDO::FETCH_ASSOC);
} else {
        $count=$con->myQuery("SELECT e.id,CONCAT(last_name,', ',first_name,' ',middle_name) AS description FROM employees e WHERE is_deleted=0 AND is_terminated=0 AND e.id IN (SELECT employee_id FROM projects_employees WHERE project_id = :project_id AND (designation_id=:des_id OR designation_id=3))", array("project_id"=>$_GET['project_id'],"des_id"=>$_GET['des_id']))->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($count);
