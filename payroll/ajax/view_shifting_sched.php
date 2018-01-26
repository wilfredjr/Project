<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'shift','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_from','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_to','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'no_of_emp','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            // $action_buttons.="<button type='button' data-toggle='modal' data-target='#myModal' class='btn btn-sm btn-danger'><span class='fa fa-search'></button>&nbsp;";
            $action_buttons.="<a href='view_shifting_sched.php?id={$row['id']}' class='btn btn-sm btn-danger'><span class='fa fa-search'></span></a>&nbsp;";
            $action_buttons.="<a href='frm_shifting_sched.php?id={$row['id']}' class='btn btn-sm btn-danger'><span class='fa fa-edit'></span></a>&nbsp;";
            $action_buttons.="<a href='delete_shifting_sched.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this entry?\")'><span class='fa fa-trash'></span></a>";


            return $action_buttons;
        })
    );


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);


$where = SSP::filter($_GET, $columns, $bindings);
$whereAll=" esm.is_deleted ='0' and esd.is_deleted ='0' ";
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
$where.= !empty($where) ? " AND ".$whereAll:(empty($whereAll))?"":"WHERE ".$whereAll;

$join_query=" INNER JOIN shifts s ON esm.shift_id = s.id INNER JOIN employees_shift_details esd ON esm.id = esd.employee_shift_master_id ";
$groupby = " GROUP BY esm.id";

$bindings=jp_bind($bindings);
$complete_query="SELECT esm.id, CONCAT(s.shift_name,' (',s.time_in,' - ',s.time_out,')') as shift, esm.date_from, esm.date_to, COUNT(esd.employee_id) as no_of_emp FROM employees_shift_master esm {$join_query} {$where} {$groupby} {$order} {$limit} ";

// var_dump($complete_query);

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

// $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsFiltered=$con->myQuery("SELECT COUNT(*) FROM (SELECT COUNT(esm.id) FROM employees_shift_master esm {$join_query} {$where} {$groupby}) as test ",$bindings)->fetchColumn();
// echo "string";
// $recordsTotal=count($recordsFiltered)

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsFiltered;
$json['recordsFiltered']=$recordsFiltered;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;