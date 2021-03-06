<?php
require_once("../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'date_filed','dt' => ++$index ,'formatter'=>function($d,$row){
         return htmlspecialchars($d);
    }),
    array( 'db' => 'project_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'phase_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return nl2br($d);
    }),
    array( 'db' => 'worked_done','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return nl2br($d);
    }),
    array( 'db' => 'requested_by','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
    array( 'db' => 'request_status','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";
            $action_buttons.="<form method='post' action='move_approval.php' style='display: inline' onsubmit='return confirm(\"Approve This Request?\")'>";
            $action_buttons.="<input type='hidden' name='id' value={$row['id']}>";
            $action_buttons.="<input type='hidden' name='employee_id' value={$row['employee_id']}>";
            // $action_buttons.="<input type='hidden' name='emp_id' value={$row['employee_id']}>";
            // $action_buttons.="<input type='hidden' name='dep_id' value={$row['department_id']}>";
            // $action_buttons.="<input type='hidden' name='date_start' value={$row['date_start']}>";
            // $action_buttons.="<input type='hidden' name='date_end' value={$row['date_end']}>";
            $action_buttons.="<input type='hidden' name='type' value='task_management_approval'>";
            $action_buttons.="<button class='btn btn-sm btn-success' name='action' value='approve' title='Approve Request'><span class='fa fa-check'></span></button> ";
            $action_buttons.="</form>";
            $action_buttons.="<button class='btn btn-sm btn-info' title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button> ";
            $action_buttons.="<button class='btn btn-sm btn-danger' title='Reject Request' onclick='reject(\"{$row['id']}\")'><span class='fa fa-times'></span></button>";
            return $action_buttons;
        }
    )
);


require( '../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereAll="";
$whereResult="";


 $query="SELECT pt.id,pt.worked_done,pt.employee_id,pt.manager_id,pt.project_phase_id AS phase_id,p.name AS project_name, pt.project_id,pt.request_status_id,pp.phase_name, rs.name AS request_status, pt.date_filed,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pt.requestor_id) AS requested_by,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pt.employee_id) AS employee_name
FROM project_task pt";
$join="JOIN request_status rs ON pt.request_status_id=rs.id JOIN projects p ON pt.project_id=p.id
JOIN employees e ON pt.employee_id=e.id JOIN project_phases pp ON pt.project_phase_id=pp.id";
$filter_sql="pt.request_status_id='1' AND (SELECT   
                         CASE   
                            WHEN pt.step_id=2 THEN pt.manager_id 
                            WHEN pt.step_id=3 THEN pt.admin_id
                         END=:employee_id)";
$filter_sql.="";
// $filter_sql.=" :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";
 $bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

if(!empty($_GET['project_name']))
{
    $ltype=" pt.project_id=:project_name ";
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'project_name','val'=>$_GET['project_name'],'type'=>0);
    $filter_sql.=$ltype;
}
if (!empty($_GET['employee_id1'])) {
    $sa_id_sql="pt.employee_id=:employee_id1";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'employee_id1','val'=>$_GET['employee_id1']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

 if(!empty($_GET['request_id1']) OR ($_GET['request_id1']=='0'))
 {
      $dep=" pt.project_phase_id=:request_id1";
      if(!empty($filter_sql))
    {
          $filter_sql.=" AND ";
      }
        $bindings[]=array('key'=>'request_id1','val'=>$_GET['request_id1'],'type'=>0);
    $filter_sql.=$dep;
 }
 if(!empty($_GET['status']))
 {

      $dep=" pt.request_status_id=:status ";
      if(!empty($filter_sql))
    {
          $filter_sql.=" AND ";
      }
        $bindings[]=array('key'=>'status','val'=>$_GET['status'],'type'=>0);
    $filter_sql.=$dep;
 }


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

$complete_query="{$query} {$join} {$where} {$order} {$limit}";
            // var_dump($complete_query);
            // die;
                    //         echo "<pre>";
                    // print_r($query);
                    // echo "</pre>";

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(pt.id) FROM `project_task` pt {$join} {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
