<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'shift_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    
    array( 'db' => 'time_in','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'time_out','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            $action_buttons.="<a href='frm_shift.php?id={$row['id']}' class='btn btn-sm btn-danger'><span class='fa fa-edit'></span></a>&nbsp;";
            $action_buttons.="<a href='delete_shifts.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this entry?\")'><span class='fa fa-trash'></span></a>";

            return $action_buttons;
        }
        )
    );


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$bindings=array();
$where ="";
$whereAll=" s.is_deleted=0";
$whereResult="";


function jp_bind($bindings)
{
    $return_array=array();
    if (is_array($bindings)) {
        for ($i=0, $ien=count($bindings); $i<$ien; $i++) {
            $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }

    return $return_array;
}
$where.= !empty($where) ? " AND ".$whereAll:(empty($whereAll))?"":"WHERE ".$whereAll;


$bindings=jp_bind($bindings);
$complete_query="SELECT s.id,s.shift_name,s.time_in,s.time_out FROM shifts s {$where} {$order} {$limit}";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(s.id) FROM shifts s {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
