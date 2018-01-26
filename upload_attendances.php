<?php
require_once("support/config.php");
if (!isLoggedIn()) {
    toLogin();
    die();
}
/**
 * Insert DTR Row to acu_records table
 * @param array
 * [A] ac_no *
 * [B] acu_id
 * [C] name
 * [D] action_time*
 * [E] state*
 * [F] new_state
 * [G] exception
 * [H] operation
 */
function InsertDTR($sheetRow)
{
    global $con;
    $inputs=array();
    $start_column="A";
    for ($i=0; $i <= 7; $i++) {
        if (empty($sheetRow[$start_column])) {
            $inputs[$i]="";
        } else {
            $inputs[$i]=$sheetRow[$start_column];
        }
        $start_column++;
    }
    $if_exists=$con->myQuery(
        "SELECT COUNT(id) FROM acu_records WHERE ac_no=? AND action_time=? AND state=?",
        array(
            $sheetRow["A"],
            $sheetRow["D"],
            $sheetRow["E"]
            )
    )->fetchColumn();

    if (empty($if_exists)) {
        $con->myQuery("INSERT INTO acu_records(ac_no,acu_id,name,action_time,state,new_state,exception,operation) VALUES(?,?,?,?,?,?,?,?)", $inputs);
    }
}
/**
 * Gets the shifts/s of the employee
 * @param  DateTime $date
 * @param  bigint $employee_id
 * @return array               Array of shifts
 */
function getEmployeeShiftByDate($date, $employee_id)
{
    global $con;
    $shifts=array();
    /*
    Add Filter based on employee id
     */
    // $change_shifts=$con->myQuery("SELECT id,shift_name,time_in,time_out,beginning_in,beginning_out,ending_in,ending_out FROM shifts LIMIT 1")->fetchAll(PDO::FETCH_ASSOC);
    // if (!empty($change_shifts)) {
    //     $shifts=$change_shifts;
    // }
    /*
    Get Default Sheep of the employee
     */
    $default_shift=$con->myQuery("SELECT id,'Default Shift' as shift_name,time_in,time_out,beginning_in,beginning_out,ending_in,ending_out FROM employees_default_shifts WHERE (:date BETWEEN start_date AND end_date) OR (:date>= start_date AND ISNULL(end_date)) ORDER BY start_date DESC LIMIT 1", array("date"=>$date->format('Y-m-d')))->fetch(PDO::FETCH_ASSOC);
    if (!empty($default_shift)) {
        array_push($shifts, $default_shift);
    }

    return $shifts;
}

$errors="";

if (!empty($_POST['company_id'])) {
    $payroll_group=$con->myQuery("SELECT payroll_group_id,name FROM payroll_groups WHERE payroll_group_id=? LIMIT 1", array($_POST['company_id']))->fetch(PDO::FETCH_ASSOC);

    if (empty($payroll_group)) {
        $errors.="<li>Invalid Pay Group selected.</li>";
    }
} else {
    $errors.="<li>No paygroup selected.</li>";
}

if (!empty($_FILES)) {
    /*
        Validate Uploaded File
     */
    if (in_array(getFileExtension($_FILES['file']['name']), array(".xls",".xlsx"))==false) {
        $errors.="Invalid File type.(Please upload only files with .xls or .xlsx extension.)<br/>";
    } elseif (!empty($_FILES['file']['error'])) {
        switch ($_FILES['file']['error']) {
            case 1:
                $errors.="<li>Exceeded upload size.</li>";
                break;
            case 2:
                $errors.="<li>Exceeded upload size.</li>";
                break;
            case 3:
                $errors.="<li>Upload did not complete.</li>";
                break;
            case 4:
                $errors.="<li>No file uploaded.</li>";
                break;
        }
    } else {
        require_once("support/PHPExcel.php");
            
        $objPHPExcel=PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
        array_shift($sheetData);
        if (empty($sheetData)) {
            $errors.="<li>Excel data is empty.</li>";
        }
    }
} else {
    $errors.="<li>No file uploaded.</li>";
}

if (!empty($errors)) {
    Alert("You have the followng errors: <ul>".$errors."</ul>", "danger");
    redirect("frm_upload_attendances.php");
    die;
} else {
    $con->beginTransaction();
    try {
        foreach ($sheetData as $key => $row) {
            InsertDTR($row);
            $employee=$con->myQuery(
                "SELECT id FROM employees WHERE acu_id=:acu_id AND payroll_group_id=:payroll_group_id LIMIT 1",
                array(
                    "acu_id"=>$row['A'],
                    "payroll_group_id"=>$payroll_group['payroll_group_id']
                    )
            )->fetch(PDO::FETCH_ASSOC);
            /*
            V0.3
             */
            $row_date=new DateTime($row['D']);
            if ($row['E']=="C/In") {
                /*
                Insert New Record
                 */
                $con->myQuery(
                    "INSERT INTO attendance(employees_id,in_time) VALUES(:employee_id,:in_time)",
                    array(
                        "employee_id"=>$employee['id'],
                        "in_time"=>$row_date->format('Y-m-d H:i:s')
                        )
                );
            } elseif ($row['E']=="C/Out") {
                /*
                Update Last attendance without timeout or update timeout
                 */
                $last_attendance=$con->myQuery("SELECT * FROM attendance WHERE employees_id=? ORDER BY in_time DESC LIMIT 1", array($employee['id']))->fetch(PDO::FETCH_ASSOC);
                /*
                if empty last attendance create new record
                 */
                if (empty($last_attendance)) {
                    $con->myQuery(
                        "INSERT INTO attendance(employee_id,in_time) VALUES(:employee_id,:in_time)",
                        array(
                            "employee_id"=>$employee['id'],
                            "in_time"=>$row_date['Y-m-d H:i:s']
                            )
                    );
                } else {
                    /*
                    Update outtime if empty or greater than current out time
                     */
                    $last_attendance['in_time']=new DateTime($last_attendance['in_time']);
                    if (empty($last_attendance['out_time']) || $last_attendance['out_time']="0000-00-00 00:00:00") {
                        $con->myQuery("UPDATE attendance SET out_time=? WHERE id=?", array($row_date->format('Y-m-d H:i:s'),$last_attendance['id']));
                    } else {
                        $last_attendance['out_time']=new DateTime($last_attendance['out_time']);
                        /*
                        Update if row time is greater thant current out time
                         */
                        if ($row_date>$last_attendance['out_time']) {
                            $con->myQuery("UPDATE attendance SET out_time=? WHERE id=?", array($row_date['Y-m-d H:i:s']));
                        }
                    }
                }
            }
            /*
            V0.2
             */
            // $row_date=new DateTime($row['D']);
            // /*
            // Get Shift based on the date of the row of the employee
            //  */
            // $shift=getEmployeeShiftByDate($row_date, $employee['id']);
            // var_dump($shift);
            // if (empty($shift)) {
            //     continue;
            // }
            /*
            Check if row time is in beginning in and out or ending in and out
             */
            
            /*
            Check if there is a record for the date
             */
            /*
            If record is not empty check if time is greater than timeout or timeout is empty then update timeout with the row time 
             */
            /*
            If empty create new timein record
             */
            /*
            V0.1
             */
            // if (!empty($employee)) {
            //     /*
            //         Check if insert or update if existing for the date of the row.
            //      */
                

            //     $cur_record=$con->myQuery(
            //         "SELECT id,out_time FROM attendance WHERE DATE(:event_time) AND employees_id=:employee_id LIMIT 1",
            //         array(
            //             "event_time"=>$row_date->format("Y-m-d"),
            //             "employee_id"=>$employee['id']
            //             )
            //     )->fetch(PDO::FETCH_ASSOC);

            //     $cur_record_date=new DateTime($cur_record['out_time']);

            //     if (empty($cur_record)) {
            //         /*
            //         Insert Time In
            //          */
            //         $con->myQuery("INSERT INTO attendance(employees_id,in_time) VALUES(?,?)", array($employee['id'],$row_date->format("Y-m-d H:i:s")));
            //     } else {
            //         /*
            //         Update Time out
            //          */
            //         if ($row_date>$cur_record_date) {
            //             $con->myQuery("UPDATE attendance SET out_time=? WHERE id=?", array($row_date->format("Y-m-d H:i:s"), $cur_record['id']));
            //         }
            //     }
            // }
        }
        // throw new Exception("DEBUG Mode mudaafucka!!", 1);
        
        $con->commit();
        Alert("Attendance Uploaded.", "success");
        redirect("frm_upload_attendances.php");
        die;
    } catch (Exception $e) {
        $con->rollback();
        Alert("Please try again.", "danger");
        redirect("frm_upload_attendances.php");
        die;
    }
}
