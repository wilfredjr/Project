<?php
require_once '../support/config.php';

if (!isLoggedIn()) {
    toLogin();
    die();
}


if (!empty($_POST)) {
    //Validate form inputs
    $inputs=$_POST;
    $inputs=array_map('trim', $inputs);
    $errors="";

    if (empty($inputs['emp_loan_id'])) {
        $errors.="<li>Invalid loan selected.</li>";
    } else {
        // $pass_loan=$con->myQuery("SELECT COUNT(emp_loan_pass_id) FROM emp_loan_pass WHERE emp_loan_pass_id=?", array($inputs['emp_loan_id']))->fetchColumn();
        
        // if (empty($pass_loan)) {
        //     $errors.="<li>Invalid loan selected.</li>";
        // }
    }

    if (empty($inputs['reason'])) {
        $errors.="<li>Please enter reason.</li>";
    }

    if (empty($inputs['effective_date'])) {
        $errors.="<li>Invalid effective date entered.</li>";
    } else {
        $effective_date=new DateTime($inputs['effective_date']);
        $already_entered=$con->myQuery("SELECT date_applied FROM emp_loan_pass WHERE emp_loan_id=? AND date_applied=?", array($inputs['emp_loan_id'], $effective_date->format("Y-m-d")))->fetchColumn();
        if (!empty($already_entered)) {
            $errors.="<li>Effective Date already passed.</li>";
        } else {
            $inputs['effective_date']=$effective_date->format("Y-m-d");
        }
    }

    if (!empty($errors)) {
        $errors="You have the following errors: <br/><ul>".$errors."</ul>";
        alert($errors, "danger");
        redirect("loan_payment_history.php?id=".$inputs['emp_loan_id']);
        die;
    } else {
        // var_dump($inputs);
        $con->myQuery("INSERT INTO emp_loan_pass(emp_loan_id, reason, filed_date, `date_applied`) VALUES(:emp_loan_id, :reason, DATE(NOW()), :effective_date)", $inputs);
        // die;
        Alert("Save Successful.", "success");
        redirect("loan_payment_history.php?id=".$inputs['emp_loan_id']);
        die;
    }

    redirect("loan_payment_history.php?id=".$inputs['emp_loan_id']);
} else {
    redirect("index.php");
}
