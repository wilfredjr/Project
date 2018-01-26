<?php
	require_once("../support/config.php");

	if(!isLoggedIn())
    {
        toLogin();
        die();
    }

   // var_dump($_POST);
   // die();
	if(!empty($_POST))
	{
		$data = $con->myQuery("SELECT * FROM company_profile")->fetch(PDO::FETCH_ASSOC);

		$params = array(
				"month_year" 		=> htmlspecialchars($_POST['input_month']."-".$_POST['input_year']),
				"tin_no" 			=> $data['tin'],
				"rdo_no"			=> $data['rdo_code'],
				"line_of_business"  => $data['line_of_business'],
				"company_name" 		=> $data['name'],
				"telephone_no" 		=> $data['contact_no'],
				"registered_add" 	=> $data['address'],
				"zip_code" 			=> $data['zip_code'],
				"category" 			=> 1				
			);

		$con->myQuery("INSERT INTO bir_1601_e_master(month_year,tin_no,rdo_no,line_of_business,company_name,telephone_no,registered_add,zip_code,category,date_generated,date_processed)
									VALUES(:month_year,:tin_no,:rdo_no,:line_of_business,:company_name,:telephone_no,:registered_add,:zip_code,:category,CURDATE(),'0000-00-00')",$params);

		$last_id = $con->lastInsertId();

		Alert("Temporarily Saved!","warning");
		redirect("report_bir_1601_e_view.php?id=".$last_id);
		die();

	}else
	{
		redirect("index.php");
	} 

?>