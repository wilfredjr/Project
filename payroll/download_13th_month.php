<?php
	require_once("../support/config.php");
    require_once("../support/PHPExcel.php"); 
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}

    $objPHPExcel = new PHPExcel();
    
    $objPHPExcel->getProperties()->setCreator("SGTSI PAYROLL SYSTEM")
                                 ->setTitle("13th MONTH Report");

    $objPHPExcel->setActiveSheetIndex(0);    
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    $nCols = 9;

    foreach (range(0, $nCols) as $col) 
    {
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);                
    }

    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'SPARK GLOBAL TECH SOLUTIONS, INC');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:C1');
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );

    $objPHPExcel->getActiveSheet()->SetCellValue('A2', '13th MONTH REPORT');
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:C2');

    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    );


    $data=$con->myQuery("SELECT id,transaction_number,13th_month.payroll_group_id,payroll_groups.name AS payroll_group,date_start,date_end,date_generated,date_processed FROM 13th_month INNER JOIN payroll_groups ON payroll_groups.payroll_group_id=13th_month.payroll_group_id  WHERE id=?",array($_GET['p_id']))->fetch(PDO::FETCH_ASSOC);
    
    $objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Transaction Number:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B4', htmlspecialchars($data['transaction_number']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Payroll Group:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B5', htmlspecialchars($data['payroll_group']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A6', 'Cut-off Date:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B6', htmlspecialchars($data['date_start']." to ".$data['date_end']));
    $objPHPExcel->getActiveSheet()->SetCellValue('A7', 'Date Processed:');
    $objPHPExcel->getActiveSheet()->SetCellValue('B7', htmlspecialchars($data['date_processed']));
    
    $row=7;
    $row++;
    $row++;
    $start_row=$row;

    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 'EMPLOYEE CODE');
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, 'EMPLOYEE FULL NAME');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, 'AMOUNT');

    $table_header=$objPHPExcel->getActiveSheet()->getStyle("A".$row.":C".$row);
    $table_header->getFont()->setBold(true);
    $table_header->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   
    
    $filter_sql="";
    $where="";
    $inputs=array();

    if(!empty($_GET['p_id']))
    {
        if(!empty($filter_sql))
        {
            $filter_sql.=" AND ";
        }
        $filter_sql.=" 13th_month_id=:thmonth ";
        $inputs['thmonth']=$_GET['p_id'];
    }

    $where.= !empty($where)?" AND ".$filter_sql:!empty($filter_sql)? " WHERE ".$filter_sql:"";

    $query="SELECT
                md.id,
                md.13th_month_id,
                md.employee_id,
                e.code AS employee_code,
                CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) AS employee_name,
                md.amount,
                m.is_processed
            FROM 13th_month_details md
            INNER JOIN 13th_month m ON m.id=md.13th_month_id
            INNER JOIN employees e ON e.id=md.employee_id";

    $data_query=$con->myQuery("{$query} {$where}",$inputs);     

    while ($data2=$data_query->fetch(PDO::FETCH_ASSOC)) 
    {
        $row++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $data2['employee_code']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $data2['employee_name']);

        $adjust=$con->myQuery("SELECT id,adjustment_type,amount,remarks FROM 13th_month_adjust WHERE 13th_month_details_id=? AND is_deleted=0",array($data2['id']))->fetchAll(PDO::FETCH_ASSOC);
        $count_adjust=count($adjust);
     
        for ($j=0; $j < $count_adjust; $j++) 
        {
            if ($adjust[$j]['adjustment_type']==1) 
            {
                $data2['amount']=$data2['amount']+$adjust[$j]['amount'];
            }else
            {
                $data2['amount']=$data2['amount']-$adjust[$j]['amount'];
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $data2['amount']);
    }

    $styleArray = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A'.$start_row.':C'.$row)->applyFromArray($styleArray);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="13th Month Report.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>