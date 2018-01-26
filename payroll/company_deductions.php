<?php
	require_once("../support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}
	makeHead("Payroll System",1);
?>

<?php
	require_once("../template/payroll_header.php");
	require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
    <section>
      	<div class='content-header'>
            <h1 class='page-header text-center text-red'>Company Deductions</h1>
        </div>
    </section>
    <section class="content">
        <div class="row">
        	<?php
        		Alert();
        		Modal();
        	?>
            <div class='col-lg-12'>
                <div class='row'>
                    <div class='col-sm-12'>
                        <a href='frm_company_deductions.php' class='btn btn-danger btn-flat pull-right'> <span class='fa fa-plus'></span> Create New</a>
                    </div>
                </div>
                <br/>    
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='dataTable_wrapper '>
                            <table class='table responsive table-bordered table-condensed table-hover ' id='dataTables'>
                                <thead>
                                    <tr>
                                    <th class='text-center' style='width:30%'>Name</th>
                                    <th class='text-center' style='width:55%'>Description</th>
                                    <th class='text-center' style='width:15%'>Actions</th>
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
    </section>
</div>
<script>
    $(function () 
    {
        $('#dataTables').DataTable(
        {
                "scrollY":"400px",
                "processing": true,
                "serverSide": true,
                "searching": false,
                "ajax": "ajax/company_deductions.php",
                "columnDefs": 
                [
                    { "orderable": false, "targets": 2 }
                ]
        });

    });
</script>
<?php
	makeFoot(WEBAPP,1);
?>