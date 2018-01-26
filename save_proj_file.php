<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }

     if(!AllowUser(array(2,4))){
         redirect("index.php");
     }
		//Validate form inputs
		//$inputs=$_POST;
		// echo "<pre>";
		// print_r($inputs);
		// print_r($_FILES);
		// echo "</pre>";
		//die;
		if(empty($_FILES['file']['name'])){
			Alert("No file selected.","danger");
			redirect("my_projects_view.php?id=".$_POST['id']."&tab=5");
			die();
		}
		elseif($_FILES['file']['error']<>0){
			Alert("Invalid file selected.","danger");
			redirect("my_projects_view.php?id=".$_POST['id']."&tab=5");
			die;
		}
		try {
	     $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
		$con->beginTransaction();
		$inputs['file_name']=$_FILES['file']['name'];
		$project_id=$_POST['id'];
		$project_phase_id=$_POST['phase_id'];
		$con->myQuery("INSERT INTO project_files(file_name,date_modified,employee_id,project_id,project_phase_id) VALUES(:file_name,NOW(),'$employee_id','$project_id','$project_phase_id')",$inputs);
		$file_id=$con->lastInsertId();

		$filename=$file_id.getFileExtension($_FILES['file']['name']);
		move_uploaded_file($_FILES['file']['tmp_name'],"proj_files/".$filename);
		$con->myQuery("UPDATE project_files SET file_location=? WHERE id=?",array($filename,$file_id));
		Alert("File Added","success");
		
		
		insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Uploaded ({$inputs['file_name']}) to project files.");


		$con->commit();
		redirect("my_projects_view.php?id=".$project_id."&tab=5");
		die;
		} catch (Exception $e) {
		  $con->rollBack();
//		  echo "Failed: " . $e->getMessage();
		  Alert("Upload failed. Please try again.","danger");
		  redirect("my_projects_view.php?id=".$project_id."&tab=5");
		  die;
		}
	
	// redirect('index.php');
?>