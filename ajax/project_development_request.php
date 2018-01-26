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
    array( 'db' => 'step_id','dt' => ++$index ,'formatter'=>function($d,$row){
        if($row['step_id']=='0'){
          return htmlspecialchars($row['team_lead']);
        }elseif($row['step_id']=='2'){
             return htmlspecialchars($row['manager']);
        }elseif($row['step_id']=='3'){
             return htmlspecialchars($row['admin']);
        }
    }),
    array( 'db' => 'hours','dt' => ++$index ,'formatter'=>function($d,$row){
          return nl2br($d);
    }),
    array( 'db' => 'comment','dt' => ++$index ,'formatter'=>function($d,$row){
          return nl2br($d);
    }),
    array( 'db' => 'request_status','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'reason','dt' => ++$index ,'formatter'=>function($d,$row){
          return nl2br($d);
    }),
      array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
                    global $con;
            $action_buttons="";
            $current=$con->myQuery("SELECT id FROM  project_files WHERE project_dev_id=?  AND is_deleted=0",array($row['id']))->fetch(PDO::FETCH_ASSOC);
                if(!empty($current['id'])){
                    $action_buttons.="<a href='download_file.php?id={$current['id']}&type=c' class='btn btn-default'><span class='fa fa-download'></span></a> ";
                }
            if($row['request_status_id']=="3"):
                $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            endif;
            if($row['request_status_id']<>"2" && $row['request_status_id']<>"5" && $row['request_status_id']<>"4"):
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='proj_id' value='{$row['project_id']}'>";
                    $action_buttons.="<input type='hidden' name='type' value='dev'>";
                if($row['step_id']=='0'){}else{
                $action_buttons.=" <button class='btn btn-sm btn-danger' value='dev' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
            }
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
if(!empty($_GET['id'])){
    $whereAll="pt.employee_id='$employee_id' AND pt.project_id=".$_GET['id'];
}else{
        $whereAll="pt.employee_id='$employee_id'"; 
}
    $filter_sql="";
    $filter_sql.=" ";

    if(!empty($_GET['status']))
    {
        $stat="pt.request_status_id=:status ";
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
        $date_filter.=" pt.date_filed >= :date_start";
        $bindings[]=array('key'=>'date_start','val'=>date_format($date_start,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    $date_filter="";
    if(!empty($date_end))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" pt.date_filed <= :date_end";
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

$join_query="JOIN request_status rs ON pt.request_status_id=rs.id JOIN projects p ON pt.project_id=p.id";


$bindings=jp_bind($bindings);
$complete_query="SELECT pt.id,pt.reason,p.name AS project_name,pt.phase_request_id,pt.comment, pt.step_id,pt.hours, pt.project_id,pt.request_status_id, rs.name AS request_status, pt.date_filed,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pt.manager_id) AS manager,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pt.admin_id) AS admin,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pt.team_lead_id) AS team_lead
FROM project_development pt {$join_query} {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(pt.id) FROM `project_development` pt {$join_query} {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
