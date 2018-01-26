<?php
require_once("../support/config.php");

if (!isLoggedIn()) {
    redirect("../frmlogin.php");
}
$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'department','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            $action_buttons.="<form method='POST' style='display:inline' action='delete_approval_flow_employees.php' onsubmit='return confirm(\"Are you sure you want to delete this employee?\")'><input type='hidden' name='id' value='{$row['id']}'><button class='btn btn-sm btn-danger' type='submit' title='Delete'><span  class='fa fa-trash'></span></button></form>";
            
            return $action_buttons;
        }
    )
);
 

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
$whereAll=" ";
$whereResult="";

$filter_sql="";

// var_dump($_GET);
if (!empty($_GET['step_id'])) {
    
    $sa_id_sql=" approval_step_id=:approval_step_id AND e.is_deleted=0 ";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'approval_step_id','val'=>$_GET['step_id']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
} else {
    return json_encode(array());
    die;
}



$whereAll=" ";
$whereAll.=$filter_sql;


function jp_bind($bindings)
{
    $return_array=array();
    if (is_array($bindings)) {
        for ($i=0, $ien=count($bindings); $i<$ien; $i++) {
            //$binding = $bindings[$i];
                // $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
                $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }

    return $return_array;
}
$where.= !empty($where) ? " AND ".$whereAll:(empty($whereAll))?"":"WHERE ".$whereAll;

$join_query="  ";

$bindings=jp_bind($bindings);
$complete_query="SELECT ase.id,e.code,CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee, d.name as department,e.department_id FROM approval_steps_employees ase JOIN employees e ON ase.employee_id=e.id JOIN departments d ON e.department_id=d.id {$where} {$order} {$limit}";
            
             
$data=$con->myQuery($complete_query, $bindings)->fetchAll(PDO::FETCH_ASSOC);

// die($where);

$recordsTotal=$con->myQuery("SELECT COUNT(ase.id) FROM approval_steps_employees ase JOIN employees e ON ase.employee_id=e.id JOIN departments d ON e.department_id=d.id {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
