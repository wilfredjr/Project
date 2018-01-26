<?php
	require_once("../support/config.php");
	if(!isLoggedIn())
	{
		toLogin();
		die();
	}

	$cbo_employees = $con->myQuery("SELECT id,CONCAT(last_name,', ',first_name,' ',middle_name,' (',code,')') AS name FROM employees WHERE is_deleted = 0 AND is_terminated = 0")->fetchAll(PDO::FETCH_ASSOC);
	$cbo_paycode=$con->myQuery("SELECT id,last_pay_code FROM last_pay WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

	makeHead("Generated Last Pay",1);
?>
<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">List of Generated Last Pay</h1>
		</div>
	</section>
	<section class="content">
		<div class="row">
            <div class='col-lg-12'>
                <?php Alert(); ?>
            </div>
        </div>  
        <br/>
		<div class="row">
			<div class='col-sm-12 col-md-12'>
				<div class='row'>
                    <div class='col-sm-12'>
						<form method="post" action="last_pay_generate.php" class="form-horizontal">
							<div class='form-group'>	
								<label class='col-md-4 text-right' >Select Employee * </label>
								<div class='col-md-4'>
									<select class='form-control cbo' name='employee_id' id='employee_id' data-placeholder='Select Employee' style='width:100%' data-allow-clear='true' data-selected="<?php //echo !empty($get_master['shift_id'])?htmlspecialchars($get_master['shift_id']):''?>" required >
										<?php echo makeOptions($cbo_employees); ?>
									</select>
								</div>
							</div>
							<br>
							<div class='form-group'>
								<label class='col-md-4 text-right' >Select Dates for Last Salary: </label>
							</div>
							<div class='form-group'>
								<label class='col-md-4 text-right' >Date From * </label>
								<div class='col-md-4'>
									<input type='text' name='date_start' class='form-control date_picker' id='date_start' pattern="\d{1,2}/\d{1,2}/\d{4}" required>
								</div>
							</div>
							<div class='form-group'>
								<label class='col-md-4 text-right' >Date To * </label>
								<div class='col-md-4'>
									<input type='text' name='date_end' class='form-control date_picker' id='date_end' pattern="\d{1,2}/\d{1,2}/\d{4}" required>
								</div>
							</div>
							<div class="form-group">
								<div class='col-md-2 col-md-offset-5 text-right'>
									<button type='submit' class='btn btn-flat btn-block btn-danger'><span class='fa fa-file'></span>  Generate Last Pay </button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="page-header"></div>
		<div class="row">
				<div class='col-md-12'>
	                <h4 class="text-red">Generated Last Pay</h4>
	            </div>
						<div class='col-sm-12 col-md-12'>  
                            <div class='row'>
                                <div class='col-sm-12'>
                                    <form method="" class="form-horizontal">
                                        <div class='form-group'>
                                            <label class='col-md-3 text-right' >Transaction Code </label>
                                            <div class='col-md-3'>
                                                <select class="form-control cbo" name="pay_code_filter" id="pay_code_filter" data-placeholder="Filter by Transaction Code" style="width:100%" data-allow-clear="true">
                                                    <?php echo makeOptions($cbo_paycode); ?>
                                                </select>
                                            </div>
                                            <label class='col-md-2 text-right' >Employee </label>
                                            <div class='col-md-3'>
                                                <select class="form-control cbo" name="pay_group_filter" id="pay_group_filter" data-placeholder="Filter by Employee" style="width:100%" data-allow-clear="true">
                                                    <?php echo makeOptions($cbo_employees); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class='form-group'>
                                            <label class='col-md-3 text-right' >Date Generated </label>
                                            <div class='col-md-3'>
                                                <input type='text' name='date_generated_filter' class='form-control date_picker' id='date_generated_filter' placeholder="Filter by Date Generated">
                                            </div>
                                            <label class='col-md-2 text-right' >Status </label>
                                            <div class='col-md-3'>
                                                <select class="form-control cbo" name="status_filter" id="status_filter" data-placeholder="Filter by Status" style="width:100%" data-allow-clear="true">
                                                    <option value=""></option>
                                                    <option value="2">Not Yet Processed</option>
                                                    <option value="1">Processed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class='col-md-2 col-md-offset-5 text-right'>
                                                <button type='button' class='btn btn-flat btn-danger' onclick='filter_search()'><span class='fa fa-search'></span>  Filter </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


			<div class="col-sm-12">
				<div class='panel panel-default'>
					<div class='panel-body ' >
						<table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
							<thead>
								<tr>
									<th class='text-center'>Transaction Code</th>
									<th class='text-center'>Employee Name</th>
									<th class='text-center'>Date Generated</th>
									<th class='text-center'>Status</th>
									<th class='text-center'>Actions</th>
								</tr>
							</thead>
							<tbody>	
							
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<script type="text/javascript">
	var data_table="";
    $(function () 
    {
        $('#ResultTable thead td').each( function () 
        {
            var title = $(this).text();
        });
        data_table=$('#ResultTable').DataTable(
        {
            "orderCellsTop": true,
            "processing":true,
            "searching": false,
            "serverSide": true,
            "scrollX":true,
            "ajax":
            {
                "url":"ajax/last_pay.php",
                "data":function(d)
                {
                    d.pay_code_filter           = $("select[name='pay_code_filter']").val();
                    d.pay_group_filter          = $("select[name='pay_group_filter']").val();
                    d.date_generated_filter     = $("#date_generated_filter").val();
                    d.status_filter             = $("#status_filter").val();
                }
            },
            "columnDefs":
            [
                { "orderable": false, "targets":4 }
            ]
        });
    });
   	function filter_search() 
    {
        // alert($("#status_filter").val());
        data_table.ajax.reload();
    }
</script>
<?php
	makeFoot(WEBAPP,1);
?>