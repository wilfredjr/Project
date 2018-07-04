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
    array( 'db' => 'requested_by','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
    array( 'db' => 'type','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if($row['type']=='comp'){
        return 'Phase Completion';}
        else{
            return 'Phase Revertion';
        }
    }),
    array( 'db' => 'request_status','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
              $action_buttons="";
                global $con;
              $current=$con->myQuery("SELECT pf.id FROM  bug_files pf JOIN project_bug_request ptl ON ptl.id=pf.bug_request_id  WHERE pf.bug_request_id=? AND pf.is_deleted=0",array($row['id']))->fetch(PDO::FETCH_ASSOC);
              if(!empty($current['id'])){
                $action_buttons.="<a href='download_file.php?id={$current['id']}&type=bf' class='btn btn-default'><span class='fa fa-download'></span></a> ";
            }
            $action_buttons.="<form method='post' action='move_approval.php' style='display: inline' onsubmit='return confirm(\"Approve This Request?\")'>";
            $action_buttons.="<input type='hidden' name='id' value={$row['id']}>";
            $action_buttons.="<input type='hidden' name='employee_id' value={$row['employee_id']}>";
            // $action_buttons.="<input type='hidden' name='emp_id' value={$row['employee_id']}>";
            // $action_buttons.="<input type='hidden' name='dep_id' value={$row['department_id']}>";
            // $action_buttons.="<input type='hidden' name='date_start' value={$row['date_start']}>";
            // $action_buttons.="<input type='hidden' name='date_end' value={$row['date_end']}>";
            $action_buttons.="<input type='hidden' name='req_type' value={$row['type']}>";
            $action_buttons.="<input type='hidden' name='type' value='bug_phase_approval'>";
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


 $query="SELECT ppr.id,ppr.type,ppr.employee_id,ppr.manager_id,ppr.bug_phase_id AS phase_id,p.name AS project_name, ppr.project_id,ppr.request_status_id,pp.name as phase_name, rs.name AS request_status, ppr.date_filed,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.employee_id) AS requested_by
FROM project_bug_request ppr JOIN request_status rs ON ppr.request_status_id=rs.id JOIN projects p ON ppr.project_id=p.id
JOIN project_bug_phase pp ON ppr.bug_phase_id=pp.id";
$join="JOIN request_status rs ON ppr.request_status_id=rs.id JOIN projects p ON ppr.project_id=p.id
JOIN project_bug_phase pp ON ppr.bug_phase_id=pp.id";
$filter_sql="ppr.request_status_id='1' AND 
(SELECT   
   CASE   
      WHEN ppr.step_id=2 THEN ppr.manager_id 
      WHEN ppr.step_id=3 THEN ppr.admin_id
      WHEN ppr.step_id=1 THEN ppr.team_lead_id
   END=:employee_id)";
$filter_sql.="";
// $filter_sql.=" :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";
 $bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

if(!empty($_GET['project_name']))
{
    $ltype=" ppr.project_id=:project_name ";
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'project_name','val'=>$_GET['project_name'],'type'=>0);
    $filter_sql.=$ltype;
}
if (!empty($_GET['employee_id1'])) {
    $sa_id_sql="ppr.employee_id=:employee_id1";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'employee_id1','val'=>$_GET['employee_id1']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

 if(!empty($_GET['request_id1']) OR ($_GET['request_id1']=='0'))
 {
      $dep=" ppr.bug_phase_id=:request_id1";
      if(!empty($filter_sql))
    {
          $filter_sql.=" AND ";
      }
        $bindings[]=array('key'=>'request_id1','val'=>$_GET['request_id1'],'type'=>0);
    $filter_sql.=$dep;
 }
 if(!empty($_GET['status']))
 {

      $dep=" request_status_id=:status ";
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

$complete_query="{$query} {$where} {$order} {$limit}";
            // var_dump($complete_query);
            // die;
                    //         echo "<pre>";
                    // print_r($query);
                    // echo "</pre>";

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(ppr.id) FROM `project_bug_request` ppr {$join} {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
