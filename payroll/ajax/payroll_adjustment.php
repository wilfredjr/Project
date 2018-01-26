<?php
require_once("../../support/config.php");

$primaryKey = 'id';
$index=-1;  

$columns = array(

    array( 'db' => 'emp_code','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'emp_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_created','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'date_occur','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'amount','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'reason','dt' => ++$index ,'formatter'=>function ($d, $row) {
        return htmlspecialchars($d);
    }),
    array( 'db' => 'status','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if ($d == 1){
            return '<button type="button" class="btn btn-flat btn-sm btn-warning" style="width: 100%;">PAID</button>';
        }else{
            return '<button type="button" class="btn btn-flat btn-sm btn-danger" style="width: 100%;">NOT PAID</button>';
        } 
    }),
    array( 'db' => 'adjustment_type','dt' => ++$index ,'formatter'=>function ($d, $row) {
        if ($d == 1){
            return '<button type="button" class="btn btn-flat btn-sm btn-warning" style="width: 100%;">PLUS</button>';
        }else{
            return '<button type="button" class="btn btn-flat btn-sm btn-danger" style="width: 100%;">LESS</button>';
        } 
    }),
    array( 'db' => 'id','dt' => ++$index ,'formatter'=>function ($d, $row) {
        $action_buttons ="";

        if($row['status'] == 0){
          
            $date = new DateTime();
            $date_o=new DateTime($row['date_occur']);
            $date_occur=$date_o->format("m/d/Y");

            $action_buttons.="<button type='button' data-toggle='modal' data-target='#myModal' data-id='{$row['id']}' data-emp_code='{$row['emp_code']}' data-emp_name='{$row['emp_name']}' data-dt_occur='{$date_occur}' data-amount='{$row['amount']}' data-reason='{$row['reason']}' data-adjustment_type='{$row['adjustment_type']}' class='btn btn-flat btn-sm btn-danger' onclick='pass(this)''><span class='fa fa-edit'></span></button>&nbsp;";
            $action_buttons.="<a href='delete_payroll_adjustment.php?id={$row['id']}' class='btn btn-flat btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this entry?\")'><span class='fa fa-trash'></span></a>";
        }else{
           $action_buttons ="";
       }

       return $action_buttons;
   }),
    );


require('../../support/ssp.class.php');


$limit = SSP::limit($_GET, $columns);
$order = SSP::order($_GET, $columns);

$where = SSP::filter($_GET, $columns, $bindings);
$whereAll=" pa.is_deleted = '0' ";
$whereResult="";


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

$join_query=" INNER JOIN employees e ON pa.employee_id = e.id ";

$bindings=jp_bind($bindings);
$complete_query="SELECT pa.id, e.code as emp_code, CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) AS emp_name, pa.date_created, pa.date_occur, pa.amount, pa.reason,pa.status, pa.adjustment_type
FROM payroll_adjustments pa {$join_query} {$where} {$order} {$limit}";


$data=$con->myQuery($complete_query, $bindings)->fetchAll();

$recordsTotal=$con->myQuery("SELECT COUNT(pa.id) FROM payroll_adjustments pa {$join_query} {$where};", $bindings)->fetchColumn();

$json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns, $data);

echo json_encode($json);
die;
