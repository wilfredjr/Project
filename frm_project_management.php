<?php
	require_once("support/config.php");
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}

  if (empty($_SESSION[WEBAPP]['user']['access_project_management'])) {
    redirect("index.php");
    die;
  }
    // if(!AllowUser(array(1,2)))
    // {
    //     redirect("index.php");
    // }
	
    // $proj_id=$con->myQuery("SELECT 
    // id,
    // employee_id
    // FROM projects
    // WHERE employee_id=?",array($_SESSION[WEBAPP]['user']['employee_id']));

    // $employee=$con->myQuery("SELECT 
    // id,
    // project_id,
    // employee_id,
    // is_deleted,
    
    
    // FROM project_employees
    // WHERE project_id=?",array($_SESSION[WEBAPP]['user']['employee_id']));
  $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
    $project_manager=$con->myQuery("SELECT e.id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name), e.pay_grade_id, pg.can_manage_projects FROM employees e JOIN pay_grade pg ON pg.id=e.pay_grade_id WHERE pg.can_manage_projects=1 AND e.is_deleted=0 AND e.is_terminated=0")->fetchAll(PDO::FETCH_ASSOC);
    $ba=$con->myQuery("SELECT e.id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) as employee_name FROM employees e JOIN departments ON e.department_id=departments.id WHERE e.is_deleted=0 AND e.is_terminated=0 AND e.utype_id='2'")->fetchAll(PDO::FETCH_ASSOC);
        $dev=$con->myQuery("SELECT e.id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) as employee_name FROM employees e JOIN departments ON e.department_id=departments.id WHERE e.is_deleted=0 AND e.is_terminated=0 AND e.utype_id='1'")->fetchAll(PDO::FETCH_ASSOC);
        $manager_name=$con->myQuery("SELECT e.id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) as employee_name FROM employees e WHERE e.id='$employee_id'")->fetch(PDO::FETCH_ASSOC);
    // echo "<pre>";
    // print_r($project_manager);
    // echo "</pre>";


    if(!empty($_GET['id']))
    {
        $data=$con->myQuery("SELECT p.id,p.name,p.description,p.department_id,p.employee_id,DATE_FORMAT(p.start_date,'".DATE_FORMAT_SQL."') as start_date,DATE_FORMAT(p.end_date,'".DATE_FORMAT_SQL."') as end_date,p.date_filed,p.project_status_id, ps.status_name,p.man_hours FROM projects p join project_status ps on p.project_status_id=ps.id WHERE p.id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $project_status=$con->myQuery("SELECT status_name as proj_id, status_name as sta_name FROM project_status");

        $project_employee=$con->myQuery("SELECT employee_id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name)  FROM projects_employees JOIN employees e ON e.id=employee_id WHERE project_id=".$_GET['id'])->fetchAll(PDO::FETCH_ASSOC);

        $project_team_lead_ba=$con->myQuery("SELECT employee_id FROM projects_employees WHERE is_team_lead_ba=1 AND is_deleted=0 AND project_id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

        $project_team_lead_dev=$con->myQuery("SELECT employee_id FROM projects_employees WHERE is_team_lead_dev=1 AND is_deleted=0 AND project_id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

        $project_manager=$con->myQuery("SELECT e.id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name), e.pay_grade_id, pg.can_manage_projects FROM employees e JOIN pay_grade pg ON pg.id=e.pay_grade_id WHERE pg.can_manage_projects=1")->fetchAll(PDO::FETCH_ASSOC);

        $project_manager_selected=$con->myQuery("SELECT employee_id FROM projects_employees WHERE is_manager=1 AND project_id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

        $approver_selected=$con->myQuery("SELECT first_approver_id, second_approver_id, third_approver_id FROM projects WHERE id=".$_GET['id'])->fetch(PDO::FETCH_ASSOC);

  
        
        if(empty($data))
        {
            Modal("Invalid Record Selected");
            redirect("project_management.php");
            die;
        }
    }






    $proj_sta=$con->myQuery("SELECT 
    status_name
    FROM project_status");



    
	makeHead("Application for Project Form");
?>
<style type="text/css">
        table.dataTable.select tbody tr,
        table.dataTable thead th:first-child {
            cursor: pointer;
        }
    </style>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 <div class="content-wrapper">
    <section class="content-header text-center">
        <h1>
             Project Application Form
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-12'>
				<?php	Alert();	?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class='col-md-12'>
                                <form class='form-horizontal' id="frm-example" action='save_project_management1.php' method="POST" enctype="multipart/form-data">
                                    <input type='hidden' name='project_id' class='form-control' id='project_id' value="<?php echo !empty($_GET['id'])?htmlspecialchars($_GET['id']):''?>">
                                    <div class='form-group'>

                                        <label for="proj_name" class="col-sm-2 control-label">Project Name: </label>
                                        <div class='col-sm-9'>
                                            <input type='text' class="form-control" name='proj_name' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label for="date_start" class="col-md-2 control-label">Project Start Date: </label>
                                        <div class="col-md-9">
                                            <input type="text" value='<?php echo !empty($data)?htmlspecialchars($data['start_date']):''; ?>' class="form-control date_picker" id="date_start" name='date_start' required>
                                        </div>
                                    </div> 
                                    <!--   <div class="form-group">
                                        <label for="description" class="col-md-2 control-label">Development <br> Man Hours *</label>
                                        <div class="col-md-9">
                                            <input type='number' class="form-control" data-allow-clear='True' name='man_hours' id='man_hours' value='<?php echo !empty($data)?htmlspecialchars($data['man_hours']):''; ?>'
                                            min='<?php echo !empty($data)?htmlspecialchars($data['man_hours']):'1'; ?>' required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_end" class="col-md-2 control-label">Project Target Date *</label>
                                        <div class="col-md-9">
                                            <input type="text" value='<?php echo !empty($data)?htmlspecialchars($data['end_date']):''; ?>' class="form-control date_picker" id="date_end" name='date_end' disabled>
                                        </div>
                                    </div> -->
                                    <div class="form-group">
                                        <label for="description" class="col-md-2 control-label">Description: </label>
                                        <div class="col-md-9">
                                            <input type='text' class="form-control" data-allow-clear='True' name='description' id='description' value='<?php echo !empty($data)?htmlspecialchars($data['description']):''; ?>' required>
                                        </div>
                                    </div>
                                    
                                    <div class='form-group'>
                                        <label for="status" class="col-sm-2 control-label">Status: </label>
                                        <div class='col-sm-9'>
                                            <select class='form-control cbo' name='status' data-placeholder="Select Status" <?php echo !(empty($data))?"data-selected='".$data['status_name']."'":NULL ?> required

                                             <?php
                                            if (empty($_GET['id'])) { 
                                                echo "disabled>";
                                                echo "<option value='1' selected>On-going</option>" ;  
                                            }  
                                            else {

                                          
                                                        echo ">". makeOptions($project_status);
                                                   
                                              
                                            }

                                                ?> 
                                            
                                               
                                           > </select>
                                        </div>
                                    </div> 
                                    <div class='form-group'>
                                    <label for='manager' class='col-sm-2 control-label'>Manager: </label>
                                        <div class='col-sm-9'>
                                           <select class='form-control cbo' name='manager'  data-placeholder='Select Manager' <?php echo !(empty($data))?"data-selected='".$project_manager_selected['employee_id']."'":NULL ?> required

                                                <?php
                                            if (empty($_GET['id'])) { 
                                                echo "disabled>";
                                                echo "<option value='".$employee_id."' selected>".$manager_name['employee_name']."</option>" ;  
                                            } ?>
                                         
                                            
                                               
                                         > </select>
                                       </div>
                                    
                                    
                                    
                                   
                                    </div> 
                                     <input type='hidden' name='fapprover' class='form-control' id='fapprover' value="">
                                     <input type='hidden' name='sapprover' class='form-control' id='sapprover' value="">
                                     <input type='hidden' name='tapprover' class='form-control' id='tapprover' value="">
                                    <!-- <div class='form-group'>
                                    <label for='fapprovaer' class='col-sm-2 control-label'>First Approver *</label>
                                        <div class='col-sm-9'>
                                           <select class='form-control cbo' name='fapprover' data-allow-clear='True' data-placeholder='Select First Approver' <?php echo !(empty($approver_selected['first_approver_id']))?"data-selected='".$approver_selected['first_approver_id']."'":NULL ?> required>

                                            <?php echo makeOptions($employees); ?>
                
                                          </select>
                                       </div>

                                   
                                    </div> 
                                    <div class='form-group'>
                                    <label for='sapprovaer' class='col-sm-2 control-label'>Second Approver</label>
                                        <div class='col-sm-9'>
                                           <select class='form-control cbo' name='sapprover' data-allow-clear='True' data-placeholder='Select Second Approver' <?php echo !(empty($approver_selected['second_approver_id']))?"data-selected='".$approver_selected['second_approver_id']."'":NULL ?>>

                                            <?php echo makeOptions($employees); ?>
                
                                          </select>
                                       </div>

                                   
                                    </div> 
                                     <div class='form-group'>
                                    <label for='tapprovaer' class='col-sm-2 control-label'>Third Approver</label>
                                        <div class='col-sm-9'>
                                           <select class='form-control cbo' name='tapprover' data-allow-clear='True' data-placeholder='Select Third Approver' <?php echo !(empty($approver_selected['third_approver_id']))?"data-selected='".$approver_selected['third_approver_id']."'":NULL ?>>

                                            <?php echo makeOptions($employees); ?>
                
                                          </select>
                                       </div>

                                   
                                    </div> -->
                                   
                                    <div class='form-group'>
                                    <label for='team_lead' class='col-sm-2 control-label'>Team Leader (BA): </label>
                                        <div class='col-sm-9'>
                                            <select class='form-control cbo' name='team_lead_ba' data-allow-clear='True' data-placeholder='Select Team Lead (BA)'  <?php echo !(empty($data))?"data-selected='".$project_team_lead_ba['employee_id']."'":NULL ?> required>
                                               <?php echo makeOptions($ba); ?>   
                                          </select>
                                        </div>
                                    </div> 
                                     <div class='form-group'>
                                    <label for='team_lead' class='col-sm-2 control-label'>Team Leader (Dev): </label>
                                        <div class='col-sm-9'>
                                            <select class='form-control cbo' name='team_lead_dev' data-allow-clear='True' data-placeholder='Select Team Lead (DEV)'  <?php echo !(empty($data))?"data-selected='".$project_team_lead_dev['employee_id']."'":NULL ?> required>
                                               <?php echo makeOptions($dev); ?>   
                                          </select>
                                        </div>
                                    </div>
                                  <div class="form-group">
                                        <label for="purpose" class="col-sm-2 control-label">Upload File: <br/> <small>Upload Limit: <?php echo ini_get('upload_max_filesize')."B";?> </small></label>
                                        <div class="col-sm-9">
                                          <input type='file' name='file' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true' required>
                                    </div>
                                </div>
                                    <!--Project  Phases Start-->
                                    <?php if(!empty($_GET['id'])){?>
                                    <section class="content-header text-center">
                                        <h1>
                                             Project Phases
                                        </h1>
                                    </section>
                                        <section class="content">
                                        <div class="row">
                                            <div class='col-md-12'>
                                                <?php   Alert();    ?>
                                                <div class="box box-primary">
                                                    <div class="box-body">
                                                        <div class="row">
                                                            <div class='col-md-12'>
                                                                <table id='ResultTable' class='table table-bordered table-striped'>
                                                                  <thead>
                                                                    <tr>
                                                                      <!-- <th class='text-center'>Type of OT</th> -->
                                                                      <th class='text-center'></th>
                                                                      <th class='text-center'>Phase Name</th>
                                                                      <th class='text-center'>Date Start</th>
                                                                      <th class='text-center'>Date End</th>
                                                                      <th class='text-center'>Status</th>
                                                                     <!--  <th class='text-center'>Deficit Days</th> -->
                                                                    </tr>
                                                                  </thead>
                                                                  <tbody>
                                                                  </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                    <?php } ?>
                                    <!--Project Phases End-->
                                    <div class="form-group">
                                        <div class="col-sm-9 col-md-offset-2 text-center">
                                            <button type='submit' class='btn btn-warning'>Save </button>
                                            <a href='project_management.php' class='btn btn-default' onclick="return confirm('Are you sure you want to Cancel?')">Cancel</a>
                                        </div>
                                    </div>
                      <!--               <div class='panel-body ' >
                                    <table class='table table-bordered table-condensed table-hover display select' id='ResultTable'>

                                        <table id="example" class="table table-bordered table-condensed table-hover display select" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th><input name="select_all" value="1" type="checkbox"></th>
                                                    <th class='text-center'>Code</th>
                                                    <th class='text-center'>Employee Name</th>
                                                    <th class='text-center'>Department</th>
                                                    <th class='text-center'>Job Title</th>
                                                    <th class='text-center'>Status</th>
                                                   
                                                </tr>
                                            </thead>
                                            
                                        </table>
                                    </div> -->
                                            
                 
                                </form>	
                        </div>
                    </div>
                </div>
            </div>
        </div>

</p>

</div>

<script type="text/javascript">
    
        // $(function () {
        //  data_table=$('#ResultTable').DataTable({
        //      "processing": true,
        //      "serverSide": true,
        //      "searching": false,
        //      "ajax":{
        //          "url":"ajax/shifting_sched.php",
        //          "data":function(s){
        //              s.emp_code=$("input[name='emp_code']").val();
        //              s.emp_name=$("select[name='emp_name']").val();
        //              s.department=$("select[name='dept']").val();
        //              s.job_title=$("select[name='job_title']").val();
        //          }
        //      },
        //      "oLanguage": { "sEmptyTable": "No employees found." }


        //  });
        // });



        function filter_search() 
        {
            //table.draw();
            dttable.ajax.reload();

        }

  var dttable="";
$(document).ready(function ()
{
    dttable=$('#ResultTable').DataTable({
        "scrollX": true,
        "processing": true,
        "serverSide": true,
        "searching": false,
        "ajax":
              {    
                "url":"ajax/project_timeline_dates.php",
                "data":function(d)
                {
                     d.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
                }
     
            
            },
        "order": [[ 0, "asc" ]]
    });
});
</script>
<?php
    makeFoot();
?>