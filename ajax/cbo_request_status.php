<?php
require_once("../support/config.php");
if (!isLoggedIn()) {
        toLogin();
        die();
}
    
    // var_dump($_GET);
if (!empty($_GET['term'])) {
        $term=$_GET['term']."%";
        $count=$con->myQuery("SELECT id,name AS description FROM request_status WHERE name LIKE :term ORDER BY name ", array("term"=>$term))->fetchAll(PDO::FETCH_ASSOC);
} else {
        $count=$con->myQuery("SELECT id,name AS description FROM request_status ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($count);
