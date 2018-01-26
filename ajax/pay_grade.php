<?php
require_once("../support/config.php");
if(!isLoggedIn()){
     redirect("../index.php");
     die();
    }
$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'level','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'can_apply_for_meal_transpo','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'allow_overtime','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'view_employee_leave_calendar','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'access_project_management','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            $action_buttons.="<a class='btn btn-sm btn-warning' href='frm_pay_grade.php?id={$row['id']}' title='Edit' ><span  class='fa fa-pencil'></span></a>";
            $action_buttons.="<form method='GET' style='display:inline' action='delete.php' onsubmit='return confirm(\"Are you sure you want to delete this pay grade?\")'><input type='hidden' name='t' value='pg'><input type='hidden' name='id' value='{$row['id']}'> <button class='btn btn-sm btn-danger' type='submit' title='Delete'><span  class='fa fa-trash'></span></button></form>";
            
            return $action_buttons;
        }
    )
);
 

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
$whereAll=" is_deleted=0 ";
$whereResult="";

$where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;



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



$bindings=jp_bind($bindings);
$complete_query="SELECT id,level,IF(can_apply_for_meal_transpo=0,'Not Allowed','Allowed') as can_apply_for_meal_transpo,IF(allow_overtime=0, 'Not Allowed', 'Allowed') as allow_overtime,IF(view_employee_leave_calendar=0, 'Not Allowed', 'Allowed') as view_employee_leave_calendar,IF(access_project_management=0, 'Not Allowed', 'Allowed') as access_project_management FROM pay_grade  {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);
             
$data=$con->myQuery($complete_query, $bindings)->fetchAll();

// die($where);

$recordsTotal=$con->myQuery("SELECT COUNT(p.id) FROM pay_grade p  {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
