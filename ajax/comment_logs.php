<?php
require_once("../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'message','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'sender','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_sent','dt' => ++$index ,'formatter'=>function($d,$row){
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

    $whereAll=" request_type=:request_type and request_id=:request_id ";
    $filter_sql="";
    $filter_sql.=" ";
    $bindings[]=array('key'=>'request_type','val'=>$_GET['request_type'],'type'=>0);
    $bindings[]=array('key'=>'request_id','val'=>$_GET['request_id'],'type'=>0);

    

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
$complete_query="SELECT message,
                (SELECT CONCAT(last_name,', ',first_name,' ',middle_name) FROM employees e WHERE e.id=sender_id) as sender,
                (SELECT CONCAT(last_name,', ',first_name,' ',middle_name) FROM employees e WHERE e.id=receiver_id) as receiver,
                DATE_FORMAT(date_sent,'".DATE_FORMAT_SQL.' '.TIME_FORMAT_SQL."') as date_sent
                FROM comments 
                {$where} {$order} {$limit}";

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
            
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();
$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM `comments` {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
