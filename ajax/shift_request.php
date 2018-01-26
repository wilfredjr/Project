<?php
require_once("../support/config.php");



$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'code','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_filed','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),DATE_FORMAT_PHP));
    }),
    array( 'db' => 'date_from','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),DATE_FORMAT_PHP));
    }),
    array( 'db' => 'date_to','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),DATE_FORMAT_PHP));
    }),
    array( 'db' => 'orig_in_time','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),TIME_FORMAT_PHP));
    }),
    array( 'db' => 'orig_out_time','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),TIME_FORMAT_PHP));
    }),
    array( 'db' => 'adj_in_time','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),TIME_FORMAT_PHP));
    }),
    array( 'db' => 'adj_out_time','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),TIME_FORMAT_PHP));
    }),
    array( 'db' => 'shift_reason','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'status','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'previous_approver','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'step_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";
            if($row['status']=="Query"):
            $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            else :
                $action_buttons.="<button class='btn btn-sm btn-info'  title='View Comments' onclick='query_logs(\"{$row['id']}\")'><span  class='fa fa-comment'></span></button>";
            endif;
           if($row['status']<>'Approved' && $row['status']<>'Cancelled' && $row['status']<>'Rejected'):
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='type' value='shift'>";
                $action_buttons.=" <button class='btn btn-sm btn-danger' value='shift' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
            endif;


            return $action_buttons;
        }
    )
);


require( '../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";


  $data=$con->myQuery("SELECT
    id,
    code,
    employee_name,
    
    status,
    date_from,
    date_to,
    orig_in_time,
    orig_out_time,
    adj_in_time,
    adj_out_time,
    shift_reason,
    request_status_id,
    step_name,
    step_id

    FROM vw_employees_change_shift
    ",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']));

    $whereAll=" (employee_id=:employee_id OR requestor_id=:employee_id) ";
    $filter_sql="";
    $filter_sql.=" ";
    $bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

    if(!empty($_GET['status']))
    {
        $stat=" request_status_id=:request_status_id ";
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'request_status_id','val'=>$_GET['status'],'type'=>0);
        $filter_sql.=$stat;
        // echo $filter_sql;
    }

    if(!empty($_GET['date_start']))
    {
        $date_start=date_create($_GET['date_start']);
    }else
    {
        $date_start="";
    }
    if(!empty($_GET['date_end']))
    {
        $date_end=date_create($_GET['date_end']);
    }else
    {
        $date_end="";
    }

    $date_filter="";
    if(!empty($date_start))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" date_from >= :date_start";
        $bindings[]=array('key'=>'date_start','val'=>date_format($date_start,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    $date_filter="";
    if(!empty($date_end))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" date_to <= :date_end";
        $bindings[]=array('key'=>'date_end','val'=>date_format($date_end,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

function jp_bind($bindings)
{
    $return_array=array();
    if ( is_array( $bindings ) ) {
            for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) {
                //$binding = $bindings[$i];
                // $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
                $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
            }
        }

        return $return_array;
}
$whereAll.=$filter_sql;
$where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;



$bindings=jp_bind($bindings);
$complete_query="SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
             FROM `vw_employees_change_shift` {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM `vw_employees_change_shift` {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
