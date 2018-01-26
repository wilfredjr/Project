<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }

    $cbo_payroll_code=$con->myQuery("SELECT id, payroll_code FROM payroll WHERE is_deleted=0")->fetchAll(PDO::FETCH_ASSOC);

    makeHead("BIR 1601-E Form",1);
    ?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
   <section class=" content-header">
        <h1 align="center" style="" class="text-red page-header text-center ">
            BIR 1601-E
        </h1>
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
                        <form method='POST' class='form-horizontal' action="report_bir_1601_e_save_master.php" onsubmit="">
                            <div class='form-group'>
                                <label class='col-md-3 text-right' >Month * </label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo' name='input_month' data-placeholder='Select Month' style='width:100%' data-allow-clear='true' required>
                                        <option value=""></option>
                                        <option value="01">January</option>
                                        <option value="02">February</option>
                                        <option value="03">March</option>
                                        <option value="04">April</option>
                                        <option value="05">May</option>
                                        <option value="06">June</option>
                                        <option value="07">July</option>
                                        <option value="08">August</option>
                                        <option value="09">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </div>

                                <label class='col-md-2 text-right' >Year * </label>
                                <div class='col-md-3'>
                                    <select class='form-control cbo' name='input_year' data-placeholder='Select Year' style='width:100%' data-allow-clear='true' required>
										<option value=""></option>
										<?php 
										   	for($i = date('Y') ; $i >= 1990 ; $i--):
										      	echo "<option value='{$i}'>$i</option>";
										   	endfor;
										?>
                                    </select>
                                </div>
                            </div>

                            <div class='form-group'>
                                <div class='col-md-2 col-md-offset-5 text-right'>
                                    <button type='submit'  class='btn-flat btn btn-block btn-danger' onclick=''><span class="fa fa-file"></span> Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
					<div class="col-md-12">
	                	<div class='panel panel-default'>
							<div class='panel-body ' >
                                <br/>
                                <table id='ResultTable' class='table table-bordered table-striped'>
                                    <thead>
                                        <tr>
                                            <th class='text-center' style=''>Company Name</th>
                                            <th class='text-center' style=''>Month-Year</th>
                                            <th class='text-center' style=''>Date Generated</th>
                                            <th class='text-center' style=''>Status</th>
                                            <th class='text-center' style=''>Action</th>
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
                "url":"ajax/report_bir_1601_e.php"
                // ,
                // "data":function(d)
                // {
                //     d.govde="<?php echo !empty($_GET['govde'])?htmlspecialchars($_GET['govde']):'' ?>";
                //     d.payroll_code="<?php echo !empty($_GET['payroll_code'])?htmlspecialchars($_GET['payroll_code']):'' ?>";
                // }
            },
            "columnDefs":
            [
                { "orderable": false, "targets": 4 },
                { "orderable": false, "targets": 3 }
            ]
        });
    });

    // function filter_search() 
    // {
    //     data_table.ajax.reload();
    // }
   
</script>
<?php
    Modal();
    makeFoot(WEBAPP,1);
?>