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
    array( 'db' => 'bug_phase','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'type','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if($row['type']=='comp'){
        return 'Phase Completion';}
        else{
            return 'Phase Revertion';
        }
    }),
    array( 'db' => 'step_id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if($row['step_id']=='2'){
        return htmlspecialchars($row['manager']);
        }elseif($row['step_id']=='3'){
        return htmlspecialchars($row['admin']);
        }elseif($row['step_id']=='1'){
        return htmlspecialchars($row['team_lead']);
        }
    }),
    array( 'db' => 'comment','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
    array( 'db' => 'request_status','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'reason','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
      array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
              $action_buttons="";
                global $con;
              $current=$con->myQuery("SELECT pf.id FROM  bug_files pf JOIN project_bug_request ptl ON ptl.id=pf.bug_request_id  WHERE pf.bug_request_id=? AND pf.is_deleted=0",array($row['id']))->fetch(PDO::FETCH_ASSOC);
              if(!empty($current['id'])){
                $action_buttons.="<a href='download_file.php?id={$current['id']}&type=bf' class='btn btn-default'><span class='fa fa-download'></span></a> ";
            }
            if($row['request_status_id']=="3"):
                $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            endif;
            if($row['request_status_id']<>"2" && $row['request_status_id']<>"5" && $row['request_status_id']<>"4"):
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='bug_id' value=''>";
                $action_buttons.="<input type='hidden' name='type' value='bug'>";
                $action_buttons.=" <button class='btn btn-sm btn-danger' value='phase' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
            endif;

            return $action_buttons;
        })
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

    $whereAll="ppr.employee_id='$employee_id'"; 
    $filter_sql="";
    $filter_sql.=" ";

    if(!empty($_GET['status']))
    {
        $stat="ppr.request_status_id=:status ";
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
        $date_filter.=" ppr.date_filed >= :date_start";
        $bindings[]=array('key'=>'date_start','val'=>date_format($date_start,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    $date_filter="";
    if(!empty($date_end))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" ppr.date_filed <= :date_end";
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

$join_query="JOIN request_status rs ON ppr.request_status_id=rs.id JOIN projects p ON ppr.project_id=p.id
JOIN project_bug_phase pp ON ppr.bug_phase_id=pp.id JOIN project_bug_list pbl ON pbl.id=ppr.bug_list_id";


$bindings=jp_bind($bindings);
$complete_query="SELECT ppr.id,ppr.type,ppr.reason,ppr.step_id,ppr.comment, p.name AS project_name, ppr.project_id,ppr.request_status_id, rs.name AS request_status, ppr.date_filed, pbl.name as bug_name, ppr.bug_phase_id, pp.name as bug_phase, ppr.bug_list_id,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.manager_id) AS manager,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.admin_id) AS admin,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.team_lead_id) AS team_lead
FROM project_bug_request ppr {$join_query} {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(ppr.id) FROM `project_bug_request` ppr {$join_query} {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
