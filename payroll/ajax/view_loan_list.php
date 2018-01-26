<?php

require_once("../../support/config.php"); 
if(!isLoggedIn())
{
    toLogin();
    die();
}
// if(!AllowUser(array(1,2)))
// {
//     redirect("index.php");
// }


$primaryKey ='loan_id';
$index=-1;

$columns = array(
array( 'db' => 'loan_id','dt' => ++$index,'formatter'=>function($d,$row)
{
    return htmlspecialchars($d);
} ),
array( 'db' => 'loan_name','dt' => ++$index,'formatter'=>function($d,$row)
{
   return htmlspecialchars($d);
} ),

array(
        'db'        => 'loan_id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) 
        {
            $action_buttons="";
               
                    // if($row['is_active'] ==1){
                    //     $action_buttons.="<a class='btn btn-flat btn-sm btn-success' href='activate.php?id={$row['user_id']}' onclick='return confirm(\"Are you sure you want to deactivate this user?\")'><span class='fa fa-lock' ></span> Deactivate</a>&nbsp;";
                    // }else{
                    //     $action_buttons.="<a class='btn btn-flat btn-sm btn-success' href='activate.php?id={$row['user_id']}' onclick='return confirm(\"Are you sure you want to activate this user?\")'><span class='fa fa-unlock' ></span> Activate</a>&nbsp;";
                    // }
                    $action_buttons.="<a class='btn btn-sm btn-danger btn-flat' title='Update loan' href='frm_loan_list.php?loan_id=" .$d. "'>  <span class='fa fa-edit'></span></a>&nbsp;";
                     $action_buttons.="<a class='btn btn-sm btn-danger btn-flat' onclick='return confirm(\"This loan will be deleted.\")' title='Delete Loan' href='delete_loan_type.php?id={$row['loan_id'] }'> <span class='fa fa-trash'></span></a>&nbsp;";
                    
      
            return $action_buttons;
        }
    ),
// array(
//         'db'        => 'is_active',
//         'dt'        => ++$index,
//         'formatter' => function( $d, $row ) 
//         { 
//             return "";
//         }
//     ),  

);  

require( '../../support/ssp.class.php' );


    
$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";

$filter_sql="  ";




// if(!empty($_GET['branch_name']))
// {
    
//     $branch_name_sql=":branch_name";
//     $inputs['branch_name']=$_GET['branch_name'];
//     $filter_sql.=" AND branches.branch_name LIKE ".$branch_name_sql."";
//     $bindings[]=array('key'=>'branch_name','val'=>"%".$_GET['branch_name']."%",'type'=>0);
//     //$company_sql = !empty($_GET['company']);
// }
$whereAll=" loans.is_deleted=0"; //dagdag ung nakasession na user :)
$whereAll.=$filter_sql;
function jp_bind($bindings)
{
    $return_array=array();
    if ( is_array( $bindings ) ) 
    {
        for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) 
        {
            //$binding = $bindings[$i];
            // $stmt->bindValueb    qA@( $binding['key'], $binding['val'], $binding['type'] );
            $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }
    return $return_array;
}
$where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;
$bindings=jp_bind($bindings);
$complete_query="SELECT loan_id, 
loan_name FROM loans 
{$where} {$order} {$limit}";    
//NEED TO CREATE VIEWS.

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(loan_id) FROM `loans` {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsFiltered;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);

// $resTotalLength = SSP::sql_exec( $db, $bindings,
//             "SELECT COUNT(`{$primaryKey}`)
//              FROM   `$table` ".
//             $whereAllSql
//         );

die;
