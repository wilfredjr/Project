    <?php
    require_once("../../support/config.php");

    $primaryKey = 'id';
    $index=-1;

    $columns = array(
        array( 'db' => 'pagibig','dt' => ++$index ,'formatter'=>function ($d, $row) {
            return htmlspecialchars($d);
        }),
        array( 'db' => 'employee_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
            return htmlspecialchars($d);
        }),
        array( 'db' => 'loan_name','dt' => ++$index ,'formatter'=>function ($d, $row) {
            return htmlspecialchars($d);
        }),
        array( 'db' => 'amount_paid','dt' => ++$index ,'formatter'=>function ($d, $row) {
            return number_format($d,2);
        }),
    );

    require('../../support/ssp.class.php');

    $limit = SSP::limit($_GET, $columns);
    $order = "ORDER BY e.last_name ASC";
    $bindings=array();
    $where ="";
    $whereAll=" l.loan_name LIKE " . "'%" . $_GET['loan'] . "%'" . " AND 
                DATE_FORMAT(eld.date_deducted,'%Y-%m') = " . "'" . $_GET['month_year'] . "'" ;
                // l.loan_name LIKE '%HDMF%'
    $whereResult="";
    $filter_sql="  ";

    $whereAll.=$filter_sql;

    function jp_bind($bindings)
    {
        $return_array=array();
        if (is_array($bindings)) {
            for ($i=0, $ien=count($bindings); $i<$ien; $i++) {
                $return_array[$bindings[$i]['key']]=$bindings[$i]['val'];
            }
        }

        return $return_array;
    }
     
    $where.= !empty($where) ? " AND ".$whereAll:(empty($whereAll))?"":"WHERE ".$whereAll;
    $bindings=jp_bind($bindings);
    $join=" INNER JOIN loans l ON l.loan_id = el.loan_id
            INNER JOIN employees e ON e.id=el.employee_id
            INNER JOIN emp_loans_det eld ON el.emp_loan_id = eld.emp_loan_id";
    $complete_query=" SELECT
                    el.emp_loan_id,
                    el.employee_id as id,
                    e.pagibig,
                    CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                    eld.amount_paid,
                    eld.date_deducted,
                    l.loan_name
                    FROM
                    emp_loans el
                    {$join} {$where}
                    GROUP BY el.emp_loan_id
                    {$order} {$limit} ";

    // var_dump($complete_query);
    // die();

    $data=$con->myQuery($complete_query, $bindings)->fetchAll();

    $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

    $recordsTotal=$con->myQuery("SELECT COUNT(el.emp_loan_id) FROM emp_loans el 
            INNER JOIN loans l ON l.loan_id = el.loan_id
            WHERE l.loan_name LIKE " . "'%" . $_GET['loan'] . "%'" , $bindings)->fetchColumn();

    $json['draw']=isset($request['draw']) ?intval($request['draw']) :0;
    $json['recordsTotal']=$recordsTotal;
    $json['recordsFiltered']=$recordsFiltered;
    $json['data']=SSP::data_output($columns, $data);

    // echo "<pre>";
    // print_r($recordsTotal);
    // echo "</pre>";
    // die();

    echo json_encode($json);
    die;
