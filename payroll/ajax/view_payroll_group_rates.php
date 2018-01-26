<?php

require_once("../../support/config.php");
if(!isLoggedIn())
{
    toLogin();
    die();
}
// if(!AllowUser(array(1,2,3)))
// {
//     redirect("index.php");
// }

$primaryKey ='payroll_group_id';
$index=-1;

$columns = array(
array( 'db' => 'payroll_group_id','dt' => ++$index,'formatter'=>function($d,$row)
{
	return htmlspecialchars($d);
} ),
array( 'db' => 'name','dt' => ++$index,'formatter'=>function($d,$row)
{
	return htmlspecialchars($d);
} ),
array( 'db' => 'set_rates','dt' => ++$index,'formatter'=>function($d,$row)
{
    $desc="";
    if($d==1){
        $desc.="<p class='text-green'>Payroll Group Settings are set. </p>";
    }else{
        $desc.="<p class='text-red'>Payroll Group Settings are not set. </p>";
    }
  
    return ($desc);
} ),

array(
        'db'        => 'payroll_group_id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) 
        {
            $action_buttons="";
                
                //     if($row['approval_status']=="Approved-Undone"):
                //     $action_buttons.="<a class='btn btn-sm btn-success btn-flat' title='Proceeds' href='frm_loan_application.php?tab=2&id={$row['loaner_id']}&u={$row['loan_code']}'><span class='fa fa-arrow-right'></span></a>&nbsp;";
                // else:
                //     $action_buttons.="<a class=' btn btn-sm btn-success btn-flat' title='View Details' href='forApprovalDetails.php?id={$d}'><span class='fa fa-eye'></span></a>&nbsp";
                //     $action_buttons.="<button class='btn btn-sm btn-danger btn-flat'  title='Reject Loan Application' onclick='reject(\"{$row['loan_code']}\")'><span  class='fa fa-close'></span></button>&nbsp;";
                // endif;

                    $action_buttons.="<a class='btn-s btn-sm btn-danger btn-flat' title='Update Paygroup Settings' href='frm_payroll_group_rate.php?pg_id=" .$d. "'>  <span class='fa fa-edit'></span></a>";
                
      
            return $action_buttons;
        }
    ),
array(
        'db'        => 'payroll_group_id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) 
        { 
            return "";
        }
    ),  

);  

require( '../../support/ssp.class.php' );


    
$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";

$filter_sql="";
$company_sql= "";

// if(!empty($_GET['company']))
// {
//     $company_sql=":company";
//     $inputs['company']=$_GET['company'];
//     $filter_sql.=" AND company_id = ".$company_sql."";
//     $bindings[]=array('key'=>'company','val'=>$_GET['company'],'type'=>0);
//     //$company_sql = !empty($_GET['company']);
// }

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

// $resTotalLength = SSP::sql_exec( $db, $bindings,
//             "SELECT COUNT(`{$primaryKey}`)
//              FROM   `$table` ".
//             $whereAllSql
//         );

die;
