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

     array( 'db' => 'comde_desc','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),

      array( 'db' => 'emp_comdeduc','dt' => ++$index ,'formatter'=>function($d,$row){
        return number_format($d,2);
    })
);
 

require( '../../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";


$query="SELECT
e.`code`,
company_deductions.comde_desc,
CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS `name`,
p.company_deduction AS emp_comdeduc
FROM
payroll_details AS p
INNER JOIN employees AS e ON e.id = p.employee_id
INNER JOIN employee_company_deductions AS ec ON ec.emp_code = e.`code`
INNER JOIN company_deductions ON ec.comde_code = company_deductions.comde_code";

$filter_sql="";

if(!empty($_GET['pay_code']))
{
    $ecode=" p.payroll_id=:pay_code ";    
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