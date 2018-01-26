<?php
    require_once("../support/config.php");
    require_once("../support/PHPExcel.php"); 
    if(!isLoggedIn())
    {
        toLogin(); 
        die();
    }

    // var_dump($_GET);
    // die();

    $objPHPExcel = new PHPExcel();
    // $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    // $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

    $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.20);
    $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.20);
    $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.20);
    $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.20);
 
     // Set properties
    $objPHPExcel->getProperties()->setCreator("SPARK GLOBAL TECH SOLUTIONS, INC")
                                 ->setTitle("Pag-ibig STLRF");

    $objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

    // $nCols = 13; //set the number of columns

    $inputFileName = 'files/STLRF.xlsx';

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);

    $company_profile=$con->myQuery("SELECT
            `name` as company_name,
            address,
            email,
            contact_no,
            website,
            foundation_day,
            fax_no,
            zip_code,
            sss_no,
            philhealth_no,
            tin,
            pagibig_no,
            rdo_code,
            line_of_business
            FROM
            company_profile ")->fetch(PDO::FETCH_ASSOC);

    // $objPHPExcel->setActiveSheetIndex(0)->mergeCells('I:M'.$row);
    $objPHPExcel->getActiveSheet()->SetCellValue('I4', $company_profile['pagibig_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('B10', $company_profile['company_name']);
    $objPHPExcel->getActiveSheet()->SetCellValue('J10', DATE('M Y', strtotime($_GET['month_year'])));
    $objPHPExcel->getActiveSheet()->SetCellValue('B13', $company_profile['address']);
    $objPHPExcel->getActiveSheet()->SetCellValue('J13', $company_profile['contact_no']);

    //TABLE

    $row=16;

    $start_row=$row;
    
    $inputs=array();
    $where="";

    $filter_sql=""; 

    $where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " AND ".$filter_sql:"";

    $query=" SELECT
                el.emp_loan_id,
                el.employee_id as id,
                e.pagibig,
                e.last_name,
                e.first_name,
                e.middle_name,
                eld.amount_paid,
                eld.date_deducted,
                l.loan_name
                FROM
                emp_loans el
                INNER JOIN loans l ON l.loan_id = el.loan_id
                INNER JOIN employees e ON e.id=el.employee_id
                INNER JOIN emp_loans_det eld ON el.emp_loan_id = eld.emp_loan_id
                WHERE l.loan_name LIKE " . "'%" . $_GET['loan'] . "%'" . " AND 
                DATE_FORMAT(eld.date_deducted,'%Y-%m') = " . "'" . $_GET['month_year'] . "'" . "
                GROUP BY el.emp_loan_id ";

    $data_query=$con->myQuery("{$query} {$where} ORDER BY e.last_name ASC",$inputs);

    // echo "<pre>";
    // print_r($total);
    // echo "</pre>";
    // die();   

    while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) 
    {
        $row++;
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B'.$row.':C'.$row);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['pagibig']);
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['last_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $data['first_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $data['middle_name']);
        if (stripos($data['loan_name'], 'Multi-Purpose') !== FALSE){ 
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, 'MPL'); } 
        elseif (stripos($data['loan_name'], 'Calamity') !== FALSE) {
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, 'CL'); } 
        else { $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, ''); }
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('J'.$row.':K'.$row);
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['amount_paid']);
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L'.$row.':M'.$row);
        $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);
    }

    // echo "<pre>";
    // print_r($total);
    // echo "</pre>";
    // die();   

    // $row++;
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells("B".($row+1).":M".($row+1));   
    $objPHPExcel->getActiveSheet()->SetCellValue("B".($row+1), '-- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- --');
    $objPHPExcel->getActiveSheet()->getStyle('B'.($row+1))->getFont()->setItalic(true);
    $objPHPExcel->getActiveSheet()->getStyle('B'.($row+1))->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $total=$con->myQuery("SELECT SUM(eld.amount_paid) AS total
                FROM emp_loans el
                INNER JOIN loans l ON l.loan_id = el.loan_id
                INNER JOIN employees e ON e.id=el.employee_id
                INNER JOIN emp_loans_det eld ON el.emp_loan_id = eld.emp_loan_id
                 WHERE l.loan_name LIKE " . "'%" . $_GET['loan'] . "%'" . " AND 
                DATE_FORMAT(eld.date_deducted,'%Y-%m') = " . "'" . $_GET['month_year'] . "'")->fetch(PDO::FETCH_ASSOC);

    $objPHPExcel->getActiveSheet()->SetCellValue("K".($row+3), $total['total']);
    $objPHPExcel->getActiveSheet()->SetCellValue("K".($row+4), $total['total']);
    

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="HDMF STLRF.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>