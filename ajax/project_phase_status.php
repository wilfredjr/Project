<?php

require_once("../support/config.php"); 
$primaryKey = 'id';
$index=-1;
// if (empty($_SESSION[WEBAPP]['user']['access_project_management'])) {
//     redirect("../index.php");
//     die;
// }
// echo $inputs['id'];
// die;
$project_id=$_GET['id'];
$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
$manage=AccessForProject($project_id, $employee_id);
$columns = array(
    array( 'db' => 'date_filed','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),

    array( 'db' => 'phase_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'step_id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if($row['step_id']=='2'){
        return htmlspecialchars($row['manager']);
        }elseif($row['step_id']=='3'){
        return htmlspecialchars($row['admin']);
        }
    }),
    array( 'db' => 'type','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if($row['type']=='comp'){
        return 'Phase Completion';}
        else{
            return 'Phase Revertion';
        }
    }),
     array( 'db' => 'hours','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
     array( 'db' => 'request_status','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'comment','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'reason','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
        array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";
            global $employee_id;
            if($row['employee_id']==$employee_id){
            global $con;
            $current=$con->myQuery("SELECT id FROM  project_files WHERE phase_request_id=? AND is_approved=0 AND is_deleted=0",array($row['id']))->fetch(PDO::FETCH_ASSOC);
            if(!empty($current['id'])){
                $action_buttons.="<a href='download_file.php?id={$current['id']}&type=c' class='btn btn-default'><span class='fa fa-download'></span></a> ";
            }
            if($row['request_status_id']=="3"):
                $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            endif;
            if($row['request_status_id']<>"2" && $row['request_status_id']<>"5" && $row['request_status_id']<>"4"):
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='proj_id' value='{$row['project_id']}'>";
                $action_buttons.="<input type='hidden' name='type' value='phase'>";
                $action_buttons.=" <button class='btn btn-sm btn-danger' value='phase' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
            endif;

            return $action_buttons;
            }
        }
    ),
    );

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
if(!empty($_GET['id'])){
    $whereAll="ppr.project_id=".$_GET['id'];
}else{
    $whereAll="";
}
$whereResult="";
$filter_sql="";
$filter_sql.=" ";
if (!empty($_GET['employee_id'])) {
    $sa_id_sql="ppr.employee_id=:employee_id";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'employee_id','val'=>$_GET['employee_id']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($_GET['department_id'])) {
    $dep_id="d.id=:department_id";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'department_id','val'=>$_GET['department_id']."",'type'=>0);
    $filter_sql.=$dep_id;
}

if (!empty($_GET['job_id'])) {
    $job_id="jt.id=:job_id ";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'job_id','val'=>$_GET['job_id']."",'type'=>0);
    $filter_sql.=$job_id;
}

if (!empty($_GET['req_type'])) {
   

    $mod_type="modification_type=:mod_type";
    $req_type='';
    if ($_GET['req_type'] == '1') {
        $req_type = 1;
    }elseif ($_GET['req_type'] == '2') {
        $req_type = 0;
    }
  
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'mod_type','val'=>$req_type."",'type'=>0);
    $filter_sql.=$mod_type;
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

$join_query=" JOIN request_status rs ON ppr.request_status_id=rs.id
JOIN project_phases pp ON ppr.project_phase_id=pp.id";

$bindings=jp_bind($bindings);
$complete_query="SELECT ppr.id,ppr.reason,ppr.step_id,ppr.comment,ppr.employee_id,ppr.hours, ppr.project_id,ppr.type,ppr.request_status_id,pp.phase_name, rs.name AS request_status, ppr.date_filed,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.employee_id) AS employee_name,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.manager_id) AS manager,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.admin_id) AS admin
FROM project_phase_request ppr {$join_query} {$where} {$order} {$limit}";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(ppr.id) FROM project_phase_request ppr {$join_query} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
