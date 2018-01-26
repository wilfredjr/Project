<?php
require_once("../../support/config.php"); 

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'code','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),

     array( 'db' => 'day','dt' => ++$index ,'formatter'=>function($d,$row){
        return number_format($d,2);
    }),

      array( 'db' => 'hour','dt' => ++$index ,'formatter'=>function($d,$row){
        return number_format($d,2);
    }),

       array( 'db' => 'night','dt' => ++$index ,'formatter'=>function($d,$row){
        return number_format($d,2);
    }),

      array( 'db' => 'absent','dt' => ++$index ,'formatter'=>function($d,$row){
        return number_format($d,2);
    }),

      array( 'db' => 'late','dt' => ++$index ,'formatter'=>function($d,$row){
        return number_format($d,2);
    }),

      array( 'db' => 'emp_ot','dt' => ++$index ,'formatter'=>function($d,$row){
        return number_format($d,2);
    })
);
 

require( '../../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";


$query="SELECT e.code,p.absent AS absent,CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as name, p.late AS late,p.overtime AS emp_ot, d.daily_rate AS day, d.hourly_rate hour,d.night_rate as night FROM payroll_details p INNER JOIN employees e ON p.employee_id=e.id INNER JOIN dtr_compute d ON d.employee_id=p.employee_id";

$filter_sql="";

if(!empty($_GET['pay_code']))
{
    $ecode=" p.payroll_code=:pay_code ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'pay_code','val'=>$_GET['pay_code'],'type'=>0);
    $filter_sql.=$ecode;
}

function jp_bind($bindings)
{
    $return_array=array();
    if ( is_array( $bindings ) ) 
    {
        for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) 
        {
            $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }

    return $return_array;
}
$whereAll.=$filter_sql;
// $where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;
$where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";

$bindings=jp_bind($bindings);
$complete_query="{$query} {$where} {$order} {$limit}";
            // echo $complete_query;
            // var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(p.id) FROM payroll_details p {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsFiltered;
$json['recordsFiltered']=$recordsFiltered;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;