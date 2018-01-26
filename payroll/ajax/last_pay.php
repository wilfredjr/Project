<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'last_pay_code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
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
        $action_buttons = "";
        $action_buttons .= "<a class='btn btn-flat btn-sm btn-default' title='View Details' href='last_pay_view.php?id={$row['id']}'><span class='fa fa-search'></span></a>";
        if($row['is_processed']==0)
        {
           
            $action_buttons.="&nbsp;<form method='post' action='delete_last_pay.php?id={$d}' onsubmit='return confirm(\"Are you sure you want to delete this record?\")' style='display:inline'><input type='hidden' name='id' value='{$d}'><button class='btn btn-sm btn-danger btn-flat'  title='Delete Record' type='submit'><span  class='fa fa-close'></span></button></form>";
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

$filter_sql     = " lp.is_deleted=0 ";

$inner_join     = " INNER JOIN employees e ON e.id=lp.employee_id ";

if(!empty($_GET['pay_code_filter']))
{
    $pay_code_filter = " lp.id=:pay_code_filter ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'pay_code_filter','val'=>$_GET['pay_code_filter'],'type'=>0);
    $filter_sql.=$pay_code_filter;
}
if(!empty($_GET['pay_group_filter']))
{
    $pay_group_filter = " lp.employee_id=:pay_group_filter ";    
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
        $status_filter = " lp.is_processed=:status_filter ";    
        $bindings[]=array('key'=>'status_filter','val'=>$_GET['status_filter'],'type'=>0);
    }else
    {
        $status_filter = " lp.is_processed=0 ";    
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
    $date_filter .= " lp.date_generated = :date_generated_filter";
    $bindings[]  = array('key'=>'date_generated_filter','val'=>date_format($date_generated_filter,'Y-m-d'),'type'=>0);
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

$whereAll .= $filter_sql;
$where .= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";

$bindings       = jp_bind($bindings);
$complete_query = "SELECT   lp.id AS id, 
                            lp.last_pay_code AS last_pay_code, 
                            lp.employee_id AS employee_id, 
                            CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name, 
                            lp.date_generated AS date_generated, 
                            lp.is_processed AS is_processed, 
                            lp.date_processed AS date_processed, 
                            lp.is_deleted AS is_deleted 
                    FROM last_pay lp {$inner_join} {$where} {$order} {$limit}";

$data = $con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal = $con->myQuery("SELECT COUNT(lp.id) FROM last_pay lp {$inner_join} {$where}", $bindings)->fetchColumn();

$json['draw']               = isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']       = $recordsTotal;
$json['recordsFiltered']    = $recordsTotal;
$json['data']               = SSP::data_output($columns, $data);

echo json_encode($json);
die;
