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

    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'designation','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'mod_type','dt' => ++$index ,'formatter'=>function ($d, $row) {

        //return htmlspecialchars($d);
        if (htmlspecialchars($d) == '1') {
             return htmlspecialchars('Add');
        } else {
           return htmlspecialchars('Remove');
        }
    }),
    array( 'db' => 'reason','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'request_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
     array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";
            
            
        global $manage;
        if($row['status_id']=="3"):
            $action_buttons.="<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button> ";
        endif;
        if($row['status_id']=="1" || $row['status_id']=="3"):
                                                            
                                                          
            $action_buttons.="<form action='delete_project_employee.php' method='post' style='display: inline'>";
            $action_buttons.="<input type='hidden' name='id' value='".$_GET['id']."'>";
            $action_buttons.="<input type='hidden' name='emp_id' value='".$row['requested_employee_id']."'>";
            $action_buttons.="<input type='hidden' name='tab' value='2'>";
            $action_buttons.="<button type='submit' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this request?\")'><span class='fa fa-trash'></span></button></form>";
        endif; 
        return $action_buttons;
        })
    );

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
if(!empty($_GET['id'])){
    $whereAll="pr.employee_id='$employee_id' AND pr.is_deleted=0 AND pr.project_id = ".$_GET['id']; ;
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

$join_query="INNER JOIN request_status rs ON rs.id=pr.status_id
JOIN project_designation pd ON pr.designation_id=pd.id";

$bindings=jp_bind($bindings);
$complete_query="SELECT pr.id, pr.project_id,pr.reason, pr.modification_type as mod_type, pr.requested_employee_id, pr.date_filed as date_filed, pr.status_id as status_id, pr.is_deleted,pr.first_approver_date,pr.second_approver_date,pr.third_approver_date,(SELECT CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) FROM employees e WHERE e.id=pr.requested_employee_id) as name,rs.name as request_name,pd.name as designation FROM project_requests pr {$join_query} {$where} {$order} {$limit}";

$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(pr.id) FROM project_requests pr {$join_query} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
