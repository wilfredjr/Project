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
    // Set properties
    $objPHPExcel->getProperties()->setCreator("SGTSI PAYROLL SYSTEM")
                                 ->setTitle("Government Deduction Report");

    $objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    // $objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A1');
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $nCols = 9; //set the number of columns

    foreach (range(0, $nCols) as $col) 
    {
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);                
    }

    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'SPARK GLOBAL TECH SOLUTIONS, INC');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:E1');
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'GOVERNMENT DEDUCTION REPORT');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:E2');

    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Type:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B4', htmlspecialchars($_GET['frm_govde']));

    $get_paycode=$con->myQuery("SELECT payroll_code,date_from,date_to FROM payroll WHERE id=?",array($_GET['frm_payroll_code']))->fetch(PDO::FETCH_ASSOC);

    $objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Payroll Code:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B5', htmlspecialchars($get_paycode['payroll_code']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'Cut-off Date:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B6', htmlspecialchars($get_paycode['date_from']." to ".$get_paycode['date_to']));
    
    $row=6;

    $row++;
    $row++;

    $start_row=$row;

    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'EMPLOYEE CODE');
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'EMPLOYEE FULL NAME');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'DEDUCTION CODE');
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, 'EMPLOYEE SHARE');
    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, 'EMPLOYER SHARE');

    $table_header=$objPHPExcel->getActiveSheet()->getStyle("A".$row.":E".$row);
    $table_header->getFont()->setBold(true);
    $table_header->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
    

    $filter_sql="";
    $where="";
    $inputs=array();

    if(!empty($_GET['frm_govde']))
    {
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $filter_sql.=" gov_desc=:govde ";
        $inputs['govde']=$_GET['frm_govde'];
    }
    if(!empty($_GET['frm_payroll_code']))
    {
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $filter_sql.=" payroll.id=:payroll_code ";
        $inputs['payroll_code']=$_GET['frm_payroll_code'];
    }

    $where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " WHERE ".$filter_sql:"";

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

    // echo "{$query} {$where}";
    // die();

    $data_query=$con->myQuery("{$query} {$where}",$inputs);     

    while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) 
    {
        $row++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['employee_code']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['employee_name']);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['govde_code']);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['employee_share']);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $data['employer_share']);
    }

    $styleArray = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':E'.$row)->applyFromArray($styleArray);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Government Deduction Report.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>