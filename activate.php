<?php
	require_once 'support/config.php';
	
	if(!isLoggedIn()){
		toLogin();
		die();
	}
	if(!AllowUser(array(1,4))){
        redirect("index.php");
        die;
    }

    if(!empty($_GET['id'])){
    	$con->myQuery("UPDATE users SET  is_active = IF(is_active=1, 0,1) WHERE id = ?",array($_GET['id']));
    	Alert("Change Successful","success");
    	redirect("users.php");
    	die;
    }
	
?>