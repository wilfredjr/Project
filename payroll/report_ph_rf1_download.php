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
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(12);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

    $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.20);
    $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.20);
    $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.20);
    $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.20);
 
     // Set properties
    $objPHPExcel->getProperties()->setCreator("SPARK GLOBAL TECH SOLUTIONS, INC")
                                 ->setTitle("Philhealth RF1 Form");

    $objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

    // $nCols = 13; //set the number of columns

    $inputFileName = 'files/RF-1.xlsx';

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
    $objPHPExcel->getActiveSheet()->SetCellValue('C6', $company_profile['philhealth_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('C7', $company_profile['tin']);
    $objPHPExcel->getActiveSheet()->SetCellValue('D8', $company_profile['company_name']);
    $objPHPExcel->getActiveSheet()->SetCellValue('D9', $company_profile['address']);
    $objPHPExcel->getActiveSheet()->SetCellValue('D11', $company_profile['contact_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('F11', $company_profile['email']);
    $objPHPExcel->getActiveSheet()->SetCellValue('M9', DATE('M Y', strtotime($_GET['month_year'])));

    //TABLE

    $row=13;

    $start_row=$row;
    
    $inputs=array();
    $where=" ";
    $filter_sql=""; 

    // $where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " AND ".$filter_sql:"";

    $query=" SELECT 
                pg.employee_id,
                e.philhealth,
                e.last_name,
                e.first_name,
                e.middle_name,
                e.birthday,
                e.gender,
                pg.govde_code,
                SUM(pg.govde_eeshare) as govde_eeshare,
                SUM(pg.govde_ershare) as govde_ershare
                FROM
                payroll_govde pg
                INNER JOIN employees e ON e.id = pg.employee_id
                INNER JOIN payroll p ON p.payroll_code = pg.payroll_code
                WHERE pg.gov_desc = 'PhilHealth' AND
                DATE_FORMAT(p.date_to,'%Y-%m') = " . "'" . $_GET['month_year'] . "'" . "
                GROUP BY pg.employee_id
                ORDER BY e.last_name ASC ";

    $data_query=$con->myQuery("{$query} {$where}",$inputs);  



    while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) 
    { 
        $date = date_create($data['birthday']);
        $date_of_birth = date_format($date,'m/d/Y');

        $row++;
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$row.':C'.$row);
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['philhealth']);
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['last_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['first_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $data['middle_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $date_of_birth);
        if ($data['gender'] == 'Male'){ 
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, 'M'); }
        else {
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, 'F'); }
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $data['govde_code']);
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $data['govde_eeshare']);
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $data['govde_ershare']);
        $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 1, 1);
    }

    $objPHPExcel->setActiveSheetIndex(0)->mergeCells("A".($row+1).":N".($row+1));   
    $objPHPExcel->getActiveSheet()->SetCellValue("A".($row+1), '-- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- -- Nothing Follows -- --');
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getFont()->setItalic(true);
    $objPHPExcel->getActiveSheet()->getStyle('A'.($row+1))->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $recordsFiltered=$con->myQuery("SELECT FOUND_ROWS();")->fetchColumn();

    $total=$con->myQuery("SELECT SUM(pg.govde_eeshare) as ps_total, 
                SUM(pg.govde_ershare) as es_total,
                SUM(pg.govde_eeshare+pg.govde_ershare) as grand_total
                FROM payroll_govde pg
                INNER JOIN payroll p ON p.payroll_code = pg.payroll_code
                WHERE pg.gov_desc = 'PhilHealth' AND
                DATE_FORMAT(p.date_to,'%Y-%m') = " . "'" . $_GET['month_year'] . "'")->fetch(PDO::FETCH_ASSOC);

    $objPHPExcel->getActiveSheet()->SetCellValue("A".($row+3), $recordsFiltered);

    $objPHPExcel->getActiveSheet()->SetCellValue("B".($row+5), DATE('M Y', strtotime($_GET['month_year'])));
    $objPHPExcel->getActiveSheet()->SetCellValue("D".($row+5), $total['grand_total']);
    $objPHPExcel->getActiveSheet()->SetCellValue("G".($row+5), $recordsFiltered);

    // var_dump($recordsFiltered);
    // die;

    $objPHPExcel->getActiveSheet()->SetCellValue("K".($row+3), $total['ps_total']);
    $objPHPExcel->getActiveSheet()->SetCellValue("L".($row+3), $total['es_total']);
    $objPHPExcel->getActiveSheet()->SetCellValue("K".($row+4), $total['grand_total']);

    $objPHPExcel->getActiveSheet()->SetCellValue("K".($row+5), $total['ps_total']);
    $objPHPExcel->getActiveSheet()->SetCellValue("L".($row+5), $total['es_total']);
    $objPHPExcel->getActiveSheet()->SetCellValue("K".($row+6), $total['grand_total']);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Philhealth RF1.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>