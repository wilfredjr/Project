<?php
ob_start();

require_once("./support/config.php");
require_once("./support/PHPExcel.php"); 



$employees=array();

if(!empty($_POST['employees_id']))
{
    if($_POST['employees_id']=='NULL' && AllowUser(array(1,4)))
    {
        $employees=$con->myQuery("SELECT id,code,CONCAT(last_name,', ',first_name,' ',middle_name) as employee FROM employees WHERE is_deleted=0 AND is_terminated=0")->fetchAll(PDO::FETCH_ASSOC);
    }else
    {
        if(AllowUser(array(1,4)))
        {
            if(is_numeric($_POST['employees_id']))
            {
                $employees=$con->myQuery("SELECT id,code,CONCAT(last_name,', ',first_name,' ',middle_name) as employee FROM employees WHERE is_deleted=0 AND is_terminated=0 AND id=?",array($_POST['employees_id']))->fetchAll(PDO::FETCH_ASSOC);
            }
        }else
        {
            $employees=$con->myQuery("SELECT id,code,CONCAT(last_name,', ',first_name,' ',middle_name) as employee FROM employees WHERE is_deleted=0 AND is_terminated=0 AND id=?",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}else
{
        if(AllowUser(array(1,4))){
			$employees=$con->myQuery("SELECT id,code,CONCAT(last_name,', ',first_name,' ',middle_name) as employee FROM employees WHERE is_deleted=0 AND is_terminated=0")->fetchAll(PDO::FETCH_ASSOC);
		}
		else{
			
        $employees=$con->myQuery("SELECT id,code,CONCAT(last_name,', ',first_name,' ',middle_name) as employee FROM employees WHERE is_deleted=0 AND is_terminated=0 AND id=?",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
		}
}
//$limit=$_POST['length'];
try 
{
    $date_start=new DateTime($_POST['date_from']);
    $date_end=new DateTime($_POST['date_to']);
    $date_end->add(new DateInterval('P1D'));
    $period = new DatePeriod(
         $date_start,
         new DateInterval('P1D'),
         $date_end
    );

    $data=array();
    $data[]=array("Employee Code","Employee Name","Date","In Time","Out Time","Overtime","Status","Note","Lates");
    $index=count($data);
    
    
    $objPHPExcel = new PHPExcel();
    // Set properties
    $objPHPExcel->getProperties()->setCreator("SGTSI HRIS")
                                 ->setTitle("Attendance Report");
    // Add some data
    $objPHPExcel->setActiveSheetIndex(0);
    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    // $objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A1');
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $row=2;
    $objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A1');
    foreach ($employees as $employee) {
        $use_ot=array();
        foreach ($period as $key => $date) {

            $data=array();
            $data[$index]['code']=$employee['code'];
            $data[$index]['employee']=$employee['employee'];
            $data[$index]['date']=PHPExcel_Shared_Date::PHPToExcel(strtotime($date->format("Y-m-d")));
            $old_time_in="";
            $day=$date->format("Y-m-d");

            $time_ins=$con->myQuery("SELECT in_time,out_time,id,note FROM `attendance` WHERE employees_id=? AND DATE(in_time)=? ORDER BY in_time ASC LIMIT 1",array($employee['id'],$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);
            $time_outs=$con->myQuery("SELECT in_time,out_time,id,note FROM `attendance` WHERE employees_id=? AND DATE(in_time)=? ORDER BY out_time DESC LIMIT 1",array($employee['id'],$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);

            $data[$index]['in_time']=!empty($time_ins['in_time'])?PHPExcel_Shared_Date::PHPToExcel(strtotime($time_ins['in_time'])):PHPExcel_Shared_Date::PHPToExcel(strtotime($day.' 00:00:00'));
            $data[$index]['out_time']=!empty($time_outs['out_time'])?PHPExcel_Shared_Date::PHPToExcel(strtotime($time_outs['out_time'])):PHPExcel_Shared_Date::PHPToExcel(strtotime($day.' 00:00:00'));
            $old_time_in=!empty($time_ins['in_time'])?$time_ins['in_time']:$day.' 00:00:00';
            $ob_date=$con->myQuery("SELECT ob_date,time_from,time_to FROM employees_ob WHERE employees_id=? AND ob_date=? AND status='Approved'",array($employee['id'],$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);

            if(!empty($ob_date))
            {
                if (date_format(date_create($time_ins['in_time']),'Y-m-d') <> date_format(date_create($ob_date['ob_date']),'Y-m-d'))
                {
                    $late=getLate($ob_date['ob_date'].' '.$ob_date['time_from']);
                    
                    $data[$index]['in_time']=PHPExcel_Shared_Date::PHPToExcel(strtotime($ob_date['ob_date'].' '.$ob_date['time_from']));
                    $data[$index]['out_time']=PHPExcel_Shared_Date::PHPToExcel(strtotime($ob_date['ob_date'].' '.$ob_date['time_to']));
                    $old_time_in=$ob_date['ob_date'].' '.$ob_date['time_from'];
                }else
                {
                    if ($time_outs['out_time'] < date_format(date_create($ob_date['time_to']),$ob_date['ob_date'].' H:m:s'))
                    {
                        $data[$index]['out_time']=PHPExcel_Shared_Date::PHPToExcel(strtotime($ob_date['ob_date'].' '.$ob_date['time_to']));
                    }
                    if ($time_ins['in_time'] > date_format(date_create($ob_date['time_from']),$ob_date['ob_date'].' H:m:s'))
                    {
                        $late=getLate($ob_date['ob_date'].' '.$ob_date['time_from']);
                        
                        $data[$index]['in_time']=PHPExcel_Shared_Date::PHPToExcel(strtotime($ob_date['ob_date'].' '.$ob_date['time_from']));
                        $old_time_in=$ob_date['ob_date'].' '.$ob_date['time_from'];
                    }
                }
            }
            else{
                $late=getLate($time_ins['in_time']);

            }

            /*
            $data[$index]['ot_in_time']=PHPExcel_Shared_Date::PHPToExcel(strtotime($day.' 00:00:00'));
            $data[$index]['ot_out_time']=PHPExcel_Shared_Date::PHPToExcel(strtotime($day.' 00:00:00'));
            $ot_date=$con->myQuery("SELECT ot_date,time_from,time_to FROM employees_ot WHERE employees_id=? AND ot_date=? AND status='Approved'",array($employee['id'],$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);
            if(!empty($ot_date))
            {   
                $data[$index]['ot_in_time']=PHPExcel_Shared_Date::PHPToExcel(strtotime($ot_date['ot_date'].' '.$ot_date['time_from']));
                $data[$index]['ot_out_time']=PHPExcel_Shared_Date::PHPToExcel(strtotime($ot_date['ot_date'].' '.$ot_date['time_to']));
                if ($ob_date['time_to'] > $ob_date['time_from'])
                {
                    $data[$index]['ot_out_time']=PHPExcel_Shared_Date::PHPToExcel( strtotime( $ot_date['time_to'].' '.date('Y-m-d', strtotime($ot_date['ot_date'] . ' +1 day')) ));
                }
            }
            */

            $leaves=$con->myQuery("SELECT id,remark,comment FROM `employees_leaves` WHERE employee_id=? AND ? BETWEEN date_start AND date_end AND status='Approved'",array($employee['id'],$date->format("Y-m-d")))->fetch(PDO::FETCH_ASSOC);
            

            $ots=$con->myQuery("SELECT id,no_hours FROM employees_ot WHERE employees_id=? AND ot_date=? AND status='Approved'".(!empty($use_ot)?" AND id NOT IN (".implode(",",$use_ot) .")":''),array($employee['id'],$date->format("Y-m-d")))->fetchAll(PDO::FETCH_ASSOC);
            $data[$index]['ot']=0;

            foreach ($ots as $key => $ot) {
                $data[$index]['ot']+=$ot['no_hours'];
                $use_ot[]=$ot['id'];
            }
            
            $obs=$con->myQuery("SELECT COUNT(id) FROM employees_ob WHERE employees_id=? AND ob_date=?  AND status='Approved'",array($employee['id'],$date->format("Y-m-d")))->fetchColumn();
            
            $weekday=$date->format("w");
            if($weekday != 0 && $weekday != 6)
            {
                $data[$index]['status']='Regular Day';
            }
            else
            {
                $data[$index]['status']='Weekend';
            } 
            
            if(!empty($leaves)){

                $data[$index]['status']=$leaves['remark']=="L"?"Leave":"Leave Without Pay";
                
                if($leaves['comment']=="AM" || $leaves['comment']=="PM"){

                    $late=getLate($old_time_in,$leaves['comment']);
                }
                else{
                    $late=getLate($old_time_in);

                }
            }
            else{
                
                $late=getLate($old_time_in);

                
            }

            if(!empty($obs)){
                $data[$index]['status']='Official Business';
            }
            //echo $date->format("Y-m-d");
            $data[$index]['note']=(!empty($time_ins['note'])?"Time in: ".$time_ins['note']:''). (!empty($time_outs['note'])?"Time out_time: ".$time_outs['note']:'');

            

            //echo $row;
            // echo $old_time_in;
            $data[$index]['lates']=$late;
            // echo "<pre>";
            // print_r($data);
            // echo "</pre>";
            // echo "A".$row;
            
             $objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A'.$row,true);
            //$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Invoice');
            $objPHPExcel->getActiveSheet()->getStyle('C'.$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$row)->getNumberFormat()->setFormatCode('hh:mm:ss');
            $objPHPExcel->getActiveSheet()->getStyle('E'.$row)->getNumberFormat()->setFormatCode('hh:mm:ss');


            //$objPHPExcel->getActiveSheet()->getStyle('F'.$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

            $index++;
            $row++;
        }
    }
  


} catch (Exception $e) {

    // echo $e;
    $data=array();
}
// echo "<pre>";
//     print_r($data);
// echo "</pre>";
// die;
//$objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A1');

// echo "<pre>";
// print_r($data);
// echo "</pre>";
// die;

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Attendance Report-'.date("Y-m-d").'.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
die;
ob_end_clean();
?>
