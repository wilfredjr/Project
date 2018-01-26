<?php
require_once("../support/config.php"); 

$primaryKey = 'id';
$index=-1;

$columns = array(
     array( 'db' => 'id','dt' => ++$index ,'formatter'=>function($d,$row){
        
        return "OT Claim";
    }),


    array( 'db' => 'code','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_filed','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'no_hours','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'ot_date','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'time_from','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'time_to','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'worked_done','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'supervisor','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'final_approver','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'status','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";

            $action_buttons.="<form method='post' action='move_approval.php' style='display: inline' onsubmit='return confirm(\"Approve This Request?\")'>
                                  <input type='hidden' name='id' value='{$row['id']}'>
                                  <input type='hidden' name='type' value='shift'>
                                  <button class='btn btn-sm btn-success' name='action' value='approve' title='Approve Request'><span class='fa fa-check'></span></button>
                                  </form>
                                  <button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>
                                  <button class='btn btn-sm btn-danger'  title='Reject Request' onclick='reject(\"{$row['id']}\")'><span class='fa fa-times'></span></button>";
            $action_buttons.="<input type='hidden' name='dep_id' value={$row['department_id']}>";
            return $action_buttons;
        }
    )
);
 

require( '../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";



$query="SELECT 
    id,
    code,
    employee_name,
    supervisor,
    final_approver,
    DATE_FORMAT(ot_date,'%m-%d-%Y') as ot_date,

    time_from,
    time_to,
    no_hours,
    worked_done,
    
    status,
    department_id,
    department,
    DATE_FORMAT(date_filed,'%m-%d-%Y') as date_filed  

    FROM vw_employees_ot";
      
$filter_sql="";
$filter_sql.=" CASE when status='Supervisor Approval' then supervisor_id when status='Final Approver Approval' then final_approver_id end  =:employee_id ";
$bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

if(!empty($_GET['emp_id']))
{
    $emp=" employee_id=:emp_id ";    
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'emp_id','val'=>$_GET['emp_id'],'type'=>0);
    $filter_sql.=$emp;
}
 if(!empty($_GET['dep_id']))
 {

      $dep=" department_id=:dept_id ";    
      if(!empty($filter_sql))
    {
          $filter_sql.=" AND ";
      }
        $bindings[]=array('key'=>'dept_id','val'=>$_GET['dep_id'],'type'=>0);
    $filter_sql.=$dep;
 }
// if(!empty($_GET['start_date']))
// {
//     $date_start_file=date_create($_GET['start_date']);
// }else
// {
//     $date_start_file="";
// }
// if(!empty($_GET['end_date']))
// {
//     $date_end_file=date_create($_GET['end_date']);
// }else
// {
//     $date_end_file="";
// }

// $date_filter="";
// if(!empty($date_start_file))
// {
//     $date_filter.=!empty($filter_sql)?" AND ":"";
//     $date_filter.=" date_from >= :date_start";
//     $bindings[]=array('key'=>'date_start','val'=>date_format($date_start_file,'Y-m-d'),'type'=>0);
// }
// $filter_sql.=$date_filter;

// $date_filter="";
// if(!empty($date_end_file))
// {
//     $date_filter.=!empty($filter_sql)?" AND ":"";
//     $date_filter.=" date_to <= :date_end";
//     $bindings[]=array('key'=>'date_end','val'=>date_format($date_end_file,'Y-m-d'),'type'=>0);
// }
// $filter_sql.=$date_filter;



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
// $complete_query="SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
             // FROM `vw_employees_leave` {$where} {$order} {$limit}";
$complete_query="{$query} {$where} {$order} {$limit}";
            // echo $complete_query;
            // var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(id) FROM `vw_employees_ot` {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;