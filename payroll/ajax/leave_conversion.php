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
        array('db' => 'transaction_code','dt' => ++$index,'formatter' => function( $d, $row ) {
                return htmlspecialchars($d);
        }),
        array( 'db' => 'payroll_group','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'date_generated','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'is_processed','dt' => ++$index ,'formatter'=>function($d,$row){
            if($d==1) 
            {
                return "Processed: ".$row['date_processed'];
            }else
            {
                return "Not yet processed";
            }
        }),
        array( 'db' => 'id','dt' => ++$index ,'formatter'=>function($d,$row){
            $action_buttons="";
            $action_buttons.="<a class='btn btn-flat btn-sm btn-default' title='View Details' href='leave_conversion_view.php?id={$d}'><span class='fa fa-search'></span></a>";
            if($row['is_processed']==0)
            {
                $action_buttons.="&nbsp;<form method='post' action='delete_leave_conversion.php?id={$d}' onsubmit='return confirm(\"Are you sure you want to delete this transaction?\")' style='display:inline'><input type='hidden' name='id' value='{$d}'><button class='btn btn-sm btn-danger btn-flat'  title='Delete Transaction' type='submit'><span  class='fa fa-close'></span></button></form>";
            }
            return $action_buttons;
        }),
        array( 'db' => 'date_processed','dt' => ++$index ,'formatter'=>function ($d, $row) {
            return "";
        })
    );

    require('../../support/ssp.class.php');

    $query="SELECT
                lc.id,
                lc.transaction_code,
                lc.pay_group_id,
                pg.name AS payroll_group,
                lc.date_generated,
                lc.for_year,
                lc.is_processed,
                lc.date_processed
            FROM leave_conversion lc
            INNER JOIN payroll_groups pg ON pg.payroll_group_id=lc.pay_group_id";

    $bindings = array();
    
    $limit = SSP::limit( $_GET, $columns );
    $order = SSP::order( $_GET, $columns );
    $where = SSP::filter( $_GET, $columns, $bindings );
    
    $whereAll="";
    $whereResult="";
    $filter_sql="";

    $filter_sql.=" lc.is_deleted=0 ";

    if(!empty($_GET['pay_code_filter']))
    {
        $pay_code_filter = " lc.id=:pay_code_filter ";    
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'pay_code_filter','val'=>$_GET['pay_code_filter'],'type'=>0);
        $filter_sql.=$pay_code_filter;
    }
    if(!empty($_GET['pay_group_filter']))
    {
        $pay_group_filter = " lc.pay_group_id=:pay_group_filter ";    
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'pay_group_filter','val'=>$_GET['pay_group_filter'],'type'=>0);
        $filter_sql.=$pay_group_filter;
    }
    if(!empty($_GET['status_filter']))
    {
        if ($_GET['status_filter']==1) 
        {
            $status_filter = " lc.is_processed=:status_filter ";    
            $bindings[]=array('key'=>'status_filter','val'=>$_GET['status_filter'],'type'=>0);
        }else
        {
            $status_filter = " lc.is_processed=0 ";            
        }
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $filter_sql.=$status_filter;
    }

    if(!empty($_GET['date_generated_filter']))
    {
        $date_generated_filter=date_create($_GET['date_generated_filter']);
    }else
    {
        $date_generated_filter="";
    }
    $date_filter="";
    if(!empty($date_generated_filter))
    {
        $date_filter .= !empty($filter_sql)?" AND ":"";
        $date_filter .= " lc.date_generated = :date_generated_filter";
        $bindings[]  = array('key'=>'date_generated_filter','val'=>date_format($date_generated_filter,'Y-m-d'),'type'=>0);
    }
    $filter_sql .= $date_filter;

    $whereAll .= $filter_sql;
    $where .= !empty($where) ? " AND ".$whereAll:!empty($whereAll)?"WHERE ".$whereAll:"";

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
    $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

    $recordsTotal = $con->myQuery("SELECT COUNT(lc.id) FROM leave_conversion lc {$where} ",$bindings)->fetchColumn();
    
    echo json_encode( 
        array(
            "draw"            => isset ( $_GET['draw'] ) ?
                intval( $_GET['draw'] ) :
                0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsTotal ),
            "data"            => SSP::data_output( $columns, $data )
        )
    );