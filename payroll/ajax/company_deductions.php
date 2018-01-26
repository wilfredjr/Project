<?php
    require_once("../../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $primaryKey = 'id';
    $index=-1;
    $columns = array(
        array('db' => 'comde_code','dt' => ++$index,'formatter' => function( $d, $row ) {
            return htmlspecialchars($d);
        }),
        array( 'db' => 'comde_desc','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'id','dt' => ++$index ,'formatter' => function( $d, $row ) {
            $action_buttons="";
            $action_buttons.="<a class='btn-s btn-sm btn-danger' title='Edit Details' href='frm_company_deductions.php?id={$d}'><span class='fa fa-pencil'></span></a>&nbsp;";
            $action_buttons.="<a href='delete_company_deduction.php?id={$row['id']}' title='Delete Item' onclick='return confirm(\"This record will be deleted.\")' class='btn-s btn-danger btn-sm'><span class='fa fa-trash'></span></a>";
            return $action_buttons;
        }),
    );
 
    require( '../../support/ssp.class.php' );

    // $bindings = array();
    //$db = self::db( $conn );
    // Build the SQL query string from the request
    $limit = SSP::limit( $_GET, $columns );
    $order = SSP::order( $_GET, $columns );
    $where = SSP::filter( $_GET, $columns, $bindings );
    // Main query to actually get the data
    $whereAll="";
    $whereResult="";
    // $filter_sql="";
    
    $query="SELECT
                    id,
                    comde_code,
                    comde_desc
                FROM company_deductions";

    $whereAll=" is_deleted=0 ";
    
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
    $where.= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";
    $bindings=jp_bind($bindings);
    $complete_query="SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", SSP::pluck($columns, 'db'))."`
             FROM `company_deductions` {$where} {$order} {$limit}";

    $data=$con->myQuery($complete_query,$bindings)->fetchAll();
    // $data=$con->myQuery("{$query} {$order} {$limit} ",$bindings)->fetchAll(PDO::FETCH_ASSOC);

    // Data set length after filtering
    $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

    // Total data set length
    //echo "SELECT COUNT(id_pdd_bom) FROM pdd_boms ".$where;
    // $recordsFiltered = $con->myQuery("{$query} ",$bindings)->fetchAll(PDO::FETCH_ASSOC);
    // $recordsFiltered = count($recordsFiltered);
    $recordsTotal = $con->myQuery("SELECT COUNT(id) FROM company_deductions {$where}",$bindings)->fetchColumn();
    /*
     * Output
     */

    $json['draw']=isset ( $request['draw'] ) ?intval( $request['draw'] ) :0;
    $json['recordsTotal']=$recordsFiltered;
    $json['recordsFiltered']=$recordsFiltered;
    $json['data']=SSP::data_output($columns,$data);

    echo json_encode($json);
    die;
    // echo json_encode( array(
    //     "draw"            => isset ( $_GET['draw'] ) ?
    //         intval( $_GET['draw'] ) :
    //         0,
    //     "recordsTotal"    => intval( $recordsFiltered ),
    //     "recordsFiltered" => intval( $recordsFiltered ),
    //     "data"            => SSP::data_output( $columns, $data )
    // ));