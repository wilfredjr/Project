<?php
    require_once("support/config.php");
     if (!isLoggedIn()) {
         toLogin();
         die();
     }


    if (empty($_POST['type'])) {
        Modal("Invalid Record Selected");
        redirect("index.php");
        die;
    } else {
        if (!in_array($_POST['type'], array('pre_overtime','overtime','official_business','adjustment','leave','shift','offset','allowance','ot_approval','ot_adjustment','project_approval_emp','project_approval_phase','task_management_approval','task_completion_approval','project_application_approval'))) {
            Modal("Invalid Record Selected");
            redirect("index.php");
            die;
        }
    }
    $startTimeStamp="";
    $endTimeStamp="";

    function validate($fields)
    {
        global $page;
        $inputs=$_POST;
        $errors="";
        foreach ($fields as $key => $value) {
            if (empty($inputs[$key])) {
                $errors.=$value;
                //var_dump($inputs[$key]);
            } else {
                #CUSTOM VALIDATION
            }
        }
        if ($errors!="") {
            Alert("You have the following errors: <br/>".$errors, "danger");
            redirect($page);
            return false;
            die;
        } else {
            return true;
        }
    }
    $inputs=$_POST;
    $required_fieds=array();
    $page='index.php';
    $approver_id=$_SESSION[WEBAPP]['user']['employee_id'];
    switch ($inputs['type']) {

        case 'allowance':
            $table="employees_allowances";
            $page="allowance_approval.php";
            $query="SELECT
                    id
                FROM vw_employees_allowances WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1";
            $record_filters['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            $filter_sql="";
            if (!empty($_POST['approve_emp_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_emp_id'];
            }
            if (!empty($_POST['approve_dep_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" department_id=:dep_id ";
                $record_filters['dep_id']=$_POST['approve_dep_id'];
            }
            if (!empty($_POST['approve_start_date'])) {
                $date_start_file=date_create($_POST['approve_start_date']);
            } else {
                $date_start_file = "";
            }
            if (!empty($_POST['approve_end_date'])) {
                $date_end_file = date_create($_POST['approve_end_date']);
            } else {
                $date_end_file = "";
            }

            if (!empty($date_start_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" date_applied >= :date_start ";
                $record_filters['date_start'] = date_format($date_start_file,'Y-m-d');
            }

            if (!empty($date_end_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" date_applied <= :date_end ";
                $record_filters['date_end'] = date_format($date_end_file,'Y-m-d');
            }
            $query.=!empty($filter_sql)?" AND ".$filter_sql:"";


            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);

        break;

        case 'offset':
            $page="offset_approval.php";
            $query="SELECT

                    id
                FROM vw_employees_offset WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1";

            $filter_sql="";
            $record_filters['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            if (!empty($_POST['approve_request_type'])) {
                $filter_sql.=" request_type_id=:request_type ";
                $record_filters['request_type'] = $_POST['approve_request_type'];
            }
            if (!empty($_POST['approve_emp_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" employees_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_emp_id'];
            }
            if (!empty($_POST['approve_dep_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" department_id=:dep_id ";
                $record_filters['dep_id']=$_POST['approve_dep_id'];
            }
            if(!empty($_GET['approve_project_id']))
            {
                $stat=" project_id=:project_id ";
                if(!empty($filter_sql))
                {
                    $filter_sql.=" AND ";
                }
                $bindings[]=array('key'=>'project_id','val'=>$_GET['approve_project_id'],'type'=>0);
                $filter_sql.=$stat;
                // echo $filter_sql;
            }
            if (!empty($_POST['approve_start_date'])) {

                $date_start_file=date_create($_POST['approve_start_date']);
            } else {
                $date_start_file = "";
            }
            if (!empty($_POST['approve_end_date'])) {

                $date_end_file = date_create($_POST['approve_end_date']);

            } else {
                $date_end_file = "";
            }

            if (!empty($date_start_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" DATE_FORMAT(start_datetime,'%Y-%m-%d') >= :date_start ";


                $record_filters['date_start'] = date_format($date_start_file,'Y-m-d');
            }

            if (!empty($date_end_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" DATE_FORMAT(end_datetime,'%Y-%m-%d') <= :date_end ";

                $record_filters['date_end'] = date_format($date_end_file,'Y-m-d');
            }


            $query.=!empty($filter_sql)?" AND ".$filter_sql:"";
            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'pre_overtime':
            $page="overtime_approval.php";
            break;
        case 'overtime':
            $page="overtime_approval.php";
            $query="SELECT
                id
                FROM vw_employees_ot";

            $filter_sql="";
            $filter_sql.=" WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";
            $record_filters['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];

            if (!empty($_POST['approve_emp_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_emp_id'];
            }
            if (!empty($_POST['approve_dep_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" department_id=:dep_id ";
                $record_filters['dep_id']=$_POST['approve_dep_id'];
            }
            if (!empty($_POST['approve_start_date'])) {
                $date_start_file=date_create($_POST['approve_start_date']);
            } else {
                $date_start_file = "";
            }
            if (!empty($_POST['approve_end_date'])) {
                $date_end_file = date_create($_POST['approve_end_date']);
            } else {
                $date_end_file = "";
            }

            if (!empty($date_start_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" ot_date >= :date_start ";
                $record_filters['date_start'] = date_format($date_start_file,'Y-m-d');
            }

            if (!empty($date_end_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" ot_date <= :date_end ";
                $record_filters['date_end'] = date_format($date_end_file,'Y-m-d');
            }
            $query.=$filter_sql;
            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'leave':
            $page="leave_approval.php";
            $query="SELECT
                    id
                FROM vw_employees_leave WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1";

            $record_filters['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            $filter_sql="";
            if (!empty($_POST['approve_leave_type_id'])) {
                $filter_sql.=" leave_id=:leave_type_id ";
                $record_filters['leave_type_id'] = $_POST['approve_leave_type_id'];
            }
            if (!empty($_POST['approve_emp_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_emp_id'];
            }
            if (!empty($_POST['approve_dep_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" department_id=:dep_id ";
                $record_filters['dep_id']=$_POST['approve_dep_id'];
            }
            if (!empty($_POST['approve_start_date'])) {
                $date_start_file=date_create($_POST['approve_start_date']);
            } else {
                $date_start_file = "";
            }
            if (!empty($_POST['approve_end_date'])) {
                $date_end_file = date_create($_POST['approve_end_date']);
            } else {
                $date_end_file = "";
            }

            if (!empty($date_start_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" date_start >= :date_start ";
                $record_filters['date_start'] = date_format($date_start_file,'Y-m-d');
            }

            if (!empty($date_end_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" date_end <= :date_end ";
                $record_filters['date_end'] = date_format($date_end_file,'Y-m-d');
            }
            $query.=!empty($filter_sql)?" AND ".$filter_sql:"";


            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);

            break;
        case 'project_approval_emp':
            
            // var_dump($_POST);
            // die;
            $employee=$_SESSION[WEBAPP]['user']['employee_id'];
            $page="project_employee_approval.php";
            $query="SELECT
                    id, project_id, employee_id, modification_type, status_id, requested_employee_id, is_deleted, status_id
                FROM project_requests pr";
            $filter_sql="pr.is_deleted=0 AND status_id=1 AND (SELECT   
                         CASE   
                            WHEN pr.step_id=2 THEN pr.manager_id 
                            WHEN pr.step_id=3 THEN pr.admin_id
                         END=:employee_id1)";

            $record_filters['employee_id1']=$_SESSION[WEBAPP]['user']['employee_id'];

            if(!empty($_POST['approve_project_name']))
            {
                $ltype=" project_id=:approve_project_name ";
                if(!empty($filter_sql))
                {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=$ltype;
                $record_filters['approve_project_name']=$_POST['approve_project_name'];
            }
           
            if (!empty($_POST['approve_employee_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }

                // $filter_sql.=" requested_employee_id=:requested_id";
                // $record_filters['requested_id']=$_POST['approve_employee_id'];

                $filter_sql.="requested_employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_employee_id'];
                
            }
             if(!empty($_POST['approve_request_id1']) OR ($_POST['approve_request_id1']=='0'))
             {
                  $dep=" modification_type=:approve_request_id1 ";
                  if(!empty($filter_sql))
                {
                      $filter_sql.=" AND ";
                  }
                $filter_sql.=$dep;
                $record_filters['approve_request_id1']=$_POST['approve_request_id1'];
             }
             if(!empty($_POST['approve_status']))
             {

                  $dep=" status_id=:approve_status ";
                  if(!empty($filter_sql))
                {

                      $filter_sql.=" AND ";

                   
                  }
                 $record_filters['approve_status']=$_POST['approve_status'];
                $filter_sql.=$dep;
             }
           
            $query.=!empty($filter_sql)?" WHERE ".$filter_sql:"";
            // var_dump($query);
            // echo "<br>";
            // var_dump($record_filters);

            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            // var_dump($records);
            // die();
            break;
     case 'project_approval_phase':
            
            // var_dump($_POST);
            // die;
            $employee=$_SESSION[WEBAPP]['user']['employee_id'];
            $page="project_phase_approval.php";
            $query="SELECT
                    id, project_id, employee_id, request_status_id, manager_id
                FROM project_phase_request";
            $filter_sql="request_status_id=1 AND manager_id";
            $filter_sql.="=:employee_id1";

            $record_filters['employee_id1']=$_SESSION[WEBAPP]['user']['employee_id'];

            if(!empty($_POST['approve_project_name']))
            {
                $ltype=" project_id=:approve_project_name ";
                if(!empty($filter_sql))
                {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=$ltype;
                $record_filters['approve_project_name']=$_POST['approve_project_name'];
            }
             if(!empty($_POST['approve_status']))
             {

                  $dep=" request_status_id=:approve_status ";
                  if(!empty($filter_sql))
                {

                      $filter_sql.=" AND ";

                   
                  }
                 $record_filters['approve_status']=$_POST['approve_status'];
                $filter_sql.=$dep;
             }
               if (!empty($_POST['approve_employee_id'])) 
            {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }

                // $filter_sql.=" requested_employee_id=:requested_id";
                // $record_filters['requested_id']=$_POST['approve_employee_id'];

                $filter_sql.="employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_employee_id'];
                
            }
            if (!empty($_POST['approve_request_id1'])) 
            {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }

                // $filter_sql.=" requested_employee_id=:requested_id";
                // $record_filters['requested_id']=$_POST['approve_employee_id'];

                $filter_sql.="project_phase_id=:phase_id ";
                $record_filters['phase_id']=$_POST['approve_request_id1'];
                
            }
           
            $query.=!empty($filter_sql)?" WHERE ".$filter_sql:"";
            // var_dump($query);
            // echo "<br>";
            // var_dump($record_filters);

            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            // var_dump($records);
            // die();
            break;
         case 'task_completion_approval':
            
            // var_dump($_POST);
            // die;
            $employee=$_SESSION[WEBAPP]['user']['employee_id'];
            $page="task_completion_approval.php";
            $query="SELECT
                    id, project_id, employee_id, request_status_id, manager_id
                FROM project_task_completion";
            $filter_sql="request_status_id=1 AND (manager_id=:employee_id1 OR team_lead_id=:employee_id1)";

            $record_filters['employee_id1']=$_SESSION[WEBAPP]['user']['employee_id'];

            if(!empty($_POST['approve_project_name']))
            {
                $ltype=" project_id=:approve_project_name ";
                if(!empty($filter_sql))
                {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=$ltype;
                $record_filters['approve_project_name']=$_POST['approve_project_name'];
            }
             if(!empty($_POST['approve_status']))
             {

                  $dep=" request_status_id=:approve_status ";
                  if(!empty($filter_sql))
                {

                      $filter_sql.=" AND ";

                   
                  }
                 $record_filters['approve_status']=$_POST['approve_status'];
                $filter_sql.=$dep;
             }
               if (!empty($_POST['approve_employee_id'])) 
            {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }

                // $filter_sql.=" requested_employee_id=:requested_id";
                // $record_filters['requested_id']=$_POST['approve_employee_id'];

                $filter_sql.="employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_employee_id'];
                
            }
            if (!empty($_POST['approve_request_id1'])) 
            {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }

                // $filter_sql.=" requested_employee_id=:requested_id";
                // $record_filters['requested_id']=$_POST['approve_employee_id'];

                $filter_sql.="project_phase_id=:phase_id ";
                $record_filters['phase_id']=$_POST['approve_request_id1'];
                
            }
           
            $query.=!empty($filter_sql)?" WHERE ".$filter_sql:"";
            // var_dump($query);
            // echo "<br>";
            // var_dump($record_filters);

            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            // var_dump($records);
            // die();
            break;

        case 'task_management_approval':
            
            // var_dump($_POST);
            // die;
            $employee=$_SESSION[WEBAPP]['user']['employee_id'];
            $page="task_management_approval.php";
            $query="SELECT
                    id, project_id, employee_id, request_status_id, manager_id
                FROM project_task";
            $filter_sql="request_status_id=1 AND manager_id=:employee_id1";

            $record_filters['employee_id1']=$_SESSION[WEBAPP]['user']['employee_id'];

            if(!empty($_POST['approve_project_name']))
            {
                $ltype=" project_id=:approve_project_name ";
                if(!empty($filter_sql))
                {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=$ltype;
                $record_filters['approve_project_name']=$_POST['approve_project_name'];
            }
             if(!empty($_POST['approve_status']))
             {

                  $dep=" request_status_id=:approve_status ";
                  if(!empty($filter_sql))
                {

                      $filter_sql.=" AND ";

                   
                  }
                 $record_filters['approve_status']=$_POST['approve_status'];
                $filter_sql.=$dep;
             }
               if (!empty($_POST['approve_employee_id'])) 
            {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }

                // $filter_sql.=" requested_employee_id=:requested_id";
                // $record_filters['requested_id']=$_POST['approve_employee_id'];

                $filter_sql.="employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_employee_id'];
                
            }
            if (!empty($_POST['approve_request_id1'])) 
            {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }

                // $filter_sql.=" requested_employee_id=:requested_id";
                // $record_filters['requested_id']=$_POST['approve_employee_id'];

                $filter_sql.="project_phase_id=:phase_id ";
                $record_filters['phase_id']=$_POST['approve_request_id1'];
                
            }
           
            $query.=!empty($filter_sql)?" WHERE ".$filter_sql:"";

            // var_dump($query);
            // echo "<br>";
            // var_dump($record_filters);

            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            // var_dump($records);
            // die();
            break;

            case 'project_application_approval':
            
            // var_dump($_POST);
            // die;
            $employee=$_SESSION[WEBAPP]['user']['employee_id'];
            $page="project_application_approval.php";
            $query="SELECT
                    id, employee_id, request_status_id
                FROM project_application";
            $filter_sql="request_status_id=1 AND employee_id=:employee_id1";

            $record_filters['employee_id1']=$_SESSION[WEBAPP]['user']['employee_id'];

            if(!empty($_POST['approve_project_name']))
            {
                $ltype=" project_id=:approve_project_name ";
                if(!empty($filter_sql))
                {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=$ltype;
                $record_filters['approve_project_name']=$_POST['approve_project_name'];
            }
             if(!empty($_POST['approve_status']))
             {

                  $dep=" request_status_id=:approve_status ";
                  if(!empty($filter_sql))
                {

                      $filter_sql.=" AND ";

                   
                  }
                 $record_filters['approve_status']=$_POST['approve_status'];
                $filter_sql.=$dep;
             }
             if (!empty($_POST['approve_start_date'])) {
                $date_start_file=date_create($_POST['approve_start_date']);
            } else {
                $date_start_file = "";
            }
            if (!empty($_POST['approve_end_date'])) {
                $date_end_file = date_create($_POST['approve_end_date']);
            } else {
                $date_end_file = "";
            }

            if (!empty($date_start_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" date_filed >= :date_start ";
                $record_filters['date_start'] = date_format($date_start_file,'Y-m-d');
            }

            if (!empty($date_end_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" date_filed <= :date_end ";
                $record_filters['date_end'] = date_format($date_end_file,'Y-m-d');
            }           
            $query.=!empty($filter_sql)?" WHERE ".$filter_sql:"";
            // var_dump($query);
            // echo "<br>";
            // var_dump($record_filters);

            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            // var_dump($records);
            // die();
            break;

        case 'ot_adjustment':
            $page="ot_adjustments_approval.php";
            $query="SELECT
            id
            FROM vw_employees_ot_adjustments ";
            $filter_sql="";
            $filter_sql.=" WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";
            $record_filters['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            if (!empty($_POST['approve_emp_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_emp_id'];
            }
            if (!empty($_POST['approve_dep_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" department_id=:dep_id ";
                $record_filters['dep_id']=$_POST['approve_dep_id'];
            }
            if (!empty($_POST['approve_start_date'])) {
                $date_start_file=date_create($_POST['approve_start_date']);
            } else {
                $date_start_file = "";
            }
            if (!empty($_POST['approve_end_date'])) {
                $date_end_file = date_create($_POST['approve_end_date']);
            } else {
                $date_end_file = "";
            }

            if (!empty($date_start_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" ot_date >= :date_start ";
                $record_filters['date_start'] = date_format($date_start_file,'Y-m-d');
            }

            if (!empty($date_end_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" ot_date <= :date_end ";
                $record_filters['date_end'] = date_format($date_end_file,'Y-m-d');
            }
            $query.=$filter_sql;
            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'adjustment':
            $page="adjustments_approval.php";
            $query="SELECT
                    id
                FROM vw_employees_adjustments WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";
            $record_filters['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            $filter_sql="";
            if (!empty($_POST['approve_emp_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_emp_id'];
            }
            if (!empty($_POST['approve_dep_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" department_id=:dep_id ";
                $record_filters['dep_id']=$_POST['approve_dep_id'];
            }
            if (!empty($_POST['approve_start_date'])) {
                $date_start_file=date_create($_POST['approve_start_date']);
            } else {
                $date_start_file = "";
            }
            if (!empty($_POST['approve_end_date'])) {
                $date_end_file = date_create($_POST['approve_end_date']);
            } else {
                $date_end_file = "";
            }

            if (!empty($date_start_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" adj_date >= :date_start ";
                $record_filters['date_start'] = date_format($date_start_file,'Y-m-d');
            }

            if (!empty($date_end_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" adj_date <= :date_end ";
                $record_filters['date_end'] = date_format($date_end_file,'Y-m-d');
            }
            $query.=!empty($filter_sql)?" AND ".$filter_sql:"";


            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'official_business':
            $table="employees_ob";
            $page="ob_approval.php";
            $query="SELECT
                    id
                FROM vw_employees_ob WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";
            $record_filters['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            $filter_sql="";
            if (!empty($_POST['approve_emp_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_emp_id'];
            }
            if (!empty($_POST['approve_dep_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" department_id=:dep_id ";
                $record_filters['dep_id']=$_POST['approve_dep_id'];
            }
            if (!empty($_POST['approve_start_date'])) {
                $date_start_file=date_create($_POST['approve_start_date']);
            } else {
                $date_start_file = "";
            }
            if (!empty($_POST['approve_end_date'])) {
                $date_end_file = date_create($_POST['approve_end_date']);
            } else {
                $date_end_file = "";
            }

            if (!empty($date_start_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" ob_date >= :date_start ";
                $record_filters['date_start'] = date_format($date_start_file,'Y-m-d');
            }

            if (!empty($date_end_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" ob_date <= :date_end ";
                $record_filters['date_end'] = date_format($date_end_file,'Y-m-d');
            }
            $query.=!empty($filter_sql)?" AND ".$filter_sql:"";


            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'shift':
            $table="employees_change_shift";
            $page="shift_approval.php";
            $query="SELECT
                    id
                FROM vw_employees_change_shift WHERE :employee_id IN (SELECT employee_id FROM approval_steps_employees WHERE approval_step_id = step_id) AND request_status_id=1 ";
            $record_filters['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
            $filter_sql="";
            if (!empty($_POST['approve_emp_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" employee_id=:emp_id ";
                $record_filters['emp_id']=$_POST['approve_emp_id'];
            }
            if (!empty($_POST['approve_dep_id'])) {
                if (!empty($filter_sql)) {
                    $filter_sql.=" AND ";
                }
                $filter_sql.=" department_id=:dep_id ";
                $record_filters['dep_id']=$_POST['approve_dep_id'];
            }
            if (!empty($_POST['approve_start_date'])) {
                $date_start_file=date_create($_POST['approve_start_date']);
            } else {
                $date_start_file = "";
            }
            if (!empty($_POST['approve_end_date'])) {
                $date_end_file = date_create($_POST['approve_end_date']);
            } else {
                $date_end_file = "";
            }

            if (!empty($date_start_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" date_from >= :date_start ";
                $record_filters['date_start'] = date_format($date_start_file,'Y-m-d');
            }

            if (!empty($date_end_file)) {
                $filter_sql.=!empty($filter_sql)?" AND ":"";
                $filter_sql.=" date_to <= :date_end ";
                $record_filters['date_end'] = date_format($date_end_file,'Y-m-d');
            }
            $query.=!empty($filter_sql)?" AND ".$filter_sql:"";


            $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'ot_approval':
        $page="overtime_approval.php";

            $query="SELECT
                id,
                code,
                employee_name,
                supervisor,
                final_approver,

                ot_date,

                DATE_FORMAT(time_from,'".TIME_FORMAT_SQL."') as time_from,
                DATE_FORMAT(time_to,'".TIME_FORMAT_SQL."') as time_to,
                no_hours,
                worked_done,

                status,
                department_id,
                department,
                overtime_type,
                date_filed

                FROM vw_ot_approvals ";
                $record_filters['employee_id']=$_SESSION[WEBAPP]['user']['employee_id'];
                $filter_sql=" WHERE CASE when status='Supervisor Approval' then supervisor_id when status='Final Approver Approval' then final_approver_id end  =:employee_id ";
                if(!empty($_POST['approve_emp_id']))
                {
                    $emp=" employee_id=:emp_id ";
                    if(!empty($filter_sql))
                    {
                        $filter_sql.=" AND ";
                    }
                    $record_filters['emp_id']=$_POST['approve_emp_id'];
                    $filter_sql.=$emp;


                }
                 if(!empty($_POST['approve_dep_id']))
                 {

                      $dep=" department_id=:dep_id ";
                      if(!empty($filter_sql))
                    {
                          $filter_sql.=" AND ";
                      }
                        $record_filters['dep_id']=$_POST['approve_dep_id'];
                    $filter_sql.=$dep;
                 }

                  if(!empty($_POST['approve_ot_type']))
                 {

                      $ot_type=" overtime_type=:ot_type ";
                      if(!empty($filter_sql))
                    {
                          $filter_sql.=" AND ";
                      }
                        $record_filters['ot_type']=$_POST['approve_ot_type'];
                    $filter_sql.=$ot_type;
                 }
                 $query.=$filter_sql;
                 echo $query;
                 $records = $con->myQuery($query, $record_filters)->fetchAll(PDO::FETCH_ASSOC);
            break;

        default:
            redirect("index.php");
            break;

    }

    if (empty($records)) {
        Alert("No request found.", "danger");
        redirect($page);
        die;
    }
        $inputs['action']="approve";
        try {
            switch ($inputs['type']) {
                case 'ot_adjustment':
                    foreach ($records as $inputs) {
                        $audit_details=$con->myQuery("SELECT employee_name,ot_date,orig_time_in,orig_time_out,adj_time_in,adj_time_out FROM vw_employees_ot_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                            $current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  employees_ot_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                            $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                             /*
                            Get Next step if exists if empty set status to approved 2
                             */
                            $next_step=getNextStep($current['approval_step_id'], $current['id'], 'ot_adjustment');
                            if (empty($next_step)) {
                                $status=2;
                                try {
                                        $con->beginTransaction();
                                        $con->myQuery("UPDATE employees_ot_adjustments SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                        $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                        $con->myQuery("UPDATE employees_ot SET time_from=:adj_time_in,time_to=:adj_time_out,no_hours=:adj_no_hours WHERE id=:employees_ot_id",array("adj_time_in"=>$current['adj_time_in'],"adj_time_out"=>$current['adj_time_out'],"employees_ot_id"=>$current['employees_ot_id'],"adj_no_hours"=>$current['adj_no_hours']));
                                        // die;
                                        $con->commit();

                                    } catch (Exception $e) {
                                        $con->rollback();
                                        Alert("Save Failed.","danger");
                                        redirect("ot_adjustments_approval.php");
                                        die;
                                    }
                            } else {
                                $con->myQuery("UPDATE employees_ot_adjustments SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                            }

                            $employees=getEmpDetails($current['employees_id']);
                            $email_settings=getEmailSettings();
                            //var_dump($supervisor);
                            insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Approved {$employees['first_name']} {$employees['last_name']}'s adjustment request. {$audit_message}");
                            if (empty($next_step)) {
                                /*
                                Notify only the sender
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $header="Overtime Adjustment Request has been Approved";
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            } else {
                                /*
                                Email next set of approvers
                                 */
                                $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                $header="New Overtime Adjustment Request For Your Approval";
                                /*
                                Modify message to be more generic and allow to be sent to multiple people.
                                 */
                                $message="Good day,<br/> You have a new overtime adjustment request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);

                                $recepients=array();
                                foreach ($approvers as $key => $approver) {
                                    if (!empty($approver['private_email'])) {
                                        $recepients[]=$approver['private_email'];
                                    }
                                    if (!empty($approver['work_email'])) {
                                        $recepients[]=$approver['work_email'];
                                    }
                                }
                                /*
                                Email Recepients
                                 */
                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Overtime Adjustment Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                /*
                                Notify request has been approved
                                 */
                                if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $header="Overtime Adjustment Request has been Approved";
                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                }
                            }
                            Alert("Overtime Adjustment Request approved succesfully.","success");
                    }

                case 'allowance':
                 foreach ($records as $inputs) {
                    // var_dump($inputs);
                        $inputs['action']="approve";
                    $audit_details=$con->myQuery("SELECT employee_name,food_allowance,transpo_allowance,request_reason,date_applied FROM vw_employees_allowances WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  employees_allowances WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $audit_message="Date applied for ({$audit_details["date_applied"]}), Food allowance (".number_format($audit_details['food_allowance'],2)."). Transportation Allowance (".number_format($audit_details['transpo_allowance'], 2)."). With a reason of ({$audit_details["request_reason"]})";
                    /*
                    Get Next step if exists if empty set status to approved 2
                     */
                    $next_step=getNextStep($current['approval_step_id'], $current['id'], 'allowance');

                    // die;
                    if (empty($next_step)) {
                        $status=2;
                        try {
                                $con->beginTransaction();
                                $con->myQuery("UPDATE employees_allowances SET request_status_id =2,reason='' WHERE id=?",array($inputs['id']));
                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                $con->commit();

                            } catch (Exception $e) {
                                $con->rollback();
                                Alert("Save Failed.","danger");
                                redirect("allowance_approval.php");
                                die;
                            }
                    } else {
                        $con->myQuery("UPDATE employees_allowances SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                        $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                    }

                    $employees=getEmpDetails($current['employees_id']);
                    $email_settings=getEmailSettings();
                    //var_dump($supervisor);

                    if (empty($next_step)) {
                        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s allowance request. {$audit_message}");
                        /*
                        Notify only the sender
                         */
                        if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                            $header="Allowance Request has been Approved";
                            $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                            $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                            $message=email_template($header,$message);

                            PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Attendance Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                        }
                    } else {
                        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name']," Approved {$employees['first_name']} {$employees['last_name']}'s allowance request. {$audit_message}");
                        /*
                        Email next set of approvers
                         */
                        $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                        $header="New Allowance Request For Your Approval";
                        /*
                        Modify message to be more generic and allow to be sent to multiple people.
                         */
                        $message="Good day,<br/> You have a new allowance request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
                        $message=email_template($header,$message);

                        $recepients=array();
                        foreach ($approvers as $key => $approver) {
                            if (!empty($approver['private_email'])) {
                                $recepients[]=$approver['private_email'];
                            }
                            if (!empty($approver['work_email'])) {
                                $recepients[]=$approver['work_email'];
                            }
                        }
                        /*
                        Email Recepients
                         */
                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Attendance Adjustment Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                        /*
                        Notify request has been approved
                         */
                        if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                            $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                            $header="Allowance Request has been Approved";
                            $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                            $message=email_template($header,$message);

                            PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Allowance Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                        }
                    }
                }
                break;
        #OFFSET
                case 'offset':
                    foreach ($records as $inputs) {
                         $audit_details=$con->myQuery("SELECT employees_name,request_type,start_datetime,end_datetime, no_hours FROM vw_employees_offset WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                        $current=$con->myQuery("SELECT id,request_status_id,approval_step_id,employees_id,no_hours FROM employees_offset_request WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                        /*
                        Get Next step if exists if empty set status to approved 2
                         */
                        $next_step=getNextStep($current['approval_step_id'], $current['id'], 'offset');

                        if (empty($next_step)) {
                            $status=2;
                              try {
                                  $con->beginTransaction();
                                  $con->myQuery("UPDATE employees_offset_request SET request_status_id ={$status} WHERE id=?",array( $inputs['id'] ));
                                  $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                  Alert("Approved Offset!", "success");

                                  $con->commit();
                                }  catch (Exception $e) {
                                            $con->rollback();
                                            Alert("Save Failed.","danger");
                                            redirect($page);
                                            die;
                                        }
                                } else {
                                    $con->myQuery("UPDATE employees_offset_request SET approval_step_id=? WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                }
                                $employees=getEmpDetails($current['employees_id']);
                                $email_settings=getEmailSettings();
                                //var_dump($supervisor);
                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Approved {$employees['first_name']} {$employees['last_name']}'s offset request. {$audit_message}");
                                if (empty($next_step)) {
                                    /*
                                    Notify only the sender
                                     */
                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                        $header="Offset Request has been Approved";
                                        $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Offset Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    }
                                } else {
                                    /*
                                    Email next set of approvers
                                     */
                                    $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                    $header="New Offset Request For Your Approval";
                                    /*
                                    Modify message to be more generic and allow to be sent to multiple people.
                                     */
                                    $message="Good day,<br/> You have a new attendance adjustment request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    $recepients=array();
                                    foreach ($approvers as $key => $approver) {
                                        if (!empty($approver['private_email'])) {
                                            $recepients[]=$approver['private_email'];
                                        }
                                        if (!empty($approver['work_email'])) {
                                            $recepients[]=$approver['work_email'];
                                        }
                                    }
                                    /*
                                    Email Recepients
                                     */
                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Offset Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                    /*
                                    Notify request has been approved
                                     */
                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                        $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                        $header="Offset Request has been Approved";
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Offset Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    }
                                }
                            }
                            break;

    #PRE-APPROVAL OVERTIME
                case 'pre_overtime':
                    $audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot_pre WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                    $current=$con->myQuery("SELECT status,supervisor_id,employee_id FROM  vw_employees_ot_pre WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                    switch ($current['status']) {
                        case 'Supervisor Approval':
                            switch ($inputs['action']) {
                                case 'approve':
                                        $con->myQuery("UPDATE employees_ot_pre SET status ='Approved',supervisor_date_action=NOW() WHERE id=?", array($inputs['id']));
                                        $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));


                                        $supervisor=getEmpDetails($current['supervisor_id']);
                                        $employees=getEmpDetails($current['employee_id']);
                                        $email_settings=getEmailSettings();
                                        //var_dump($supervisor);
                                        if ((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)) {
                                            $header="Overtime Request has been Approved";
                                            $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                            $message=email_template($header, $message);
                                            // var_dump($email_settings);
                                             //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                            emailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($employees['private_email'],$employees['work_email'])), "Overtime Request (Approved)", $message, $email_settings['host'], $email_settings['port']);
                                        }
                                        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s overtime request. From {$audit_details['date_from']} To {$audit_details['date_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");

                                        // die;
                                    break;
                                case 'reject':
                                $required_fieds=array(
                                    "reason"=>"Enter Reason for rejection. <br/>"
                                    );
                                    if (validate($required_fieds)) {
                                        $con->myQuery("UPDATE employees_ot_pre SET status ='Rejected (Supervisor)',reason=?,supervisor_date_action=NOW() WHERE id=?", array($inputs['reason'],$inputs['id']));
                                        $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));


                                        $supervisor=getEmpDetails($current['supervisor_id']);
                                        $employees=getEmpDetails($current['employee_id']);
                                        $email_settings=getEmailSettings();

                                        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "(Supervisor) Rejected {$employees['first_name']} {$employees['last_name']}'s overtime request. The reason given is '{$inputs['reason']}. From {$audit_details['date_from']} To {$audit_details['date_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");

                                        //var_dump($supervisor);
                                        if ((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)) {
                                            $header="Overtime Request Rejected by Supervisor";
                                            $message="Hi {$employees['first_name']},<br/> Your request has been rejected by your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                            $message=email_template($header, $message);
                                            // var_dump($email_settings);
                                             //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                            emailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($employees['private_email'],$employees['work_email'])), "Overtime Request (Rejected)", $message, $email_settings['host'], $email_settings['port']);
                                        }
                                    }
                                    break;
                            }
                            break;
                    }
                    break;



                case 'overtime':
                    foreach ($records as $inputs) {
                        $audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                        $current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM employees_ot WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                         /*
                        Get Next step if exists if empty set status to approved 2
                         */
                        $next_step=getNextStep($current['approval_step_id'], $current['id'], 'overtime');
                        if (empty($next_step)) {
                            $status=2;
                            try {
                                    $con->beginTransaction();
                                    $con->myQuery("UPDATE employees_ot SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                    // die;
                                    $con->commit();

                                } catch (Exception $e) {
                                    $con->rollback();
                                    Alert("Save Failed.","danger");
                                    redirect("overtime_approval.php");
                                    die;
                                }
                        } else {
                            $con->myQuery("UPDATE employees_ot SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                            $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                        }

                        $employees=getEmpDetails($current['employees_id']);
                        $email_settings=getEmailSettings();
                        //var_dump($supervisor);
                        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Approved {$employees['first_name']} {$employees['last_name']}'s overtime claim request. {$audit_message}");
                        if (empty($next_step)) {
                            /*
                            Notify only the sender
                             */
                            if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                $header="Overtime Claim Request has been Approved";
                                $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);

                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Claim Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                            }
                        } else {
                            /*
                            Email next set of approvers
                             */
                            $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                            $header="New Overtime Claim Request For Your Approval";
                            /*
                            Modify message to be more generic and allow to be sent to multiple people.
                             */
                            $message="Good day,<br/> You have a new overtime claim request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
                            $message=email_template($header,$message);

                            $recepients=array();
                            foreach ($approvers as $key => $approver) {
                                if (!empty($approver['private_email'])) {
                                    $recepients[]=$approver['private_email'];
                                }
                                if (!empty($approver['work_email'])) {
                                    $recepients[]=$approver['work_email'];
                                }
                            }
                            /*
                            Email Recepients
                             */
                            PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Overtime Claim Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                            /*
                            Notify request has been approved
                             */
                            if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                $header="Overtime Claim Request has been Approved";
                                $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);

                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Overtime Claim Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                            }
                        }
                    }

                    break;
        #Project Approval Emp

                case 'project_approval_emp':
                // var_dump($inputs);
                // die;
                    foreach ($records as $inputs) {
                        // $audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                        $current_employee=$_SESSION[WEBAPP]['user']['employee_id'];
                          $current=$con->myQuery("SELECT id,first_approver_date,second_approver_date,third_approver_date,first_approver_id,second_approver_id,third_approver_id,modification_type,project_id,requested_employee_id,employee_id,manager_id,designation_id,step_id,admin_id FROM  project_requests WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                         /*
                        Get Next step if exists if empty set status to approved 2
                         */
                                $con->beginTransaction();   
                                  if($current['step_id']=='2'){
                                   $con->myQuery("UPDATE project_requests SET step_id = 3 WHERE id=?",array($inputs['id']));
                                  }elseif($current['step_id']=='3'){
                                    if($current['modification_type']=='1'){
                                    $param=array(
                                    "project_id"=>$current['project_id'],
                                    "employee_id"=>$current['requested_employee_id'],
                                    'designation'=>$current['designation_id']
                                    );

                                    $con->myQuery("INSERT INTO projects_employees (project_id,employee_id,designation_id) VALUES (:project_id,:employee_id,:designation)",$param);
                                    $param1=array(
                                    "project_id"=>$current['project_id'],
                                    "employee_id"=>$current['requested_employee_id'],
                                    'start_date'=>$date_removed,
                                    'added_by_id'=>$current['employee_id'],
                                    'designation'=>$current['designation_id']
                                    );
                                    $con->myQuery("INSERT INTO project_employee_history (project_id,employee_id,start_date,added_by,designation_id) VALUES (:project_id,:employee_id,:start_date,:added_by_id,:designation)",$param1);
                                    $con->myQuery("UPDATE project_requests SET status_id = 2 WHERE id=?",array($current['id']));  
                                }elseif($current['modification_type']=='0'){
                                    $get_start_date=$con->myQuery("SELECT id, employee_id, project_id, start_date FROM project_employee_history WHERE employee_id=".$current['requested_employee_id'] . " AND project_id=".$current['project_id']);
                                    while($rows =$get_start_date->fetch(PDO::FETCH_ASSOC)):
                                        if (empty($rows['removed_by'])) {

                                            $project_history_id = $rows['id'];
                                        }
                                    
                                    endwhile;
                                    
                                    
                                    $con->myQuery("UPDATE projects_employees SET is_deleted=1 WHERE project_id=".($current['project_id'])." AND employee_id=".($current['requested_employee_id']));

                                    if (!empty($project_history_id)) {
                                        $con->myQuery("UPDATE project_employee_history SET end_date='$date_removed', removed_by='$current_employee' WHERE id=".$project_history_id);
                                    }
                                    $con->myQuery("UPDATE project_requests SET status_id = 2 WHERE id=?",array($current['id']));  
                                }
                            }
                            $con->commit(); 
                        }
                    break;
         #Project Approval Phase

                case 'project_approval_phase':
                // var_dump($inputs);
                // die;
                    foreach ($records as $inputs) {
                        // $audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                        $current_employee=$_SESSION[WEBAPP]['user']['employee_id'];
                         $current=$con->myQuery("SELECT * FROM  project_phase_request WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                        $next_phase=$current['project_phase_id']+1;
                        $prev_phase=$current['project_phase_id'];
                        $date = (new DateTime())->getTimestamp();
                        $date_now=date('Y-m-d',$date);
                         /*
                        Get Next step if exists if empty set status to approved 2
                         */
                        if($current['step_id']=='2'){

                            $con->myQuery("UPDATE project_phase_request SET step_id = 3 WHERE id=?",array($inputs['id']));

                        }else{
                        if($current['type']=='comp'){#manager comp
                        $def_check=$con->myQuery("SELECT * FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND (in_deficit='1' OR status_id='4')",array($current['project_id'],$current['project_phase_id']))->fetch(PDO::FETCH_ASSOC);
                        $def_check_start=$con->myQuery("SELECT * FROM project_deficit WHERE project_id=? AND project_phase_id=? AND done_days='0' AND done_hours='0'",array($current['project_id'],$current['project_phase_id']))->fetch(PDO::FETCH_ASSOC);
                        if(empty($def_check)){
                        $con->myQuery("UPDATE project_phase_dates SET status_id='2',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($current['date_filed'],$current['project_id'],$current['project_phase_id']));

                            if($current['project_phase_id']=='2'){
                             $phase_check=$con->myQuery("SELECT id FROM  project_phase_dates WHERE project_id=? AND project_phase_id='3'",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
                             if(empty($phase_check)){
                                $current1=$con->myQuery("SELECT * FROM  projects WHERE id=?",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);

                                     $con->myQuery("INSERT INTO project_development (project_id,employee_id,team_lead_id,manager_id,admin_id,type,request_status_id,date_filed, phase_request_id) VALUES(?,?,?,?,?,'admin','1','$date_now',?)",array($current1['id'],$current1['employee_id'],$current1['team_lead_dev'],$current1['manager_id'],$current1['employee_id'],$current['id']));
                                     }else{
                                 $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    if(!empty($stat_check)){
                                        $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                         $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    }
                                if($current['project_phase_id']=='8'){
                                    $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                }else{
                                    $con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$current['project_id']));
                                    }
                                }
                            }else{
                                 $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    if(!empty($stat_check)){
                                        $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                         $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    }
                                if($current['project_phase_id']=='8'){
                                    $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                }else{
                                    $con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$current['project_id']));
                                    }
                                }
                        }else{
                            if(($def_check['status_id']=='4') && ($def_check['in_deficit']=='0')){
                                    $date_end1= new DateTime($def_check['date_end']);
                                    $date_end_next=($date_end1->getTimestamp())+$addDay;
                                    do{
                                    $try=date('Y-m-d', ($date_end_next+$addDay));
                                            $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                    $nextDay = date('w', ($date_end_next+$addDay));
                                    $date_end_next = $date_end_next+$addDay;}
                                    while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                    $date_end_next=date('Y-m-d',$date_end_next);
                                    $date_now1= new DateTime($current['date_filed']);
                                    // $date_now1->modify('+1 day');
                                    $interval = $date_now1->diff($date_end1);
                                    $days = $interval->days;
                                    $period = new DatePeriod($date_end1, new DateInterval('P1D'), $date_now1);
                                    foreach($period as $dt) {
                                    $curr = $dt->format('D');
                                    $holiday= $con->myQuery("SELECT holiday_date FROM holidays WHERE holiday_date=?",array($dt->format('Y-m-d')))->fetchAll(PDO::FETCH_ASSOC);
                                    // substract if Saturday or Sunday
                                    if ($curr == 'Sat' || $curr == 'Sun') {
                                        $days--;
                                        }
                                    // (optional) for the updated question
                                    elseif (!empty($holiday)) {
                                        $days--;
                                        }
                                    }
                                    if($days=='0'){$days='1';}
                                    $hours=$days*8;
                                    $con->myQuery("UPDATE project_phase_dates SET status_id='2',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($def_check['date_end'],$current['project_id'],$current['project_phase_id']));
                                    if($current['project_phase_id']=='2'){
                                     $phase_check=$con->myQuery("SELECT id FROM  project_phase_dates WHERE project_id=? AND project_phase_id='3'",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);
                                     if(empty($phase_check)){
                                        $current1=$con->myQuery("SELECT * FROM  projects WHERE id=?",array($current['project_id']))->fetch(PDO::FETCH_ASSOC);

                                             $con->myQuery("INSERT INTO project_development (project_id,employee_id,team_lead_id,manager_id,admin_id,type,request_status_id,date_filed, phase_request_id) VALUES(?,?,?,?,?,'admin','1','$date_now',?)",array($current1['id'],$current1['employee_id'],$current1['team_lead_dev'],$current1['manager_id'],$current1['employee_id'],$current['id']));

                                     }else{
                                    $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    if($stat_check['status_id']=='3'){
                                        $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                         $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    }
                                    if($current['project_phase_id']=='8'){
                                            $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                        }else{
                                            $con->myQuery("UPDATE projects SET project_status_id=?,cur_phase=? WHERE id=?",array($stat_check['status_id'],$next_phase,$current['project_id']));
                                        }
                                    $con->myQuery("INSERT INTO project_deficit (project_id,project_phase_id,date_start,date_end,in_hours,in_days,done_days,done_hours) VALUES(?,?,'$date_end_next',?,'$hours','$days','$days','$hours')",array($current['project_id'],$current['project_phase_id'],$current['date_filed']));
                                    }
                                    }else{
                                        $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                        if($stat_check['status_id']=='3'){
                                            $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                             $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                        }
                                        if($current['project_phase_id']=='8'){
                                                $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                            }else{
                                                $con->myQuery("UPDATE projects SET project_status_id=?,cur_phase=? WHERE id=?",array($stat_check['status_id'],$next_phase,$current['project_id']));
                                            }
                                        $con->myQuery("INSERT INTO project_deficit (project_id,project_phase_id,date_start,date_end,in_hours,in_days,done_days,done_hours) VALUES(?,?,'$date_end_next',?,'$hours','$days','$days','$hours')",array($current['project_id'],$current['project_phase_id'],$current['date_filed']));
                                    }
                                
                                }elseif(($def_check['status_id']=='4')&&($def_check['in_deficit']=='1')){
                                $date_start1= new DateTime($def_check_start['date_start']);
                                $date_now1= new DateTime($current['date_filed']);
                                // $date_now1->modify('+1 day');
                                $interval = $date_now1->diff($date_start1);
                                $days = $interval->days;
                                $period = new DatePeriod($date_start1, new DateInterval('P1D'), $date_now1);
                                foreach($period as $dt) {
                                $curr = $dt->format('D');
                                $holiday= $con->myQuery("SELECT holiday_date FROM holidays WHERE holiday_date=?",array($dt->format('Y-m-d')))->fetchAll(PDO::FETCH_ASSOC);
                                // substract if Saturday or Sunday
                                if ($curr == 'Sat' || $curr == 'Sun') {
                                    $days--;
                                    }
                                // (optional) for the updated question
                                elseif (!empty($holiday)) {
                                    $days--;
                                    }
                                }
                                if($days=='0'){$days='1';}
                                $hours=$days*8;
                                $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                $con->myQuery("UPDATE project_phase_dates SET status_id='2',in_deficit='0' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$current['project_phase_id']));
                                if(!empty($stat_check)){
                                    $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                }
                                if($current['project_phase_id']=='8'){
                                        $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                    }else{
                                        $con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$current['project_id']));
                                    }
                                $con->myQuery("UPDATE project_deficit SET done_days='$days',done_hours='$hours',date_end=? WHERE done_days='0' AND done_hours='0' AND project_id=? AND project_phase_id=?",array($current['date_filed'],$current['project_id'],$current['project_phase_id']));
                            }elseif(($def_check['status_id']=='0')&&($def_check['in_deficit']=='1')){
                                $date_end_check=(new DateTime($def_check['date_end']))->getTimestamp();
                                if($date_end_check<$date_now){
                                    $date_end1= new DateTime($def_check['date_end']);
                                    $date_end_next=($date_end1->getTimestamp())+$addDay;
                                    do{
                                    $try=date('Y-m-d', ($date_end_next+$addDay));
                                            $holiday= $con->myQuery("SELECT holiday_name, holiday_category FROM holidays WHERE holiday_date=?", array($try))->fetch(PDO::FETCH_ASSOC);
                                    $nextDay = date('w', ($date_end_next+$addDay));
                                    $date_end_next = $date_end_next+$addDay;}
                                    while($nextDay == 0 || $nextDay == 6 || !empty($holiday));
                                    $date_end_next=date('Y-m-d',$date_end_next);
                                    $date_now1= new DateTime($date_applied);
                                    // $date_now1->modify('+1 day');
                                    $interval = $date_now1->diff($date_end1);
                                    $days = $interval->days;
                                    $period = new DatePeriod($date_end1, new DateInterval('P1D'), $date_now1);
                                    foreach($period as $dt) {
                                    $curr = $dt->format('D');
                                    $holiday= $con->myQuery("SELECT holiday_date FROM holidays WHERE holiday_date=?",array($dt->format('Y-m-d')))->fetchAll(PDO::FETCH_ASSOC);
                                    // substract if Saturday or Sunday
                                    if ($curr == 'Sat' || $curr == 'Sun') {
                                        $days--;
                                        }
                                    // (optional) for the updated question
                                    elseif (!empty($holiday)) {
                                        $days--;
                                        }
                                    }
                                    if($days=='0'){$days='1';}
                                    $hours=$days*8;
                                    $con->myQuery("UPDATE project_phase_dates SET status_id='2',in_deficit='0',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($def_check['date_end'],$current['project_id'],$current['project_phase_id']));
                                    $stat_check=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=? AND status_id='3'",array($current['project_id'],$next_phase))->fetch(PDO::FETCH_ASSOC);
                                    if(!empty($stat_check)){
                                        $con->myQuery("UPDATE project_phase_dates SET status_id='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$next_phase));
                                    }
                                        if($current['project_phase_id']=='8'){
                                            $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                        }else{
                                            $con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$current['project_id']));
                                        }
                                    $con->myQuery("UPDATE project_deficit SET done_days='$days',done_hours='$hours',date_start=?,date_end='$date_applied' WHERE done_days='0' AND done_hours='0' AND project_id=? AND project_phase_id=?",array($date_end_next,$current['project_id'],$current['project_phase_id']));
                                }else{
                                     if($current['project_phase_id']=='8'){
                                            $con->myQuery("UPDATE projects SET project_status_id='2',cur_phase='8' WHERE id=?",array($current['project_id']));
                                        }else{
                                            $con->myQuery("UPDATE projects SET project_status_id='1',cur_phase=? WHERE id=?",array($next_phase,$current['project_id']));
                                        }
                                     $con->myQuery("UPDATE project_phase_dates SET status_id='2',in_deficit='0',temp_date_end=? WHERE project_id=? AND project_phase_id=?",array($current['date_filed'],$current['project_id'],$current['project_phase_id']));
                                      $con->myQuery("UPDATE project_deficit SET date_end=? WHERE project_id=? AND project_phase_id=?",array($current['date_filed'],$current['project_id'],$current['project_phase_id']));
                                }
                            }
                    }
                }else{#rev
                            $hours=$current['hours'];
                            $days=($hours/8);
                            if (is_float($days)){
                                $days=floor($days)+1;
                            }else{
                                $days=$days;
                            }
                            $date_end=$con->myQuery("SELECT temp_date_end,date_end FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$prev_phase))->fetch(PDO::FETCH_ASSOC);
                            $prev_temp_date_end=(new DateTime($date_end['temp_date_end']))->getTimestamp();;
                            $prev_date_end=(new DateTime($date_end['date_end']))->getTimestamp();;
                            $date_now1=(new DateTime($date_now))->getTimestamp();
                        if(($prev_temp_date_end<=$prev_date_end)&&($prev_temp_date_end>$date_now1)){
                        $con->myQuery("UPDATE project_phase_dates SET status_id='1',in_deficit='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$prev_phase));
                      }else{
                        $con->myQuery("UPDATE project_phase_dates SET status_id='4',in_deficit='1' WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$prev_phase));
                        $con->myQuery("UPDATE projects SET project_status_id='4',cur_phase=? WHERE id=?",array($prev_phase,$current['project_id']));
                        $con->myQuery("INSERT INTO project_deficit (project_id,project_phase_id,date_start,in_hours,in_days) VALUES(?,?,?,'$hours','$days')",array($current['project_id'],$prev_phase,$current['date_filed']));
                      }
                    }
                    $con->myQuery("UPDATE project_phase_request SET request_status_id = 2, date_approved=? WHERE id=?",array($date_now,$inputs['id']));
                    $con->myQuery("UPDATE project_files SET is_approved = 1 WHERE phase_request_id=?",array($current['id']));
                    }
                }
                    break;

             #Task Management Approval

                case 'task_management_approval':
                // var_dump($inputs);
                // die;
                    foreach ($records as $inputs) {
                        // $audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                       $current=$con->myQuery("SELECT * FROM  project_task WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $next_phase=$current['project_phase_id']+1;
                    $prev_phase=$current['project_phase_id'];
                $date = (new DateTime())->getTimestamp();
                $date_now=date('Y-m-d',$date);
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                    $phase_stat=$con->myQuery("SELECT status_id FROM project_phase_dates WHERE project_id=? AND project_phase_id=?",array($current['project_id'],$current['project_phase_id']))->fetch(PDO::FETCH_ASSOC);
                    $params1=array(
                    "employee"=>$current['employee_id'],
                    "project_id"=>$current['project_id'],
                    "phase_id"=>$current['project_phase_id'],
                    "date_start"=>$current['date_start'],
                    "date_end"=>$current['date_end'],
                    "manager_id"=>$current['manager_id'],
                    "w"=>$current['worked_done'],
                    "stats"=>$phase_stat['status_id']
                    );
                    $con->myQuery("INSERT INTO
                                project_task_list(
                                    employee_id,
                                    project_id,
                                    project_phase_id,
                                    date_start,
                                    date_end,
                                    status_id,
                                    manager_id,
                                    worked_done
                                ) VALUES(
                                    :employee,
                                    :project_id,
                                    :phase_id,
                                    DATE_FORMAT(:date_start,'%Y-%m-%d'),
                                    DATE_FORMAT(:date_end,'%Y-%m-%d'),
                                    :stats,
                                    :manager_id,
                                    :w
                                )",$params1);
                    $con->myQuery("UPDATE project_task SET request_status_id = 2, date_approved=? WHERE id=?",array($date_now,$inputs['id']));
                    }
                    break;

             #Task Completion Approval

                case 'task_completion_approval':
                // var_dump($inputs);
                // die;
                    foreach ($records as $inputs) {
                        // $audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                       $current=$con->myQuery("SELECT * FROM  project_task_completion WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $next_phase=$current['project_phase_id']+1;
                    $prev_phase=$current['project_phase_id'];
                $date = (new DateTime())->getTimestamp();
                $date_now=date('Y-m-d',$date);
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                    $con->myQuery("UPDATE project_task_completion SET request_status_id = 2, date_approved=? WHERE id=?",array($date_now,$inputs['id']));
                    $con->myQuery("UPDATE project_task_list SET status_id = 2, date_finished=?, work_done=? WHERE id=?",array($current['date_filed'],$current['worked_done'],$current['task_list_id']));
                    $con->myQuery("UPDATE project_files SET is_approved = 1 WHERE task_completion_id=?",array($inputs['id']));
                    }
                    break;

            #Task Completion Approval

                case 'project_application_approval':
                // var_dump($inputs);
                // die;
                    foreach ($records as $inputs) {
                        // $audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                       $current=$con->myQuery("SELECT * FROM  project_task_completion WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                    $next_phase=$current['project_phase_id']+1;
                    $prev_phase=$current['project_phase_id'];
                $date = (new DateTime())->getTimestamp();
                $date_now=date('Y-m-d',$date);
                // $audit_message="From {$audit_details['orig_time_in']}-{$audit_details['orig_time_out']} to {$audit_details['adj_time_in']}-{$audit_details['adj_time_out']}.";
                    $con->myQuery("UPDATE project_task_completion SET request_status_id = 2, date_approved=? WHERE id=?",array($date_now,$inputs['id']));
                    $con->myQuery("UPDATE project_task_list SET status_id = 2, date_finished=?, work_done=? WHERE id=?",array($current['date_filed'],$current['worked_done'],$current['task_list_id']));
                    $con->myQuery("UPDATE project_files SET is_approved = 1 WHERE task_completion_id=?",array($inputs['id']));
                    }
                    break;
        #LEAVE
                case 'leave':
                    foreach ($records as $inputs) {
                        $audit_details=$con->myQuery("SELECT employee_name,leave_type,date_start,date_end,reason,status,step_name FROM vw_employees_leave WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                        $current=$con->myQuery("SELECT id,request_status_id,approval_step_id,employee_id,comment FROM employees_leaves WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                        if (empty($audit_details['leave_type'])) {
                            $audit_details['leave_type']="Leave Without Pay";
                        }
                        // die;
                        /*
                        Get Next step if exists if empty set status to approved 2
                         */
                        $next_step=getNextStep($current['approval_step_id'], $current['id'], 'leave');

                        if (empty($next_step)) {
                            $status=2;
                              try {
                                $con->beginTransaction();
                                $con->myQuery("UPDATE employees_leaves SET request_status_id ={$status} WHERE id=?",array( $inputs['id'] ));
                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                Alert("Approved Leave!", "success");
                                $con->commit();
                                $hd="";
                                if (!empty($current['comment'] || $current['comment'] !== "")) {
                                    $hd=$current['comment'];
                                }
                                $employee_leave=$con->myQuery("SELECT id,employee_id,balance_per_year FROM employees_available_leaves WHERE leave_id=? AND employee_id=? AND is_cancelled=0 AND is_deleted=0 ", array($inputs['leave_id'],$inputs['emp_id']))->fetch(PDO::FETCH_ASSOC);

                                if (!empty($employee_leave)) {
                                    #WITH PAY
                                    $leave_balance=$employee_leave['balance_per_year'];
                                    if ($hd !== "") {
                                        $less=0.5;
                                        $leave_deduct=$leave_balance-$less;
                                        $remark='L-HD-'.$hd;
                                    } else {
                                        $remark='L';
                                        $leave_balance=$employee_leave['balance_per_year'];
                                        $leave_deduct=0;

                                        $datetime2 = new DateTime($inputs['date_end']);
                                        $datetime1 = new DateTime($inputs['date_start']);
                                        $woweekends = 0;

                                        if ($datetime1==$datetime2) {
                                            $woweekends=1;
                                        } else {
                                            $interval = $datetime1->diff($datetime2);
                                            for ($i=0; $i<=$interval->d; $i++) {
                                                $modif = $datetime1->modify('+1 day');
                                                $weekday = $datetime1->format('w');
                                                if ($weekday != 0 && $weekday != 1) { # 0=Sunday and 6=Saturday
                                                    $woweekends+=1;
                                                }
                                            }
                                        }
                                        $leave_deduct=$leave_balance-$woweekends;
                                    }

                                    $if_with_pay=$con->myQuery("SELECT el.id FROM employees_leaves el INNER JOIN LEAVES l ON l.id=el.leave_id WHERE el.id=? AND el.leave_id=? AND l.is_pay=1", array($inputs['id'],$inputs['leave_id']))->fetch(PDO::FETCH_ASSOC);

                                    if (!empty($if_with_pay)) {
                                        if ($leave_deduct>=0) {
                                            $con->myQuery("UPDATE employees_available_leaves SET balance_per_year=? WHERE leave_id=? AND employee_id=? AND is_cancelled=0", array($leave_deduct,$inputs['leave_id'],$inputs['emp_id']));
                                        } else {
                                            #WITHOUT PAY
                                            $remark='A';
                                        }
                                    } else {
                                        #WITHOUT PAY
                                        $remark='A';
                                    }
                                } else {
                                    #WITHOUT PAY
                                    $remark='A';
                                }
                              }
                              catch (Exception $e) {
                                         $con->rollback();
                                         Alert("Save Failed.","danger");
                                         redirect($page);
                                         die;
                                     }
                             }
                             else {
                                  $con->myQuery("UPDATE employees_leaves SET approval_step_id=? WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                  $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                  }
                                  $employees=getEmpDetails($current['employee_id']);
                                  $email_settings=getEmailSettings();


                                    if (empty($next_step)) {
                                      insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s leave ({$audit_details['leave_type']}) request. From {$audit_details['date_start']} To {$audit_details['date_end']}. Status of Request: {$audit_details['status']}");
                                    //var_dump($supervisor);
                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                        $header="Leave Request has been Approved";
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                        $message=email_template($header, $message);

                                        PHPemailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", array($employees['private_email'],$employees['work_email']), "Leave Request (Approved)", $message, $email_settings['host'], $email_settings['port']);
                                    }
                                  }
                                  else {
                                      /*
                                      Email next set of approvers
                                       */
                                      $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                      $header="New Leave Request For Your Approval";
                                      /*
                                      Modify message to be more generic and allow to be sent to multiple people.
                                       */
                                       insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "({$audit_details['step_name']}) Approved {$employees['first_name']} {$employees['last_name']}'s leave ({$audit_details['leave_type']}) request. From {$audit_details['date_start']} To {$audit_details['date_end']}. Status of Request: {$audit_details['status']}");
                                      $message="Good day,<br/> You have a new leave request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
                                      $message=email_template($header,$message);

                                      $recepients=array();
                                      foreach ($approvers as $key => $approver) {
                                          if (!empty($approver['private_email'])) {
                                              $recepients[]=$approver['private_email'];
                                          }
                                          if (!empty($approver['work_email'])) {
                                              $recepients[]=$approver['work_email'];
                                          }
                                      }
                                      /*
                                      Email Recepients
                                       */
                                      PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Leave Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                      /*
                                      Notify request has been approved
                                       */
                                      if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                          $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                          $header="Leave Request has been Approved";
                                          $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                          $message=email_template($header,$message);

                                          PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Leave Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                      }
                                  }

                    }
                    break;
                case 'adjustment':
                    foreach ($records as $inputs) {
                        $audit_details=$con->myQuery("SELECT employee_name,adjustment_reason,adj_date,orig_in_time,orig_out_time,adj_in_time,adj_out_time FROM vw_employees_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                        $current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  employees_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                        if($audit_details['orig_in_time']=="00:00:00"){
                            $audit_message="Add {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']}";
                        }
                        else{

                            $audit_message="From {$audit_details['orig_in_time']}-{$audit_details['orig_out_time']} to {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']}. Adjustment Reason:{$audit_details['adjustment_reason']}";
                        }

                        /*
                        Get Next step if exists if empty set status to approved 2
                         */
                        $next_step=getNextStep($current['approval_step_id'], $current['id'], 'adjustment');
                        if (empty($next_step)) {
                            $status=2;
                            try {
                                    $con->beginTransaction();
                                    $con->myQuery("UPDATE employees_adjustments SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                    $current=$con->myQuery("SELECT adj_date,adj_in_time,adj_out_time,attendance_id,employees_id,adjustment_reason FROM employees_adjustments WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);
                                    if($audit_details['orig_in_time']=="00:00:00" || $audit_details['orig_in_time']==NULL){
                                        $con->myQuery("INSERT INTO attendance (in_time,out_time,employees_id,note) VALUES(:adj_in_time,:adj_out_time,:employees_id,:note)",array("adj_in_time"=>$current['adj_in_time'],"adj_out_time"=>$current['adj_out_time'],"employees_id"=>$current['employees_id'],"note"=>$current['adjustment_reason']));
                                    }
                                    else{
                                        $con->myQuery("UPDATE attendance SET in_time=:adj_in_time,out_time=:adj_out_time,note=:note WHERE id=:attendance_id",array("adj_in_time"=>$current['adj_in_time'],"adj_out_time"=>$current['adj_out_time'],"attendance_id"=>$current['attendance_id'],"note"=>$current['adjustment_reason']));
                                    }

                                    $get_payroll_group_id=$con->myQuery("SELECT payroll_group_id FROM employees WHERE is_deleted =0 AND id=?",array($current['employees_id']))->fetch(PDO::FETCH_ASSOC);

                                    $date = new DateTime();
                                    $date_created=date_format($date, 'Y-m-d');

                                    $adj_out_time           = new DateTime($current['adj_out_time']);
                                    $adj_in_time            = new DateTime($current['adj_in_time']);
                                    $no_of_work_hours       = $adj_out_time->diff($adj_in_time);
                                    $no_of_work_hours       = $no_of_work_hours->h;

                                    $days_per_month = get_salary_settings($get_payroll_group_id['payroll_group_id'])['days_per_month'];
                                    $basic_salary   = get_basic_salary($current['employees_id'])['basic_salary'];
                                    $dailyrate      = ($basic_salary / $days_per_month);
                                    $hourlyrate     = ($dailyrate / 8);
                                    $pa_amount      = ($hourlyrate * $no_of_work_hours);

                                    $param=array(
                                        'emp_id'        =>$current['employees_id'],
                                        'date_created'  =>$date_created,
                                        'date_occur'    =>$current['adj_date'],
                                        'amount'        =>number_format($pa_amount,2),
                                        'reason'        =>'Attendance Adjusment',
                                        'status'        =>'0',
                                        'a_type'        =>'1'
                                    );

                                    $con->myQuery("INSERT INTO payroll_adjustments (employee_id,date_created,date_occur,amount,reason,status,adjustment_type) VALUES (:emp_id,:date_created,:date_occur,:amount,:reason,:status,:a_type)",$param);

                                        //die;
                                    // die;
                                    $con->commit();

                                } catch (Exception $e) {
                                    $con->rollback();
                                    Alert("Save Failed.","danger");
                                    redirect($page);
                                    die;
                                }
                        } else {
                            $con->myQuery("UPDATE employees_adjustments SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                            $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                        }

                        $employees=getEmpDetails($current['employees_id']);
                        $email_settings=getEmailSettings();
                        //var_dump($supervisor);
                        insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"Approved {$employees['first_name']} {$employees['last_name']}'s attendance adjustment request. {$audit_message}");
                        if (empty($next_step)) {
                            /*
                            Notify only the sender
                             */
                            if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                $header="Attendance Adjustment Request has been Approved";
                                $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);

                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Attendance Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                            }
                        } else {
                            /*
                            Email next set of approvers
                             */
                            $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                            $header="New Attendance Adjustment Request For Your Approval";
                            /*
                            Modify message to be more generic and allow to be sent to multiple people.
                             */
                            $message="Good day,<br/> You have a new attendance adjustment request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
                            $message=email_template($header,$message);

                            $recepients=array();
                            foreach ($approvers as $key => $approver) {
                                if (!empty($approver['private_email'])) {
                                    $recepients[]=$approver['private_email'];
                                }
                                if (!empty($approver['work_email'])) {
                                    $recepients[]=$approver['work_email'];
                                }
                            }
                            /*
                            Email Recepients
                             */
                            PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Attendance Adjustment Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                            /*
                            Notify request has been approved
                             */
                            if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                $header="Attendance Adjustment Request has been Approved";
                                $message="Hi {$employees['first_name']},<br/> Your request has been approved by, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                $message=email_template($header,$message);

                                PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Attendance Adjustment Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                            }
                        }
                    }
                    break;
                case 'official_business':
                    foreach ($records as $inputs) {

                    $audit_details=$con->myQuery("SELECT employee_name,destination,purpose,ob_date,time_from,time_to FROM vw_employees_ob WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                    $audit_message="Destination: {$audit_details['destination']}. Purpose: {$audit_details['purpose']} during ".date("Y-m-d",strtotime($audit_details['time_from']))." - ".date("Y-m-d",strtotime($audit_details['time_to']));

                    $current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                                 $next_step=getNextStep($current['approval_step_id'], $current['id'], 'official_business');

                                    // var_dump($current);
                                    // die;


                                if (empty($next_step)) {

                                     $status=2;

                                    $con->myQuery("UPDATE {$table} SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));


                                    $employees=getEmpDetails($current['employees_id']);
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $email_settings=getEmailSettings();
                                    //var_dump($supervisor);

                                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s official business request. {$audit_message}");

                                    if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
                                        $header="Official Business Request has been Approved";
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Official Business Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    }
                                    // var_dump($current);
                                    // die;

                               }
                                else {





                                    // var_dump($next_step);
                                    // die;
                                    $con->myQuery("UPDATE {$table} SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));



                                    $employees=getEmpDetails($current['employees_id']);
                                    $email_settings=getEmailSettings();

                                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Approved {$employees['first_name']} {$employees['last_name']}'s official business request. {$audit_message}");
                                }




                                if (empty($next_step)) {
                                    /*
                                    Notify only the sender
                                     */
                                    // var_dump($next_step);
                                    // die;
                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                        $header="Official Business Request has been Approved";
                                        $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Official Business Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    }
                                } else {
                                    /*
                                    Email next set of approvers
                                     */
                                    $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                    $header="New Official Business Request For Your Approval";
                                    /*
                                    Modify message to be more generic and allow to be sent to multiple people.
                                     */
                                    $message="Good day,<br/> You have a new offical business request from {$employees['last_name']}, {$employees['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    $recepients=array();
                                    foreach ($approvers as $key => $approver) {
                                        if (!empty($approver['private_email'])) {
                                            $recepients[]=$approver['private_email'];
                                        }
                                        if (!empty($approver['work_email'])) {
                                            $recepients[]=$approver['work_email'];
                                        }
                                    }
                                    /*
                                    Email Recepients
                                     */
                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Official Business Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                    /*
                                    Notify request has been approved
                                     */
                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                        $supervisor=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                        $header="Official Busines Request has been Approved";
                                        
                                       $message="Hi {$employees['first_name']},<br/> Your request has been approved by {$supervisor['first_name']} {$supervisor['last_name']}. For more details please login to the Secret 6 HRIS.";

                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Official Business Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    }
                                }




                    }
                    break;
                case 'ot_approval':
                    // echo "<pre>";
                    // print_r($records);
                    // echo "</pre>";

                    foreach ($records as $inputs) {
                        $inputs['action']="approve";

                        if ($inputs['overtime_type'] == "OT Claim") {
                            // echo "<pre>";
                            // print_r($records);
                            // echo "</pre>";
                            $audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                            $current=$con->myQuery("SELECT status,supervisor_id,final_approver_id,employee_id FROM  vw_employees_ot WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                            switch ($current['status']) {
                                case 'Supervisor Approval':
                                    switch ($inputs['action']) {
                                        case 'approve':
                                                $con->myQuery("UPDATE employees_ot SET status ='Final Approver Approval',reason='',supervisor_date_action=NOW() WHERE id=?", array($inputs['id']));
                                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                                $supervisor=getEmpDetails($current['supervisor_id']);
                                                $final_approver=getEmpDetails($current['final_approver_id']);
                                                $employees=getEmpDetails($current['employee_id']);
                                                $email_settings=getEmailSettings();
                                                //var_dump($supervisor);

                                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "(Supervisor) Approved {$employees['first_name']} {$employees['last_name']}'s overtime request. From {$audit_details['time_from']} To {$audit_details['time_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");

                                                if ((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)) {
                                                    $header="Overtime Request Approved by Supervisor";
                                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                                    $message=email_template($header, $message);

                                                    emailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($employees['private_email'],$employees['work_email'])), "Overtime Request (For Approval)", $message, $email_settings['host'], $email_settings['port']);

                                                    if (!empty($final_approver['private_email']) || !empty($final_approver['work_email'])) {
                                                        $header="New Overtime Request For Your Approval";
                                                        $message="Hi {$final_approver['first_name']},<br/> You have a new overtime request from {$employees['first_name']} {$employees['last_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                                        $message=email_template($header, $message);
                                                    // var_dump($email_settings);
                                                     //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                                    emailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($final_approver['private_email'],$final_approver['work_email'])), "Overtime Request (For Approval)", $message, $email_settings['host'], $email_settings['port']);
                                                    }
                                                }
                                                // die;
                                            break;
                                        case 'reject':
                                        $required_fieds=array(
                                            "reason"=>"Enter Reason for rejection. <br/>"
                                            );
                                            if (validate($required_fieds)) {
                                                $con->myQuery("UPDATE employees_ot SET status ='Rejected (Supervisor)',reason=?,supervisor_date_action=NOW() WHERE id=?", array($inputs['reason'],$inputs['id']));
                                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                                $supervisor=getEmpDetails($current['supervisor_id']);
                                                $employees=getEmpDetails($current['employee_id']);
                                                $email_settings=getEmailSettings();

                                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "(Supervisor) Rejected {$employees['first_name']} {$employees['last_name']}'s overtime request. The reason given is '{$inputs['reason']}. From {$audit_details['date_from']} To {$audit_details['date_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");

                                                //var_dump($supervisor);
                                                if ((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)) {
                                                    $header="Overtime Request Rejected by Supervisor";
                                                    $message="Hi {$employees['first_name']},<br/> Your request has been rejected by your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                                    $message=email_template($header, $message);
                                                    // var_dump($email_settings);
                                                     //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                                    emailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($employees['private_email'],$employees['work_email'])), "Overtime Request (Rejected)", $message, $email_settings['host'], $email_settings['port']);
                                                }
                                            }
                                            break;
                                    }
                                    break;
                                case 'Final Approver Approval':
                                    switch ($inputs['action']) {
                                        case 'approve':
                                                $con->myQuery("UPDATE employees_ot SET status ='Approved',reason='',ot_approver_date_action=NOW() WHERE id=?", array($inputs['id']));
                                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                                $supervisor=getEmpDetails($current['supervisor_id']);
                                                $employees=getEmpDetails($current['employee_id']);
                                                $final_approver=getEmpDetails($current['final_approver_id']);
                                                $email_settings=getEmailSettings();
                                                //var_dump($supervisor);
                                                if ((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)) {
                                                    $header="Overtime Request has been Approved";
                                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                                    $message=email_template($header, $message);
                                                    // var_dump($email_settings);
                                                     //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                                    emailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($employees['private_email'],$employees['work_email'])), "Overtime Request (Approved)", $message, $email_settings['host'], $email_settings['port']);
                                                }
                                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s overtime request. From {$audit_details['time_from']} To {$audit_details['time_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");
                                            break;
                                        case 'reject':
                                        $required_fieds=array(
                                            "reason"=>"Enter Reason for rejection. <br/>"
                                            );
                                            if (validate($required_fieds)) {
                                                $con->myQuery("UPDATE employees_ot SET status ='Rejected (Final Approver)',reason=?,ot_approver_date_action=NOW() WHERE id=?", array($inputs['reason'],$inputs['id']));
                                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));
                                                $supervisor=getEmpDetails($current['supervisor_id']);
                                                $employees=getEmpDetails($current['employee_id']);
                                                $final_approver=getEmpDetails($current['final_approver_id']);
                                                $email_settings=getEmailSettings();

                                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "(Final Approver) Rejected {$employees['first_name']} {$employees['last_name']}'s overtime request. The reason given is '{$inputs['reason']}'. From {$audit_details['date_from']} To {$audit_details['date_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");
                                                //var_dump($supervisor);
                                                if ((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)) {
                                                    $header="Overtime Request Rejected by Final Approver";
                                                    $message="Hi {$employees['first_name']},<br/> Your request has been rejected by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. The reason given is '{$inputs['reason']}'.  For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                                    $message=email_template($header, $message);
                                                    // var_dump($email_settings);
                                                     //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                                    emailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($employees['private_email'],$employees['work_email'])), "Overtime Request (Rejected)", $message, $email_settings['host'], $email_settings['port']);
                                                }
                                            }
                                            break;
                                    }
                                    break;
                            }




                        } else if ($inputs['overtime_type'] == "Pre-approval OT") {
                    //         echo "<pre>";
                    // print_r($records);
                    // echo "</pre>";
                            $audit_details=$con->myQuery("SELECT employee_name,ot_date,time_from,time_to,worked_done,no_hours FROM vw_employees_ot_pre WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                            $current=$con->myQuery("SELECT status,supervisor_id,employee_id FROM  vw_employees_ot_pre WHERE id=?", array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                            switch ($current['status']) {
                                case 'Supervisor Approval':
                                    switch ($inputs['action']) {
                                        case 'approve':
                                                $con->myQuery("UPDATE employees_ot_pre SET status ='Approved',supervisor_date_action=NOW() WHERE id=?", array($inputs['id']));
                                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                                $supervisor=getEmpDetails($current['supervisor_id']);
                                                $employees=getEmpDetails($current['employee_id']);
                                                $email_settings=getEmailSettings();
                                                //var_dump($supervisor);
                                                if ((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)) {
                                                    $header="Overtime Request has been Approved";
                                                    $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$supervisor['last_name']} {$supervisor['first_name']}. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                                    $message=email_template($header, $message);
                                                    // var_dump($email_settings);
                                                     //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                                    emailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($employees['private_email'],$employees['work_email'])), "Overtime Request (Approved)", $message, $email_settings['host'], $email_settings['port']);
                                                }
                                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s overtime request. From {$audit_details['time_from']} To {$audit_details['time_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");

                                                // die;
                                            break;
                                        case 'reject':
                                        $required_fieds=array(
                                            "reason"=>"Enter Reason for rejection. <br/>"
                                            );
                                            if (validate($required_fieds)) {
                                                $con->myQuery("UPDATE employees_ot_pre SET status ='Rejected (Supervisor)',reason=?,supervisor_date_action=NOW() WHERE id=?", array($inputs['reason'],$inputs['id']));
                                                $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                                $supervisor=getEmpDetails($current['supervisor_id']);
                                                $employees=getEmpDetails($current['employee_id']);
                                                $email_settings=getEmailSettings();

                                                insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'], "(Supervisor) Rejected {$employees['first_name']} {$employees['last_name']}'s overtime request. The reason given is '{$inputs['reason']}. From {$audit_details['date_from']} To {$audit_details['date_to']} for {$audit_details['no_hours']} Hours. Worked to be done:{$audit_details['worked_done']}");

                                                //var_dump($supervisor);
                                                if ((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)) {
                                                    $header="Overtime Request Rejected by Supervisor";
                                                    $message="Hi {$employees['first_name']},<br/> Your request has been rejected by your supervisor, {$supervisor['first_name']} {$supervisor['last_name']}. The reason given is '{$inputs['reason']}'. For more details please login to the Spark Global Tech Systems Inc HRIS.";
                                                    $message=email_template($header, $message);
                                                    // var_dump($email_settings);
                                                     //emailer($username,$password,$from,$to,$subject,$body,$host='tls://smtp.gmail.com',$port=465
                                                    emailer($email_settings['username'], decryptIt($email_settings['password']), "info@hris.com", implode(",", array($employees['private_email'],$employees['work_email'])), "Overtime Request (Rejected)", $message, $email_settings['host'], $email_settings['port']);
                                                }
                                            }
                                            break;
                                    }
                                    break;
                            }

                        }
                    }
                    break;
                case 'shift':
                    foreach ($records as $inputs) {
                        $audit_details=$con->myQuery("SELECT employee_name,orig_in_time,orig_out_time,adj_in_time,adj_out_time,date_from,date_to FROM vw_employees_change_shift WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);

                        $audit_message="From {$audit_details['orig_in_time']}-{$audit_details['orig_out_time']} to {$audit_details['adj_in_time']}-{$audit_details['adj_out_time']} during ".date("Y-m-d",strtotime($audit_details['date_from']))." - ".date("Y-m-d",strtotime($audit_details['date_to']));



                        $current=$con->myQuery("SELECT id,employees_id as employees_id,request_status_id,approval_step_id FROM  {$table} WHERE id=?",array($inputs['id']))->fetch(PDO::FETCH_ASSOC);




                                $next_step=getNextStep($current['approval_step_id'], $current['id'], 'shift');

                                //die;
                                if (empty($next_step)) {

                                    $status=2;
                                    $con->myQuery("UPDATE {$table} SET request_status_id ={$status},reason='' WHERE id=?",array( $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                    $employees=getEmpDetails($current['employees_id']);
                                    $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                    $email_settings=getEmailSettings();
                                    //var_dump($supervisor);

                                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Final Approver) Approved {$employees['first_name']} {$employees['last_name']}'s change shift request. {$audit_message}");


                                    // if((!empty($supervisor['private_email']) || !empty($supervisor['work_email'])) && !empty($email_settings)){
                                    //     $header="Change Shift Request has been Approved";
                                    //     $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                    //     $message=email_template($header,$message);

                                    //     PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Change Shift Request (Approved)",$message,$email_settings['host'],$email_settings['port']);


                                    // }
                                    // var_dump($current);
                                    // die;

                               }
                                else {





                                    // var_dump($next_step);
                                    // die;
                                    $con->myQuery("UPDATE {$table} SET approval_step_id=?,reason='' WHERE id=?",array($next_step['approval_step_id'], $inputs['id'] ));
                                    $con->myQuery("UPDATE request_steps SET employee_id ={$approver_id} WHERE request_type=? AND approval_step_id=? AND request_id=?",array( $inputs['type'],$current['approval_step_id'],$inputs['id'] ));

                                    
                                    $employees=getEmpDetails($current['employees_id']);
                                    $email_settings=getEmailSettings();

                                    insertAuditLog($_SESSION[WEBAPP]['user']['last_name'].", ".$_SESSION[WEBAPP]['user']['first_name']." ".$_SESSION[WEBAPP]['user']['middle_name'],"(Supervisor) Approved {$employees['first_name']} {$employees['last_name']}'s change shift request. {$audit_message}");
                                }




                                if (empty($next_step)) {
                                    /*
                                    Notify only the sender
                                     */
                                    // var_dump($next_step);
                                    // die;
                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){

                                        $header="Change Shift Request has been Approved";
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Change Shift Request (Approved)",$message,$email_settings['host'],$email_settings['port']);

                                    }
                                } else {
                                    /*
                                    Email next set of approvers
                                     */
                                    $approvers=getEmployeesFromSteps($next_step['approval_step_id']);
                                    $header="New Change Shift Request For Your Approval";
                                    $message="Hi {$final_approver['first_name']},<br/> You have a new change shift request from {$employees['first_name']} {$employees['last_name']}. For more details please login to the Secret 6 HRIS.";
                                    $message=email_template($header,$message);

                                    $recepients=array();
                                    foreach ($approvers as $key => $approver) {
                                        if (!empty($approver['private_email'])) {
                                            $recepients[]=$approver['private_email'];
                                        }
                                        if (!empty($approver['work_email'])) {
                                            $recepients[]=$approver['work_email'];
                                        }
                                    }
                                    /*
                                    Email Recepients
                                     */
                                    PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",$recepients,"Change Shift Request (For Approval)",$message,$email_settings['host'],$email_settings['port']);
                                    /*
                                    Notify request has been approved
                                     */
                                    if((!empty($employees['private_email']) || !empty($employees['work_email'])) && !empty($email_settings)){
                                        $final_approver=getEmpDetails($_SESSION[WEBAPP]['user']['employee_id']);
                                        $header="Change Shift Request has been Approved";


                                        // $message="Hi {$employees['first_name']},<br/> Your request has been approved by the final approver, {$final_approver['last_name']} {$final_approver['first_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message="Hi {$employees['first_name']},<br/> Your request has been approved by {$final_approver['first_name']} {$final_approver['last_name']}. For more details please login to the Secret 6 HRIS.";
                                        $message=email_template($header,$message);

                                        PHPemailer($email_settings['username'],decryptIt($email_settings['password']),"info@hris.com",array($employees['private_email'],$employees['work_email']),"Change Shift Request (Approved)",$message,$email_settings['host'],$email_settings['port']);
                                    }



                                }

                    }
                break;
        }
        #end switch
            // die;
            Alert("Approved Successfully", "success");
            if ($page=="index.php") {
                //var_dump($_POST);
                die();
            }
            redirect($page);
        } catch (Exception $e) {
            redirect("index.php");
            die($e);
        }


    if (!empty($page)) {
        redirect($page);
    } else {
        redirect('index.php');
        die;
    }
