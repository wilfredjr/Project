<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}
    
    // var_dump($_GET);
if (!empty($_GET['term'])) {
        $term=$_GET['term']."%";
        $count=$con->myQuery("SELECT id,CONCAT(name,' (',(SELECT name FROM payroll_groups WHERE payroll_group_id=departments.payroll_group_id),')') AS description FROM departments WHERE name LIKE :term AND is_deleted=0 ORDER BY name ", array("term"=>$term))->fetchAll(PDO::FETCH_ASSOC);
} else {
        $count=$con->myQuery("SELECT id,CONCAT(name,' (',(SELECT name FROM payroll_groups WHERE payroll_group_id=departments.payroll_group_id),')') AS description FROM departments WHERE is_deleted=0 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($count);
