<?php
    require_once("support/config.php");
     if(!isLoggedIn()){
        toLogin();
        die();
     }


        if(!empty($_POST)){
        //Validate form inputs
        $inputs=$_POST;
        if (empty($inputs['department_id'])) {
            redirect("departments.php");
            die;
        }
        $required_fieds=array(
            "step_name"=>"Enter Step Name. <br/>"
            );
        $errors="";

        foreach ($required_fieds as $key => $value)
        {
            if(empty($inputs[$key]))
            {
                $errors.=$value;
            }else
            {
                #CUSTOM VALIDATION
            }
        }

        if($errors!="")
        {
            Alert("You have the following errors: <br/>".$errors,"danger");
            redirect("approval_flow?dep_id={$inputs['department_id']}.php");
            die;
        }
        else
        {
            $department_id=$inputs['department_id'];
            if (empty($inputs['id'])) {
                /*
                GET step number;
                 */
                unset($inputs['id']);
                $cur_step_number=$con->myQuery("SELECT IFNULL(MAX(step_number),0) step_number FROM approval_steps WHERE department_id=?", array($inputs['department_id']))->fetchColumn();
                $cur_step_number++;
                $con->myQuery("INSERT INTO approval_steps(department_id, name, step_number) SELECT :department_id, :name, IFNULL(MAX(step_number),0)+1 as step_number FROM approval_steps WHERE department_id=:department_id", array(
                        "department_id"=>$inputs['department_id'],
                        "name"=>$inputs['step_name']
                    ));
            } else {
                // var_dump($inputs);
                unset($inputs['department_id']);
                $con->myQuery("UPDATE approval_steps SET name=:step_name WHERE id =:id",$inputs);
            }
            // die;
            Alert("Save Succesful","success");
            redirect("approval_flow.php?dep_id=".$department_id);
            die;
        }
        die;
    }
    else{
        redirect('index.php');
        die();
    }
    redirect('index.php');
?>