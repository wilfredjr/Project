<?php
require_once '../support/config.php';

if(!isLoggedIn()){
	toLogin();
	die();
}


if(!empty($_POST)){
		//Validate form inputs

	$inputs=$_POST;
	$errors="";

	// echo '<pre>';
	// print_r($inputs);
	// echo '</pre>';
	// die;

	unset($inputs['example_length']);
	unset($inputs['select_all']);
	
	if(empty($inputs['shifting_id']) && empty($inputs['ushifting_id'])){
		$con->beginTransaction();	

		unset($inputs['shifting_id']);

		$shift = $inputs['shift'];
		$date = new DateTime();
		$date_f=new DateTime($inputs['dt_from']);
		$date_t=new DateTime($inputs['dt_to']);
		$date_from=$date_f->format("Y-m-d");
		$date_to=$date_t->format("Y-m-d");
		$date_applied=date_format($date, 'Y-m-d');

		$params=array(
			'shift'=>$shift,
			'date_from'=>$date_from,
			'date_to'=>$date_to,
			'date_applied'=>$date_applied
			);


		$con->myQuery("INSERT INTO employees_shift_master (shift_id,date_from,date_to,date_applied) VALUES (:shift,:date_from,:date_to,:date_applied)",$params);

		$array_count = count($inputs['emp_id']);

		if(!empty($array_count)){
			$employee_shift_master_id = $con->lastInsertId();

			for ($i=0; $i < $array_count; $i++) { 
				$param=array(
					"employee_shift_master_id"=>$employee_shift_master_id,
					"employee_id"=>$inputs['emp_id'][$i]
					);
				$con->myQuery("INSERT INTO employees_shift_details (employee_shift_master_id,employee_id) VALUES (:employee_shift_master_id,:employee_id)",$param);
			}

		}else{
			Alert("Please select employee","danger");
			redirect("../payroll/frm_shifting_sched.php");
			die;
		}

		
		$con->commit();	
		Alert("Shifting of employee successfully created","success");

		redirect("../payroll/view_shifting_sched.php");
		die;

	}else{
		if(empty($inputs['for_u'])){
			$con->beginTransaction();	

			$shift = $inputs['shift'];
			$date = new DateTime();
			$date_f=new DateTime($inputs['dt_from']);
			$date_t=new DateTime($inputs['dt_to']);
			$date_from=$date_f->format("Y-m-d");
			$date_to=$date_t->format("Y-m-d");
			$id = $inputs['shifting_id'];

			$params=array(
				'shift'=>$shift,
				'date_from'=>$date_from,
				'date_to'=>$date_to,
				'id'=>$id
				);

			// echo '<pre>';
			// print_r($params);
			// echo '</pre>';
			// die;

			$con->myQuery("UPDATE employees_shift_master SET shift_id=:shift ,date_from=:date_from ,date_to=:date_to WHERE id=:id ",$params);

			$array_count = count($inputs['emp_id']);

			$employee_shift_master_id = $id;

			for ($i=0; $i < $array_count; $i++) { 
				$param=array(
					"employee_shift_master_id"=>$employee_shift_master_id,
					"employee_id"=>$inputs['emp_id'][$i]
					);
				$con->myQuery("INSERT INTO employees_shift_details (employee_shift_master_id,employee_id) VALUES (:employee_shift_master_id,:employee_id)",$param);
			}

			$con->commit();	
			Alert("Shifting of employee successfully updated","success");

			redirect("../payroll/view_shifting_sched.php");
			die;
		}else{
			$con->beginTransaction();	

			$shifting_id = $inputs['ushifting_id'];
			$shift = $inputs['ushift'];
			$date = new DateTime();
			$date_f=new DateTime($inputs['udt_from']);
			$date_t=new DateTime($inputs['udt_to']);
			$date_from=$date_f->format("Y-m-d");
			$date_to=$date_t->format("Y-m-d");
			$date_applied=date_format($date, 'Y-m-d');

			$params=array(
				'shift'=>$shift,
				'date_from'=>$date_from,
				'date_to'=>$date_to,
				'date_applied'=>$date_applied
				);

			$con->myQuery("INSERT INTO employees_shift_master (shift_id,date_from,date_to,date_applied) VALUES (:shift,:date_from,:date_to,:date_applied)",$params);

			$employee_shift_master_id = $con->lastInsertId();
			$employee_id = $inputs['uemp_id'];

			$con->myQuery("INSERT INTO employees_shift_details (employee_shift_master_id,employee_id) VALUES ($employee_shift_master_id,$employee_id)");

			$thisupadte=$con->myQuery("UPDATE employees_shift_details SET is_deleted=1 WHERE employee_shift_master_id={$shifting_id} AND employee_id={$employee_id}");

			$con->commit();	
			Alert("Shifting of employee successfully created","success");

			redirect("../payroll/frm_shifting_sched.php?id=".$shifting_id);
			die;
		}	
	}	
}


?>