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
                            <label class="col-md-3 control-label">Start Date</label>
                            <div class="col-md-3">
                                <input type="text"  class="form-control date_picker" id="date_start" name='date_start'>
                            </div>
                            <label class="col-md-2 control-label">End Date</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control date_picker" id="date_end" name='date_end'>
                            </div>
                        </div>
                        <div class='form-group'>
                                        <label for="employee_id" class="col-sm-3 control-label">Employee Name </label>
                                        <div class='col-md-3'>
                                      <select class='form-control cbo-employee-id' name='employee_id' id='employee_id' data-allow-clear='True' data-placeholder="Select Employee Name">
                                      
                            </select>
                            </div> 
                                         <label class='col-md-2 text-right' >Department </label>
                                <div class='col-md-3'>
                                      <select class='form-control cbo-department-id' name='department_id' id='department_id' data-placeholder="Select Department" >
                            </select>                                
                            </div>
                                    
                                    </div>
                                    <div class='form-group'>
                                        <label for="employee_id" class="col-sm-3 control-label">Job Title </label>
                                        <div class='col-md-3'>
                                      <select class='form-control cbo' name='job_id' id='job_id' data-allow-clear='True' data-placeholder="Select Job Title">
                                      <?php echo makeOptions($job_title); ?>
                            </select>

                            </div> 
                                    
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
                                                    <th class='text-center'>Code</th>
                                                    <th class='text-center'>Employee Name</th>
                                                    <th class='text-center'>Department</th>
                                                    <th class='text-center'>Job Title</th>
                                                    <th class='text-center'>Project Position</th>
	                                                <th class='text-center'>Added By</th>
                                                    <th class='text-center'>Added Date</th> 
                                                    <th class='text-center'>Removed By</th>
                                                    <th class='text-center'>Removed Date</th> 
                                                    
                                                    
                                                   
                                                </tr>
                                            </thead>
                                            
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
        			"url":"ajax/project_employee_history.php",
        			"data":function(d){
           			d.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>';
           			d.employee_id=$("select[name='employee_id']").val();
                    d.department_id=$("select[name='department_id']").val();
                    d.job_id=$("select[name='job_id']").val();
                   
                   d.date_start=$("input[name='date_start']").val();
                    d.date_end=$("input[name='date_end']").val();
           		}
        		},
           	    "columnDefs": [{ "orderable": false, "targets": 4 }],
                  "order": [[ 1, "asc" ]],
        		"oLanguage": { "sEmptyTaWble": "No employee/s found." }
	});
});


</script>

