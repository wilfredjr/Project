<?php
require_once("../support/config.php"); 

$primaryKey = 'id';
$index=-1;

$columns = array(
    //  array( 'db' => 'overtime_type','dt' => ++$index ,'formatter'=>function($d,$row){
    //     return htmlspecialchars($d);
    // }),


    array( 'db' => 'code','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'department','dt' => ++$index ,'formatter'=>function($d,$row){
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
        return htmlspecialchars(date_format(date_create($d),TIME_FORMAT_PHP));
    }),
    array( 'db' => 'time_to','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars(date_format(date_create($d),TIME_FORMAT_PHP));
    }),
    array( 'db' => 'ot_date','dt' => ++$index ,'formatter'=>function($d,$row){
        global $con;
        #Actual Time Out
        $time_out=$con->myQuery("SELECT out_time FROM attendance WHERE DATE(in_time) = :ot_date AND employees_id = :employee_id ORDER BY out_time DESC", array("ot_date"=>$d, "employee_id"=>$row['employee_id']))->fetchColumn();

        return !empty($time_out)?htmlspecialchars(date_format(date_create($time_out),TIME_FORMAT_PHP)):"-";
    }),
    array( 'db' => 'ot_date','dt' => ++$index ,'formatter'=>function($d,$row){
        global $con;
        #actual Hours
        $hours=$con->myQuery("SELECT TIMEDIFF(out_time, '{$row['ot_date']} {$row['time_from']}') FROM attendance WHERE DATE(in_time) = :ot_date AND employees_id = :employee_id ORDER BY out_time DESC", array("ot_date"=>$d, "employee_id"=>$row['employee_id']))->fetchColumn();
        if (!empty($hours)) {
            $hms = explode(":", $hours);
            return ($hms[0] + ($hms[1]/60));
        } else {
            return 0;
        }

    }),
    array( 'db' => 'worked_done','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'project','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'status','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'step_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";

            $action_buttons.="<form method='post' action='move_approval.php' style='display: inline' onsubmit='return confirm(\"Approve This Request?\")'>";
            $action_buttons.="<input type='hidden' name='id' value='{$row['id']}'>";
            $action_buttons.="<input type='hidden' name='emp_id' value={$row['employee_id']}>";
            $action_buttons.="<input type='hidden' name='dep_id' value={$row['department_id']}>";
            $action_buttons.="<input type='hidden' name='type' value='overtime'>";
            
            $action_buttons.="<input type='hidden' name='request_type' value='overtime'>";
            // if ($row['overtime_type'] == "OT Claim") {

            //     $action_buttons.="<input type='hidden' name='type' value='overtime'>";
                
            //     $action_buttons.="<input type='hidden' name='request_type' value='overtime'>";

            // } else if ($row['overtime_type'] == "Pre-approval OT") {

            //     $action_buttons.="<input type='hidden' name='type' value='pre_overtime'>";
               
            //     $action_buttons.="<input type='hidden' name='request_type' value='pre_overtime'>";
            // }

            
            $action_buttons.="<button class='btn btn-sm btn-success' name='action' value='approve' title='Approve Request'><span class='fa fa-check'></span></button>";
            $action_buttons.="</form>";
            $action_buttons.=" <button class='btn btn-sm btn-info' title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button> ";
            $action_buttons.="<button class='btn btn-sm btn-danger' title='Reject Request' onclick='reject(\"{$row['id']}\")'><span class='fa fa-times'></span></button>";
            // if ($row['overtime_type'] == "OT Claim") {
            // $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query_ot(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            // }
            // else if ($row['overtime_type'] == "Pre-approval OT") {
            // $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query_pre_ot(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            // }

            // if ($row['overtime_type'] == "OT Claim") {
            // $action_buttons.="<button class='btn btn-sm btn-danger'  title='Reject Request' onclick='reject_ot(\"{$row['id']}\")'><span class='fa fa-times'></span></button>";
            // }
            // else if ($row['overtime_type'] == "Pre-approval OT") {

            //     $action_buttons.="<button class='btn btn-sm btn-danger'  title='Reject Request' onclick='reject_pre_ot(\"{$row['id']}\")'><span class='fa fa-times'></span></button>";
            // }


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
    employee_id,
    employee_name,
    ot_date,
    time_from,
    time_to,
    no_hours,
    worked_done,
    status,
    department_id,
    department,
    date_filed,
    step_name,
    project
    FROM vw_employees_ot";
      
$filter_sql="";
$filter_sql.=" :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";
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
if(!empty($_GET['project_id']))
{
    $stat=" project_id=:project_id ";
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'project_id','val'=>$_GET['project_id'],'type'=>0);
    $filter_sql.=$stat;
    // echo $filter_sql;
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
    $date_filter.=" ot_date >= :date_start";
    $bindings[]=array('key'=>'date_start','val'=>date_format($date_start_file,'y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;

$date_filter="";
if(!empty($date_end_file))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" ot_date <= :date_end";
    $bindings[]=array('key'=>'date_end','val'=>date_format($date_end_file,'y-m-d'),'type'=>0);
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