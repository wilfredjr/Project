<?php
require_once("../support/config.php");

if(!isLoggedIn()){
    redireect("../frmlogin.php");
    die();
}

if (!empty($_GET['id'])) {
    $offset_count=$con->myQuery("SELECT offset_count FROM employees_offset WHERE employees_id=?", array($_GET['id']))->fetchColumn();
    echo intval($offset_count);
} else {
    echo 0;
}