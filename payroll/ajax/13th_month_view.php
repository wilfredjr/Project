<?php
    require_once("../../support/config.php"); 
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }
    //$table = 'pdd_items';
    $primaryKey = 'id';
    $status_filter="";
    $index=-1;
    $columns = array(
        array('db' => 'employee_code','dt' => ++$index,'formatter' => function( $d, $row ) {
                return htmlspecialchars($d);
        }),
        array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'amount','dt' => ++$index ,'formatter'=>function($d,$row){
            return number_format($d,2);
        }),
        array( 'db' => 'is_processed','dt' => ++$index ,'formatter'=>function($d,$row)
        {
            if($d==1):
                return "<a href='download_13th_month_details.php?id={$row['id']}' class='btn-sm btn-danger btn-flat' title='Download'><span class='fa fa-download'></span></a>";
            else:
                return "<a href='13th_month_adjust.php?id={$row['id']}' class='btn-sm btn-warning btn-flat' title='Adjust 13th Month'><span class='fa fa-pencil-square-o'></span></a>";
            endif;
        })
    );

    require('../../support/ssp.class.php');

    $query="SELECT
                md.id,
                md.13th_month_id,
                md.employee_id,
                e.code AS employee_code,
                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                md.amount,
                m.is_processed
            FROM 13th_month_details md
            INNER JOIN 13th_month m ON m.id=md.13th_month_id
            INNER JOIN employees e ON e.id=md.employee_id";

    $bindings = array();
    
    $limit = SSP::limit( $_GET, $columns );
    $order = SSP::order( $_GET, $columns );
    $where = SSP::filter( $_GET, $columns, $bindings );
    
    $whereAll="";
    $whereResult="";
    $filter_sql="";

    if(!empty($_GET['thmonth'])) 
    {
        $thmonth=" 13th_month_id=:thmonth ";
        if(!empty($filter_sql)) 
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'thmonth','val'=>$_GET['thmonth'],'type'=>0);
        $filter_sql.=$thmonth;
    }

    $whereAll.=$filter_sql;
    $where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";

    function jp_bind($bindings)
    {
        $return_array=array();
        if ( is_array( $bindings ) ) 
        {
                for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) 
                {
                    $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
                }
            }
            return $return_array;
    }
    $bindings=jp_bind($bindings);


    $data=$con->myQuery("{$query} {$where} {$limit}",$bindings)->fetchAll(PDO::FETCH_ASSOC);
    $count_data=count($data);
    
    for($i=0; $i < $count_data; $i++) 
    { 
        $adjust=$con->myQuery("SELECT id,adjustment_type,amount,remarks FROM 13th_month_adjust WHERE 13th_month_details_id=? AND is_deleted=0",array($data[$i]['id']))->fetchAll(PDO::FETCH_ASSOC);
        $count_adjust=count($adjust);
     
        for ($j=0; $j < $count_adjust; $j++) 
        { 
            if ($adjust[$j]['adjustment_type']==1) 
            {
                $data[$i]['amount']=$data[$i]['amount']+$adjust[$j]['amount'];
            }else
            {
                $data[$i]['amount']=$data[$i]['amount']-$adjust[$j]['amount'];
            }
        }
    }

    $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();
    $recordsFiltered = $con->myQuery("SELECT COUNT(md.id) FROM 13th_month_details md {$where} ",$bindings)->fetchColumn();
    $recordsTotal = $con->myQuery("SELECT COUNT(id) FROM 13th_month_details {$where}",$bindings)->fetchColumn();

    echo json_encode( 
        array(
            "draw"            => isset ( $_GET['draw'] ) ?
                intval( $_GET['draw'] ) :
                0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => SSP::data_output( $columns, $data )
        )
    );