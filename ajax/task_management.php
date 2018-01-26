<?php
require_once("../support/config.php"); 

$primaryKey = 'id';
$index=-1;
$columns = array(
    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'description','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'start_date','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'end_date','dt' => ++$index ,'formatter'=>function($d,$row){
       if (empty(strtotime($d))){
        //$end_date=str_replace("Not yet done");
        return htmlspecialchars('------');
      }
      else {
        return htmlspecialchars($d);
      }
    }),
    
        array( 'db' => 'status_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
        array( 'db' => 'leader_name_ba','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
        array( 'db' => 'leader_name_dev','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
        array( 'db' => 'manager_id','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),

    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            // $action_buttons.="<button type='button' data-toggle='modal' data-target='#myModal' class='btn btn-sm btn-danger'><span class='fa fa-search'></button>&nbsp;";
                 $action_buttons.="<a href='task_management_project.php?id={$row['id']}' class='btn btn-sm btn-warning'><span class='fa fa-search'></span></a>&nbsp;";
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
$whereAll=" pe.employee_id=:employee_id AND p.is_deleted=0 AND p.project_status_id!=2 AND
((SELECT pe.employee_id FROM projects_employees pe WHERE pe.is_team_lead_ba=1 AND pe.project_id=p.id)=:employee_id OR
(SELECT pe.employee_id FROM projects_employees pe WHERE pe.is_team_lead_dev=1 AND pe.project_id=p.id)=:employee_id OR
p.manager_id=:employee_id)";
    $filter_sql="";
    $filter_sql.=" ";
    $bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);
if (!empty($_GET['project_status'])) {
    
    $project_status="p.project_status_id=:project_status";
    
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


$join_query="";

$bindings=jp_bind($bindings);
$complete_query="SELECT p.id,p.name,p.description,p.start_date,p.end_date,pe.employee_id,p.project_status_id,ps.status_name,
(SELECT pe.employee_id FROM projects_employees pe WHERE pe.is_team_lead_ba=1 AND pe.project_id=p.id) AS leader_ba,
(SELECT CONCAT(last_name,', ',first_name) FROM employees WHERE id=leader_ba) AS leader_name_ba,
(SELECT pe.employee_id FROM projects_employees pe WHERE pe.is_team_lead_dev=1 AND pe.project_id=p.id) AS leader_dev,
(SELECT CONCAT(last_name,', ',first_name) FROM employees WHERE id=leader_dev) AS leader_name_dev,
(SELECT CONCAT(e.last_name,', ',e.first_name) FROM employees e JOIN projects_employees pe WHERE pe.is_manager=1 AND pe.project_id=p.id AND pe.employee_id=e.id) AS manager_id
FROM projects p 
JOIN projects_employees pe ON pe.project_id=p.id JOIN project_status ps ON p.project_status_id=ps.id {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(p.id) FROM `projects` p JOIN projects_employees pe ON pe.project_id=p.id {$where};",$bindings)->fetchColumn();

$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;