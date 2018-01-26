<?php
require_once("../support/config.php");
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
    // array( 'db' => 'break_one','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     if ($row['break_one']!="00:00:00") {
    //         return htmlspecialchars($d);
    //     } else {
    //         return "";
    //     }
    // }),
    // array( 'db' => 'break_two','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     if ($row['break_two']!="00:00:00") {
    //         return htmlspecialchars($d);
    //     } else {
    //         return "";
    //     }
    // }),
    // array( 'db' => 'break_three','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     if ($row['break_three']!="00:00:00") {
    //         return htmlspecialchars($d);
    //     } else {
    //         return "";
    //     }
    // }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            $action_buttons="<button type='button ' class='btn btn-success btn-sm btn-flat' onclick=\"select_shift('".$row['id']."','".htmlspecialchars($row['shift_name'])."')\"><span class='fa fa-check'></span> Select</button>";
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
$where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;



$bindings=jp_bind($bindings);
// SELECT time_in,time_out,beginning_in,beginning_out,ending_in,ending_out,start_date,end_date FROM employees_default_shifts WHERE employee_id=?
$complete_query="SELECT 
id,
shift_name,
time_in,
time_out,
IF(break_one_start<>'00:00:00' OR break_one_end<>'00:00:00',CONCAT(DATE_FORMAT(break_one_start,'%H:%i'),' - ',DATE_FORMAT(break_one_end,'%H:%i')),'00:00:00')AS break_one,
IF(break_two_start<>'00:00:00' OR break_two_end<>'00:00:00',CONCAT(DATE_FORMAT(break_two_start,'%H:%i'),' - ',DATE_FORMAT(break_two_end,'%H:%i')),'00:00:00')AS break_two,
IF(break_three_start<>'00:00:00' OR break_three_end<>'00:00:00',CONCAT(DATE_FORMAT(break_three_start,'%H:%i'),' - ',DATE_FORMAT(break_three_end,'%H:%i')),'00:00:00')AS break_three,
working_days FROM shifts {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query, $bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM `shifts` {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
