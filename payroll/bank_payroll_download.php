<?php
	require_once("../support/config.php");
    require_once("../support/PHPExcel.php"); 
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}

    // var_dump($_POST);
    // die();

    $objPHPExcel = new PHPExcel();
    // Set properties
    $objPHPExcel->getProperties()->setCreator("SGTSI PAYROLL SYSTEM")
                                 ->setTitle("Bank Payroll Report");

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
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'BANK PAYROLL REPORT');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:D2');

    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $get_paycode=$con->myQuery("SELECT payroll_code,date_from,date_to FROM payroll WHERE id=?",array($_POST['frm_payroll_code']))->fetch(PDO::FETCH_ASSOC);

    $objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Payroll Code:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B4', htmlspecialchars($get_paycode['payroll_code']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Cut-off Date:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B5', htmlspecialchars($get_paycode['date_from']." to ".$get_paycode['date_to']));
    
    $row=6;

    $row++;
    $row++;

    $start_row=$row;

    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'EMPLOYEE CODE');
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'EMPLOYEE FULL NAME');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'CARD NUMBER');
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, 'AMOUNT');

    $table_header=$objPHPExcel->getActiveSheet()->getStyle("A".$row.":D".$row);
    $table_header->getFont()->setBold(true);
    $table_header->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
    

    $filter_sql="";
    $where="";
    $inputs=array();


    if(!empty($_POST['frm_payroll_code']))
    {
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $filter_sql.=" payroll.id=:payroll_code ";
        $inputs['payroll_code']=$_POST['frm_payroll_code'];
    }

    $where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " WHERE ".$filter_sql:"";

    $query="SELECT
                pd.id,
                payroll.id,
                pd.payroll_code, 
                e.code AS employee_code,
                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                e.card_number AS card_number,
                pd.net_pay AS amount
                FROM payroll_details pd
                INNER JOIN employees e ON e.id=pd.employee_id
                INNER JOIN payroll ON payroll.payroll_code=pd.payroll_code";

    // echo "{$query} {$where}";
    // die();

    $data_query=$con->myQuery("{$query} {$where}",$inputs);     
//    $sum= mysql_query("SELECT SUM(emp_netpay) AS totalamount FROM payroll_details");
    $row_start=$row;
    while ($data=$data_query->fetch(PDO::FETCH_ASSOC)) 
    {
        $row++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data['employee_code']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data['employee_name']);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode('0000 0000 0000 0000');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data['card_number']);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $data['amount']);
    }

    $styleArray = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':D'.$row)->applyFromArray($styleArray);

        $row++;
        $row++;
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'TOTAL');   
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, '=SUM(D'.$row_start.':D'.($row -1).')');

        $summary=$objPHPExcel->getActiveSheet()->getStyle("C".$row.":D".$row);
        $summary->getFont()->setBold(true);
        $summary->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);   

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Bank Payroll Report.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>