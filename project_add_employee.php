<?php
 require_once("support/config.php");
if(!isLoggedIn()){
  toLogin();
  die();

$employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
$project_id=$_GET['id'];
$manage=AccessForProject($project_id, $employee_id);

  // if($manage['is_team_lead']=='0' && $manage['is_manager']=='0'){
  //   redirect("my_projects.php");
  // }
}
  $employees=$con->myQuery("SELECT e.id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) as employee_name FROM employees e JOIN departments ON e.department_id=departments.id WHERE e.is_deleted=0 AND e.is_terminated=0 AND (e.utype_id='1' OR e.utype_id='2')")->fetchAll(PDO::FETCH_ASSOC);
  $proj_req=$con->myQuery("SELECT pr.id, pr.project_id FROM project_requests pr WHERE pr.project_id = ".$_GET['id']);
  $designation=$con->myQuery("SELECT id, name FROM project_designation")->fetchAll(PDO::FETCH_ASSOC);
  $manager_id=$con->myQuery("SELECT id, manager_id FROM projects WHERE id = ".$_GET['id'])->fetch(PDO::FETCH_ASSOC);
?>


  <!-- Content Header (Page header) -->

<div class="box box-warning">
<div class="box-body"><br>
<div class='row'>

    <div class='col-sm-12'>
          <form method='post' class='form-horizontal' action='save_project_add_employee1.php' id='frmclear'>
                <div class='form-group'>
                      <label for="employee_id" class="col-sm-3 control-label">Employee Name </label>
                            <div class='col-md-5'>
                                      <select class='form-control cbo' name='employee_id' id='employee_id' data-allow-clear='True' data-placeholder="Select Employee Name">
                                      <?php echo makeOptions($employees); ?>
                                      </select>
                            </div>                    
                                    <input type="hidden" name='id' value=<?php echo $_GET['id']; ?>>
                                    <input type="hidden" name='manager_id' value=<?php echo $manager_id['manager_id']; ?>>
                                    <button type='submit'  class='btn btn-warning' onclick="return confirm('Are you sure you want add this employee?')">Add</button>
                                    <button type='button'  class='btn btn-default' onclick='form_clear("frmclear")'> Clear</button>                      
                </div>                    
                                    
          </form>
    </div>
</div>
<hr>
<div class='row'>

                                                                <!-- Filter Report -->
    <div class='col-sm-12'>
                        <form method='get' class='form-horizontal' id='frm_search'>
                          <div class='form-group'>
                              <label for="employee_id" class="col-md-3 control-label">Employee Name </label>
                                  <div class='col-md-3'>
                                        <select class='form-control cbo' name='employee_name' id='employee_id' data-allow-clear='True' data-placeholder="Select Employee Name">
                                        <?php echo makeOptions($employees); ?>
                                        </select>
                                  </div> 
                               <label for="request_type" class="col-md-2 control-label">Request Type </label>
                                  <div class='col-md-3'>
                                      <select class='form-control cbo' name='req_type' id='req_type' data-allow-clear='True' data-placeholder="Select Request Type">
                                      <option value=></option>
                                      <option value='1'>Add</option>
                                      <option value='2'>Remove</option>
                                      </select>
                                  </div>       
                          </div>
                            <div class='form-group'>
                                <div class='col-md-3 col-md-offset-4 text-right'><br>
                                    <button type='button'  class=' btn btn-warning' onclick='filter_search(this)'><span class="fa fa-search"></span> Filter</button>
                                    <button type='button'  class=' btn btn-default' onclick='form_clear("frm_search")'> Clear</button>
                                </div>
                            </div>
                        </form>
                    </div>
    </div>
</div>

						 <br>

             <div class='panel-body ' >
                    <!-- <table class='table table-bordered table-condensed table-hover display select' id='ResultTable'> -->

                                        <table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
                                            <thead>
                                                <tr>
                                                    <th class='text-center'>Date Filed</th>
                                                    <th class='text-center'>Employee Name</th>
                                                    <th class='text-center'>Designation</th>
                                                    <th class='text-center'>Request Type</th> 
                                                    <th class='text-center'>Reason</th> 
                                                    <th class='text-center'>Status</th>  
	                                                  <th class='text-center'>Actions</th>  
                                                </tr>
                                            </thead>
                                            <!-- <tbody>
                                                
                                                    <?php
                                                      
                                                    
                                                    
                                                       $proj_req=$con->myQuery("SELECT pr.id, pr.project_id, pr.modification_type, pr.requested_employee_id, pr.date_filed, pr.status_id, pr.is_deleted,pr.first_approver_date,pr.second_approver_date,pr.third_approver_date, e.code, e.department_id, e.job_title_id, CONCAT(e.last_name,', ',e.first_name) as name, d.name as department, jt.description, rs.name as request_name FROM project_requests pr JOIN employees e ON e.id=pr.requested_employee_id JOIN departments d ON d.id=e.department_id JOIN job_title jt ON jt.id=e.job_title_id JOIN request_status rs ON rs.id=pr.status_id WHERE pr.is_deleted=0 AND pr.project_id = ".$_GET['id']);

                                                      while($rows = $proj_req->fetch(PDO::FETCH_ASSOC)):

                                                          echo "<tr><td>".htmlspecialchars($rows['date_filed'])."</td>";
                                                          echo "<td>".htmlspecialchars($rows['code'])."</td>";
                                                          echo "<td>".htmlspecialchars($rows['name'])."</td>";
                                                          
                                                          echo "<td>".htmlspecialchars($rows['department'])."</td>";
                                                          
                                                          echo "<td>".htmlspecialchars($rows['description'])."</td>";
                                                          if ($rows['modification_type'] == '1') {
                                                           echo "<td>".htmlspecialchars('Add')."</td>";
                                                          } else {
                                                            echo "<td>".htmlspecialchars('Remove')."</td>";
                                                          }
                                                          echo "<td>";
                                                                  if($rows['first_approver_date']=='0000-00-00'){
                                                                  echo htmlspecialchars($rows['request_name'])."</br>"." (First Approver)";
                                                              }
                                                              elseif($rows['second_approver_date']=='0000-00-00'){
                                                                  echo htmlspecialchars($rows['request_name'])."</br>"." (Second Approver)";
                                                              }
                                                              elseif($rows['third_approver_date']=='0000-00-00'){
                                                                  echo htmlspecialchars($rows['request_name'])."</br>"." (Third Approver)";
                                                              }
                                                              else{
                                                                echo htmlspecialchars($rows['request_name']);
                                                              }
                                                          echo "</td>";
                                                          echo "<td>";
                                                           if($rows['status_id']=="3"):
                                                           echo"<button class='btn btn-sm btn-info'  title='Query Request' onclick='query(\"{$rows['id']}\")'><span  class='fa fa-question'></span></button>";
                                                           endif;
                                                           if($rows['status_id']<>"2" && $rows['status_id']<>"5" && $rows['status_id']<>"4"):
                                                            echo"<form action='delete_project_employee.php' method='post'>";
                                                            echo"<input type='hidden' name='id' value='".$_GET['id']."'>";
                                                            echo"<input type='hidden' name='emp_id' value='".$rows['requested_employee_id']."'>";
                                                            echo"<input type='hidden' name='tab' value='2'>";
                                                            echo "<button type='submit' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this request?\")'><span class='fa fa-trash'></span></button></form>";
                                                          endif;
                                                          echo"</td></tr>";


                                                     endwhile;

                                                    ?>

                                            </tbody> -->
                                        </table>
            </div>
    <?php
    $request_type="project_approval_emp";
    $redirect_page="my_projects_view.php?id={$_GET['id']}&tab=3";
    require_once("include/modal_reject.php");
    require_once("include/modal_query.php");
    ?>
			</div>
		</div>
	</section>
</div>
<script type="text/javascript">

function filter_search() 
        {
            //table.draw();
            data_table.ajax.reload();

        }

$(function () {
  data_table=$('#ResultTable').DataTable({
    "processing": true,
   
    "searching": false,

    "ajax":{
      "url":"ajax/project_add_employee.php?id=<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>",
      "data":function(d){
             d.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
             d.employee_id=$("select[name='employee_name']").val();
              d.department_id=$("select[name='department_id']").val();
             d.job_id=$("select[name='job_id']").val();
             d.req_type=$("select[name='req_type']").val();
      }
    },

      
        "columnDefs": [{ "orderable": false, "targets": -1 },
        {"sClass": "text-center", "aTargets": [ -1 ]}],
          "order": [[ 0, "desc" ]]
    
  });
});

</script>
<?php
makeFoot(WEBAPP);
?>