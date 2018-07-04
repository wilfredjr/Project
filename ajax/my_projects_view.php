<?php

require_once("../support/config.php"); 
$primaryKey = 'id';
$index=-1;  

// echo $inputs['id'];
// die;
$project_id=$_GET['id'];
$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
$emp_des=$con->myQuery("SELECT designation_id FROM projects_employees WHERE employee_id=? and project_id=?",array($employee_id,$project_id))->fetch(PDO::FETCH_ASSOC);
$des=$emp_des['designation_id'];
// var_dump($des);
// die;
$manage=AccessForProject($project_id, $employee_id);
$columns = array(
    array( 'db' => 'emp_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
         if (($row['team_lead_ba']=='1')&&($row['is_manager']=='1')&&($row['team_lead_dev']=='1')){
            return htmlspecialchars($d)." <span class='badge'>Team Lead DEV & BA</span> <span class='badge'>Manager</span> ";
        }
         elseif(($row['team_lead_ba']=='1')&&($row['team_lead_dev']=='1')){
           return htmlspecialchars($d)." <span class='badge'>Team Lead DEV & BA</span>";
        }
        elseif(($row['is_manager']=='1')&&($row['team_lead_ba']=='1')){
            return htmlspecialchars($d)." <span class='badge'>Team Lead BA</span>  <span class='badge'>Manager</span> ";
        }
        elseif(($row['is_manager']=='1')&&($row['team_lead_dev']=='1')){
            return htmlspecialchars($d)." <span class='badge'>Team Lead DEV</span>  <span class='badge'>Manager</span> ";
        }
        elseif($row['is_manager']=='1'){
           return htmlspecialchars($d)." <span class='badge'>Manager</span>";
        }  
         elseif($row['team_lead_ba']=='1'){
           return htmlspecialchars($d)." <span class='badge'>Team Lead BA</span>";
        }         
         elseif($row['team_lead_dev']=='1'){
           return htmlspecialchars($d)." <span class='badge'>Team Lead DEV</span>";
        }                
        else{
            return htmlspecialchars($d);
        }
    }),
    array( 'db' => 'designation','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_assigned','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
   
     array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            // $action_buttons.="<button type='button' data-toggle='modal' data-target='#myModal' class='btn btn-sm btn-danger'><span class='fa fa-search'></button>&nbsp;";
            global $manage;
            global $des;
            //$action_buttons.="<input type='text' name='id' value='{$row['manager_id']}'>";
            $action_buttons.="<form method='post' action='delete_project_employee.php?id={$row['proj_id']}&tab=1' style='display: inline'>";
            $action_buttons.="<input type='hidden' name='id' value={$_GET['id']}>";
            $action_buttons.="<input type='hidden' name='tab' value=1>";
            $action_buttons.="<input type='hidden' name='employee_id' value={$row['emplo_id']}>";
            $action_buttons.="<input type='hidden' name='manager_id' value={$row['manager_id']}>";
             $action_buttons.="<input type='hidden' name='designation' value={$row['des_id']}>";
            $action_buttons.="<input type='hidden' name='project_name' value='{$row['project_name']}'>";
            //$action_buttons.="<button class='btn btn-sm btn-danger'><span class='fa fa-remove'></span></button>&nbsp;";
            if($row['emplo_id']==$row['admin_id']){}else{
            if($row['project_status_id']=='1'||$row['project_status_id']=='3'||$row['project_status_id']=='4'){
                if($manage['is_manager']=='1'){
                    if(($row['emplo_id']==$_SESSION[WEBAPP]['user']['employee_id'])||($row['team_lead_ba']=='1')||($row['team_lead_dev']=='1')){
                            
                        }
                         else{
                            $action_buttons.="<button class='btn btn-sm btn-danger'  title='delete' onclick='return confirm(\"Do you request to remove this employee?\")'><span class='fa fa-times'></span></button>";
                        }
                }
                 elseif(($manage['is_team_lead_ba']=='1')OR($manage['is_team_lead_dev']=='1')){
                    if($row['is_manager']=='0'){
                        if(($row['team_lead_ba']=='1')||($row['team_lead_dev']=='1')){

                        }
                        elseif($des==$row['des_id']){

                            $action_buttons.="<button class='btn btn-sm btn-danger'  title='delete' onclick='return confirm(\"Do you request to remove this employee?\")'><span class='fa fa-times'></span></button>";
                        }
                    }
                }
            }
        }
            return $action_buttons;
           
        })
        
    );

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
if(!empty($_GET['id'])){
    $whereAll="pe.is_deleted=0 AND p.id=".$_GET['id'] ;
}else{
    $whereAll="";
}
$whereResult="";
$filter_sql="";
$filter_sql.=" ";
if (!empty($_GET['employee_id'])) {
    $sa_id_sql="pe.employee_id=:employee_id";

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

$join_query=" INNER JOIN projects p ON pe.project_id=p.id
LEFT JOIN project_designation pd ON pe.designation_id=pd.id ";

$bindings=jp_bind($bindings);
$complete_query="SELECT p.name as project_name,pe.date_assigned, pe.project_id as proj_id, p.manager_id as manager_id, pe.employee_id as emplo_id, p.id,pe.is_team_lead_ba as team_lead_ba,pe.is_team_lead_dev as team_lead_dev,(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pe.employee_id) AS emp_name,pe.is_manager as manager,project_status_id,is_manager,pd.name as designation,pe.designation_id as des_id,p.employee_id as admin_id
FROM projects_employees pe {$join_query} {$where} {$order} {$limit}";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(pe.id) FROM projects_employees pe {$join_query} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
