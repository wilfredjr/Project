<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}
    
    // var_dump($_GET);
if (!empty($_GET['term'])) {
        $term=$_GET['term']."%";
        $count=$con->myQuery("SELECT p.id,p.name as description FROM projects p WHERE  is_deleted=0 AND p.name LIKE :term ORDER BY p.name DESC", array("term"=>$term))->fetchAll(PDO::FETCH_ASSOC);
} else {
        $count=$con->myQuery("SELECT p.id,p.name as description FROM projects p WHERE  is_deleted=0 ORDER BY p.name DESC")->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($count);
