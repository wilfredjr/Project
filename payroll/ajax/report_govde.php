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
        array( 'db' => 'govde_code','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'employee_share','dt' => ++$index ,'formatter'=>function($d,$row){
            return number_format($d,2);
        }),
        array( 'db' => 'employer_share','dt' => ++$index ,'formatter'=>function($d,$row){
            return number_format($d,2);
        })
    );

    require('../../support/ssp.class.php');

    $query="SELECT
                    pg.id,
                    payroll.id,
                    pg.payroll_code,
                    pg.employee_id,
                    e.code AS employee_code,
                    CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                    pg.govde_code,
                    pg.govde_eeshare AS employee_share,
                    pg.govde_ershare AS employer_share,
                    pg.gov_desc
                FROM payroll_govde pg
                INNER JOIN employees e ON e.id=pg.employee_id
                INNER JOIN payroll ON payroll.payroll_code=pg.payroll_code";

    $bindings = array();
    //$db = self::db( $conn );
    // Build the SQL query string from the request
    $limit = SSP::limit( $_GET, $columns );
    $order = SSP::order( $_GET, $columns );
    $where = SSP::filter( $_GET, $columns, $bindings );
    // Main query to actually get the data
    $whereAll="";
    $whereResult="";
    $filter_sql="";
    //$inputs=array();

    if(!empty($_GET['govde'])) 
    {
        $govde=" gov_desc=:govde ";
        if(!empty($filter_sql)) 
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'govde','val'=>$_GET['govde'],'type'=>0);
        $filter_sql.=$govde;
    }
    if(!empty($_GET['payroll_code'])) 
    {
        $payroll_code=" payroll.id=:payroll_code ";
        if(!empty($filter_sql)) 
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'payroll_code','val'=>$_GET['payroll_code'],'type'=>0);
        $filter_sql.=$payroll_code;
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

    // echo $query." ".$where."<br>";

    $data=$con->myQuery("{$query} {$where} {$limit}",$bindings)->fetchAll(PDO::FETCH_ASSOC);        
    // $count_stock=count($data_stock);
    $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

    // Total data set length
    $recordsFiltered = $con->myQuery("SELECT COUNT(payroll_govde.id) FROM payroll_govde INNER JOIN employees e ON e.id=payroll_govde.employee_id INNER JOIN payroll ON payroll.payroll_code=payroll_govde.payroll_code {$where} ",$bindings)->fetchColumn();
    $recordsTotal = $con->myQuery("SELECT COUNT(id) FROM payroll_govde ",$bindings)->fetchColumn();

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