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
        $count=$con->myQuery("SELECT eal.leave_id as id,
                                  CONCAT((SELECT NAME FROM LEAVES WHERE id=eal.leave_id AND NAME LIKE :term),' (',eal.balance_per_year,' day/s left)') AS description
                            FROM employees_available_leaves eal
                            WHERE is_cancelled=0 AND is_deleted=0 AND employee_id=:employee_id", array("employee_id"=>$_GET['emp_id'], "term"=>$term))->fetchAll(PDO::FETCH_ASSOC);
} else {
        $count=$con->myQuery("SELECT eal.leave_id as id,
                                  CONCAT((SELECT NAME FROM LEAVES WHERE id=eal.leave_id),' (',eal.balance_per_year,' day/s left)') AS description
                            FROM employees_available_leaves eal
                            WHERE is_cancelled=0 AND is_deleted=0 AND employee_id=:employee_id", array("employee_id"=>$_GET['emp_id']))->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($count);
