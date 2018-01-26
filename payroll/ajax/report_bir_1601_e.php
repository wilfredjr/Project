<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'company_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'month_year','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_generated','dt' => ++$index ,'formatter'=>function ($d, $row) {
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
        $action_buttons.="<a class='btn-s btn-flat btn-sm btn-default' title='View Details' href='report_bir_1601_e_view.php?id={$row['id']}'><span class='fa fa-search'></span></a>&nbsp;";
        if($row['is_processed']==0)
        {
            $action_buttons.="<a href='report_bir_1601_e_delete_master.php?id={$row['id']}' class='btn-s btn-flat btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this record?\")'><span class='fa fa-trash'></span></a>";
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

$bindings       = array();
$filter_sql     = "";
$where          = "";
$whereAll       = "";
$whereResult    = "";

$filter_sql     .= " is_deleted=0 ";

function jp_bind($bindings)
{
    $return_array=array();
    if (is_array($bindings)) 
    {
        for ($i=0, $ien=count($bindings); $i<$ien; $i++) 
        {
            $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }
    return $return_array;
}

$whereAll.=$filter_sql;
$where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";


$bindings=jp_bind($bindings);
$complete_query="SELECT id,company_name,month_year,date_generated,is_processed,date_processed FROM bir_1601_e_master {$where} {$order} {$limit}";

// echo $complete_query."<br>";
// var_dump($bindings);

$data=$con->myQuery($complete_query, $bindings)->fetchAll();
$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM bir_1601_e_master {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
