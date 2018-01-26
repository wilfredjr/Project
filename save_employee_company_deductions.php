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
        // echo "<pre>";
        // print_r($inputs);
        // echo "</pre>";
        // die;

        if (empty($inputs['employee_id'])) {
            Modal("Invalid Record Selected");
            redirect("employees.php");
        }

            $required_fieds=array(
            "company_deduction_id"=>"<li>Select Company Deduction. </li>",
            "deduction_type_id"=>"<li>Select Deduction Type. </li>",
            "start_date"=>"<li>Select Start Date.</li>",
            "end_date"=>"<li>Select End Date.</li>",
            "amount"=>"<li>Enter Amount</li>"
            );
            $errors="";

            foreach ($required_fieds as $key => $value) {

                if (empty($inputs[$key])) {
                    $errors.=$value;
                } else {
                    #CUSTOM VALIDATION
                    if($key=="amount"){
                        if(empty(floatval($inputs[$key]))){
                            $errors.="<li>Invalid Amount entered.</li>";
                        }
                    }
                }
            }


            $tab=12;

            try {
              $start_date=new DateTime($inputs['start_date']);
              $end_date=new DateTime($inputs['end_date']);
            } catch (Exception $e) {
              $errors.="Invalid Date Format";
            }

            if ($end_date<$start_date) {
                $errors.="<li>Start Date cannot be greater than End Date.</li>";
            }

            if ($errors!="") {
                Alert("You have the following errors: <br/><ul>".$errors."</ul>", "danger");
                if (empty($inputs['id'])) {
                    redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}");
                } else {
                    redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}&ee_id={$inputs['id']}");
                }
                die;
            } else {



            //IF id exists update ELSE insert
            $inputs['start_date']=$start_date->format("Y-m-d");
            $inputs['end_date']=$end_date->format("Y-m-d");
            $emp=getEmpDetails($inputs['employee_id']);
            $inputs['emp_code']=$emp['code'];

                if (empty($inputs['id'])) {
                    //Insert
                unset($inputs['id']);

                    $con->myQuery("INSERT INTO employee_company_deductions(
                    emp_code,
                    comde_code,
                    emp_comde_amt,
                    emp_comde_start_date,
                    emp_comde_end_date,
                    emp_deduct_type,
                    emp_id
                    ) VALUES(
                    :emp_code,
                    :company_deduction_id,
                    :amount,
                    :start_date,
                    :end_date,
                    :deduction_type_id,
                    :employee_id
                    )", $inputs);



                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], " Added ({$inputs['company_deduction_id']}) Company Deduction to ({$emp['last_name']}, {$emp['first_name']} {$emp['middle_name']}).");
                } else {
                    //Update

                $con->myQuery("UPDATE employee_company_deductions SET
                    emp_code=:employee_id,
                    comde_code=:company_deduction_id,
                    emp_comde_amt=:amount,
                    emp_comde_start_date=:start_date,
                    emp_comde_end_date=:end_date,
                    emp_deduct_type=:deduction_type_id,
                    emp_id=:employee_id

                    WHERE id=:id
                    ", $inputs);

                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], " Modified ({$inputs['company_deduction_id']}) Company Deduction of ({$emp['last_name']}, {$emp['first_name']} {$emp['middle_name']}).");
                }

                Alert("Save succesful", "success");
                redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}");
            }
            die;
        } else {
        redirect('index.php');
        die();
    }
    redirect('index.php');
