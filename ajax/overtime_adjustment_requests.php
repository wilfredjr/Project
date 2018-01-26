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
        return htmlspecialchars($d);
    }),
    array( 'db' => 'ot_date','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'orig_time_in','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'orig_time_out','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'orig_no_hours','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'adj_time_in','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'adj_time_out','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'adj_no_hours','dt' => ++$index ,'formatter'=>function($d,$row){
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
            if($row['request_status_id']==3):
            $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            endif;
            if($row['request_status_id']<>2 && $row['request_status_id']<>5 && $row['request_status_id']<>4 ):
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='type' value='ot_adj'>";
                $action_buttons.=" <button class='btn btn-sm btn-danger' value='ob' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
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


 $query="SELECT
            id,
            code,
            employee_name,
            step_name,
            status,
            DATE_FORMAT(ot_date,'".DATE_FORMAT_SQL."') as ot_date,
            DATE_FORMAT(orig_time_in,'".TIME_FORMAT_SQL."') as orig_time_in,
            DATE_FORMAT(orig_time_out,'".TIME_FORMAT_SQL."') as orig_time_out,
            orig_no_hours,
            DATE_FORMAT(adj_time_in,'".TIME_FORMAT_SQL."') as adj_time_in,
            DATE_FORMAT(adj_time_out,'".TIME_FORMAT_SQL."') as adj_time_out,
            adj_no_hours,
            department_id,
            department,
            request_status_id,
            previous_approver,
            DATE_FORMAT(date_filed,'".DATE_FORMAT_SQL."') as date_filed
        FROM vw_employees_ot_adjustments";
                        
$filter_sql="";
$filter_sql.=" (employee_id=:employee_id OR requestor_id=:employee_id) ";
$bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

if(!empty($_GET['emp_id']))
{
    $emp=" employee_id=:emp_id ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'emp_id','val'=>$_GET['emp_id'],'type'=>0);
    $filter_sql.=$emp;
}
 if(!empty($_GET['dep_id']))
 {

      $dep=" department_id=:dept_id ";    
      if(!empty($filter_sql))
    {
          $filter_sql.=" AND ";
      }
        $bindings[]=array('key'=>'dept_id','val'=>$_GET['dep_id'],'type'=>0);
    $filter_sql.=$dep;
 }
if(!empty($_GET['start_date']))
{
    $date_start_file=date_create($_GET['start_date']);
}else
{
    $date_start_file="";
}
if(!empty($_GET['end_date']))
{
    $date_end_file=date_create($_GET['end_date']);
}else
{
    $date_end_file="";
}

$date_filter="";
if(!empty($date_start_file))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" ot_date >= :date_start";
    $bindings[]=array('key'=>'date_start','val'=>date_format($date_start_file,'Y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;

$date_filter="";
if(!empty($date_end_file))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" ot_date <= :date_end";
    $bindings[]=array('key'=>'date_end','val'=>date_format($date_end_file,'Y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;
if(!empty($_GET['status']))
{
    $stat=" request_status_id=:status ";
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'status','val'=>$_GET['status'],'type'=>0);
    $filter_sql.=$stat;
    // echo $filter_sql;
}


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
// $where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;
$where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";
$bindings=jp_bind($bindings);
// $complete_query="SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
             // FROM `vw_employees_leave` {$where} {$order} {$limit}";
$complete_query="{$query} {$where} {$order} {$limit}";
// var_dump($_GET);
            // echo $complete_query;
//             die;
            // var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM `vw_employees_ot_adjustments` {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;