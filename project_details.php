<?php
 require_once("support/config.php");
if(!isLoggedIn()){
  toLogin();
  die();
}
    $project_id=$_GET['id'];
    $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
    $manage=AccessForProject($project_id, $employee_id);
?>
<div class="box box-warning">
<div class="box-body"><br>
<div class='row'>
                    <div class='col-sm-12'>
                        <form method='get' class='form-horizontal' id='frm_search'>
                        <div class='form-group'>
                                        <label for="employee_id" class="col-sm-5 control-label">Employee Name </label>
                                        <div class='col-md-3'>
                                      <select class='form-control cbo-employee-id' name='employee_id' id='employee_id' data-allow-clear='True' data-placeholder="Select Employee Name">
                                      
                            </select>
                       <!--      </div> 
                                         <label class='col-md-2 text-right' >Department </label>
                                <div class='col-md-3'>
                                      <select class='form-control cbo-department-id' name='department_id' id='department_id' data-placeholder="Select Department" >
                            </select>                                
                            </div> -->
                                    
                                    </div>
                              <!--       <div class='form-group'>
                                        <label for="employee_id" class="col-sm-3 control-label">Job Title </label>
                                        <div class='col-md-3'>
                                      <select class='form-control cbo' name='job_id' id='job_id' data-allow-clear='True' data-placeholder="Select Job Title">
                                      <?php echo makeOptions($job_title); ?>
                            </select>
                            </div>  -->
                                    
                                    </div>
                            <div class='form-group'>
                                <div class='col-md-2 col-md-offset-5 text-right'>
                                    <button type='button'  class=' btn btn-warning' onclick='filter_search(this)'><span class="fa fa-search"></span> Filter</button>
                                    <button type='button'  class=' btn btn-default' onclick='form_clear("frm_search")'> Clear</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

						 <br><div class='panel-body ' >
                    <!-- <table class='table table-bordered table-condensed table-hover display select' id='ResultTable'> -->

                                        <table id='example' class="table table-bordered table-condensed table-hover display select" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class='text-center'>Employee Name</th>
                                                    <th class='text-center'>Designation</th>
                                                    <th class='text-center'>Date Assigned</th>
                                                    <?php 
                                                    if(($manage['is_team_lead_ba']=='1')OR($manage['is_manager']=='1')OR($manage['is_team_lead_dev']=='1')){
                                                    ?>
	                                                   <th class='text-center'>Actions</th>  
                                                    <?php
                                                    }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>
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
	data_table=$('#example').DataTable({
		"processing": true,
		"serverSide": true,
		"searching": false,
		"ajax":{
			"url":"ajax/my_projects_view.php",
			"data":function(d){
   			d.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
   			d.employee_id=$("select[name='employee_id']").val();
            d.department_id=$("select[name='department_id']").val();
            d.job_id=$("select[name='job_id']").val();
   		}
		},
        <?php 
        if(($manage['is_team_lead_ba']=='1')OR($manage['is_manager']=='1')||($manage['is_team_lead_dev']=='1')){
        ?>
        "columnDefs": [{ "orderable": false, "targets": -1 },
        {"sClass": "text-center", "aTargets": [ -1 ]}],
        <?php
        }
        ?>
          "order": [[ 1, "asc" ]],
		"oLanguage": { "sEmptyTaWble": "No employee/s found." }
	});
});


</script>