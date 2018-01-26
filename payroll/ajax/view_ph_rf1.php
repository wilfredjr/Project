<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'philhealth','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'birthday','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'gender','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'govde_code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'govde_eeshare','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'govde_ershare','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
);

require('../../support/ssp.class.php');

$limit = SSP::limit($_GET, $columns);
$order = "ORDER BY e.last_name ASC";
$bindings=array();
$where ="";
$whereAll=" pg.gov_desc = 'PhilHealth' AND
            DATE_FORMAT(p.date_to,'%Y-%m') = " . "'" . $_GET['month_year'] . "'";
$whereResult="";
$filter_sql="  ";

$whereAll.=$filter_sql;

function jp_bind($bindings)
{
    $return_array=array();
    if (is_array($bindings)) {
        for ($i=0, $ien=count($bindings); $i<$ien; $i++) {
            $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }

    return $return_array;
}

$where.= !empty($where) ? " AND ".$whereAll:(empty($whereAll))?"":"WHERE ".$whereAll;
$bindings=jp_bind($bindings);
$join=" INNER JOIN employees e ON e.id = pg.employee_id
        INNER JOIN payroll p ON p.payroll_code = pg.payroll_code";
$complete_query=" SELECT
                pg.employee_id,
                e.philhealth,
                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS 'employee_name', 
                e.birthday,
                e.gender,
                pg.govde_code,
                SUM(pg.govde_eeshare) as govde_eeshare,
                SUM(pg.govde_ershare) as govde_ershare
                FROM
                payroll_govde pg
                {$join} {$where}
                GROUP BY pg.employee_id
                {$order} {$limit} ";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(pg.employee_id) FROM payroll_govde pg
WHERE pg.gov_desc = 'PhilHealth' ", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsFiltered;
$json['data']=SSP::data_output($columns, $data);

// var_dump($recordsTotal);
// die();

// echo "<pre>";
// print_r($json);
// echo "</pre>";
// die();


echo json_encode($json);
die;
