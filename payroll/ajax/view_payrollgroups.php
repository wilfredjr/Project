<?php

require_once("../../support/config.php");
if(!isLoggedIn())
{
    toLogin();
    die();
}

$primaryKey ='payroll_group_id';
$index=-1;

$columns = array(
    array( 'db' => 'name','dt' => ++$index,'formatter'=>function($d,$row)
    {
    	return htmlspecialchars($d);
    }),
    array( 'db' => 'website','dt' => ++$index,'formatter'=>function($d,$row)
    {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'address','dt' => ++$index,'formatter'=>function($d,$row)
    {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'email','dt' => ++$index,'formatter'=>function($d,$row)
    {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'mobile_no','dt' => ++$index,'formatter'=>function($d,$row)
    {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'bank_account_number','dt' => ++$index,'formatter'=>function($d,$row)
    {
        return htmlspecialchars($d);
    }),
    array(  'db'        => 'payroll_group_id',
            'dt'        => ++$index,
            'formatter' => function( $d, $row ) 
    {
        $action_buttons="";
        $action_buttons.="<a class='btn-s btn-sm btn-danger btn-flat' title='Update Paygroup' href='frm_payrollgroup.php?pg_id=" .$d. "'>  <span class='fa fa-edit'></span></a>&nbsp;";
        $action_buttons.="<a class='btn-s btn-sm btn-danger btn-flat' title='Delete Paygroup' href='delete_payrollgroup.php?id=" .$d. "' onclick='return confirm(\"Are you sure to delete this record?\")'>  <span class='fa fa-trash'></span></a>&nbsp;";
        return $action_buttons;
    })
);  

require( '../../support/ssp.class.php' );


    
$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";

$filter_sql="";
$company_sql= "";

$whereAll=" is_deleted=0";
$whereAll.=$filter_sql;

function jp_bind($bindings)
{
    $return_array=array();
    if ( is_array( $bindings ) ) 
    {
        for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) 
        {
            //$binding = $bindings[$i];
            // $stmt->bindValueb   	qA@( $binding['key'], $binding['val'], $binding['type'] );
            $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }
    return $return_array;
}
$where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;
$bindings=jp_bind($bindings);
$complete_query="SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
             FROM `payroll_groups`  {$where} {$order} {$limit}";    
//NEED TO CREATE VIEWS.

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(payroll_group_id) FROM `payroll_groups` {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsFiltered;
$json['recordsFiltered']=$recordsFiltered;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
