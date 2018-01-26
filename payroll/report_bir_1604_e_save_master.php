<?php
	require_once("../support/config.php");

	if(!isLoggedIn())
    {
        toLogin();
        die();
    }


	if(!empty($_POST))
	{

		$start_date = new DateTime($_POST['input_monthyear_start']);
		$end_date 	= new DateTime($_POST['input_monthyear_end']);

		$_POST['input_monthyear_start'] = $start_date->format('m-Y');
		$_POST['input_monthyear_end'] 	= $end_date->format('m-Y');


		$data = $con->myQuery("SELECT * FROM company_profile")->fetch(PDO::FETCH_ASSOC);

		$get_month_start 	= substr($_POST['input_monthyear_start'], 0, 2);
		$get_year_start		= substr($_POST['input_monthyear_start'], -4);

		$get_month_end 		= substr($_POST['input_monthyear_end'], 0, 2);
		$get_year_end		= substr($_POST['input_monthyear_end'], -4);

		$params_1604 = array(
				"for_year" 			=> $get_year_end,
				"month_year_start" 	=> $_POST['input_monthyear_start'],
				"month_year_end" 	=> $_POST['input_monthyear_end'],
				"tin_no" 			=> $data['tin'],
				"rdo_no" 			=> $data['rdo_code'],
				"line_of_business"  => $data['line_of_business'],
				"company_name" 		=> $data['name'],
				"telephone_no" 		=> $data['contact_no'],
				"registered_add" 	=> $data['address'],
				"zip_code" 			=> $data['zip_code']
			);

		$params_sched1 	= array();
		$end 			= 12;
		$start 			= $get_month_start;
		$x 				= 0;
			
		while ($get_year_start <= $get_year_end) 
		{
			for ($i=$start; $i <= $end; $i++) 
			{
				$month_year = str_pad($i, 2, '0', STR_PAD_LEFT)."-".$get_year_start; 
				$get_1601 = $con->myQuery("SELECT id,month_year,date_processed,total_tax FROM bir_1601_e_master WHERE month_year=?",array($month_year))->fetch(PDO::FETCH_ASSOC);

				if (!empty($get_1601)) 
				{
					$params_sched1[$x] = array(
							"bir_1601_e_master_id" 	=> $get_1601['id'],
							"month" 				=> str_pad($i, 2, '0', STR_PAD_LEFT),
							"date_remittance" 		=> $get_1601['date_processed'],
							"tax_withheld" 			=> $get_1601['total_tax']
						);
					$x++;
				}
			}
			
			$get_year_start++;
			$end 	= $get_month_end;
			$start 	= 1;
		}


		# INSERT INTO
		$con->myQuery("INSERT INTO bir_1604_e_master(for_year,month_year_start,month_year_end,tin_no,rdo_no,line_of_business,company_name,telephone_no,registered_add,zip_code,date_generated,date_processed)
									VALUES(:for_year,:month_year_start,:month_year_end,:tin_no,:rdo_no,:line_of_business,:company_name,:telephone_no,:registered_add,:zip_code,CURDATE(),'0000-00-00')",$params_1604);

		$last_id = $con->lastInsertId();

		for ($i=0; $i < count($params_sched1); $i++) 
		{ 
			$params_sched1[$i]['bir_1604_e_master_id'] = $last_id;

			$con->myQuery("INSERT INTO bir_1604_e_schedule_1(bir_1604_e_master_id,bir_1601_e_master_id,month,date_remittance,tax_withheld)
													VALUES(:bir_1604_e_master_id,:bir_1601_e_master_id,:month,:date_remittance,:tax_withheld)",$params_sched1[$i]);	
		}

		Alert("Temporarily Saved!","warning");
		redirect("report_bir_1604_e_view.php?id=".$last_id);
		die();

	}else
	{
		redirect("index.php");
	} 

?>