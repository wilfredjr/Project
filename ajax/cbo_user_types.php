<?php
require_once("../support/config.php"); 
if(!isLoggedIn()){
		toLogin();
		die();
	}
if(!empty($_GET['term'])){
	$users=$con->myQuery("SELECT id, description FROM user_type WHERE description LIKE ? ORDER BY description",array($_GET['term']."%"))->fetchAll(PDO::FETCH_ASSOC);
}
else{
	$users=$con->myQuery("SELECT id, description FROM user_type ORDER BY description")->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($users);
