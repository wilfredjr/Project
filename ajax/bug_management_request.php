<?php
require_once("../support/config.php");

$primaryKey = 'id';
$index=-1;
$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
$columns = array(
    // array( 'db' => 'code','dt' => ++$index ,'formatter'=>function($d,$row){
    //     return htmlspecialchars($d);
    // }),
    // array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function($d,$row){
    //     return htmlspecialchars($d);
    // }),
    array( 'db' => 'date_filed','dt' => ++$index ,'formatter'=>function($d,$row){
         return htmlspecialchars($d);
    }),
    array( 'db' => 'project_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'bug_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'phase_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_start','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
    array( 'db' => 'date_end','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
    array( 'db' => 'bug_rate','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
    array( 'db' => 'manager','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
    array( 'db' => 'description','dt' => ++$index ,'formatter'=>function($d,$row){
          return nl2br($d);
    }),
    array( 'db' => 'status_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            // $action_buttons.="<button type='button' data-toggle='modal' data-target='#myModal' class='btn btn-sm btn-danger'><span class='fa fa-search'></button>&nbsp;";
                 $action_buttons.="<a href='bugs_view.php?id={$row['id']}' class='btn btn-sm btn-warning'><span class='fa fa-search'></span></a>&nbsp;";
            return $action_buttons;
        })
      // array(
      //   'db'        => 'id',
      //   'dt'        => ++$index,
      //   'formatter' => function ($d, $row) {
      //       $action_buttons="";
      //       if($row['request_status_id']=="3"):
      //           $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
      //       endif;
      //       if($row['request_status_id']<>"2" && $row['request_status_id']<>"5" && $row['request_status_id']<>"4"):
      //           $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
      //           $action_buttons.="<input type='hidden' name='proj_id' value='{$row['project_id']}'>";
      //           if(!empty($_GET['id'])){
      //               $action_buttons.="<input type='hidden' name='type' value='assign'>";
      //           }else{
      //               $action_buttons.="<input type='hidden' name='type' value='assign1'>";
      //           }
      //           $action_buttons.=" <button class='btn btn-sm btn-danger' value='phase' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
      //       endif;

      //       return $action_buttons;
      //   })
);


require( '../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";

  // $data=$con->myQuery("SELECT
  //   id,
  //   code,
  //   employee_name,
  //   supervisor,
  //   final_approver,
  //   status,
  //   date_applied,
  //   food_allowance,
  //   transpo_allowance,
  //   request_reason,
  //   date_filed
  //   FROM vw_employees_allowances
  //   ",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']));
if(!empty($_GET['id'])){
    $whereAll="(pbl.manager_id='$employee_id' OR pbl.team_lead_ba='$employee_id' OR pbl.team_lead_dev='$employee_id' OR pbl.ba_test='$employee_id' OR pbl.dev_control='$employee_id' OR pbl.employee_id='$employee_id' OR pbl.admin_id='$employee_id') AND pbl.project_id=".$_GET['id'];
}else{
        $whereAll="(pbl.manager_id='$employee_id' OR pbl.team_lead_ba='$employee_id' OR pbl.team_lead_dev='$employee_id' OR pbl.ba_test='$employee_id' OR pbl.dev_control='$employee_id' OR pbl.employee_id='$employee_id' OR pbl.admin_id='$employee_id')"; 
}
    $filter_sql="";
    $filter_sql.=" ";

    if(!empty($_GET['status']))
    {
        $stat="pbl.project_status_id=:status ";
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'status','val'=>$_GET['status'],'type'=>0);
        $filter_sql.=$stat;
        // echo $filter_sql;
    }

    if(!empty($_GET['date_start']))
    {
        $date_start=date_create($_GET['date_start']);
    }else
    {
        $date_start="";
    }
    if(!empty($_GET['date_end']))
    {
        $date_end=date_create($_GET['date_end']);
    }else
    {
        $date_end="";
    }

    $date_filter="";
    if(!empty($date_start))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" pbl.date_filed >= :date_start";
        $bindings[]=array('key'=>'date_start','val'=>date_format($date_start,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    $date_filter="";
    if(!empty($date_end))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" pbl.date_filed <= :date_end";
        $bindings[]=array('key'=>'date_end','val'=>date_format($date_end,'Y-m-d'),'type'=>0);
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
$where.= "WHERE ".$whereAll;

$join_query="JOIN project_status ps ON pbl.project_status_id=ps.id JOIN projects p ON pbl.project_id=p.id
JOIN project_bug_phase pbp ON pbl.bug_phase_id=pbp.id JOIN project_bug_rate pbr ON pbr.id=pbl.bug_rate_id";


$bindings=jp_bind($bindings);
$complete_query="SELECT pbl.id,pbl.name as bug_name,pbl.description,p.name AS project_name, pbl.project_id,pbl.project_status_id,pbp.name as phase_name, ps.status_name, pbl.date_filed,pbl.date_start,pbl.date_end,pbr.desc as bug_rate,pbl.admin_id,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pbl.manager_id) AS manager,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pbl.team_lead_ba) AS team_lead_ba,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pbl.team_lead_dev) AS team_lead_dev
FROM project_bug_list pbl {$join_query} {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(pbl.id) FROM `project_bug_list` pbl {$join_query} {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
