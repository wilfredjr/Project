<?php
require_once("../support/config.php"); 

$primaryKey = 'id';
$index=-1;
$columns = array(
    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function($d,$row){
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
        array( 'db' => 'status_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'worked_done','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'work_done','dt' => ++$index ,'formatter'=>function($d,$row){
        if(empty($row['work_done'])){
            return "-";
        }else{
        return htmlspecialchars($d);}
    }),
    array( 'db' => 'date_finished','dt' => ++$index ,'formatter'=>function($d,$row){
        if($row['date_finished']=="0000-00-00"){
            return "-";
        }else{
        return htmlspecialchars($d);}
    }),
        array( 'db' => 'manager','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),

    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            // $action_buttons.="<button type='button' data-toggle='modal' data-target='#myModal' class='btn btn-sm btn-danger'><span class='fa fa-search'></button>&nbsp;";
            // $action_buttons.="<input type='hidden' name='emp_id' value={$row['employee_id']}>";
            // $action_buttons.="<input type='hidden' name='dep_id' value={$row['department_id']}>";
            // $action_buttons.="<input type='hidden' name='date_start' value={$row['date_start']}>";
            // $action_buttons.="<input type='hidden' name='date_end' value={$row['date_end']}>";
            global $con;
              $current=$con->myQuery("SELECT pf.id FROM  project_files pf JOIN project_task_list ptl ON ptl.request_id=pf.task_completion_id  WHERE pf.task_completion_id=? AND pf.is_deleted=0 AND ptl.is_submitted=1",array($row['request_id']))->fetch(PDO::FETCH_ASSOC);
            if(!empty($current['id'])){
                $action_buttons.="<a href='download_file.php?id={$current['id']}&type=c' class='btn btn-default'><span class='fa fa-download'></span></a> ";
            }
            if($row['is_query']==1):
            $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            endif;
            if($row['is_submitted']==1 AND $row['status_id']!=2){
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='proj_id' value='{$row['project_id']}'>";
                $action_buttons.="<input type='hidden' name='type' value='task_submit'>";
                $action_buttons.=" <button class='btn btn-sm btn-danger' value='phase' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
            }elseif($row['is_submitted']==0 AND $row['status_id']!=2){
                $current1=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($row['project_id'],$row['project_phase_id']))->fetch(PDO::FETCH_ASSOC);
                if($current1['status_id']=='3'){}else{
            $action_buttons.="<button class='btn btn-sm btn-success' title='Task Completion Request' onclick='submit(\"{$row['id']}\")'><span  class='fa fa-check'></span></button> ";}
            }
            return $action_buttons;
        })
    );
 

require( '../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";


// $data=$con->myQuery("SELECT p.id,p.name,p.description,p.start_date,p.end_date,pe.employee_id,p.project_status_id,ps.status_name,
// (SELECT pe.employee_id FROM projects_employees pe WHERE pe.is_team_lead=1 AND pe.project_id=p.id) AS team_leader_id,
// (SELECT CONCAT(last_name,',',first_name) FROM employees WHERE id=team_leader_id) AS team_leader_name 
// FROM projects p 
// JOIN projects_employees pe ON pe.project_id=p.id JOIN project_status ps ON p.project_status_id=ps.id
// WHERE pe.employee_id=? AND p.is_deleted=0",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
$whereAll="ptl.employee_id=:employee_id";
    $filter_sql="";
    $filter_sql.=" ";
    $bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);
if (!empty($_GET['project_status'])) {
    
    $project_status="ptl.status_id=:project_status";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'project_status','val'=>$_GET['project_status']."",'type'=>0);
    $filter_sql.=$project_status;
}
if (!empty($_GET['project_name'])) {
    
    $project_name="p.id=:project_name";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'project_name','val'=>$_GET['project_name']."",'type'=>0);
    $filter_sql.=$project_name;
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
    $date_filter.=" p.start_date >= :start_date";
    $bindings[]=array('key'=>'start_date','val'=>date_format($date_start_file,'y-m-d'),'type'=>0);
}
$filter_sql.=$date_filter;

$date_filter="";
if(!empty($date_end_file))
{
    $date_filter.=!empty($filter_sql)?" AND ":"";
    $date_filter.=" p.end_date <= :end_date";
    $bindings[]=array('key'=>'end_date','val'=>date_format($date_end_file,'y-m-d'),'type'=>0);
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


$join_query=" JOIN projects p ON ptl.project_id=p.id JOIN project_phases pp ON ptl.project_phase_id=pp.id
JOIN project_status ps ON ptl.status_id=ps.id ";

$bindings=jp_bind($bindings);
$complete_query="SELECT ptl.id,ptl.status_id,ptl.request_id,ptl.date_finished,ptl.work_done,p.name,pp.phase_name,ptl.date_start,ptl.date_end,ps.status_name,ptl.worked_done,ptl.is_submitted,ptl.is_query,ptl.project_id,ptl.project_phase_id,
(SELECT CONCAT(e.last_name,', ',e.first_name) FROM employees e WHERE e.id=ptl.manager_id) AS manager
FROM project_task_list ptl {$join_query} {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(ptl.id) FROM `project_task_list` ptl {$join_query} {$where};",$bindings)->fetchColumn();

$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;