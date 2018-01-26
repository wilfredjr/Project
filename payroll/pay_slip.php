<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
    	toLogin();
    	die();
    }

    $getPayCode=$con->myQuery("SELECT id,payroll_code FROM payroll WHERE is_deleted=0 AND is_processed=1")->fetchAll(PDO::FETCH_ASSOC);
    $getEmployeeCode=$con->myQuery("SELECT e.id,e.code  FROM employees e INNER JOIN payroll_details p ON p.employee_id=e.id  WHERE p.is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

    makeHead("Payslip",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
	<section>
		<div class="content-header">
			<h1 class="page-header text-center text-red">Pay Slip</h1>
		</div>
	</section>
	<section class="content">
		<div class="row">
			<?php
    			Alert();
    			Modal();
			?>
			<div class="row">
                <form action="" method="" class="form-horizontal">
                    <div class='form-group'>
                        <label class="col-md-3 control-label">Employee Code :</label>
                        <div class="col-md-3">
                            <select class="form-control cbo" name="e_code" data-placeholder="Select Employee"   required> 
                                <?php echo makeOptions($getEmployeeCode); ?> 
                            </select>
                        </div>
                        <label class="col-md-2 control-label">Payroll Code :</label>
                        <div class='col-md-3'>
                            <select class="form-control cbo" name="p_code" data-placeholder="Select PayCode"   required> 
                                <?php echo makeOptions($getPayCode); ?> 
                            </select>
                        </div>                 
                    </div>
                    <div class='form-group'>
                        <div class='col-md-7 text-right'>
                            <button type='button' class='btn-flat btn btn-danger ' onclick='filter_search()'><span class="fa fa-search"></span> Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
		<br/>
		<div class='panel panel-default'>
			<div class='panel-body ' >
				<table class='table table-bordered table-condensed table-hover ' id='dataTables'>
					<thead>
						<tr>
                            <th class='text-center'>Payroll Code</th>
                            <th class='text-center'>Emp Code</th>
                            <th class='text-center'>Employee Name</th>
                            <th class='text-center'>Action</th>
                        </tr>
					</thead>
					<tbody align="center">
					
					</tbody>
				</table>    
            </div>
		</div>
	</section>
</div>
	
<script type="text/javascript">
	var dttable="";
    $(document).ready(function () 
    {
        dttable=$('#dataTables').DataTable({
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "searching": false,
            "ajax":
            {    
                "url":"ajax/payslip_ajax.php",
                "data":function(d)
                {
                    d.p_code_text=$("select[name='p_code'] :selected").text();
                    d.e_code=$("select[name='e_code']").val();
                }
            },
            "columnDefs": [{ "orderable": false, "targets": 3 }]
        });
    });
    function filter_search() 
    {
            dttable.ajax.reload();
            //console.log(dttable);
    }
</script>

<?php
	makeFoot(WEBAPP,1);
?>