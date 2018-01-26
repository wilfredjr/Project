<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;  

$columns = array(
    array( 'db' => 'payroll_code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'pg_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_gen','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_from','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_to','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    );


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);

$whereAll=" payroll.is_processed = 0 AND payroll.is_deleted = 0 ";

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

$join_query=" ";

$bindings=jp_bind($bindings);
$complete_query="SELECT
                payroll.payroll_code,
                payroll_groups.name as pg_name,
                payroll.date_gen,
                payroll.date_from,
                payroll.date_to
                FROM
                payroll
                INNER JOIN payroll_groups ON payroll.pay_group_id = payroll_groups.payroll_group_id {$join_query} {$where} {$order} {$limit}";


$data=$con->myQuery($complete_query, $bindings)->fetchAll();


$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM payroll {$join_query} {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
