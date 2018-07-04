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
$request_id=$_GET['id'];
$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
$columns = array(
    array( 'db' => 'date_filed','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),

    array( 'db' => 'phase_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
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
    array( 'db' => 'comment','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'request_status','dt' => ++$index ,'formatter'=>function ($d, $row) {
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
                        global $con;
              $current=$con->myQuery("SELECT pf.id FROM  bug_files pf JOIN project_bug_request ptl ON ptl.id=pf.bug_request_id  WHERE pf.bug_request_id=? AND pf.is_deleted=0",array($row['id']))->fetch(PDO::FETCH_ASSOC);
              if(!empty($current['id'])){
                $action_buttons.="<a href='download_file.php?id={$current['id']}&type=bf' class='btn btn-default'><span class='fa fa-download'></span></a>";
            }
            if($row['employee_id']==$_SESSION[WEBAPP]['user']['employee_id']):
            if($row['request_status_id']=="3"):
                $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button>";
            endif;
            if($row['request_status_id']<>"2" && $row['request_status_id']<>"5" && $row['request_status_id']<>"4"):
                $action_buttons.="<form method='post' action='cancel.php?id={$row['id']}' onsubmit='return confirm(\"Cancel This Request?\")' style='display:inline'>";
                $action_buttons.="<input type='hidden' name='bug_id' value='{$row['bug_list_id']}'>";
                $action_buttons.="<input type='hidden' name='type' value='bug'>";
                $action_buttons.=" <button class='btn btn-sm btn-danger' value='phase' title='Cancel Request'><span class='fa fa-trash'></span></button></form>";
            endif;
          endif;
            return $action_buttons;
        }
    ),
    );

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
if(!empty($_GET['id'])){
    $whereAll="ppr.bug_list_id=".$_GET['id'];
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
JOIN employees e ON ppr.manager_id=e.id JOIN project_bug_phase pp ON pp.id=ppr.bug_phase_id";

$bindings=jp_bind($bindings);
$complete_query="SELECT ppr.id,ppr.reason,ppr.comment, ppr.project_id,ppr.type,ppr.request_status_id,pp.name as phase_name,ppr.bug_phase_id,ppr.bug_list_id, rs.name AS request_status, ppr.date_filed, ppr.step_id, ppr.employee_id,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.manager_id) AS manager,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.admin_id) AS admin,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.team_lead_id) AS team_lead,
(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=ppr.employee_id) AS employee
FROM project_bug_request ppr {$join_query} {$where} {$order} {$limit}";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(ppr.id) FROM project_bug_request ppr {$join_query} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
