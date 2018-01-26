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
    array( 'db' => 'ot_date','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),DATE_FORMAT_PHP));
    }),
    array( 'db' => 'time_from','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),TIME_FORMAT_PHP));
    }),
    array( 'db' => 'time_to','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),TIME_FORMAT_PHP));
    }),
    array( 'db' => 'no_hours','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'ot_date','dt' => ++$index ,'formatter'=>function($d,$row){
        global $con;
        #Actual Time Out
        $time_out=$con->myQuery("SELECT out_time FROM attendance WHERE DATE(in_time) = :ot_date AND employees_id = :employee_id ORDER BY out_time DESC", array("ot_date"=>$d, "employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
        return !empty($time_out)?htmlspecialchars(date_format(date_create($time_out),TIME_FORMAT_PHP)):"-";
    }),
    array( 'db' => 'ot_date','dt' => ++$index ,'formatter'=>function($d,$row){
        global $con;
        #actual Hours
        $hours=$con->myQuery("SELECT TIMEDIFF(out_time, '{$row['ot_date']} {$row['time_from']}') FROM attendance WHERE DATE(in_time) = :ot_date AND employees_id = :employee_id ORDER BY out_time DESC", array("ot_date"=>$d, "employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
        if (!empty($hours)) {
            $hms = explode(":", $hours);
            return ($hms[0] + ($hms[1]/60));
        } else {
            return 0;
        }

    }),
    array( 'db' => 'worked_done','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'project','dt' => ++$index ,'formatter'=>function($d,$row){
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
            global $con;
            $action_buttons="";
            if ($row['request_status_id']==2) {
                $overtime=$con->myQuery("SELECT id,DATE_FORMAT(ot_date,'".DATE_FORMAT_SQL."') as ot_date,DAtE_FORMAT(time_from,'".TIME_FORMAT_SQL."') as time_from,DATE_FORMAT( time_to,'".TIME_FORMAT_SQL."')as time_to FROM employees_ot WHERE id=:ot_id AND request_status_id=2 AND id NOT IN (SELECT employees_ot_id FROM employees_ot_adjustments WHERE employees_ot_id=:ot_id AND (request_status_id=2 OR request_status_id=1)) LIMIT 1",array("ot_id"=>$d))->fetch(PDO::FETCH_ASSOC);
              if (!empty($overtime)) {
                $action_buttons.="<form method='get' action='frm_overtime_adjustment_request.php' onsubmit='return confirm(\"Proceed to OT Adjustment?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='id' value='{$row['id']}'>";
                $action_buttons.=" <button class='btn btn-sm btn-success' value='' title='Proceed to OT Adjustment'><span class='fa fa-arrow-circle-right'></span></button></form>&nbsp;";
              }
              
            }
            if($row['request_status_id']==3 ):
                $action_buttons.=" <button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            else :
                $action_buttons.="<button class='btn btn-sm btn-info'  title='View Comments' onclick='query_logs(\"{$row['id']}\")'><span  class='fa fa-comment'></span></button>";
            endif;
            if($row['request_status_id']<>2 && $row['request_status_id']<>5 && $row['request_status_id']<>4 ):
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='type' value='overtime'>";
                $action_buttons.=" <button class='btn btn-sm btn-danger' value='overtime' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
            endif;


            return $action_buttons;
        }
    ),
     array( 'db' => 'request_status_id','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    })
);


require( '../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";


  // $data=$con->myQuery("SELECT
  //   id,
  //   code,
  //   employee_name,
  //   step_name,
  //   no_hours,
  //   worked_done,
  //   request_status_id,
  //   ot_date,
  //   time_from,
  //   time_to,
  //   date_filed,
  //   status
  //   FROM vw_employees_ot
  //   ",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']));

    $whereAll=" (employee_id=:employee_id OR requestor_id=:employee_id) ";
    $filter_sql="";
    $filter_sql.=" ";
    $bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

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
    if(!empty($_GET['project_id']))
    {
        $stat=" project_id=:project_id ";
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'project_id','val'=>$_GET['project_id'],'type'=>0);
        $filter_sql.=$stat;
        // echo $filter_sql;
    }
    if(!empty($_GET['ot_date_start']))
    {
        $ot_date_start=date_create($_GET['ot_date_start']);
    }else
    {
        $ot_date_start="";
    }
    if(!empty($_GET['ot_date_end']))
    {
        $ot_date_end=date_create($_GET['ot_date_end']);
    }else
    {
        $ot_date_end="";
    }

    $date_filter="";
    if(!empty($ot_date_start))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" ot_date >= :ot_date_start";
        $bindings[]=array('key'=>'ot_date_start','val'=>date_format($ot_date_start,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    $date_filter="";
    if(!empty($ot_date_end))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" ot_date <= :ot_date_end";
        $bindings[]=array('key'=>'ot_date_end','val'=>date_format($ot_date_end,'Y-m-d'),'type'=>0);
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
             FROM `vw_employees_ot` {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();
$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM `vw_employees_ot` {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
