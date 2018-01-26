<?php
require_once("../support/config.php");
if (!AllowUser(array(1,4))) {
    redirect("index.php");
}



// $leaves=$con->myQuery("SELECT id,CONCAT(IFNULL(leave_type,'Leave Without Pay'), ' ',employee_name,' ', date_start, date_end)  as title,'true' as allDay, DATE_FORMAT(date_start, '%Y-%m-%d') AS `start`,CONCAT(DATE_FORMAT(date_end, '%Y-%m-%d'),' 24:00:00') AS `end`, reason FROM vw_employees_leave WHERE status='Approved' AND (date_start BETWEEN :date_start AND :date_end OR date_end BETWEEN :date_start AND :date_end)", array("date_start"=>$_GET['start'], "date_end"=>$_GET['end']))->fetchAll(PDO::FETCH_ASSOC);

$leaves=$con->myQuery("SELECT 
            e.id                                                          AS employees_id,
            e.code                                                        AS code,
            CONCAT(IFNULL((SELECT NAME FROM LEAVES WHERE id=el.leave_id),'Leave Without Pay'),' ', e.first_name,' ',e.last_name)                          AS title, 
            CONCAT(e.last_name, ', ',e.first_name) as employee,
            el.date_start as date_start,
            el.date_end as date_end,
            IFNULL((SELECT NAME FROM LEAVES WHERE id=el.leave_id),'Leave Without Pay') as leave_type,
            el.leave_id                                                   AS id,
            eld.date_leave                                                AS start,
            eld.date_leave                                                AS end,
            el.reason                                                     AS reason
        FROM employees_leaves_date eld
        INNER JOIN employees_leaves el
          ON el.id=eld.employees_leaves_id
        INNER JOIN employees e
          ON e.id=el.employee_id
           WHERE 
           eld.date_leave BETWEEN :date_start AND :date_end AND request_status_id=2" , array("date_start"=>$_GET['start'], "date_end"=>$_GET['end']))->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($leaves);
