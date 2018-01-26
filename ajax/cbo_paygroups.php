<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}
    
    // var_dump($_GET);
if (!empty($_GET['term'])) {
        $term=$_GET['term']."%";
        $count=$con->myQuery("SELECT payroll_group_id as id,name AS description FROM payroll_groups WHERE name LIKE :term AND is_deleted=0 ORDER BY name", array("term"=>$term))->fetchAll(PDO::FETCH_ASSOC);
} else {
        $count=$con->myQuery("SELECT payroll_group_id as id,name AS description FROM payroll_groups WHERE is_deleted=0 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($count);
