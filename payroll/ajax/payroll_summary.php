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
        return htmlspecialchars($d);
    }),
    array( 'db' => 'overtime','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'receivable','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'de_minimis','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'late','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'absent','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'tax_earning','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    // array( 'db' => 'tax_allowance','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return number_format($d,2);
    // }),
    array( 'db' => 'company_deduction','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    // array( 'db' => 'government_deduction','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return number_format($d,2);
    // }),
    array( 'db' => 'sss','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'philhealth','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'hdmf','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'total','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'withholding_tax','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'total_deduction','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'payroll_adjustment_minus','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'loan','dt' => ++$index ,'formatter'=>function ($d, $row) {
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

$filter_sql.=" pd.payroll_id=" . $_GET['id'];
//$bindings[]=array('key'=>'id','val'=>$_GET['id'],'type'=>0);

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
// $join=" INNER JOIN employees e ON pd.employee_id = e.id ";
$complete_query="SELECT 
    pay_details.code,
    pay_details.employee_name,
    pay_details.tax_compensation, 
    pay_details.basic_salary, 
    pay_details.late, 
    pay_details.absent, 
    pay_details.overtime, 
    pay_details.tax_allowance, 
    pay_details.receivable, 
    pay_details.de_minimis, 
    pay_details.company_deduction, 
    pay_details.government_deduction, 
    pay_details.tax_earning, 
    pay_details.withholding_tax, 
    pay_details.total_deduction, 
    pay_details.payroll_adjustment_minus, 
    pay_details.payroll_adjustment_plus, 
    pay_details.13th_month, 
    pay_details.net_pay, 
    pay_details.loan,
    sss.govde_eeshare as 'sss',
    philhealth.govde_eeshare as 'philhealth',
    hdmf.govde_eeshare as 'hdmf',
    (sss.govde_eeshare + philhealth.govde_eeshare + hdmf.govde_eeshare) as 'total'
FROM (SELECT 
            e.id,
            e.code, 
            pd.payroll_code,
            CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as 'employee_name', 
            ts.code as 'tax_compensation', 
            pd.basic_salary, 
            pd.late, 
            pd.absent, 
            pd.overtime, 
            pd.tax_allowance, 
            pd.receivable, 
            pd.de_minimis, 
            pd.company_deduction, 
            pd.government_deduction, 
            pd.tax_earning, 
            pd.withholding_tax, 
            pd.total_deduction, 
            pd.payroll_adjustment_minus, 
            pd.payroll_adjustment_plus, 
            pd.13th_month, pd.net_pay, 
            pd.loan 
            FROM payroll_details pd 
            INNER JOIN employees e ON pd.employee_id = e.id 
            INNER JOIN tax_status ts ON pd.tax_compensation = ts.id {$where}) as pay_details
            LEFT OUTER JOIN (SELECT payroll_code,employee_id,govde_eeshare 
                                             FROM payroll_govde 
                                             WHERE gov_desc = 'SSS') as sss 
                                             ON pay_details.payroll_code = sss.payroll_code AND pay_details.id = sss.employee_id
            LEFT OUTER JOIN (SELECT payroll_code,employee_id,govde_eeshare 
                                             FROM payroll_govde 
                                             WHERE gov_desc = 'PhilHealth') as philhealth 
                                             ON pay_details.payroll_code = philhealth.payroll_code AND pay_details.id = philhealth.employee_id
            LEFT OUTER JOIN (SELECT payroll_code,employee_id,govde_eeshare 
                                             FROM payroll_govde 
                                             WHERE gov_desc = 'HDMF') as hdmf 
                                             ON pay_details.payroll_code = hdmf.payroll_code AND pay_details.id = hdmf.employee_id
 {$order} {$limit}";

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

