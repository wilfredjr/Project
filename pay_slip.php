<?php
    require_once("support/config.php");
    if(!isLoggedIn()){
        toLogin();
        die();
    }
    $id = $_SESSION[WEBAPP]['user']['employee_id'];
    $getPayCode=$con->myQuery("SELECT p.id,p.payroll_code FROM payroll p 
                INNER JOIN payroll_details r ON r.payroll_code=p.payroll_code INNER JOIN employees e ON r.employee_id=e.id WHERE r.employee_id=? ",array($id))->fetchAll(PDO::FETCH_ASSOC);
    $getEmployeeCode=$con->myQuery("SELECT e.id,e.code  FROM employees e INNER JOIN payroll_details p ON p.employee_id=e.id  WHERE p.is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

    makeHead("Payslip");
?>
<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
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
                    <div class="col-md-12">
                        <div class="row">
                        <label class="col-md-2 control-label">Payroll Code :</label>
                        <div class='col-md-3'>
                            <select class="form-control cbo" name="p_code" data-placeholder="Select PayCode"   required> 
                                <?php echo makeOptions($getPayCode); ?> 
                            </select>
                        </div>
                        <button type='button' class='btn-flat btn btn-warning btn-sm' onclick='filter_search()'><span class="fa fa-search"></span> Filter</button>
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
	makeFoot(WEBAPP);
?>