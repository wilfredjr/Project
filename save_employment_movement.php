<?php
	require_once("support/config.php");
	if(!isLoggedIn())
	{
	 	toLogin();
	 	die();
	}

    if(!AllowUser(array(1,4)))
    {
        redirect("index.php");
    }
		$tab=14;
	if(!empty($_POST))
	{
		//Validate form inputs
		$inputs = $_POST;
		$cat 	= array();
		$x 		= -1;

		if(empty($inputs['employee_id'])){
			Modal("Invalid Record Selected");
			redirect("employees.php");
		}

		if(empty($inputs['employment_status_id'])){
			$errors.="Invalid Employement Status <br>";
		}

		if(empty($inputs['employment_status_id'])){
			$errors.="Invalid Employement Status <br>";
		}

		if(empty($inputs['job_title_id'])){
			$errors.="Invalid Job Title <br>";
		}

		if(empty($inputs['pay_grade_id'])){
			$errors.="Invalid Employement Status <br>";
		}

		if(empty($inputs['department_id'])){
			$errors.="Invalid Employement Status <br>";
		}

		if(empty($inputs['pay_group_id'])){
			$errors.="Invalid Employement Status <br>";
		}

		try {
			$test=new DateTime($inputs['joined_date']);
			$test1=new DateTime($inputs['regularization_date']);
			$test2=new DateTime($inputs['bond_date']);
		} catch (Exception $e) {
			$errors.="Invalid Date Format <br>";
		}

		if(empty($inputs['basic_salary'])){
			$errors.="Invalid Basic Salary <br>";
		}
		else if(empty(floatval($inputs['basic_salary']))){
			$errors.="Invalid Basic Salary <br>";
		}


				if($errors!=""){

					Alert("You have the following errors: <br/>".$errors,"danger");
					if(empty($inputs['id'])){
						redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}");
					}
					else{
						redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab={$tab}&ee_id={$inputs['id']}");
					}
					die;
				}
				else{
		$employee = $con->myQuery("SELECT
										e.id,
										e.employment_status_id,
										es.name 				AS employment_status,
										e.job_title_id,
										jt.description 			AS job_title,
										e.pay_grade_id,
										pgrade.level 			AS pay_grade,
										e.department_id,
										CONCAT(d.description,' (',d.name,')') AS department,
										e.payroll_group_id,
										pg.name 				AS payroll_group,
										e.supervisor_id,
										(SELECT CONCAT(last_name,', ',first_name,' ',middle_name) FROM employees WHERE id=e.supervisor_id) AS supervisor,
										e.joined_date,
										e.regularization_date,
										basic_salary,bond_date FROM employees e
					INNER JOIN employment_status es ON es.id=e.employment_status_id
					INNER JOIN job_title jt ON jt.id=e.job_title_id
					INNER JOIN pay_grade pgrade ON pgrade.id=e.pay_grade_id
					INNER JOIN departments d ON d.id=e.department_id
					INNER JOIN payroll_groups pg ON pg.payroll_group_id=e.payroll_group_id
					WHERE e.id=?",array($inputs['employee_id']))->fetch(PDO::FETCH_ASSOC);


		if (!empty($employee['employment_status_id']))
		{

			# CHANGES IN EMPLOYMENT STATUS
				$old_employment_status_id = $employee['employment_status_id'];
				if ($old_employment_status_id <> $inputs['employment_status_id'])
				{
					$x++;
					$cat[$x]['cat'] = "Employement Status";
					$cat[$x]['old'] = $employee['employment_status'];
					$new_emp_stat 	= $con->myQuery("SELECT name FROM employment_status WHERE id=?",array($inputs['employment_status_id']))->fetch(PDO::FETCH_ASSOC);
					$cat[$x]['new'] = $new_emp_stat['name'];
				}

			#CHANGES IN JOB TITLE
				$old_job_title_id = $employee['job_title_id'];
				if ($old_job_title_id <> $inputs['job_title_id'])
				{
					$x++;
					$cat[$x]['cat'] = "Job Title";
					$cat[$x]['old'] = $employee['job_title'];
					$new_job_title 	= $con->myQuery("SELECT description AS name FROM job_title WHERE id=?",array($inputs['job_title_id']))->fetch(PDO::FETCH_ASSOC);
					$cat[$x]['new'] = $new_job_title['name'];
				}

			#CHANGES IN PAY GRADE
				$old_pay_grade = $employee['pay_grade_id'];
				if ($old_pay_grade <> $inputs['pay_grade_id'])
				{
					$x++;
					$cat[$x]['cat'] = "Pay Grade";
					$cat[$x]['old'] = $employee['pay_grade'];
					$new_p_grade 	= $con->myQuery("SELECT level AS name FROM pay_grade WHERE id=?",array($inputs['pay_grade_id']))->fetch(PDO::FETCH_ASSOC);
					$cat[$x]['new'] = $new_p_grade['name'];
				}

			#CHANGES IN DEPARTMENT
				$old_department = $employee['department_id'];
				if ($old_department <> $inputs['department_id'])
				{
					$x++;
					$cat[$x]['cat'] = "Department";
					$cat[$x]['old'] = $employee['department'];
					$new_dept 		= $con->myQuery("SELECT CONCAT(description,' (',name,')') AS name FROM departments WHERE id=?",array($inputs['department_id']))->fetch(PDO::FETCH_ASSOC);
					$cat[$x]['new'] = $new_dept['name'];
				}

			#CHANGES IN PAYROLL GROUP
				$old_payroll_group = $employee['payroll_group_id'];
				if ($old_payroll_group <> $inputs['pay_group_id'])
				{
					$x++;
					$cat[$x]['cat'] = "Payroll Group";
					$cat[$x]['old'] = $employee['payroll_group'];
					$new_pg 		= $con->myQuery("SELECT name FROM payroll_groups WHERE payroll_group_id=?",array($inputs['pay_group_id']))->fetch(PDO::FETCH_ASSOC);
					$cat[$x]['new'] = $new_pg['name'];
				}

			#CHANGES IN SUPERVISOR
				$old_supervisor = $employee['supervisor_id'];
				if (!empty($inputs['supervisor_id']))
				{
					if ($old_supervisor <> $inputs['supervisor_id'])
					{
						$x++;
						$cat[$x]['cat'] = "Supervisor";
						$cat[$x]['old'] = $employee['supervisor'];
						$new_sv 		= $con->myQuery("SELECT CONCAT(last_name,', ',first_name,' ',middle_name) AS name FROM employees WHERE id=?",array($inputs['supervisor_id']))->fetch(PDO::FETCH_ASSOC);
						$cat[$x]['new'] = $new_sv['name'];
					}
				}

			#CHANGES IN JOINED DATE
				$old_joined_date 	= $employee['joined_date'];
				$jd 				= new DateTime($inputs['joined_date']);
				$input_joined_date 	= $jd->format('Y-m-d');
				// echo $old_joined_date."<br>".$input_joined_date;
				if ($old_joined_date <> $input_joined_date)
				{
					$x++;
					$cat[$x]['cat'] = "Joined Date";
					$cat[$x]['old'] = $employee['joined_date'];
					$cat[$x]['new'] = $input_joined_date;
				}

			#CHANGES IN REGULARIZATION DATE
				$old_regdate 	= $employee['regularization_date'];
				if (!empty($inputs['regularization_date']) || $inputs['regularization_date']=='0000-00-00')
				{
					$rd 			= new DateTime($inputs['regularization_date']);
					$input_reg_date = $rd->format('Y-m-d');
					if ($old_regdate <> $input_reg_date)
					{
						$x++;
						$cat[$x]['cat'] = "Regularization Date";
						$cat[$x]['old'] = $employee['regularization_date'];
						$cat[$x]['new'] = $input_reg_date;
					}
				}

			#CHANGES IN BASIC SALARY
				$old_basic = $employee['basic_salary'];
				if ($old_basic <> $inputs['basic_salary'])
				{
					$x++;
					$cat[$x]['cat'] = "Basic Salary";
					$cat[$x]['old'] = $employee['basic_salary'];
					$cat[$x]['new'] = $inputs['basic_salary'];
				}

			#CHANGES IN BOND DATE
				$old_bond 	= $employee['bond_date'];
				if (!empty($inputs['bond_date']) || $inputs['bond_date']=='0000-00-00')
				{
					$bd 		= new DateTime($inputs['bond']);
					$input_bond = $bd->format('Y-m-d');
					if ($old_bond <> $input_bond)
					{
						$x++;
						$cat[$x]['cat'] = "Bond Date";
						$cat[$x]['old'] = $employee['bond_date'];
						$cat[$x]['new'] = $input_bond;
					}
				}
		}


	// echo "<pre>";
	// print_r($cat);
	// echo "</pre>";

		if (empty($inputs['supervisor_id']))
		{
			$inputs['supervisor_id'] = 0;
		}
		if (empty($inputs['regularization_date']))
		{
			$inputs['regularization_date'] = '0000-00-00';
		}else
		{
			$rd = new DateTime($inputs['regularization_date']);
			$inputs['regularization_date'] = $rd->format('Y-m-d');
		}
		if (empty($inputs['bond_date']))
		{
			$inputs['bond_date'] = '0000-00-00';
		}else
		{
			$bd = new DateTime($inputs['bond_date']);
			$inputs['bond_date'] = $bd->format('Y-m-d');
		}
		$jd = new DateTime($inputs['joined_date']);
		$inputs['joined_date'] = $jd->format('Y-m-d');

		$remarks = $inputs['remarks'];
		unset($inputs['remarks']);

		$con->myQuery("UPDATE employees SET employment_status_id=:employment_status_id, job_title_id=:job_title_id, pay_grade_id=:pay_grade_id, department_id=:department_id, payroll_group_id=:pay_group_id, joined_date=:joined_date, regularization_date=:regularization_date, basic_salary=:basic_salary, bond_date=:bond_date, supervisor_id=:supervisor_id WHERE id=:employee_id",$inputs);

		if (!empty($employee['employment_status_id']) || $employee['employment_status_id'] <> 0)
		{
			for($i = 0; $i < count($cat); $i++)
			{
				$params=array(
						"employee_id" 	=> $inputs['employee_id'],
						"category" 		=> $cat[$i]['cat'],
						"old_record" 	=> $cat[$i]['old'],
						"new_record" 	=> $cat[$i]['new'],
						"remarks" 		=> $remarks
					);

				$con->myQuery("INSERT INTO employees_employment_movement(employee_id, category, old_record, new_record, date_changed, remarks)
										VALUES(:employee_id, :category, :old_record, :new_record, CURDATE(), :remarks)",$params);
			}
		}


		Alert("Save succesful","success");
		redirect("frm_employee.php"."?id={$inputs['employee_id']}&tab=14");


}

		die;
	}
	else{
		redirect('index.php');
		die();
	}
	redirect('index.php');
?>
