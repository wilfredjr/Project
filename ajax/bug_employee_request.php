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
        // return htmlspecialchars(date_format(date_create($d),DATE_FORMAT_PHP));
        return htmlspecialchars($d);
    }),
    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'project','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'bug_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'bug_desc','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'bug_rate','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'designation','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'step_id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if($row['step_id']=='2'){
        return htmlspecialchars($row['manager']);
        }elseif($row['step_id']=='3'){
        return htmlspecialchars($row['admin']);
        }
    }),
    array( 'db' => 'request_name','dt' => ++$index ,'formatter'=>function($d,$row){
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
            
            
        global $manage;
        if($row['status_id']=="3"):
            $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
        endif;
        if($row['status_id']<>"2" && $row['status_id']<>"5" && $row['status_id']<>"4"):
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='proj_id' value='{$row['project_id']}'>";
                $action_buttons.="<input type='hidden' name='type' value='bug_emp'>";
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

    $whereAll="pr.requested_by='$employee_id'";  
    $filter_sql="";
    $filter_sql.=" ";
    if(!empty($_GET['status']))
    {
        $stat="pr.request_status_id=:status ";
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
        $date_filter.="pr.date_filed >= :date_start";
        $bindings[]=array('key'=>'date_start','val'=>date_format($date_start,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    $date_filter="";
    if(!empty($date_end))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.="pr.date_filed <= :date_end";
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

$join_query="INNER JOIN request_status rs ON rs.id=pr.request_status_id
LEFT JOIN project_designation pd ON pr.designation_id=pd.id
JOIN projects p ON pr.project_id=p.id JOIN project_bug_rate pbr ON pbr.id=pr.bug_rate_id";


$bindings=jp_bind($bindings);
$complete_query="SELECT pr.id,pr.step_id,pr.project_id,pr.reason, pr.date_filed as date_filed, pr.designation_id, pr.request_status_id as status_id, pbr.desc as bug_rate, (SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE pr.employee_id=e.id) as name, rs.name as request_name,pd.name as designation,p.name AS project, pr.bug_name, pr.bug_desc,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pr.manager_id) AS manager,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pr.admin_id) AS admin
FROM project_bug_employee pr {$join_query} {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(pr.id) FROM `project_bug_employee` pr {$join_query} {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
