<?php
require_once("../support/config.php");
if(!AllowUser(array(1,4))){
         redirect("index.php");
     }


$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'full_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'job_title','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'department','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'private_email','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'contact_no','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employment_status','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'joined_date','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'expected_regularization_date','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            return "";
            $action_buttons="";
            $action_buttons.="<form method='POST' action='regularize_employee.php' onsubmit='return confirm(\"Are you sure you want to regularize this employee?\")'>";
            $action_buttons.="<input type='hidden' name='id' value='{$d}'>";
            $action_buttons.="<button type='submit' title='Regularize Employee' class='btn btn-success'><span class='ion ion-checkmark-round'></span></button>";
            $action_buttons.="</form>";
            
            return $action_buttons;
        }
    )
);
 

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
$whereAll="";
$whereResult="";



$whereAll.=" es.is_regular=0 AND e.is_deleted=0 AND e.is_terminated=0  AND DATE_ADD(joined_date, INTERVAL 5 MONTH) <= NOW() AND joined_date <= NOW() ";

if (!empty($_GET['employee_id'])) {
    $whereAll.=" AND ";
    $whereAll.=" e.id=:employee_id";
    $bindings[]=array('key'=>'employee_id','val'=>$_GET['employee_id'],'type'=>0);
}

if (!empty($_GET['department_id'])) {
    $whereAll.=" AND ";
    $whereAll.=" e.department_id=:department_id";
    $bindings[]=array('key'=>'department_id','val'=>$_GET['department_id'],'type'=>0);
}


if (!empty($_GET['date_start'])) {
    $test_start=true;
    try {
        new DateTime($_GET['date_start']);
    } catch (Exception $e) {
        $test_start=false;
    }
    if ($test_start) {
        $whereAll.=" AND joined_date >= :date_start ";
        $date_start=new DateTime($_GET['date_start']);
        $bindings[]=array('key'=>'date_start','val'=>$date_start->format("Y-m-d"),'type'=>0);
    }
}

if (!empty($_GET['date_end'])) {
    $test_end=true;
    try {
        new DateTime($_GET['date_end']);
    } catch (Exception $e) {
        $test_end=false;
    }
    if ($test_end) {
        $whereAll.=" AND joined_date <= :date_end ";
        $date_end=new DateTime($_GET['date_end']);
        $bindings[]=array('key'=>'date_end','val'=>$date_end->format("Y-m-d"),'type'=>0);
    }
}
function jp_bind($bindings)
{
    $return_array=array();
    if (is_array($bindings)) {
        for ($i=0, $ien=count($bindings) ; $i<$ien ; $i++) {
            //$binding = $bindings[$i];
                // $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
                $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }

    return $return_array;
}
$where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;



$bindings=jp_bind($bindings);
$complete_query="SELECT 
e.id,e.code,CONCAT(e.last_name,', ',e.first_name,' ',IFNULL(e.middle_name,'')) AS 'full_name',e.private_email,e.contact_no, jt.description AS 'job_title',d.name AS 'department',joined_date, DATE_ADD(joined_date, INTERVAL 6 MONTH) as expected_regularization_date, es.name as employment_status
FROM employees e LEFT JOIN job_title jt ON e.job_title_id=jt.id LEFT JOIN departments d ON e.department_id=d.id LEFT JOIN employment_status es ON es.id=e.employment_status_id {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query, $bindings)->fetchAll();


$recordsTotal=$con->myQuery("SELECT 
count(e.id)
FROM employees e LEFT JOIN job_title jt ON e.job_title_id=jt.id LEFT JOIN departments d ON e.department_id=d.id LEFT JOIN employment_status es ON es.id=e.employment_status_id {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
