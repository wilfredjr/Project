<?php
    require_once("support/config.php");
    if(!isLoggedIn()){
        toLogin();
        die();
    }


    if(!empty($_POST)){
        //Validate form inputs
        $inputs=$_POST;
        $errors="";
        $department_id="";
        if (empty($inputs['step_id'])) {
            redirect('departments.php');
            die;
        } else {

            // $department_id=$con->myQuery("SELECT department_id FROM approval_steps WHERE id =?)",array($inputs['step_id']))->fetchColumn();
        }
        if (empty($inputs['employee_id'])) {
            $errors.="Please select employee. <br/>";
        }

        if($errors!=""){

            Alert("You have the following errors: <br/>".$errors,"danger");
            redirect("approval_flow_employees.php?id=".urlencode($inputs['step_id']));
            die;
        }
        else{
            $con->myQuery("INSERT INTO approval_steps_employees(approval_step_id, employee_id) VALUES(:step_id, :employee_id)",$inputs);
            Alert("Save succesful","success");
            redirect("approval_flow_employees.php?id=".urlencode($inputs['step_id']));
        }
        die;
    }
    else{
        redirect('index.php');
        die();
    }
    redirect('index.php');
?>