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
        

            if (empty($inputs['employee_id'])) {
                Modal("Invalid Record Selected");
                redirect("employees.php");
            }

        
            $tab=11;
        
            $errors="";

            if ($errors!="") {
                Alert("You have the following errors: <br/>".$errors, "danger");
                if (empty($inputs['id'])) {
                    redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}");
                } else {
                    redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}&ee_id={$inputs['id']}");
                }
                die;
            } else {
                // echo "<pre>";
            // print_r($inputs);
            // echo "</pre>";
            // die;
            
            //IF id exists update ELSE insert
            

            $con->myQuery("DELETE FROM employee_receivable_and_taxable_allowances WHERE emp_id=?",array($inputs['employee_id']));
            if(!empty($inputs['dmb_code'])){
                foreach ($inputs['dmb_code'] as $dmb_code) {
                    $con->myQuery("INSERT INTO employee_receivable_and_taxable_allowances(emp_id,rta_code,emp_code) VALUES(?,?,?)",array($inputs['employee_id'],$dmb_code,$inputs['emp_code']));
                }
                }
            }
                    $emp=getEmpDetails($inputs['employee_id']);
                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], " Modified Receivable and Taxable Allowances of ({$emp['last_name']}, {$emp['first_name']} {$emp['middle_name']}).");
                    Alert("Save succesful", "success");
                    redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}");
                    die;
            
        } else {
        redirect('index.php');
        die();
    }
    redirect('index.php');
