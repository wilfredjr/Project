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
        array('db' => 'transaction_number','dt' => ++$index,'formatter' => function( $d, $row ) {
                return htmlspecialchars($d);
        }),
        array( 'db' => 'payroll_group','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'date_start','dt' => ++$index ,'formatter'=>function($d,$row){
            return htmlspecialchars($d);
        }),
        array( 'db' => 'date_end','dt' => ++$index ,'formatter'=>function($d,$row){
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
            $action_buttons.="<a class='btn btn-flat btn-sm btn-default' title='View Details' href='13th_month_view.php?id={$d}'><span class='fa fa-search'></span></a>";
            if($row['is_processed']==0)
            {
                $action_buttons.="&nbsp;<form method='post' action='delete_13th_month.php?id={$d}' onsubmit='return confirm(\"Are you sure you want to delete this transaction?\")' style='display:inline'><input type='hidden' name='id' value='{$d}'><button class='btn btn-sm btn-danger btn-flat'  title='Delete Transaction' type='submit'><span  class='fa fa-close'></span></button></form>";
            }
            return $action_buttons;
        }),
        array( 'db' => 'date_processed','dt' => ++$index ,'formatter'=>function ($d, $row) {
            return "";
        })   
    );

    require('../../support/ssp.class.php');

    

    $bindings = array();
    
    $limit = SSP::limit( $_GET, $columns );
    $order = SSP::order( $_GET, $columns );
    $where = SSP::filter( $_GET, $columns, $bindings );
    
    $whereAll="";
    $whereResult="";
    $filter_sql="";

    $filter_sql.=" tm.is_deleted=0 ";

    if(!empty($_GET['pay_code_filter']))
    {
        $pay_code_filter = " tm.id=:pay_code_filter ";    
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $bindings[]=array('key'=>'pay_code_filter','val'=>$_GET['pay_code_filter'],'type'=>0);
        $filter_sql.=$pay_code_filter;
    }
    if(!empty($_GET['pay_group_filter']))
    {
        $pay_group_filter = " tm.payroll_group_id=:pay_group_filter ";    
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
            $status_filter = " tm.is_processed=:status_filter ";    
            $bindings[]=array('key'=>'status_filter','val'=>$_GET['status_filter'],'type'=>0);
        }else
        {
            $status_filter = " ( isnull(tm.is_processed) || tm.is_processed <> 1 ) ";    
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
        $date_filter .= " tm.date_generated = :date_generated_filter";
        $bindings[]  = array('key'=>'date_generated_filter','val'=>date_format($date_generated_filter,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    if(!empty($_GET['date_start_filter']))
    {
        $date_start_filter=date_create($_GET['date_start_filter']);
    }else
    {
        $date_start_filter="";
    }
    if(!empty($_GET['date_end_filter']))
    {
        $date_end_filter=date_create($_GET['date_end_filter']);
    }else
    {
        $date_end_filter="";
    }

    $date_filter="";
    if(!empty($date_start_filter))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" tm.date_start >= :date_start_filter";
        $bindings[]=array('key'=>'date_start_filter','val'=>date_format($date_start_filter,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    $date_filter="";
    if(!empty($date_end_filter))
    {
        $date_filter.=!empty($filter_sql)?" AND ":"";
        $date_filter.=" tm.date_end <= :date_end_filter";
        $bindings[]=array('key'=>'date_end_filter','val'=>date_format($date_end_filter,'Y-m-d'),'type'=>0);
    }
    $filter_sql.=$date_filter;

    
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
    $query="SELECT
                tm.id,
                tm.transaction_number,
                tm.payroll_group_id,
                pg.name AS payroll_group,
                tm.date_start,
                tm.date_end,
                tm.date_generated,
                tm.date_processed,
                tm.is_processed
            FROM 13th_month tm
            INNER JOIN payroll_groups pg ON pg.payroll_group_id=tm.payroll_group_id {$where} {$order} {$limit}";

  

    $data=$con->myQuery($query,$bindings)->fetchAll();

    $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();
    $recordsTotal = $con->myQuery("SELECT COUNT(id) FROM 13th_month tm {$where}",$bindings)->fetchColumn();

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