<?php
require_once("../support/config.php");
$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'start_date','dt' => ++$index ,'formatter'=>function ($d, $row) {
        $start_date=new DateTime($d);
        return $start_date->format(DATE_FORMAT_PHP);
    }),
    array(
        'db'        => 'end_date',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            if (empty($d) || $d=="null") {
                return "Active";
            } else {
                $end_date=new DateTime($d);
                return $end_date->format(DATE_FORMAT_PHP);
            }
        }
    ),
    array( 'db' => 'working_days','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'time_in','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'time_out','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'late_start','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'grace_minutes','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d)." Minutes";
    }),
    // array( 'db' => 'beginning_in','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return htmlspecialchars($d);
    // }),
    // array( 'db' => 'ending_in','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return htmlspecialchars($d);
    // }),
    // array( 'db' => 'beginning_out','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return htmlspecialchars($d);
    // }),
    // array( 'db' => 'ending_out','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return htmlspecialchars($d);
    // }),
    // array( 'db' => 'break_one_start','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     if ($row['break_one_start']!="00:00:00" && $row['break_one_end']!="00:00:00") {
    //         return htmlspecialchars($d);
    //     } else {
    //         return "";
    //     }
    // }),
    // array( 'db' => 'break_one_end','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     if ($row['break_one_start']!="00:00:00" && $row['break_one_end']!="00:00:00") {
    //         return htmlspecialchars($d);
    //     } else {
    //         return "";
    //     }
    // }),
    // array( 'db' => 'break_two_start','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     if ($row['break_two_start']!="00:00:00" && $row['break_two_end']!="00:00:00") {
    //         return htmlspecialchars($d);
    //     } else {
    //         return "";
    //     }
    // }),
    // array( 'db' => 'break_two_end','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     if ($row['break_two_start']!="00:00:00" && $row['break_two_end']!="00:00:00") {
    //         return htmlspecialchars($d);
    //     } else {
    //         return "";
    //     }
    // }),
    // array( 'db' => 'break_three_start','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     if ($row['break_three_start']!="00:00:00" && $row['break_three_end']!="00:00:00") {
    //         return htmlspecialchars($d);
    //     } else {
    //         return "";
    //     }
    // }),
    // array( 'db' => 'break_three_end','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     if ($row['break_three_start']!="00:00:00" && $row['break_three_end']!="00:00:00") {
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

            $action_buttons.="<form method='post' action='delete_employee_default_shift.php' onsubmit='return confirm(\"Remove default shift? \")' style='display:inline'>";
            $action_buttons.="<input type='hidden' name='id' value='{$row['id']}'>";
            $action_buttons.="<input type='hidden' name='employee_id' value='{$row['employee_id']}'>";
            $action_buttons.=" <button class='btn btn-sm btn-danger' value='adjustment' title='Delete shift'><span class='fa fa-trash'></span></button></form>";


            return $action_buttons;
        }
    )
);
$employee_id=0;
$employee_id=$con->myQuery("SELECT id FROM employees WHERE id=?", array($_GET['employee_id']))->fetchColumn();

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
$whereAll="";
$whereResult="";

$whereAll=" employee_id=:employee_id";
$bindings[]=array('key'=>'employee_id','val'=>$employee_id,'type'=>0);
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
$complete_query="SELECT employee_id,id,DATE_FORMAT(time_in, '".TIME_FORMAT_SQL."')as time_in,DATE_FORMAT(time_out, '".TIME_FORMAT_SQL."')as time_out,beginning_in,beginning_out,ending_in,ending_out,start_date,end_date, break_one_start, break_one_end, break_two_start, break_two_end, break_three_start, break_three_end, working_days, DATE_FORMAT(late_start, '".TIME_FORMAT_SQL."')as late_start, grace_minutes
             FROM `employees_default_shifts` {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query, $bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM `employees_default_shifts` {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
