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
    array( 'db' => 'time_in','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'time_out','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'daily_rate','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'hourly_rate','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'night_rate','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'late','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),

    // array( 'db' => 'no_of_work_hours','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return htmlspecialchars($d);
    // }),
    array( 'db' => 'absent','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'worked_hours','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'special_holiday','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'legal_holiday','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'no_of_work_hours_regular','dt' => ++$index ,'formatter'=>function ($d, $row) { 
       return htmlspecialchars($d); 
    }),
    array( 'db' => 'overtime','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2); //REGULAR
    }),
    // array( 'db' => 'no_of_work_hours_premium','dt' => ++$index ,'formatter'=>function ($d, $row) { 
    //    return number_format($d,2); //PREMIUM
    // }),
    // array( 'db' => 'premium','dt' => ++$index ,'formatter'=>function ($d, $row) { 
    //    return number_format($d,2); //PREMIUM
    // }),
    array( 'db' => 'overtime_special_holiday','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'overtime_legal_holiday','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2);
    }),
    array( 'db' => 'rest_day','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'rest_day_special_holiday','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'rest_day_legal_holiday','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2);
    }),
    array( 'db' => 'ordinary_day_night_shift','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2);
    }),
    array( 'db' => 'special_holiday_night_shift','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2);
    }),
    array( 'db' => 'legal_holiday_night_shift','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'night_diff_ordinary_ot','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2);
    }),
    array( 'db' => 'night_diff_restday_ot','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2);
    }),
    array( 'db' => 'rest_day_night_shift','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2);
    }),
    array( 'db' => 'special_holiday_rest_day_night_shift','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2);
    }),
    array( 'db' => 'legal_holiday_rest_day_night_shift','dt' => ++$index ,'formatter'=>function ($d, $row) {
       return number_format($d,2);;
    })
);

require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);
$where = SSP::filter($_GET, $columns, $bindings);

$whereAll="";
$filter_sql="";
// $bindings=array();
$filter_sql.=" d.payroll_id=:id ";
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
$join=" INNER JOIN employees e ON e.id=d.employee_id ";
$complete_query="SELECT  d.employee_id, e.code, CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name, d.payroll_id, d.time_in, d.time_out, d.daily_rate, d.hourly_rate, d.night_rate, d.late,  d.absent, d.worked_hours, d.no_of_work_hours_regular,d.overtime,d.overtime_special_holiday, d.overtime_legal_holiday, d.special_holiday, d.legal_holiday, d.rest_day, d.rest_day_special_holiday, d.rest_day_legal_holiday, d.ordinary_day_night_shift, d.rest_day_night_shift, d.special_holiday_night_shift, d.legal_holiday_night_shift, d.special_holiday_rest_day_night_shift, d.legal_holiday_rest_day_night_shift,d.night_diff_ordinary_ot,d.night_diff_restday_ot FROM dtr_compute d {$join} {$where} {$order} {$limit}";

// echo $complete_query.' '.$order.' '.$limit.'<br>';
// echo "<pre>";
// print_r($columns);
// echo "</pre>";

// echo $complete_query;


$data=$con->myQuery($complete_query,$bindings)->fetchAll();

// $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();
$recordsTotal=$con->myQuery("SELECT COUNT(d.id) FROM dtr_compute d {$where}", $bindings)->fetchColumn();

$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);


echo json_encode($json);
die;

