<?php
require_once("../../support/config.php"); 

$primaryKey = 'id';
$index=-1;

$columns = array(
     array( 'db' => 'payroll_code','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'code','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
 
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";
            $action_buttons.="<a href='pay_slip_print.php?cd={$row['id']}&pc={$row['payroll_code']}' class='btn btn-sm btn-danger'>Details</a>";
            return $action_buttons;
        }
    )
);
 

require( '../../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";


  $query="SELECT p.payroll_code,e.id,e.code, 
                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as name
                FROM payroll_details p 
                INNER JOIN employees e ON p.employee_id=e.id 
                INNER JOIN payroll r ON r.payroll_code=p.payroll_code";

$filter_sql="";
// $filter_sql.=" p.is_deleted = 0 ";
// $bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

if(!empty($_GET['e_code']))
{
   $ecode=" p.employee_id=:e_code ";    

    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'e_code','val'=>$_GET['e_code'],'type'=>0);
    $filter_sql.=$ecode;
}

if(!empty($_GET['p_code_text']))
{


    $pcode=" p.payroll_code=:p_code_text ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'p_code_text','val'=>$_GET['p_code_text'],'type'=>0);
    $filter_sql.=$pcode;
}

// if(!empty($_GET['half_day_mode']))
// {
//     $hd=" comments=:half_day_mode ";    
//     if(!empty($filter_sql))
//     {
//         $filter_sql.=" AND ";
//     }
//     $bindings[]=array('key'=>'half_day_mode','val'=>$_GET['leave_type_id'],'type'=>0);
//     $filter_sql.=$hd;
// }
if(!empty($_GET['e_name']))
{
    $ename="CONCAT(last_name,', ',first_name,' ',middle_name) = :e_name ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'e_name','val'=>$_GET['e_name'],'type'=>0);
    $filter_sql.=$ename;
}

if(!empty($_GET['start_date']))
{
    $date_start_file=date_create($_GET['start_date']);
}else
{
    $date_start_file="";
}
if(!empty($_GET['end_date']))
{
    $date_end_file=date_create($_GET['end_date']);
}else
{
    $date_end_file="";
}

$date_filter="";
if(!empty($date_start_file))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" date_start >= :date_start";
    $bindings[]=array('key'=>'date_start','val'=>date_format($date_start_file,'Y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;

$date_filter="";
if(!empty($date_end_file))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" date_end <= :date_end";
    $bindings[]=array('key'=>'date_end','val'=>date_format($date_end_file,'Y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;



function jp_bind($bindings)
{
    $return_array=array();
    if ( is_array( $bindings ) ) {
            for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) {
                //$binding = $bindings[$i];
                // $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
                $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
            }
        }

        return $return_array;
}
$whereAll.=$filter_sql;
// $where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;
$where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";

$bindings=jp_bind($bindings);
$complete_query="{$query} {$where} {$order} {$limit}";
            // echo $complete_query;
            // var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(p.id) FROM payroll_details p {$where};",$bindings)->fetchColumn();

$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;