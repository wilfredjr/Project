<?php
require_once("../../support/config.php"); 

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'nature_of_business','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
     array( 'db' => 'tax_rate','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d)." %";
    }),
      array( 'db' => 'atc_type','dt' => ++$index ,'formatter'=>function($d,$row){
        if ($d==1) 
        {
            return "Individual";
        }else
        {
            return "Corporation";
        }
    }),
       array( 'db' => 'atc_code','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array('db' => 'id','dt' => ++$index,'formatter' => function( $d, $row ) {
        $action_buttons = "";
        $action_buttons .= "<a class='btn-s btn-sm btn-primary' href='bir_1601_e_form.php?id={$d}'><span class='fa fa-pencil'></span></a>&nbsp;";
        $action_buttons .= "<a class='btn-s btn-sm btn-danger' href='bir_1601_e_delete.php?id={$d}' onclick='return confirm(\"Are you sure you want to delete this record.\")'><span class='fa fa-trash'></span></a>";
        return $action_buttons;
    })
);
 

require( '../../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );
$where = SSP::filter( $_GET, $columns, $bindings );

$whereAll       = "";
$whereResult    = "";
$filter_sql     = "";


$query="SELECT 
            id,
            nature_of_business,
            tax_rate,
            atc_type,
            atc_code 
        FROM bir_1601_e_reference";

$filter_sql.=" is_deleted = 0 ";

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
$where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";

$bindings=jp_bind($bindings);
$complete_query="{$query} {$where} {$order} {$limit}";
            
// echo $complete_query;
// var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM bir_1601_e_reference {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsFiltered;
$json['recordsFiltered']=$recordsFiltered;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;