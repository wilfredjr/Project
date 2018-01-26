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
        array( 'db' => 'remaining_leave_credit','dt' => ++$index ,'formatter'=>function($d,$row){
            return number_format($d,2);
        }),
        array( 'db' => 'rate_per_day','dt' => ++$index ,'formatter'=>function($d,$row){
            return number_format($d,2);
        }),
        array( 'db' => 'total_amount','dt' => ++$index ,'formatter'=>function($d,$row){
            return number_format($d,2);
        })
        // ,
        // array( 'db' => 'is_processed','dt' => ++$index ,'formatter'=>function($d,$row)
        // {
        //     if($d==1):
        //         return "<a href='download_13th_month_details.php?id={$row['id']}' class='btn-sm btn-danger btn-flat' title='Download'><span class='fa fa-download'></span></a>";
        //     else:
        //         return "<a href='13th_month_adjust.php?id={$row['id']}' class='btn-sm btn-warning btn-flat' title='Adjust 13th Month'><span class='fa fa-pencil-square-o'></span></a>";
        //     endif;
        // })
    );

    require('../../support/ssp.class.php');

    $query="SELECT
                lcd.id,
                lcd.leave_conversion_id,
                lc.transaction_code,
                lcd.employee_id,
                e.code AS employee_code,
                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                SUM(lcd.remaining_leave) AS remaining_leave_credit,
                lcd.rate_per_day,
                SUM(lcd.amount) AS total_amount
            FROM leave_conversion_details lcd 
            INNER JOIN leave_conversion lc  ON lc.id=lcd.leave_conversion_id
            INNER JOIN employees e      ON e.id=lcd.employee_id";

    $bindings = array();
    
    $limit = SSP::limit( $_GET, $columns );
    $order = SSP::order( $_GET, $columns );
    $where = SSP::filter( $_GET, $columns, $bindings );
    
    $whereAll="";
    $whereResult="";
    $filter_sql="";

    $group_by = " GROUP BY lcd.employee_id ";

    if(!empty($_GET['lc_id'])) 
    {

        $lc_id=" lcd.leave_conversion_id=:lc_id ";
        if(!empty($filter_sql)) 
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'lc_id','val'=>$_GET['lc_id'],'type'=>0);
        $filter_sql.=$lc_id;
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


    $data=$con->myQuery("{$query} {$where} {$group_by} {$limit} ",$bindings)->fetchAll(PDO::FETCH_ASSOC);
    $count_data=count($data);
    

    // $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();
    $recordsFiltered = $con->myQuery("SELECT COUNT(lcd.id) FROM leave_conversion_details lcd {$where} {$group_by} ",$bindings)->fetchColumn();
    // $recordsTotal = $con->myQuery("SELECT COUNT(id) FROM 13th_month_details",$bindings)->fetchColumn();

    echo json_encode( 
        array(
            "draw"            => isset ( $_GET['draw'] ) ?
                intval( $_GET['draw'] ) :
                0,
            "recordsTotal"    => intval( $recordsFiltered ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => SSP::data_output( $columns, $data )
        )
    );