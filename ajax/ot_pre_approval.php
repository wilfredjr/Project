<?php
require_once("../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    // array( 'db' => 'code','dt' => ++$index ,'formatter'=>function($d,$row){
    //     return htmlspecialchars($d);
    // }),
    // array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function($d,$row){
    //     return htmlspecialchars($d);
    // }),
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
    array( 'db' => 'worked_done','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'status','dt' => ++$index ,'formatter'=>function($d,$row){
      $status='';
      if (strpos($d, 'Supervisor') !== false){
        $status=str_replace("Supervisor","Level 1",$d);
        return htmlspecialchars($status);
      }
      else if (strpos($d, 'Final Approver') !== false) {
        $status=str_replace("Final Approver","Level 2",$d);
        return htmlspecialchars($status);
      }
        else {
              return htmlspecialchars($d);
        }
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";
            if($row['status']=="Query (Supervisor)"):
                $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            endif;
            if($row['status']<>'Approved' && $row['status']<>'Cancelled' && $row['status']<>'Rejected (Supervisor)'):
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='type' value='pre_overtime'>";
                $action_buttons.=" <button class='btn btn-sm btn-danger' value='pre_overtime' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
            endif;
            if($row['status']=='Approved' && $row['if_proceed']=='0'):
                    $action_buttons.="<form method='post' action='frm_overtime_request.php?id={$row['id']}' onsubmit='return confirm(\"Proceed to OT Claim?\")' style='display:inline'>";
                    //$action_buttons.="<input type='hidden' name='type' value='pre_overtime'>";
                    $action_buttons.=" <button class='btn btn-sm btn-success' value='pre_overtime' title='Proceed to OT Claim'><span class='fa fa-arrow-circle-right'></span></button></form>";
            endif;

            return $action_buttons;
        }
    ),
    array( 'db' => 'if_proceed','dt' => ++$index ,'formatter'=>function($d,$row){
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
  //   supervisor,
  //   no_hours,
  //   worked_done,
  //   status,
  //   ot_date,
  //   time_from,
  //   time_to,
  //   date_filed,
  //   if_proceed
  //   FROM vw_employees_ot_pre
  //   ",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']));


$whereAll=" employee_id=:employee_id ";
$filter_sql="";
$filter_sql.=" ";
$bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

if(!empty($_GET['status']))
{
    $stat=" status=:status ";
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'status','val'=>$_GET['status'],'type'=>0);
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
             FROM `vw_employees_ot_pre` {$where} {$order} {$limit}";
             //echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM `vw_employees_ot_pre` {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
