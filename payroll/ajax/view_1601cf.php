<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'month_year','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_processed','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'tax_required_to_be_withheld','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'adjustment_from_26ofsectiona','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    })
      

    );


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$bindings=array();
$where ="";
// if(!empty($_GET['txt_year'])){
  $whereAll=" is_processed=1 AND is_deleted = 0 AND SUBSTR(month_year,1,4)= " . $_GET['txt_year'];
// }else{
//     $whereAll="";
// }

$whereResult="";
$filter_sql=" ";

$whereAll.=$filter_sql;

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

$join="";

$complete_query=" SELECT
id,
month_year,
date_processed,
if(adjustment_from_26ofsectiona <> 0,0,tax_required_to_be_withheld) as tax_required_to_be_withheld,
adjustment_from_26ofsectiona
FROM
sixteen_zero_one_c {$join} {$where} {$order} {$limit} ";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();
// echo $complete_query;
$recordsTotal=$con->myQuery("SELECT COUNT(sz.id) FROM sixteen_zero_one_c sz {$join} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

// var_dump($recordsTotal);
// die;

echo json_encode($json);
die;
