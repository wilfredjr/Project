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
            <h1 class='page-header text-center text-red'>De Minimis/Non-taxable Allowance</h1>
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
                        <a href='frm_deminimis.php' class='btn btn-danger btn-flat pull-right'> <span class='fa fa-plus'></span> Create New</a>
                    </div>
                </div>
                <br/>    
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='dataTable_wrapper '>
                            <table class='table responsive table-bordered table-condensed table-hover ' id='dataTables'>
                                <thead>
                                    <tr>
                                    <th class='text-center' style='width:20%'>Code</th>
                                    <th class='text-center' style='width:45%'>Description</th>
                                    <th class='text-center' style='width:20%'>Amount</th>
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
    $(document).ready(function() 
    {
        $('#dataTables').DataTable(
        {
                "scrollY":"400px",
                "processing": true,
                "serverSide": true,
                "searching": false,
                "ajax": "ajax/deminimis.php",
                "columnDefs": 
                [
                    { "orderable": false, "targets": 3 }
                ]
        });

    });
</script>
<?php
	makeFoot(WEBAPP,1);
?>