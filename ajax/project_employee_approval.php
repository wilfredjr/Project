<?php
require_once("../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'date_filed','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'project_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'designation','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'request_id','dt' => ++$index ,'formatter'=>function($d,$row){
        if($d=='0'){
        $d='Remove';
        return htmlspecialchars($d);}
        elseif($d=='1'){
        $d='Add';
        return htmlspecialchars($d);}
    }),
    array( 'db' => 'requested_by','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'status','dt' => ++$index ,'formatter'=>function($d,$row){
        // if($row['first_approver_date']=='0000-00-00'){
        //     return htmlspecialchars($d)."</br>"." (First Approver)";
        // }
        // elseif($row['second_approver_date']=='0000-00-00'){
        //     return htmlspecialchars($d)."</br>"." (Second Approver)";
        // }
        // elseif($row['third_approver_date']=='0000-00-00'){
        //     return htmlspecialchars($d)."</br>"." (Third Approver)";
        // }
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            $action_buttons="";
            $action_buttons.="<form method='post' action='move_project_emp1.php' style='display: inline' onsubmit='return confirm(\"Approve This Request?\")'>";
            $action_buttons.="<input type='hidden' name='id' value={$row['id']}>";
            $action_buttons.="<input type='hidden' name='requested_employee_id' value={$row['requested_employee_id']}>";
            // $action_buttons.="<input type='hidden' name='emp_id' value={$row['employee_id']}>";
            // $action_buttons.="<input type='hidden' name='dep_id' value={$row['department_id']}>";
            // $action_buttons.="<input type='hidden' name='date_start' value={$row['date_start']}>";
            // $action_buttons.="<input type='hidden' name='date_end' value={$row['date_end']}>";
            $action_buttons.="<input type='hidden' name='type' value='project_approval_emp'>";
            $action_buttons.="<button class='btn btn-sm btn-success' name='action' value='approve' title='Approve Request'><span class='fa fa-check'></span></button> ";
            $action_buttons.="</form>";
            $action_buttons.="<button class='btn btn-sm btn-info' title='Query Request' onclick='query(\"{$row['id']}\")'><span  class='fa fa-question'></span></button> ";
            $action_buttons.="<button class='btn btn-sm btn-danger' title='Reject Request' onclick='reject(\"{$row['id']}\")'><span class='fa fa-times'></span></button>";
            return $action_buttons;
        }
    )
);


require( '../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";


 $query="SELECT pr.id,requested_employee_id,
            (SELECT CONCAT(e.last_name,', ',e.first_name) FROM employees e WHERE e.id=pr.requested_employee_id) AS employee_name,
            p.name AS project_name,pr.date_filed,pr.first_approver_id,pr.second_approver_id,pr.third_approver_id,
            pr.first_approver_date,pr.second_approver_date,pr.third_approver_date,
            (SELECT CONCAT(e.last_name,', ',e.first_name) FROM employees e WHERE e.id=pr.manager_id) AS manager_name,
            pr.modification_type as request_id,rs.name AS status,
            (SELECT CONCAT(e.last_name,', ',e.first_name) FROM employees e WHERE e.id=pr.employee_id) AS requested_by,status_id,
            pd.name as designation
            FROM project_requests pr JOIN employees e ON e.id=pr.employee_id JOIN projects p ON pr.project_id=p.id
            JOIN request_status rs ON rs.id=pr.status_id
            JOIN project_designation pd on pr.designation_id=pd.id";
$join="JOIN employees e ON e.id=pr.employee_id JOIN projects p ON pr.project_id=p.id
            JOIN request_status rs ON rs.id=pr.status_id
            JOIN project_designation pd on pr.designation_id=pd.id";
$filter_sql="pr.is_deleted=0 AND status_id=1 AND (SELECT   
                         CASE   
                            WHEN pr.step_id=2 THEN pr.manager_id 
                            WHEN pr.step_id=3 THEN pr.admin_id
                         END=:employee_id)";
$filter_sql.=" ";
// $filter_sql.=" :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";
 $bindings[]=array('key'=>'employee_id','val'=>$_SESSION[WEBAPP]['user']['employee_id'],'type'=>0);

if(!empty($_GET['project_name']))
{
    $ltype=" pr.project_id=:project_name ";
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'project_name','val'=>$_GET['project_name'],'type'=>0);
    $filter_sql.=$ltype;
}
if (!empty($_GET['employee_id1'])) {
    $sa_id_sql="pr.requested_employee_id=:employee_id1";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'employee_id1','val'=>$_GET['employee_id1']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

 if(!empty($_GET['request_id1']) OR ($_GET['request_id1']=='0'))
 {
      $dep=" pr.modification_type=:request_id1 ";
      if(!empty($filter_sql))
    {
          $filter_sql.=" AND ";
      }
        $bindings[]=array('key'=>'request_id1','val'=>$_GET['request_id1'],'type'=>0);
    $filter_sql.=$dep;
 }
 if(!empty($_GET['status']))
 {

      $dep=" status_id=:status ";
      if(!empty($filter_sql))
    {
          $filter_sql.=" AND ";
      }
        $bindings[]=array('key'=>'status','val'=>$_GET['status'],'type'=>0);
    $filter_sql.=$dep;
 }


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
// $where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;
$where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";
$bindings=jp_bind($bindings);
// $complete_query="SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
             // FROM `vw_employees_leave` {$where} {$order} {$limit}";

$complete_query="{$query} {$where} {$order} {$limit}";
            // var_dump($complete_query);
            // die;
                    //         echo "<pre>";
                    // print_r($query);
                    // echo "</pre>";

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(pr.id) FROM `project_requests` pr {$join} {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
