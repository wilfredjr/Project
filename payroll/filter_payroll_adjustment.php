<?php
$tab=2;
require_once("../support/config.php");
if(!isLoggedIn()){
	toLogin();
	die();
}

if(!empty($_GET['id'])){
	$get_master=$con->myQuery("SELECT id, shift_id, date_from, date_to FROM employees_shift_master WHERE id = ?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
}
$cbo_emp=$con->myQuery("SELECT employees.id, CONCAT(employees.first_name,' ',employees.middle_name,' ',employees.last_name) AS emp_name FROM employees
	INNER JOIN employment_status ON employees.employment_status_id = employment_status.id
	WHERE employment_status.`name` <> 'Resigned' and  employment_status.`name` <> 'Terminated' and employees.is_deleted ='0'")->fetchAll(PDO::FETCH_ASSOC);
	?>



  	<div class="content-wrapper">
      <section class="content">
          <div class="row"><br>
              <form action="" method="" class="form-horizontal" id="frmclear">
                <label class="col-sm-3 control-label">Department *</label>
                <div class='col-sm-3'>
                    <select class='form-control cbo-department-id' name='dept_id' id='dept_id' data-placeholder="Select Department">
                    </select>
                </div>
							</div>
						</section>
					</div>
