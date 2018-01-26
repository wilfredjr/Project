<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'ref_no','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'month/year','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'ss_details','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'ec_details','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'ref_no',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) 
        {
            $action_buttons="";
                    
                $action_buttons.="<a class='btn btn-sm btn-success btn-flat' title='Download R5' href='report_r5.php?ref_no={$row['ref_no']}'>  <span class='fa fa-file-excel-o'></span></a>&nbsp;";
                $action_buttons.="<a class='btn btn-sm btn-success btn-flat' title='Update R5' href='frm_sss_r5.php?ref_no={$row['ref_no']}'>  <span class='fa fa-edit'></span></a>&nbsp;";
                
                $action_buttons.="<a class='btn btn-sm btn-danger btn-flat' onclick='return confirm(\"Are you sure to delete this file?\")' title='Delete Room' href='delete_r5_file.php?id={$row['ref_no']}'> <span class='fa fa-trash'></span></a>&nbsp;";        
      
            return $action_buttons;
        }
    )
    


);


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);
$bindings=array();
$where ="";
$whereAll="  sss_r5_main.is_deleted = 0 ";
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

if(!empty($_GET['month_year'])){
    //CONVERTU JUTSU!
    $for_date_of = $_GET['month_year'] .'-01';
    $filter_sql.=" AND for_date_of = '".$for_date_of."'";
    $bindings[]=array('key'=>'for_date_of','val'=>$for_date_of,'type'=>0);
}else{
        $whereAll.= " AND (YEAR(for_date_of) = '".date("Y")."')";
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
$join="";
$complete_query=" SELECT 
                    ref_no,
                    DATE_FORMAT(for_date_of,'%M %Y')as `month/year`,
                   CONCAT(amt_ss_contribution,' (',ss_contribution,')') as ss_details,                                         CONCAT(amt_ec_contribution,' (',ec_contribution,')') as ec_details
                FROM sss_r5_main
                {$join} {$where} {$order} {$limit} ";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(ref_no) FROM sss_r5_main {$join} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

// var_dump($complete_query);
// die;

echo json_encode($json);
die;
