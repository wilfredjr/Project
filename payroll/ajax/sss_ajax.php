<?php
require_once("../../support/config.php"); 

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'sss_code','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
     array( 'db' => 'sss_from_comp','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
      array( 'db' => 'sss_to_comp','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
       array( 'db' => 'sss_ee','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
        array( 'db' => 'sss_er','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
         array( 'db' => 'sss_ec','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
 
 
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";

            $action_buttons.="<button type='button' data-toggle='modal' data-target='#myModal' data-s_code='{$row['sss_code']}'
                                    data-r_comp_from='{$row['sss_from_comp']}' data-r_comp_to='{$row['sss_to_comp']}'
                                    data-ee_share='{$row['sss_ee']}' data-er_share='{$row['sss_er']}' data-e_comp='{$row['sss_ec']}' class='btn btn-sm btn-danger' onclick='pass(this)''><span class='fa fa-edit'></span></button>";


            $action_buttons.=" <a href='delete.php?cd={$row['sss_code']}&tb=s' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this entry?\")' ><span class='fa fa-trash'></span></button>";


            // if($row['status']=="Query (Final Approver)" || $row['status']=="Query (Supervisor)"):
            //     $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            // endif;
            // if($row['status']<>'Approved' && $row['status']<>'Cancelled' && $row['status']<>'Rejected (Supervisor)' && $row['status']<>'Rejected (Final Approver)'):
            //     $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
            //     $action_buttons.="<input type='hidden' name='type' value='leave'>";
            //     $action_buttons.=" <button class='btn btn-sm btn-danger' value='leave' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
            // endif;

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


$query="SELECT * FROM gd_sss";

$filter_sql="";
$filter_sql.=" is_deleted = 0 ";
// $bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

if(!empty($_GET['leave_type_id']))
{
    $ltype=" leave_id=:leave_type_id ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'leave_type_id','val'=>$_GET['leave_type_id'],'type'=>0);
    $filter_sql.=$ltype;
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
if(!empty($_GET['status']))
{
    $stat=" status=:status ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'status','val'=>$_GET['status'],'type'=>0);
    $filter_sql.=$stat;
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

$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM gd_sss {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;