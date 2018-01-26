<?php
    $tab=14;

    $employment_status     = $con->myQuery("SELECT id,name FROM employment_status WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
    $job_titles            = $con->myQuery("SELECT id,description FROM job_title WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
    $pay_grades            = $con->myQuery("SELECT id,level FROM pay_grade WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
    $departments           = $con->myQuery("SELECT id,name FROM departments WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
    $payroll_group         = $con->myQuery("SELECT payroll_group_id AS id,name FROM payroll_groups WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);
    if(!empty($employee))
    {
        $employees=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name) FROM employees WHERE is_deleted=0 AND is_terminated=0 AND id <> ?",array($employee['id']))->fetchAll(PDO::FETCH_ASSOC);
    }else
    {
        $employees=$con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name) FROM employees WHERE is_deleted=0 AND is_terminated=0")->fetchAll(PDO::FETCH_ASSOC);
    }

    $data = $con->myQuery("SELECT 
                                eem.id,
                                eem.employee_id,
                                eem.category,
                                eem.old_record,
                                eem.new_record,
                                eem.date_changed,
                                eem.remarks
                            FROM employees_employment_movement eem
                            INNER JOIN employees e ON e.id=eem.employee_id
                            WHERE eem.employee_id=?",array($employee['id']));

?>

<?php
    $has_error=FALSE;
    if(!empty($_SESSION[WEBAPP]['Alert']) && $_SESSION[WEBAPP]['Alert']['Type']=="danger")
    {
        $has_error=TRUE;
    }
    Alert();
?>

<div class='text-right'>
    <button class='btn btn-warning' data-toggle="collapse" data-target="#collapseForm" aria-expanded="false" aria-controls="collapseForm">Toggle Form </button>
    <button class='btn btn-warning' id="btn_update" data-id="<?php echo $employee['id']; ?>" onclick="show_modal(this)">Update Details </button>
</div>
<br/>
<div id='collapseForm' class='collapse'>
    <form class='form-horizontal' action='save_employment_movement.php' method="POST" onsubmit="return confirm('Do you want to save the changes in employee information?')">
        <input type='hidden' name='employee_id' value='<?php echo !empty($employee)?$employee['id']:''; ?>'>
     
        <div class="form-group">
            <label for="employment_status_id" class="col-md-3 control-label">Employment Status *</label>
            <div class="col-md-7">
                <select name='employment_status_id' class='form-control cbo' data-placeholder="Select Employment Status" <?php echo !(empty($employee))?"data-selected='".$employee['employment_status_id']."'":NULL ?> style='width:100%' required>
                    <?php
                        echo makeOptions($employment_status);
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="job_title_id" class="col-md-3 control-label">Job Title *</label>
            <div class="col-md-7">
                <select name='job_title_id' class='form-control cbo' data-placeholder="Select Job Title " <?php echo !(empty($employee))?"data-selected='".$employee['job_title_id']."'":NULL ?> style='width:100%' required>
                    <?php
                        echo makeOptions($job_titles);
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="pay_grade_id" class="col-md-3 control-label">Pay Grade *</label>
            <div class="col-md-7">
                <select name='pay_grade_id' class='form-control cbo' data-placeholder="Select Pay Grade " <?php echo !(empty($employee))?"data-selected='".$employee['pay_grade_id']."'":NULL ?> style='width:100%'  required>
                    <?php
                        echo makeOptions($pay_grades);
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="department_id" class="col-md-3 control-label">Department *</label>
            <div class="col-md-7">
                <select name='department_id' class='form-control cbo' data-placeholder="Select Department " <?php echo !(empty($employee))?"data-selected='".$employee['department_id']."'":NULL ?> style='width:100%' required>
                    <?php
                        echo makeOptions($departments);
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-3 control-label">Pay Group *</label>
            <div class="col-md-7">
                <select name='pay_group_id' class='form-control cbo' data-placeholder="Select Pay Group " <?php echo !(empty($employee))?"data-selected='".$employee['payroll_group_id']."'":NULL ?> style='width:100%' required>
                    <?php
                        echo makeOptions($payroll_group);
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="supervisor_id" class="col-md-3 control-label">Supervisor </label>
            <div class="col-md-7">
                <select name='supervisor_id' id='supervisor_id' class='form-control cbo' data-placeholder="Select Supervisor " <?php echo !(empty($employee))?"data-selected='".$employee['supervisor_id']."'":NULL ?> style='width:100%'>
                    <?php
                        echo makeOptions($employees);
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="joined_date" class="col-md-3 control-label">Join Date * </label>
            <div class="col-md-7">
                <input type="text" class="form-control date_picker" id="joined_date"  name='joined_date' value='<?php echo !empty($employee)?$employee['joined_date']=='0000-00-00'?'mm/dd/yyyy ':htmlspecialchars(DisplayDate($employee['joined_date'])):''; ?>' required>
            </div>
        </div>

        <div class="form-group">
            <label for="basic_salary" class="col-md-3 control-label">Basic Salary * </label>
            <div class="col-md-7">
                <input type="text" class="form-control" id="basic_salary"  name='basic_salary' placeholder="0000.00" value='<?php echo !empty($employee)?htmlspecialchars($employee['basic_salary']):''; ?>' required>
            </div>
        </div>

        <div class="form-group">
            <label for="bond_date" class="col-md-3 control-label">Regularization Date </label>
            <div class="col-md-7">
                <input type="text" class="form-control date_picker" id="regularization_date"  name='regularization_date' value='<?php echo !empty($employee)?$employee['regularization_date']=='0000-00-00'?'mm/dd/yyyy ':htmlspecialchars(DisplayDate($employee['regularization_date'])):''; ?>'>
            </div>
        </div>

        <div class="form-group">
            <label for="bond_date" class="col-md-3 control-label">Bond Date </label>
            <div class="col-md-7">
                <input type="text" class="form-control date_picker" id="bond_date"  name='bond_date' value='<?php echo !empty($employee)?$employee['bond_date']=='0000-00-00'?'mm/dd/yyyy ':htmlspecialchars(DisplayDate($employee['bond_date'])):''; ?>'>
            </div>
        </div>

        <div class='form-group'>
            <label class='col-md-3 control-label'>Remarks (for update only)</label>
            <div class='col-md-7'>
                <textarea name='remarks' class='form-control ' style='resize: none' rows='4'></textarea>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-9 col-md-offset-2 text-center">
                <a href='frm_employee.php?id=<?php echo $employee['id']?>&tab=<?php echo $tab?>' class='btn btn-default'>Cancel</a>
                <button type='submit' class='btn btn-warning'>Save </button>
            </div>
        </div>

    </form>
</div>

<br/>
<table id='ResultTable' class='table table-bordered table-striped'>
    <thead>
        <tr>
            <th class='text-center'>Date Changed</th>
            <th class='text-center'>Category</th>
            <th class='text-center'>Old Record</th>
            <th class='text-center'>New Record</th>
            <th class='text-center'>Remarks</th>
            <!-- <th class='text-center'>Action</th> -->
        </tr>
    </thead>
    <tbody>
        <?php while($row = $data->fetch(PDO::FETCH_ASSOC)): ?>        
            <tr>
                <td><?php echo htmlspecialchars($row['date_changed']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['old_record']); ?></td>
                <td><?php echo htmlspecialchars($row['new_record']); ?></td>
                <td><?php echo htmlspecialchars($row['remarks']); ?></td>
            </tr>   
        <?php endwhile; ?>
    </tbody>
</table>


<!-- MODAL -->
<div class="modal" id='modal_adjustment'>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Update Detail</h4>
            </div>
            <div class="modal-body" >
                <div class='' id='' style=''>
                    <form class='form-horizontal' action='' method="POST" onsubmit='return validate_update(this)'>
                        <input type='hidden' value='' id='emp_id' name='emp_id'>
<!-- 
                        <div class="form-group">
                            <label class="col-md-3 control-label">Category *</label>
                            <div class="col-md-7">
                                <select name='category' id='category' class='form-control cbo' onchange="show_form()" data-placeholder="Select Category" style='width:100%'>
                                    <option value=''></option>
                                    <option value='Basic Salary'>Basic Salary</option>
                                    <option value='Department'>Department</option>
                                    <option value='Employment Status'>Employment Status</option>
                                    <option value='Job Title'>Job Title</option>
                                    <option value='Joined Date'>Joined Date</option>
                                    <option value='Pay Grade'>Pay Grade</option>
                                    <option value='Payroll Group'>Payroll Group</option>
                                    <option value='Regularization Date'>Regularization Date</option>
                                    <option value='Supervisor'>Supervisor</option>
                                </select>
                            </div>
                        </div>

                                <div class="form-group" id="basic_salary_frm">
                                    <label for="basic_salary" class="col-md-3 control-label">Basic Salary * </label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control" id="frm_basic_salary"  name='frm_basic_salary' placeholder="0000.00" value='<?php //echo !empty($employee)?htmlspecialchars($employee['basic_salary']):''; ?>' required>
                                    </div>
                                </div>

                                <div class="form-group" id="department_frm">
                                    <label for="department_id" class="col-md-3 control-label">Department *</label>
                                    <div class="col-md-7">
                                        <select name='frm_department_id' class='form-control cbo' data-placeholder="Select Department " <?php //echo !(empty($employee))?"data-selected='".$employee['department_id']."'":NULL ?> style='width:100%' required>
                                            <?php
                                                // echo makeOptions($departments);
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" id="employment_status_frm">
                                    <label for="employment_status_id" class="col-md-3 control-label">Employment Status *</label>
                                    <div class="col-md-7">
                                        <select name='frm_employment_status_id' class='form-control cbo' data-placeholder="Select Employment Status" <?php //echo !(empty($employee))?"data-selected='".$employee['employment_status_id']."'":NULL ?> style='width:100%' required>
                                            <?php
                                                //echo makeOptions($employment_status);
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" id="job_title_frm">
                                    <label for="job_title_id" class="col-md-3 control-label">Job Title *</label>
                                    <div class="col-md-7">
                                        <select name='frm_job_title_id' class='form-control cbo' data-placeholder="Select Job Title " <?php //echo !(empty($employee))?"data-selected='".$employee['job_title_id']."'":NULL ?> style='width:100%' required>
                                            <?php
                                                //echo makeOptions($job_titles);
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" id="joined_date_frm">
                                    <label for="joined_date" class="col-md-3 control-label">Join Date * </label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control date_picker" id="frm_joined_date"  name='frm_joined_date' value='<?php //echo !empty($employee)?htmlspecialchars(DisplayDate($employee['joined_date'])):''; ?>' required>
                                    </div>
                                </div>

                                <div class="form-group" id="pay_grade_frm">
                                    <label for="pay_grade_id" class="col-md-3 control-label">Pay Grade *</label>
                                    <div class="col-md-7">
                                        <select name='frm_pay_grade_id' class='form-control cbo' data-placeholder="Select Pay Grade " <?php //echo !(empty($employee))?"data-selected='".$employee['pay_grade_id']."'":NULL ?> style='width:100%'  required>
                                            <?php
                                                //echo makeOptions($pay_grades);
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" id="pay_group_frm">
                                    <label class="col-md-3 control-label">Pay Group *</label>
                                    <div class="col-md-7">
                                        <select name='frm_pay_group_id' class='form-control cbo' data-placeholder="Select Pay Group " <?php //echo !(empty($employee))?"data-selected='".$employee['payroll_group_id']."'":NULL ?> style='width:100%' required>
                                            <?php
                                                //echo makeOptions($payroll_group);
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" id="regular_date_frm">
                                    <label for="bond_date" class="col-md-3 control-label">Regularization Date </label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control date_picker" id="frm_regularization_date"  name='frm_regularization_date' value='<?php //echo !empty($employee)?$employee['regularization_date']=='0000-00-00'?'mm/dd/yyyy ':htmlspecialchars(DisplayDate($employee['regularization_date'])):''; ?>'>
                                    </div>
                                </div>

                                <div class="form-group" id="supervisor_frm">
                                    <label for="supervisor_id" class="col-md-3 control-label">Supervisor </label>
                                    <div class="col-md-7">
                                        <select name='frm_supervisor_id' class='form-control cbo' data-placeholder="Select Supervisor " <?php //echo !(empty($employee))?"data-selected='".$employee['supervisor_id']."'":NULL ?> style='width:100%'>
                                            <?php
                                                //echo makeOptions($employees);
                                            ?>
                                        </select>
                                    </div>
                                </div> -->


                        <div class='form-group'>
                            <label class='col-md-3 control-label'>Remarks *</label>
                            <div class='col-md-7'>
                                <textarea name='remarks' class='form-control ' style='resize: none' rows='4'></textarea>
                            </div>
                        </div>
                        <div class='form-group '>
                            <div class='col-md-3 col-md-offset-5'>
                                <button type='submit' class='btn btn-warning'>
                                    Save Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>          
        </div>
    </div>
</div>
<!-- END MODAL-->


<script type="text/javascript">
    $(function () 
    {
        $('#btn_update').hide();
            $('#basic_salary_frm').hide();
            $('#department_frm').hide();
            $('#employment_status_frm').hide();
            $('#job_title_frm').hide();
            $('#joined_date_frm').hide();
            $('#job_title_frm').hide();
            $('#pay_grade_frm').hide();
            $('#pay_group_frm').hide();
            $('#regular_date_frm').hide();
            $('#supervisor_frm').hide();
    });
    function show_modal(btn)
    {
        $('#modal_adjustment').modal('show');
        $("#a_id").val($(btn).data("id"));
    }
    function show_form()
    {
        var category = $('#category').val();

        if (category == "Basic Salary")         { $('#basic_salary_frm').show();        }else { $('#basic_salary_frm').hide(); }
        if (category == "Department")           { $('#department_frm').show();          }else { $('#department_frm').hide(); }
        if (category == "Employment Status")    { $('#employment_status_frm').show();   }else { $('#employment_status_frm').hide(); }
        if (category == "Job Title")            { $('#job_title_frm').show();           }else { $('#job_title_frm').hide(); }
        if (category == "Joined Date")          { $('#joined_date_frm').show();         }else { $('#joined_date_frm').hide();}
        if (category == "Pay Grade")            { $('#pay_grade_frm').show();           }else { $('#pay_grade_frm').hide(); }
        if (category == "Payroll Group")        { $('#pay_group_frm').show();           }else { $('#pay_group_frm').hide(); }
        if (category == "Regularization Date")  { $('#regular_date_frm').show();        }else { $('#regular_date_frm').hide(); }
        if (category == "Supervisor")           { $('#supervisor_frm').show();          }else { $('#supervisor_frm').hide(); }
    }


</script>

<?php 
    if($has_error===TRUE || !empty($data)):
?>
    <script type="text/javascript">
        $(function()
        {
            $('#collapseForm').collapse(
            {
                toggle: true
            })    
        });
    </script>
<?php
    endif;
?>




    
