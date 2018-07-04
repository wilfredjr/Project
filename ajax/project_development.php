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
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
     array( 'db' => 'worked_done','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'work_done','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'status_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_finished','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if($row['date_finished']=="0000-00-00"){
            return "-";
        }else{
        return htmlspecialchars($d);}
    }),
        array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";
            global $employee_id;
            global $con;
              $current=$con->myQuery("SELECT pf.id FROM  project_files pf JOIN project_task_list ptl ON ptl.request_id=pf.task_completion_id  WHERE pf.task_completion_id=? AND pf.is_deleted=0 AND ptl.is_submitted=1",array($row['request_id']))->fetch(PDO::FETCH_ASSOC);
            if(!empty($current['id'])){
                $action_buttons.="<a href='download_file.php?id={$current['id']}&type=c' class='btn btn-default'><span class='fa fa-download'></span></a> ";
            }

            if($row['status_id']!=2){
            $action_buttons.="<button class='btn btn-sm btn-success' title='Reassign Task' onclick='submit(\"{$row['id']}\")'><span  class='fa fa-user'></span></button> ";
            }
            return $action_buttons;
        }
    ),
    );

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
if(!empty($_GET['id'])){
    $whereAll="ptl.project_id=".$_GET['id']." AND ptl.project_phase_id='3' ";
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

$join_query="JOIN project_status ps ON ps.id=ptl.status_id";

$bindings=jp_bind($bindings);
$complete_query="SELECT ptl.id,ptl.project_id,ptl.status_id, ptl.worked_done,date_finished,ptl.project_phase_id,ptl.work_done,ps.status_name,ptl.employee_id,ptl.request_id,
(SELECT CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) FROM employees e WHERE e.id=ptl.employee_id) as employee_name
FROM project_task_list ptl {$join_query} {$where} {$order} {$limit}";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(ptl.id) FROM project_task_list ptl {$join_query} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
