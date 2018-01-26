<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'month_year','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    
    array( 'db' => 'is_processed','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if($d==1) 
        {
            return "Processed: ".$row['date_processed'];
        }else
        {
            return "Not yet processed";
        }
    }),
    array( 'db' => 'id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        $action_buttons="";
        $action_buttons.="<a class='btn btn-flat btn-sm btn-default' title='View Details' href='1601c_view.php?id={$row['id']}&month_year={$row['month_year']}'><span class='fa fa-search'></span></a>";
         if($row['is_processed']==0)
        {
             $action_buttons.="&nbsp;<form method='post' action='delete_1601c.php?id={$d}' onsubmit='return confirm(\"Are you sure you want to delete this transaction?\")' style='display:inline'><input type='hidden' name='id' value='{$d}'><button class='btn btn-sm btn-danger btn-flat'  title='Delete Transaction' type='submit'><span  class='fa fa-close'></span></button></form>";
        }
        return $action_buttons;
    }),
    array( 'db' => 'date_processed','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return "";
    })    

    );


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$bindings=array();
$where ="";
$whereAll=" is_deleted = 0 ";
$whereResult="";
$filter_sql="  ";

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
is_processed
FROM
sixteen_zero_one_c {$join} {$where} {$order} {$limit} ";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();


$recordsTotal=$con->myQuery("SELECT COUNT(sz.id) FROM sixteen_zero_one_c sz {$join} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

// var_dump($recordsTotal);
// die;

echo json_encode($json);
die;
