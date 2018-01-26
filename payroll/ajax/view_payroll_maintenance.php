<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;



$columns = array(
    array( 'db' => 'payroll_code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'pay_group','dt' => ++$index ,'formatter'=>function ($d, $row) {
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
        $action_buttons.="<a class='btn btn-flat btn-sm btn-default' title='View Details' href='frm_generate_payroll.php?id={$row['id']}&date_start={$row['date_from']}&date_end={$row['date_to']}&pay_group={$row['pay_group_id']}'><span class='fa fa-search'></span></a>";
         if($row['is_processed']==0)
        {
            $action_buttons.="<a href='view_payroll_maintenance.php?id={$row['id']}' class='btn btn-flat btn-sm btn-danger'><span class='fa fa-trash'></span></a>";
        }
        return $action_buttons;
    }),
    array( 'db' => 'date_process','dt' => ++$index ,'formatter'=>function ($d, $row) {
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

$filter_sql     .= " p.is_deleted=0 ";

if(!empty($_GET['pay_code_filter']))
{
    $pay_code_filter = " p.id=:pay_code_filter ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'pay_code_filter','val'=>$_GET['pay_code_filter'],'type'=>0);
    $filter_sql.=$pay_code_filter;
}
if(!empty($_GET['pay_group_filter']))
{
    $pay_group_filter = " p.pay_group_id=:pay_group_filter ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'pay_group_filter','val'=>$_GET['pay_group_filter'],'type'=>0);
    $filter_sql.=$pay_group_filter;
}
if(!empty($_GET['status_filter']))
{
    if ($_GET['status_filter']==1) 
    {
        $status_filter = " ( p.is_processed=:status_filter )";    
        $bindings[]=array('key'=>'status_filter','val'=>$_GET['status_filter'],'type'=>0);
    }else
    {
        $status_filter = " ( isnull(p.is_processed) || p.is_processed <> 1 ) ";    
    }
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $filter_sql.=$status_filter;
}

if(!empty($_GET['date_generated_filter']))
{
    $date_generated_filter=date_create($_GET['date_generated_filter']);
}else
{
    $date_generated_filter="";
}
$date_filter="";
if(!empty($date_generated_filter))
{
    $date_filter .= !empty($filter_sql)?" AND ":"";
    $date_filter .= " p.date_gen = :date_generated_filter";
    $bindings[]  = array('key'=>'date_generated_filter','val'=>date_format($date_generated_filter,'Y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;

if(!empty($_GET['date_start_filter']))
{
    $date_start_filter=date_create($_GET['date_start_filter']);
}else
{
    $date_start_filter="";
}
if(!empty($_GET['date_end_filter']))
{
    $date_end_filter=date_create($_GET['date_end_filter']);
}else
{
    $date_end_filter="";
}

$date_filter="";
if(!empty($date_start_filter))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" p.date_from >= :date_start_filter";
    $bindings[]=array('key'=>'date_start_filter','val'=>date_format($date_start_filter,'Y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;

$date_filter="";
if(!empty($date_end_filter))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" p.date_to <= :date_end_filter";
    $bindings[]=array('key'=>'date_end_filter','val'=>date_format($date_end_filter,'Y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;



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
$complete_query="SELECT p.id,p.payroll_code, p.date_gen, p.date_from, p.date_to,p.is_processed,p.date_process,p.pay_group_id,pg.name as pay_group FROM payroll p inner join payroll_groups pg on pg.payroll_group_id=p.pay_group_id {$where} {$order} {$limit}";


// echo $complete_query."<br>";
// var_dump($bindings);

$data=$con->myQuery($complete_query, $bindings)->fetchAll();
$recordsTotal=$con->myQuery("SELECT COUNT(p.id) FROM payroll p {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
