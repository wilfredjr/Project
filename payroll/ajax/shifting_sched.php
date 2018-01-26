<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;  

$columns = array(
    array( 'db' => 'id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
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
        if(!empty($_GET['id'])){
         global $con;
         $action_buttons="";

         $get_emp_id=$con->myQuery("SELECT employee_id FROM employees_shift_details WHERE employee_shift_master_id = ? AND is_deleted = 0 AND employee_id=?",array($_GET['id'],$d))->fetch(PDO::FETCH_ASSOC);

        
         if(!empty($get_emp_id['employee_id'])){
            $action_buttons.="<a href='frm_shifting_sched.php?id={$_GET['id']}&eid={$row['id']}&u=1' class='btn btn-sm btn-danger'><span class='fa fa-edit'></span></a>&nbsp;";
        } else {
            $action_buttons="";
        }

           // $action_buttons.="<a href='view_shifting_sched.php?id={$row['id']}' class='btn btn-sm btn-danger'><span class='fa fa-search'></span></a>&nbsp;";

            //$action_buttons.="<a href='delete_shifting_sched.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this entry?\")'><span class='fa fa-trash'></span></a>";

        return $action_buttons;
    }
}),
    
    array( 'db' => 'id','dt' => ++$index ,'formatter'=>function ($d, $row) { 
        if(!empty($_GET['id'])){
            global $con;

            $get_emp_id=$con->myQuery("SELECT employee_id FROM employees_shift_details WHERE employee_shift_master_id = ? AND is_deleted = 0 AND employee_id=?",array($_GET['id'],$d))->fetch(PDO::FETCH_ASSOC);
            if(!empty($get_emp_id)){
                return "1";
            } else {
                return "0";
            }
        }else{
           return "0";
       }

   }),
    

    );


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
$whereAll=" es.name <> 'Resigned' and es.name <> 'Terminated' and e.is_deleted ='0'";
$whereResult="";

$filter_sql="";

if (!empty($_GET['emp_code'])) {
    $sa_id_sql=" e.code = :emp_code ";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'emp_code','val'=>$_GET['emp_code']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($_GET['emp_name'])) {
    $sa_id_sql=" e.id = :emp_name ";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'emp_name','val'=>$_GET['emp_name']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($_GET['department'])) {
    $sa_id_sql=" d.id = :department ";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'department','val'=>$_GET['department']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($_GET['job_title'])) {
    $sa_id_sql=" jt.id = :job_title ";
    
    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'job_title','val'=>$_GET['job_title']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

if (!empty($filter_sql)) {
    $whereAll.=" AND ".$filter_sql;
} else {
    $whereAll.=$filter_sql;
}
// $whereAll=" employees_id= :employees_id";
// $bindings[]=array('key'=>'employees_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);
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
$where.= !empty($where) ? " AND ".$whereAll:(empty($whereAll))?"":"WHERE ".$whereAll;

$join_query=" INNER JOIN job_title jt ON e.job_title_id = jt.id INNER JOIN employment_status es ON e.employment_status_id = es.id INNER JOIN departments d ON e.department_id = d.id ";

$bindings=jp_bind($bindings);
$complete_query="SELECT e.id, e.code as emp_code, CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) AS emp_name, CONCAT(d.name, ' (',d.description,')') as department, jt.description AS job_title FROM employees e {$join_query} {$where} {$order} {$limit}";


$data=$con->myQuery($complete_query, $bindings)->fetchAll();
$recordsTotal=$con->myQuery("SELECT COUNT(e.id) FROM employees e {$join_query} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);



echo json_encode($json);
die;
