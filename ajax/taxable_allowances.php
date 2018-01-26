<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $primaryKey = 'id';
    $index=-1;
    $columns = array(
        array('db' => 'rta_code','dt' => ++$index,'formatter' => function( $d, $row ) {
                return htmlspecialchars($d);
        }),
        array( 'db' => 'rta_desc','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'rta_amount','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'rta_taxable','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'id','dt' => ++$index ,'formatter' => function( $d, $row ) {
            $action_buttons="";
            $action_buttons.="<a class='btn-s btn-sm btn-warning' title='Edit Details' href='frm_taxable_allowances.php?id={$d}'><span class='fa fa-pencil'></span></a>&nbsp;";
            $action_buttons.="<a href='delete_taxable_allowances.php?id={$row['id']}' title='Delete Item' onclick='return confirm(\"This record will be deleted.\")' class='btn-s btn-danger btn-sm'><span class='fa fa-trash'></span></a>";
            return $action_buttons;
        }),
    );
 
    require( '../support/ssp.class.php' );

    $query="SELECT
                    id,
                    rta_code,
                    rta_desc,
                    rta_amount,
                    rta_taxable
                FROM receivable_and_taxable_allowances
                WHERE is_deleted=0";

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


    $data=$con->myQuery("{$query} {$where} {$order} {$limit} ",$bindings)->fetchAll(PDO::FETCH_ASSOC);

    // Data set length after filtering
    $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

    // Total data set length
    //echo "SELECT COUNT(id_pdd_bom) FROM pdd_boms ".$where;
    $recordsFiltered = $con->myQuery("{$query} ",$bindings)->fetchAll(PDO::FETCH_ASSOC);
    $recordsFiltered = count($recordsFiltered);
    $recordsTotal = $con->myQuery("SELECT COUNT(id) FROM receivable_and_taxable_allowances",$bindings)->fetchColumn();
    /*
     * Output
     */
    echo json_encode( array(
        "draw"            => isset ( $_GET['draw'] ) ?
            intval( $_GET['draw'] ) :
            0,
        "recordsTotal"    => intval( $recordsFiltered ),
        "recordsFiltered" => intval( $recordsFiltered ),
        "data"            => SSP::data_output( $columns, $data )
    ));