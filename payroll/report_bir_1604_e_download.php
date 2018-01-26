<?php
    require_once("../support/config.php");
    require_once("../support/PHPExcel.php"); 

    if(!isLoggedIn())
    {
        toLogin(); 
        die();
    }

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(12);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

    $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.20);
    $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.20);
    $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.20);
    $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.20);


    $objPHPExcel->getProperties()->setCreator("SECRET 6")->setTitle("BIR 1604-E");

    $objPHPExcel->setActiveSheetIndex(0);
    
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

    $inputFileName = 'files/SECRET_6 BIR 1604-E.xlsx';

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);

    $data = $con->myQuery("SELECT id,
                                for_year,
                                tin_no,
                                rdo_no,
                                line_of_business,
                                company_name,
                                telephone_no,
                                registered_add,
                                zip_code,
                                total_tax_withheld,
                                date_generated,
                                date_processed
                            FROM bir_1604_e_master WHERE is_deleted = 0 AND id =?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

    $tin1 = substr($data['tin_no'], 0,3);
    $tin2 = substr($data['tin_no'], 3,3);
    $tin3 = substr($data['tin_no'], 6,3);
    $tin4 = '0000';


    $objPHPExcel->getActiveSheet()->SetCellValue('H13',$data['for_year']);
    $objPHPExcel->getActiveSheet()->SetCellValue('AL13','2');
    $objPHPExcel->getActiveSheet()->SetCellValue('C18',htmlspecialchars($tin1));
    $objPHPExcel->getActiveSheet()->SetCellValue('G18',htmlspecialchars($tin2));
    $objPHPExcel->getActiveSheet()->SetCellValue('K18',htmlspecialchars($tin3));
    $objPHPExcel->getActiveSheet()->SetCellValue('P18',htmlspecialchars($tin4));
    $objPHPExcel->getActiveSheet()->SetCellValue('X18',$data['rdo_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('AH18',$data['line_of_business']);
    $objPHPExcel->getActiveSheet()->SetCellValue('C23',$data['company_name']);
    $objPHPExcel->getActiveSheet()->SetCellValue('AI23',$data['telephone_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('C27',$data['registered_add']);
    $objPHPExcel->getActiveSheet()->SetCellValue('AJ27',$data['zip_code']);
    $objPHPExcel->getActiveSheet()->SetCellValue('M30','x');

    $cell = 37;
    $total_amount_remitted = 0;
    $tax_withheld = 0;
    $penalties = 0;

    for($i=0; $i<12; $i++):
        $data_details = $con->myQuery("SELECT id, bir_1604_e_master_id, bir_1601_e_master_id, month, DATE_FORMAT(date_remittance,'%M %d, %Y') AS date_remittance, ror_details, tax_withheld, penalties, total_amount_remitted FROM bir_1604_e_schedule_1 WHERE bir_1604_e_master_id=? AND month=?",array($_GET['id'],str_pad($i+1, 2, '0', STR_PAD_LEFT)))->fetch(PDO::FETCH_ASSOC);
        
        if(!empty($data_details)):
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$cell,$data_details['date_remittance']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$cell,$data_details['ror_details']);
            $objPHPExcel->getActiveSheet()->SetCellValue('R'.$cell,$data_details['tax_withheld']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$cell,$data_details['penalties']);
            $objPHPExcel->getActiveSheet()->SetCellValue('AG'.$cell,$data_details['total_amount_remitted']);

            $tax_withheld = $tax_withheld + floatval($data_details['tax_withheld']);
            $penalties = $penalties + floatval($data_details['penalties']);
            $total_amount_remitted = $total_amount_remitted + floatval($data_details['total_amount_remitted']);
        endif;
        
        $cell++;
    endfor;

    $objPHPExcel->getActiveSheet()->SetCellValue('R49',$tax_withheld);
    $objPHPExcel->getActiveSheet()->SetCellValue('Z49',$penalties);
    $objPHPExcel->getActiveSheet()->SetCellValue('AG49',$total_amount_remitted);
    


    $data = $con->myQuery("SELECT * FROM bir_1604_e_schedule_4 WHERE bir_1604_e_master_id = ?",array($_GET['id']));

    $cell = 99;
    $x = 1;
    $totat_tax_sched_4 = 0;
    while($row = $data->fetch(PDO::FETCH_ASSOC)):
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$cell,$x);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$cell,$row['tin_tax_payer']);
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$cell,$row['name_payees']);
        $objPHPExcel->getActiveSheet()->SetCellValue('P'.$cell,$row['atc']);
        $objPHPExcel->getActiveSheet()->SetCellValue('S'.$cell,$row['nature_of_income_payment']);
        $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$cell,$row['tax_base']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AF'.$cell,$row['tax_rate']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AI'.$cell,$row['tax_withheld']);

        $totat_tax_sched_4 = $totat_tax_sched_4 + floatval($row['tax_withheld']);

        $x++;
        $cell++;
    endwhile;

    $objPHPExcel->getActiveSheet()->SetCellValue('AI132',$totat_tax_sched_4);


    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="BIR_1604_E.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');

    die;
?>