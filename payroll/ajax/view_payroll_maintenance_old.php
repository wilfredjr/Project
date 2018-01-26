<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'payroll_code','dt' => ++$index ,'formatter'=>function ($d, $row) {
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
    array( 'db' => 'is_processed','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if($d==1) 
        {
            return "Processed: ".$row['date_process'];
        }else
        {
            return "Not yet processed";
        }
    }),
    array( 'db' => 'id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        $action_buttons="";
        // $action_buttons.="<a class='btn btn-flat btn-sm btn-default' title='View Details' href='frm_generate_payroll.php?id={$row['id']}'><span class='fa fa-search'></span></a>";
        $action_buttons.="<a class='btn btn-flat btn-sm btn-default' title='View Details' href='frm_generate_payroll.php?id={$row['id']}&date_start={$row['date_from']}&date_end={$row['date_to']}&pay_group={$row['pay_group_id']}'><span class='fa fa-search'></span></a>";
         if($row['is_processed']==0)
        {
            $action_buttons.="<a href='view_payroll_maintenance.php?id={$row['id']}' class='btn btn-flat btn-sm btn-danger'><span class='fa fa-trash'></span></a>";
        }
        return $action_buttons;
    }),
    array( 'db' => 'date_process','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return "";
    }),
    array( 'db' => 'pay_group_id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return "";
    })        
);


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$bindings=array();
$where ="";
$whereAll=" p.is_deleted=0 ";
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
$complete_query="SELECT p.id,p.payroll_code, p.date_gen, p.date_from, p.date_to,p.is_processed,p.date_process,p.pay_group_id FROM payroll p {$where} {$order} {$limit}";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(p.id) FROM payroll p {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
