<?php
require_once("../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'description','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'parent_dep','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    // array( 'db' => 'approver','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return htmlspecialchars($d);
    // }),
    array( 'db' => 'paygroup','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            $action_buttons.="<a class='btn btn-sm btn-warning' href='frm_departments.php?id={$row['id']}' title='Edit' ><span  class='fa fa-pencil'></span></a>";
            $action_buttons.="<form method='GET' style='display:inline' action='delete.php?t=dep' onsubmit='return confirm(\"Are you sure you want to delete this department?\")'><input type='hidden' name='id' value='{$row['id']}'> <input type='hidden' name='t' value='dep'><button class='btn btn-sm btn-danger' type='submit' title='Delete'><span  class='fa fa-trash'></span></button></form>";
            
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
if (!empty($_GET['company_id'])) {
    
    $sa_id_sql="d.payroll_group_id = :company_id";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'company_id','val'=>$_GET['company_id']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($_GET['approver_id'])) {
    $sa_id_sql="d.approver_id = :approver_id";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'approver_id','val'=>$_GET['approver_id']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($_GET['department_id'])) {
    $sa_id_sql="d.id = :department_id";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'department_id','val'=>$_GET['department_id']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($_GET['parent_department_id'])) {
    $sa_id_sql="d.parent_id = :parent_id";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'parent_id','val'=>$_GET['parent_department_id']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($filter_sql)) {
    $whereAll.=" AND ".$filter_sql;
} else {
    $whereAll.=$filter_sql;
}



// $whereAll=" employees_id= :employees_id";
// $bindings[]=array('key'=>'employees_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);


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

$join_query=" JOIN payroll_groups c ON d.payroll_group_id=c.payroll_group_id ";

$bindings=jp_bind($bindings);
$complete_query="SELECT d.id,d.name,d.description,d.parent_id,(SELECT name FROM departments WHERE id=d.parent_id LIMIT 1) as parent_dep,d.approver_id,(SELECT CONCAT(last_name,', ',first_name,' ',middle_name) FROM employees WHERE id=d.approver_id LIMIT 1)as approver,c.name as paygroup FROM departments d {$join_query} {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);
             
$data=$con->myQuery($complete_query, $bindings)->fetchAll();

// die($where);

$recordsTotal=$con->myQuery("SELECT COUNT(d.id) FROM departments d {$join_query} {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
