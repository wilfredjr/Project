<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'pagibig','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'lastname','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'firstname','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'middlename','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'eeshare','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'ershare','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'is_terminated','dt' => ++$index ,'formatter'=>function ($d, $row) {
    	if ($d == 1){
    		return htmlspecialchars('Terminated');
    	}else{
    		return htmlspecialchars('Active'); 
    	}
        
    }),

    


);


require('../../support/ssp.class.php');
$exploded_str =explode("-",$_GET['month_year']);

$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);
$bindings=array();
$where ="";
$whereAll="  payroll.is_deleted = 0 AND gov_desc = 'HDMF' and (YEAR(STR_TO_DATE(payroll.date_to,'%Y-%m-%d'))= ". $exploded_str[0] . " and MONTH(STR_TO_DATE(payroll.date_to,'%Y-%m-%d')) =" . $exploded_str[1] . " )";
$whereResult="";
$filter_sql="  ";

// if(!empty($_GET['date_start']) && !empty($_GET['date_end']))
// {
//     // var_dump($_GET['date_purchased']);
//     // die;
   // (YEAR(for_date_of) = '".date("Y")."') AND
//     $date_start_sql=":date_start";
//     $date_end_sql=":date_end";
//     $date_start= date_create($_GET['date_start']);
//     $date_end= date_create($_GET['date_end']);
//     $inputs['date_start']=date_format($date_start,'Y-m-d');
//     $inputs['date_end']=date_format($date_end,'Y-m-d');


//     $filter_sql.=" AND e.joined_date BETWEEN ".$date_start_sql."  AND " .$date_end_sql ;
//     $bindings[]=array('key'=>'date_start','val'=>$inputs['date_start'],'type'=>0);
//     $bindings[]=array('key'=>'date_end','val'=>$inputs['date_end'],'type'=>0);

// }

// if(!empty($_GET['month_year'])){
//     //CONVERTU JUTSU!
//     $for_date_of = $_GET['month_year'] .'-01';
//     $filter_sql.=" AND for_date_of = '".$for_date_of."'";
//     $bindings[]=array('key'=>'for_date_of','val'=>$for_date_of,'type'=>0);
// }else{
//         $whereAll.= " AND (YEAR(for_date_of) = '".date("Y")."')";
// }
// $whereAll.=$filter_sql;

// function jp_bind($bindings)
// {
//     $return_array=array();
//     if (is_array($bindings)) {
//         for ($i=0, $ien=count($bindings); $i<$ien; $i++) {
//             $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
//         }
//     }

//     return $return_array;
// }

$where.= !empty($where) ? " AND ".$whereAll:(empty($whereAll))?"":"WHERE ".$whereAll;
//$bindings=jp_bind($bindings);
$join=" INNER JOIN payroll_govde ON payroll_govde.employee_id = employees.id
INNER JOIN payroll ON payroll.payroll_code = payroll_govde.payroll_code ";
$complete_query=" SELECT
employees.pagibig as `pagibig`,
employees.last_name as `lastname`,
employees.first_name as `firstname`,
employees.middle_name as`middlename`,
payroll_govde.govde_eeshare as `eeshare`,
payroll_govde.govde_ershare as `ershare`,
employees.is_terminated as `is_terminated`
FROM
employees {$join} {$where} {$order} {$limit} ";

$data=$con->myQuery($complete_query)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(employees.pagibig) FROM employees {$join} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

// var_dump($complete_query);
// die;

echo json_encode($json);
die;
