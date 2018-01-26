<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'date_action','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    
    array( 'db' => 'status_type','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'amount','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if (empty($d)) {
            return '';
        } else {
            return htmlspecialchars(number_format($d, 2));
        }
    }),
    array( 'db' => 'reason','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    })
    );


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$bindings=array();
$where ="";
$whereAll=" ";
$whereResult="";
$filter_sql="";
$pass_date_start="";
$pass_date_end="";
$det_date_start="";
$det_date_end="";
$total_where="";
    $category=" emp_loan_id=:loan_id ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'loan_id','val'=>$_GET['loan_id'],'type'=>0);
    $filter_sql.=$category;
    $total_where.=$category;
    

    if(!empty($_GET['date_start']))
    {
        $obj_date_start=new DateTime($_GET['date_start']);
        $date_start=" date_action>=:date_start ";
        $pass_date_start=" `date_applied`>=:date_start";
        $det_date_start=" date_deducted>=:date_start";
        if(!empty($filter_sql))
        {
            // $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'date_start','val'=>$obj_date_start->format("Y-m-d"),'type'=>0);
        // $filter_sql.=$date_start;
    }

    if(!empty($_GET['date_end']))
    {
        $obj_date_end=new DateTime($_GET['date_end']);
        $date_end=" date_action<=:date_end ";
        $pass_date_end=" `date_applied`<=:date_end";
        $det_date_end=" date_deducted<=:date_end";
        if(!empty($filter_sql))
        {
            // $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'date_end','val'=>$obj_date_end->format("Y-m-d"),'type'=>0);
        // $filter_sql.=$date_end;
    }

    if(!empty($_GET['action_type']))
    {
        $action_type=" status_type=:action_type ";    
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'action_type','val'=>$_GET['action_type'],'type'=>0);
        $filter_sql.=$action_type;
        $total_where.=$action_type;
    }

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
$totalwhere="";
$totalwhere.= !empty($totalwhere) ? " AND ".$total_where:(empty($total_where))?"":"WHERE ".$total_where;


$bindings=jp_bind($bindings);


$loan_pass_filter="";
$loan_det_filter="";
if (!empty($pass_date_start) || !empty($pass_date_end)) {
    if (!empty($pass_date_start)) {
        $loan_pass_filter.=" `date_applied` >= :date_start ";
    }

    if (!empty($pass_date_end)) {
        if (!empty($loan_pass_filter)) {
            $loan_pass_filter.=" AND `date_applied` <= :date_end";
        } else {
            $loan_pass_filter.=" `date_applied` <= :date_end ";
        }
    }
    $loan_pass_filter=" WHERE ".$loan_pass_filter;
}

if (!empty($det_date_start) || !empty($det_date_end)) {
    if (!empty($det_date_start)) {
        $loan_det_filter.=" date_deducted >= :date_start ";
    }

    if (!empty($det_date_end)) {
        if (!empty($loan_det_filter)) {
            $loan_det_filter.=" AND date_deducted <= :date_end";
        } else {
            $loan_det_filter.=" date_deducted <= :date_end ";
        }
    }
    $loan_det_filter=" WHERE ".$loan_det_filter;
}


$complete_query="SELECT * FROM ((SELECT
`date_applied` as date_action,
'Passed' AS status_type,
'0' AS amount,
reason,
emp_loan_id
FROM
emp_loan_pass 
{$loan_pass_filter})
UNION
(SELECT
`date_deducted` AS date_action,
'Deduct' AS status_type,
amount_paid AS amount,
'' AS reason,
emp_loan_id
FROM
emp_loans_det {$loan_det_filter}))a {$where} {$order} {$limit}";
// var_dump($_GET);
// echo $complete_query;
$data=$con->myQuery($complete_query, $bindings)->fetchAll();
/*
SELECT
`filed_date` AS date_action,
'Passed' AS status_type,
'0' AS amount,
reason,
`date_applied`
FROM
emp_loan_pass
 */

$recordsTotal=$con->myQuery("SELECT COUNT(a.num) FROM ((SELECT
emp_loan_pass_id AS num,
`date_applied` AS date_action,
emp_loan_id,
'Passed' as status_type
FROM
emp_loan_pass
{$loan_pass_filter})
UNION
(SELECT
emp_loan_det_id AS num,
`date_deducted` AS date_action,
emp_loan_id,
'Deduct' as status_type
FROM
emp_loans_det {$loan_det_filter}))a {$where}", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
