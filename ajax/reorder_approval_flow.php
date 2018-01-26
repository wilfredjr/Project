<?php
require_once '../support/config.php';
if(!isLoggedIn()){
    redirect("../frmlogin.php");
    die();
}

$update_steps=json_decode($_REQUEST['post_data']);
foreach ($update_steps as $key => $update_step) {
    // var_dump($update_step);
     $con->myQuery("UPDATE approval_steps SET step_number = ? WHERE id =?", array($update_step->newPosition+1, $update_step->step_id));
}
?>
