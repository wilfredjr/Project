<?php
require_once("../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'step_number','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        global $con;
        $employees=$con->myQuery("SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as employee FROM approval_steps_employees afe JOIN employees e ON afe.employee_id=e.id WHERE approval_step_id=?", array($row['id']))->fetchAll(PDO::FETCH_ASSOC);
        $display="";
        if (!empty($employees)) {

            $display.="<ul>";
            foreach ($employees as $key => $employee) {
                $display.="<li>".htmlspecialchars($employee['employee'])."</li>";
            }
            $display.="</ul>";
        }
        if (empty($display)) {
            return "<span class='fa fa-exclamation-circle'></span><b class='text-danger'> This step will be skipped. <small>No employee selected.</small></b>";
        } else {
            return $display;
        }
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            $action_buttons.="<input type='hidden' name='step_id' value='{$d}'>";
            $action_buttons.="<a class='btn btn-sm btn-warning' href='approval_flow.php?dep_id={$row['department_id']}&id={$row['id']}' title='Edit' ><span  class='fa fa-pencil'></span></a>";
            $action_buttons.="&nbsp;<a class='btn btn-sm btn-warning' href='approval_flow_employees.php?id={$row['id']}' title='Edit Employees' ><span  class='fa fa-users'></span></a>";
            $action_buttons.="<form method='POST' style='display:inline' action='delete_approval_flow_step.php' onsubmit='return confirm(\"Are you sure you want to delete this step?\")'><input type='hidden' name='id' value='{$row['id']}'> <button class='btn btn-sm btn-danger' type='submit' title='Delete'><span  class='fa fa-trash'></span></button></form>";
            
            return $action_buttons;
        }
    )
);
 

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
$whereAll=" d.is_deleted=0";
$whereResult="";

$filter_sql="";

// var_dump($_GET);
if (!empty($_GET['department_id'])) {
    
    $sa_id_sql="department_id = :department_id";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'department_id','val'=>$_GET['department_id']."",'type'=>0);
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
$complete_query="SELECT id, name, step_number, department_id FROM approval_steps {$where} ORDER BY step_number ASC {$limit}";
            // echo $complete_query;
             //var_dump($bindings);
             
$data=$con->myQuery($complete_query, $bindings)->fetchAll();

// die($where);

$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM approval_steps {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
