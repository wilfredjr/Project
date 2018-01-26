<?php
require_once("../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'holiday_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'holiday_date','dt' => ++$index ,'formatter'=>function ($d, $row) {
        try {
            $date_from=date_create($d);
            $date_from=date_format($date_from, DATE_FORMAT_PHP);
        } catch (Exception $e) {
            $date_from="";
        }
        return htmlspecialchars($date_from);
    }),
    array( 'db' => 'holiday_category','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            $action_buttons.="<a class='btn btn-sm btn-warning' href='frm_general_holiday.php?id={$row['id']}' title='Edit' ><span  class='fa fa-pencil'></span></a>";
            $action_buttons.="<form method='post' style='display:inline' action='delete_general_holidays.php' onsubmit='return confirm(\"Are you sure you want to delete this holiday?\")'><input type='hidden' name='id' value='{$row['id']}'> <button class='btn btn-sm btn-danger' type='submit' title='Delete'><span  class='fa fa-trash'></span></button></form>";
            


            return $action_buttons;
        }
    )
);
 

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
$whereAll=" h.is_deleted=0";
$whereResult="";

$filter_sql="";

if (!empty($_GET['start_date'])) {
    $date_start_file=date_create($_GET['start_date']);
} else {
    $date_start_file="";
}

if (!empty($_GET['end_date'])) {
    $date_end_file=date_create($_GET['end_date']);
} else {
    $date_end_file="";
}

$date_filter="";

if (!empty($date_start_file)) {
    $date_filter="";
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" holiday_date >= :date_start";
    $bindings[]=array('key'=>'date_start','val'=>date_format($date_start_file, 'Y-m-d'),'type'=>0);
    // '".date_format($date_start_file,'Y-m-d')."'
}
$filter_sql.=$date_filter;

$date_filter="";
if (!empty($date_end_file)) {
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" holiday_date <= :date_end";
    $bindings[]=array('key'=>'date_end','val'=>date_format($date_end_file, 'Y-m-d'),'type'=>0);
    // '".date_format($date_end_file,'Y-m-d')."  23:59:59'
}
$filter_sql.=$date_filter;


if (!empty($_GET['category'])) {
    $sa_id_sql="holiday_category = :category";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'category','val'=>$_GET['category']."",'type'=>0);
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

$join_query=" ";

$bindings=jp_bind($bindings);
$complete_query="SELECT h.id,h.holiday_name,h.holiday_date,h.holiday_category FROM default_holidays h {$join_query} {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);
$data=$con->myQuery($complete_query, $bindings)->fetchAll();

// die($where);

$recordsTotal=$con->myQuery("SELECT COUNT(h.id) FROM default_holidays h {$join_query} {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
