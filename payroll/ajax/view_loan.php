<?php
    require_once("../../support/config.php"); 
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $primaryKey = 'emp_loan_id';
    $index=-1;
    $columns = array(
        array('db' => 'code','dt' => ++$index,'formatter' => function( $d, $row ) {
                return htmlspecialchars($d);
        }),
        array('db' => 'loan_name','dt' => ++$index,'formatter' => function( $d, $row ) {
                return htmlspecialchars($d);
        }),
        array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'loan_amount','dt' => ++$index ,'formatter'=>function($d,$row){
            return number_format($d,2);
        }),
        // array( 'db' => 'amount_paid','dt' => ++$index ,'formatter'=>function($d,$row){
        //     return number_format($d,2);
        // }),
        array( 'db' => 'balance','dt' => ++$index ,'formatter'=>function($d,$row){
            return number_format($d,2);
        }),
        array( 'db' => 'status','dt' => ++$index ,'formatter'=>function($d,$row){   
            return htmlspecialchars($d);
        }),
        array(
        'db'        => 'emp_loan_id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";
            $action_buttons.="<a class='btn-s btn-sm btn-info btn-danger' title='View Loan' href='loan_payment_history.php?id={$d}'>  <span class='fa fa-eye'></span></a>";      
            return $action_buttons;
        })
    );

    require('../../support/ssp.class.php');

$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";

$filter_sql="";

// $whereAll=" is_deleted=0";
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
// $where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;
$bindings=jp_bind($bindings);
$complete_query="SELECT 
            emp_loans.emp_loan_id,
            emp_loans.emp_loan_id as code, 
            loans.loan_name as loan_name, 
            emp_loans.loan_amount as loan_amount,
            -- emp_loans_det.amount_paid as amount_paid, 
            emp_loans.balance as balance, 
            CONCAT(employees.last_name,', ',employees.first_name,' ',employees.middle_name) AS employee_name, 
            loan_status.status_name as status 
            FROM `emp_loans` 
            INNER JOIN employees ON employees.id=emp_loans.employee_id 
            INNER JOIN loans ON loans.loan_id=emp_loans.loan_id
            -- INNER JOIN emp_loans_det ON emp_loans_det.emp_loan_id=emp_loans.emp_loan_id 
            INNER JOIN loan_status ON loan_status.status_id=emp_loans.status_id {$where} {$order} {$limit}";    

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();
$recordsTotal=$con->myQuery("SELECT COUNT(emp_loan_id) FROM emp_loans {$where}",$bindings)->fetchColumn();

$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);

// $resTotalLength = SSP::sql_exec( $db, $bindings,
//             "SELECT COUNT(`{$primaryKey}`)
//              FROM   `$table` ".
//             $whereAllSql
//         );

die;