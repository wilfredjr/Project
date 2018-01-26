<?php

require_once("../support/config.php"); 
$primaryKey = 'id';
$index=-1;  

// echo $inputs['id'];
// die;
$project_id=$_GET['id'];
$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
$manage=AccessForProject($project_id, $employee_id);
$columns = array(
    array( 'db' => 'emp_code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'emp_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
         
            return htmlspecialchars($d);
       
    }),
    array( 'db' => 'department','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'job_title','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        
        if (($row['team_lead_ba']=='1')&&($row['manager']=='1')&&($row['team_lead_dev'])){
            return htmlspecialchars("Manager and Team Lead");
        }
         elseif(($row['team_lead_ba']=='1')&&($row['team_lead_dev']=='1')){
           return htmlspecialchars("Team Lead BA & DEV");
        }
        elseif(($row['manager']=='1')&&($row['team_lead_ba']=='1')){
           return htmlspecialchars("Team Lead BA & Manager");
        }
        elseif(($row['manager']=='1')&&($row['team_lead_dev']=='1')){
           return htmlspecialchars("Team Lead DEV & Manager");
        }
        elseif($row['manager']=='1'){
           return htmlspecialchars("Manager");
        }    
        elseif($row['team_lead_ba']=='1'){
           return htmlspecialchars("Team Lead BA");
        }  
        elseif($row['team_lead_dev']=='1'){
           return htmlspecialchars("Team Lead DEV");
        }        
        else{
            return htmlspecialchars("Employee");
        }
    }),
    array( 'db' => 'added_by_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'start_date','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'removed_by_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
         if (empty($d)){
        //$end_date=str_replace("Not yet done");
        return htmlspecialchars('------');
      }
      else {
        return htmlspecialchars($d);
      }
    }),
    array( 'db' => 'end_date','dt' => ++$index ,'formatter'=>function ($d, $row) {
       if (empty(strtotime($d))){
        //$end_date=str_replace("Not yet done");
        return htmlspecialchars('------');
      }
      else {
        return htmlspecialchars($d);
      }
    }),
   
     array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            // $action_buttons.="<button type='button' data-toggle='modal' data-target='#myModal' class='btn btn-sm btn-danger'><span class='fa fa-search'></button>&nbsp;";
            global $manage;
            //$action_buttons.="<input type='text' name='id' value='{$row['manager_id']}'>";
            // $action_buttons.="<form method='post' action='delete_project_employee.php?id={$row['proj_id']}&tab=1' style='display: inline'>";
            // $action_buttons.="<input type='hidden' name='id' value={$_GET['id']}>";
           // if(!empty($_GET['date_start'])) {
           //   $action_buttons.="<input type='text' name='tab' value=1>";
           //  }
            // $action_buttons.="<input type='hidden' name='employee_id' value={$row['emplo_id']}>";
           
            // $action_buttons.="<input type='hidden' name='project_name' value='{$row['project_name']}'>";
            //$action_buttons.="<button class='btn btn-sm btn-danger'><span class='fa fa-remove'></span></button>&nbsp;";
                
            return $action_buttons;
           
        })
        
    );

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
if(!empty($_GET['id'])){
    $whereAll="p.id=".$_GET['id'] ;
}else{
    $whereAll="";
}
$whereResult="";
$filter_sql="";
$filter_sql.=" ";
if (!empty($_GET['employee_id'])) {
    $sa_id_sql="e.id=:employee_id";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'employee_id','val'=>$_GET['employee_id']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($_GET['department_id'])) {
    $sa_id_sql="d.id=:department_id";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'department_id','val'=>$_GET['department_id']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($_GET['job_id'])) {
    $sa_id_sql="jt.id=:job_id ";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'job_id','val'=>$_GET['job_id']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}
if(!empty($_GET['date_start']))
{
 
    $date_start_file=date_create($_GET['date_start']);
}else
{
    $date_start_file="";
}
if(!empty($_GET['date_end']))
{
    $date_end_file=date_create($_GET['date_end']);
}else
{
    $date_end_file="";
}

$date_filter="";
if(!empty($date_start_file))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";   
    $date_filter.=" peh.start_date = :date_start";
    $bindings[]=array('key'=>'date_start','val'=>date_format($date_start_file,'Y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;

$date_filter="";
if(!empty($date_end_file))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" peh.end_date = :date_end";
    $bindings[]=array('key'=>'date_end','val'=>date_format($date_end_file,'Y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;
function jp_bind($bindings)
{
    $return_array=array();
    if (is_array($bindings)) {
        for ($i=0, $ien=count($bindings); $i<$ien; $i++) {
            //$binding = $bindings[$i];
                // $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
            $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }

    return $return_array;
}
$whereAll.=$filter_sql;
$where.= "WHERE ".$whereAll;

$join_query=" INNER JOIN employees e ON peh.employee_id=e.id INNER JOIN projects p ON peh.project_id=p.id
INNER JOIN job_title jt ON e.job_title_id = jt.id INNER JOIN departments d ON e.department_id = d.id
INNER JOIN employees ab ON peh.added_by=ab.id
LEFT JOIN employees rb ON peh.removed_by=rb.id
 ";

$bindings=jp_bind($bindings);
$complete_query="SELECT peh.project_id as proj_id, peh.employee_id as emplo_id, p.id,peh.is_team_lead_ba as team_lead_ba,peh.is_team_lead_dev as team_lead_dev,e.id as emp_id, e.code AS emp_code, CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) AS emp_name, CONCAT(d.name, ' (',d.description,')') AS department, jt.description AS job_title,jt.id,d.id,peh.is_manager as manager, peh.start_date as start_date, peh.end_date as end_date, peh.added_by as added_by, peh.removed_by as removed_by, 
    CONCAT(ab.first_name,' ',ab.middle_name,' ',ab.last_name) AS added_by_name, 
    CONCAT(rb.first_name,' ',rb.middle_name,' ',rb.last_name) AS removed_by_name
FROM project_employee_history peh {$join_query} {$where} {$order} {$limit}";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(peh.id) FROM project_employee_history peh {$join_query} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
