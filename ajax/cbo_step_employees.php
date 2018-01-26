<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
        redirect('../index.php');
        die();
}
$step_id="";
$step_id=$_GET['step_id'];
    // var_dump($_GET);
if (!empty($_GET['term'])) {
        $term=$_GET['term']."%";
        $count=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name) AS description FROM employees WHERE (first_name LIKE :term OR middle_name LIKE :term OR last_name LIKE :term)  AND is_deleted=0 AND id NOT IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id=:step_id) ORDER BY last_name", array("term"=>$term, "step_id"=>$step_id))->fetchAll(PDO::FETCH_ASSOC);
} else {
        $count=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name) AS description FROM employees WHERE is_deleted=0 AND id NOT IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id=:step_id) ORDER BY last_name",array("step_id"=>$step_id))->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($count);
