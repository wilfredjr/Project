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


    $objPHPExcel->getProperties()->setCreator("SECRET 6")->setTitle("BIR 1601-E");

    $objPHPExcel->setActiveSheetIndex(0);
    
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');

    $inputFileName = 'files/SECRET_6 BIR 1601-E.xlsx';

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);

    $data = $con->myQuery("SELECT id,
                                month_year,
                                tin_no,
                                rdo_no,
                                line_of_business,
                                company_name,
                                telephone_no,
                                registered_add,
                                zip_code,
                                total_tax,
                                date_generated,
                                date_processed
                            FROM bir_1601_e_master WHERE is_deleted = 0 AND id =?",array($_POST['master_id']))->fetch(PDO::FETCH_ASSOC);

    $month  = substr($data['month_year'], 0, 2);
    $year   = substr($data['month_year'], -4);

    $tin1 = substr($data['tin_no'], 0,3);
    $tin2 = substr($data['tin_no'], 3,3);
    $tin3 = substr($data['tin_no'], 6,3);
    $tin4 = '0000';

    $objPHPExcel->getActiveSheet()->SetCellValue('E12',$month);
    $objPHPExcel->getActiveSheet()->SetCellValue('H12',$year);
    $objPHPExcel->getActiveSheet()->SetCellValue('T13','1');
    $objPHPExcel->getActiveSheet()->SetCellValue('C17',$tin1);
    $objPHPExcel->getActiveSheet()->SetCellValue('E17',$tin2);
    $objPHPExcel->getActiveSheet()->SetCellValue('I17',$tin3);
    $objPHPExcel->getActiveSheet()->SetCellValue('M17',$tin4);
    $objPHPExcel->getActiveSheet()->SetCellValue('R17',$data['rdo_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('Z17',$data['line_of_business']);
    $objPHPExcel->getActiveSheet()->SetCellValue('C22',$data['company_name']);
    $objPHPExcel->getActiveSheet()->SetCellValue('AA22',$data['telephone_no']);
    $objPHPExcel->getActiveSheet()->SetCellValue('C26',$data['registered_add']);
    $objPHPExcel->getActiveSheet()->SetCellValue('AB26',$data['zip_code']);
    $objPHPExcel->getActiveSheet()->SetCellValue('B30','x');


    $data_details = $con->myQuery("SELECT id,nature_of_business,atc_code,tax_base,tax_rate,tax_withheld FROM bir_1601_e_details WHERE is_deleted=0 AND bir_1601_e_master_id=?",array($_POST['master_id']));

    $cell = 35;
    while($row = $data_details->fetch(PDO::FETCH_ASSOC)):
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$cell,$row['nature_of_business']);
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$cell,$row['atc_code']);
        $objPHPExcel->getActiveSheet()->SetCellValue('P'.$cell,$row['tax_base']);
        $objPHPExcel->getActiveSheet()->SetCellValue('W'.$cell,$row['tax_rate']);
        $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$cell,$row['tax_withheld']);

        $cell++;
    endwhile;

    $objPHPExcel->getActiveSheet()->SetCellValue('Y57',$data['total_tax']);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="BIR_1601_E.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    die;
?>