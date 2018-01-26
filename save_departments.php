<?php
    require_once("support/config.php");
    if (!isLoggedIn()) {
        toLogin();
        die();
    }

    if (!AllowUser(array(1,4))) {
        redirect("index.php");
    }


        if (!empty($_POST)) {
            //Validate form inputs
        $inputs=$_POST;
        
        if (empty($inputs['parent_id'])) {
            $inputs['parent_id']="";
        }
            $inputs=array_map('trim', $inputs);
            $errors="";
            if (empty($inputs['name'])) {
                $errors.="<li>Enter Department Name. </li>";
            }
            if (empty($inputs['description'])) {
                $errors.="<li>Enter Description. </li>";
            }
            // if (empty($inputs['approver_id'])) {
            //     $errors.="<li>Select Departent Approver. </li>";
            // }

            if (empty($inputs['paygroup_id'])) {
                $errors.="<li>Select Pay Group. </li>";
            }


            if ($errors!="") {
                Alert("You have the following errors: <br/><ul>".$errors."<ul>", "danger");
                if (empty($inputs['id'])) {
                    redirect("frm_departments.php");
                } else {
                    redirect("frm_departments.php?id=".urlencode($inputs['id']));
                }
                die;
            } else {
                //IF id exists update ELSE insert
            if (empty($inputs['id'])) {
                //Insert
                unset($inputs['id']);
                
                $con->myQuery("INSERT INTO departments(name,description,parent_id,payroll_group_id) VALUES(:name,:description,:parent_id,:paygroup_id)", $inputs);
            } else {
                //Update
                var_dump($inputs);

                $con->myQuery("UPDATE departments SET name=:name,description=:description,parent_id=:parent_id,payroll_group_id=:paygroup_id WHERE id=:id", $inputs);
            }
                Alert("Save succesful", "success");
                redirect("departments.php");
            }
            die;
        } else {
        redirect('index.php');
        die();
    }
    redirect('index.php');
