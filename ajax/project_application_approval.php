<?php
require_once("../support/config.php");

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'date_filed','dt' => ++$index ,'formatter'=>function($d,$row){
         return htmlspecialchars($d);
    }),
    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'des','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_start','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return nl2br($d);
    }),
    array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return nl2br($d);
    }),
    array( 'db' => 'team_lead_ba','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
    array( 'db' => 'team_lead_dev','dt' => ++$index ,'formatter'=>function($d,$row){
          return htmlspecialchars($d);
    }),
    array( 'db' => 'request_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function( $d, $row ) {
            global $con;
            $action_buttons="";
            $current=$con->myQuery("SELECT id FROM  project_files WHERE project_application_id=? AND is_approved=0 AND is_deleted=0",array($row['id']))->fetch(PDO::FETCH_ASSOC);
            if(!empty($current['id'])){
                $action_buttons.="<a href='download_file.php?id={$current['id']}&type=c' class='btn btn-default'><span class='fa fa-download'></span></a> ";
            }
            $action_buttons.="<form method='post' action='move_approval.php' style='display: inline' onsubmit='return confirm(\"Approve This Request?\")'>";
            $action_buttons.="<input type='hidden' name='id' value={$row['id']}>";
            $action_buttons.="<input type='hidden' name='employee_id' value={$row['employee_id']}>";
            // $action_buttons.="<input type='hidden' name='emp_id' value={$row['employee_id']}>";
            // $action_buttons.="<input type='hidden' name='dep_id' value={$row['department_id']}>";
            // $action_buttons.="<input type='hidden' name='date_start' value={$row['date_start']}>";
            // $action_buttons.="<input type='hidden' name='date_end' value={$row['date_end']}>";
            $action_buttons.="<input type='hidden' name='type' value='project_application_approval'>";
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
$whereAll="";
$whereResult="";


 $query="SELECT pa.id,pa.name,pa.des,pa.date_start,pa.request_status_id,pa.employee_id,pa.date_filed,pa.reason,rs.name as request_name,
(SELECT CONCAT(e.last_name,', ',e.first_name) FROM employees e WHERE e.id=pa.team_lead_ba) AS team_lead_ba,
(SELECT CONCAT(e.last_name,', ',e.first_name) FROM employees e WHERE e.id=pa.team_lead_dev) AS team_lead_dev,
(SELECT CONCAT(e.last_name,', ',e.first_name) FROM employees e WHERE e.id=pa.employee_id) AS employee_name
FROM project_application pa";
$join=" JOIN request_status rs ON rs.id=pa.request_status_id";
$filter_sql="pa.request_status_id='1'";
$filter_sql.="";
// $filter_sql.=" :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";

if(!empty($_GET['project_name']))
{
    $ltype=" pa.id=:project_name ";
    if(!empty($filter_sql))
    {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'project_name','val'=>$_GET['project_name'],'type'=>0);
    $filter_sql.=$ltype;
}
if (!empty($_GET['employee_id1'])) {
    $sa_id_sql="pa.employee_id=:employee_id1";

    if (!empty($filter_sql)) {
        $filter_sql.=" AND ";
    }
    $bindings[]=array('key'=>'employee_id1','val'=>$_GET['employee_id1']."",'type'=>0);
    $filter_sql.=$sa_id_sql;
}

    if(!empty($_GET['date_start']))
    {
        $date_start=date_create($_GET['date_start']);
    }else
    {
        $date_start="";
    }
    if(!empty($_GET['date_end']))
    {
        $date_end=date_create($_GET['date_end']);
    }else
    {
        $date_end="";
    }

    $date_filter="";
    if(!empty($date_start))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" pa.date_filed >= :date_start";
        $bindings[]=array('key'=>'date_start','val'=>date_format($date_start,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    $date_filter="";
    if(!empty($date_end))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" pa.date_filed <= :date_end";
        $bindings[]=array('key'=>'date_end','val'=>date_format($date_end,'Y-m-d'),'type'=>0);
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
// $where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;
$where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";
$bindings=jp_bind($bindings);
// $complete_query="SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
             // FROM `vw_employees_leave` {$where} {$order} {$limit}";

$complete_query="{$query} {$join} {$where} {$order} {$limit}";
            // var_dump($complete_query);
            // die;
                    //         echo "<pre>";
                    // print_r($query);
                    // echo "</pre>";

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(pa.id) FROM `project_application` pa {$join} {$where};",$bindings)->fetchColumn();


$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;
