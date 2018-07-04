<?php
require_once("../support/config.php"); 

$primaryKey = 'id';
$index=-1;

$columns = array(
    array( 'db' => 'name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
    // array( 'db' => 'date_filed','dt' => ++$index ,'formatter'=>function($d,$row){
    //     return htmlspecialchars($d);
    // }),
    // array( 'db' => 'start_date','dt' => ++$index ,'formatter'=>function($d,$row){
    //     return htmlspecialchars($d);
    // }),
    // array( 'db' => 'end_date','dt' => ++$index ,'formatter'=>function($d,$row){
    //    if (empty(strtotime($d))){
    //     //$end_date=str_replace("Not yet done");
    //     return htmlspecialchars('------');
    //   }
    //   else {
    //     return htmlspecialchars($d);
    //   }
    // }),
    
    //     array( 'db' => 'description','dt' => ++$index ,'formatter'=>function($d,$row){
    //     return htmlspecialchars($d);
    // }),
     array( 'db' => 'phase_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
        array( 'db' => 'status_name','dt' => ++$index ,'formatter'=>function($d,$row){
        return htmlspecialchars($d);
    }),
        array( 'db' => 'status_name','dt' => ++$index ,'formatter'=>function($d,$row){
            if($row['project_status_id']=='2'){
                $percent=100;
            }else{
            $percent=(($row['cur_phase']-1)/8)*100;}
            if($row['project_status_id']=='1'){
                return "<div class='progress progress-sm progress-striped active'>
                      <div class='progress-bar progress-bar-info' style='width:".$percent."%;background-color: #ffa500 !important;'></div>
                    </div>";
                }
             elseif($row['project_status_id']=='2'){
                return "<div class='progress progress-sm progress-striped active'>
                      <div class='progress-bar progress-bar-success' style='width:".$percent."%'></div>
                    </div>";
                }
            elseif($row['project_status_id']=='4'){
                return "<div class='progress progress-sm progress-striped active'>
                      <div class='progress-bar progress-bar-danger' style='width:".$percent."%'></div>
                    </div>";
                }
    }),
        array( 'db' => 'status_name','dt' => ++$index ,'formatter'=>function($d,$row){
            if($row['project_status_id']=='2'){
                $percent=100;
            }else{
            $percent=(($row['cur_phase']-1)/8)*100;}
            if($row['project_status_id']=='1'){
                return "<center><span class='badge' style='background-color: #ffa500 !important;'>".$percent."%</span></center>";
            }
            elseif($row['project_status_id']=='2'){
                return "<center><span class='badge bg-green'>".$percent."%</span></center>";
            }
            elseif($row['project_status_id']=='4'){
                return "<center><span class='badge bg-red'>".$percent."%</span></center>";
            }
    })
    );
 

require( '../support/ssp.class.php' );


$limit = SSP::limit( $_GET, $columns );
$order = SSP::order( $_GET, $columns );

$where = SSP::filter( $_GET, $columns, $bindings );
$whereAll="";
$whereResult="";

    $whereAll="p.is_deleted=0";
    $filter_sql="";
    $filter_sql.=" ";

    if(!empty($_GET['status']))
    {
        $stat=" project_status_id=:project_status_id ";
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'project_status_id','val'=>$_GET['status'],'type'=>0);
        $filter_sql.=$stat;
        // echo $filter_sql;
    }
    if(!empty($_GET['proj_name']))
    {
        $name=" name=:proj_id ";
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'proj_id','val'=>$_GET['proj_name'],'type'=>0);
        $filter_sql.=$name;
        // echo $filter_sql;
    }
    if(!empty($_GET['manager']))
    {
        $mana=" manager_id=:mana_id ";
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'mana_id','val'=>$_GET['manager'],'type'=>0);
        $filter_sql.=$mana;
        // echo $filter_sql;
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
        $date_filter.=" start_date >= :date_start";
        $bindings[]=array('key'=>'date_start','val'=>date_format($date_start,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    $date_filter="";
    if(!empty($date_end))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" start_date <= :date_end";
        $bindings[]=array('key'=>'date_end','val'=>date_format($date_end,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;


$date_filter="";


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
$where.= !empty($where) ? " AND ".$whereAll:"WHERE ".$whereAll;

$order=" ORDER BY ps.order";
$join_query="JOIN project_status ps ON p.project_status_id = ps.id JOIN project_phases pp ON p.cur_phase=pp.id";

$bindings=jp_bind($bindings);
$complete_query="SELECT ps.status_name,ps.order,pp.phase_name, p.id as proj_id, p.project_status_id, p.name, p.description, p.department_id, p.employee_id,  DATE_FORMAT(p.start_date,'".DATE_FORMAT_SQL."') as start_date, DATE_FORMAT(p.date_filed,'".DATE_FORMAT_SQL."') as date_filed, DATE_FORMAT(p.end_date,'".DATE_FORMAT_SQL."') as end_date, p.is_deleted,cur_phase FROM projects p {$join_query} {$where} {$order} {$limit}";
            // echo $complete_query;
             //var_dump($bindings);

$data=$con->myQuery($complete_query,$bindings)->fetchAll();
$recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

$recordsTotal=$con->myQuery("SELECT COUNT(p.id) FROM `projects` p {$join_query} {$where}",$bindings)->fetchColumn();

$json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
$json['recordsTotal']=$recordsTotal;
$json['recordsFiltered']=$recordsTotal;
$json['data']=SSP::data_output($columns,$data);

echo json_encode($json);
die;