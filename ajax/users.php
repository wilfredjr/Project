<?php
require_once("../support/config.php");
if(!AllowUser(array(1,4))){
         redirect("index.php");
     }


$primaryKey = 'id';
$index=-1;

$columns = array(
    // array( 'db' => 'code','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return htmlspecialchars($d);
    // }),
    array( 'db' => 'full_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'username','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'user_type','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    // array( 'db' => 'email','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return htmlspecialchars($d);
    // }),
    // array( 'db' => 'contact_no','dt' => ++$index ,'formatter'=>function ($d, $row) {
    //     return htmlspecialchars($d);
    // }),
    array(
        'db'        => 'id',
        'dt'        => ++$index,
        'formatter' => function ($d, $row) {
            $action_buttons="";

            if ($row['is_active']==1) :
                $action_buttons.="<a class='btn  btn-sm btn-success' href='activate.php?id={$row['id']}' onclick='return confirm(\"Are you sure you want to deactivate this user?\")'><span class='fa fa-lock' ></span> Deactivate</a>";
            else :
                $action_buttons.="<a class='btn  btn-sm btn-success' href='activate.php?id={$row['id']}' onclick='return confirm(\"Are you sure you want to activate this user?\")'><span class='fa fa-unlock' ></span> Activate</a>";
            endif;


            $action_buttons.=" <a href='frm_users.php?id={$row['id']}' class='btn btn-success  btn-sm'><span class='fa fa-pencil'></span></a>";
            if($row['emp_id']==$_SESSION[WEBAPP]['user']['employee_id']){

            }else{
            $action_buttons.=" <a href='delete.php?t=u&id={$row['id']}' onclick=\"return confirm('This record will be deleted.')\" class='btn  btn-danger btn-sm'><span class='fa fa-trash'></span></a>";}
            return $action_buttons;
        }
    )
);
 

require('../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
$whereAll="";
$whereResult="";



$whereAll=" u.is_deleted=0 ";




if (!empty($_GET['employee_id'])) {
    $whereAll.=" AND ";
    $whereAll.=" u.employee_id=:employee_id";
    $bindings[]=array('key'=>'employee_id','val'=>$_GET['employee_id'],'type'=>0);
}

if (!empty($_GET['user_type_id'])) {
    $whereAll.=" AND ";
    $whereAll.=" u.user_type_id=:user_type_id";
    $bindings[]=array('key'=>'user_type_id','val'=>$_GET['user_type_id'],'type'=>0);
}

function jp_bind($bindings)
{
    $return_array=array();
    if (is_array($bindings)) {
        for ($i=0, $ien=count($bindings) ; $i<$ien ; $i++) {
            //$binding = $bindings[$i];
                // $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
                $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
        }
    }

    return $return_array;
}
$where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;



$bindings=jp_bind($bindings);
$complete_query="SELECT u.id as id,u.employee_id as emp_id, e.code, CONCAT(e.first_name,' ',e.last_name) AS full_name,e.first_name,e.last_name, u.username as username, e.work_email as email, e.contact_no as contact_no,password,u.is_active, ut.description as user_type FROM users u INNER JOIN employees e ON e.id=u.employee_id JOIN user_type ut ON ut.id=u.user_type_id {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query, $bindings)->fetchAll();


$recordsTotal=$con->myQuery("SELECT COUNT(u.id) FROM users u INNER JOIN employees e ON e.id=u.employee_id {$where};", $bindings)->fetchColumn();


$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
