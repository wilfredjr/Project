<?php
    require_once("support/config.php");
     if(!isLoggedIn()){
        toLogin();
        die();
     }


        if(!empty($_POST)){
        //Validate form inputs
        var_dump($inputs);
        die;
        $inputs=$_POST;
        $required_fieds=array(
            "worked_done"=>"Enter Worked Done. <br/>"
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
            redirect("frm_overtime_request.php");
            die;
        }
        else
        {
        }
        die;
    }
    else{
        redirect('index.php');
        die();
    }
    redirect('index.php');
?>