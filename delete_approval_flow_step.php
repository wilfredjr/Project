<?php
    require_once 'support/config.php';
    
    if(!isLoggedIn()){
        toLogin();
        die();
    }
   if(!empty($_POST['id'])) {
    $step=$con->myQuery("SELECT department_id,id FROM approval_steps WHERE id=?", array($_POST['id']))->fetch(PDO::FETCH_ASSOC);
    if (!empty($step)) {
        $department_id=$step['department_id'];
        $con->beginTransaction();
        try {
            $con->myQuery("DELETE FROM approval_steps WHERE id=?", array($step['id']));
            $con->myQuery("DELETE FROM approval_steps_employees WHERE approval_step_id=?", array($step['id']));
            /*
            Get the new tep number of the approval_steps
             */
            $steps=$con->myQuery("SELECT id,step_number FROM approval_steps WHERE department_id=? ORDER BY step_number ASC ", array($department_id))->fetchAll(PDO::FETCH_ASSOC);
            /*
            Loop using for then update using the counter
            EZ
             */
            foreach ($steps as $key => $step_record) {
                $con->myQuery("UPDATE approval_steps SET step_number=? WHERE id=?", array($key+1, $step_record['id']));
            }
            $con->commit();
        } catch (Exception $e) {
            $con->rollback();
        }

        Alert("Delete Successful.","success");
        redirect("approval_flow.php?dep_id=".$department_id);
        die;
    } else {
        redirect("departments.php");
        die;
    }
   } else {
    redirect("departments.php");
    die;
   }
?>