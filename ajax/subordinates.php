<?php
require_once("../support/config.php");


$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee','dt' => ++$index ,'formatter'=>function ($d, $row) {
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
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
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



$whereAll="  e.is_deleted=0 AND e.is_terminated=0 ";


$whereAll.=" AND e.supervisor_id=:employee_id ";
$bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);


if (!empty($_GET['employee_id'])) {
    $whereAll.=" AND ";
    $whereAll.=" e.id=:subordinate_id";
    $bindings[]=array('key'=>'subordinate_id','val'=>$_GET['employee_id'],'type'=>0);
}

if (!empty($_GET['department_id'])) {
    $whereAll.=" AND ";
    $whereAll.=" e.department_id=:department_id";
    $bindings[]=array('key'=>'department_id','val'=>$_GET['department_id'],'type'=>0);
}

if (!empty($_GET['job_title_id'])) {
    $whereAll.=" AND ";
    $whereAll.=" e.job_title_id=:job_title_id";
    $bindings[]=array('key'=>'job_title_id','val'=>$_GET['job_title_id'],'type'=>0);
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
e.id,e.code,CONCAT(e.last_name,', ',e.first_name,' ',IFNULL(e.middle_name,'')) as 'employee',e.private_email,e.contact_no, jt.description as 'job_title',d.name as 'department'
FROM employees e LEFT JOIN job_title jt ON e.job_title_id=jt.id LEFT JOIN departments d ON e.department_id=d.id {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query, $bindings)->fetchAll();


$recordsTotal=$con->myQuery("SELECT COUNT(u.id) FROM users u INNER JOIN employees e ON e.id=u.employee_id {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
