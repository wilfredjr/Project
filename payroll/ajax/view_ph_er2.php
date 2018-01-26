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
    array( 'db' => 'description','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'basic_salary','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return number_format($d,2);
    }),
    array( 'db' => 'joined_date','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'company','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),

);


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$bindings=array();
$where ="";
$whereAll=" e.is_deleted = 0 AND e.is_terminated = 0 ";
$whereResult="";
$filter_sql="  ";

if(!empty($_GET['date_start']) && !empty($_GET['date_end']))
{
    // var_dump($_GET['date_purchased']);
    // die;
    
    $date_start_sql=":date_start";
    $date_end_sql=":date_end";
    $date_start= date_create($_GET['date_start']);
    $date_end= date_create($_GET['date_end']);
    $inputs['date_start']=date_format($date_start,'Y-m-d');
    $inputs['date_end']=date_format($date_end,'Y-m-d');


    $filter_sql.=" AND e.joined_date BETWEEN ".$date_start_sql."  AND " .$date_end_sql ;
    $bindings[]=array('key'=>'date_start','val'=>$inputs['date_start'],'type'=>0);
    $bindings[]=array('key'=>'date_end','val'=>$inputs['date_end'],'type'=>0);

}

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
$join=" INNER JOIN job_title jt ON e.job_title_id = jt.id AND jt.is_deleted = 0 
                LEFT JOIN employees_employment_history eh ON e.id = eh.employee_id AND eh.is_deleted = 0 ";
$complete_query=" SELECT 
                e.philhealth,
                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as 'employee_name',
                jt.description,
                e.basic_salary,
                e.joined_date,
                eh.company
                FROM employees e {$join} {$where} {$order} {$limit} ";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(e.id) FROM employees e {$join} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

// var_dump($recordsTotal);
// die;

echo json_encode($json);
die;
