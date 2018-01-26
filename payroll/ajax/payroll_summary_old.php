<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'tax_compensation','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'basic_salary','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'payroll_adjustment_plus','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'overtime','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'receivable','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'tax_earning','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'absent','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'late','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'payroll_adjustment_minus','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'company_deduction','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'government_deduction','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'withholding_tax','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'total_deduction','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'loan','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'de_minimis','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => '13_month','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'net_pay','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    })
    );

require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);
$where = SSP::filter($_GET, $columns, $bindings);

$whereAll="";
$filter_sql="";

$filter_sql.=" pd.payroll_id=:id ";
$bindings[]=array('key'=>'id','val'=>$_GET['id'],'type'=>0);

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
$where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?" WHERE ".$whereAll:"";

$bindings=jp_bind($bindings);
$join=" INNER JOIN employees e ON pd.employee_id = e.id ";
$complete_query="SELECT
                e.code,
                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as 'employee_name',
                pd.tax_compensation,
                pd.basic_salary,
                pd.late,
                pd.absent,
                pd.overtime,
                pd.receivable,
                pd.de_minimis,
                pd.company_deduction,
                pd.government_deduction,
                pd.tax_earning,
                pd.withholding_tax,
                pd.total_deduction,
                pd.payroll_adjustment_minus,
                pd.payroll_adjustment_plus,
                pd.13_month,
                pd.net_pay,
                pd.loan
                FROM
                payroll_details pd {$join} {$where} {$order} {$limit}";

// echo $complete_query.' '.$order.' '.$limit.'<br>';
// echo "<pre>";
// print_r($columns);
// echo "</pre>";

$data=$con->myQuery($complete_query,$bindings)->fetchAll();

// $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();
$recordsTotal=$con->myQuery("SELECT COUNT(pd.id) FROM payroll_details pd {$where}", $bindings)->fetchColumn();

$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);


echo json_encode($json);
die;

