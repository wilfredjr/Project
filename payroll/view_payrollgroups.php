<?php
    require_once("../support/config.php");
    if(!isLoggedIn())
    {
        toLogin();
        die();
    }
    makeHead("Payroll Groups",1);
?>
<?php
    require_once("../template/payroll_header.php");
    require_once("../template/payroll_sidebar.php");
?>
<div class='content-wrapper'>
    <section>
        <div class='content-header'>
            <h1 class='page-header text-center text-red'>Payroll Groups</h1>
        </div>
    </section>
    <section class='content'>
        <div class="row">
            <?php
                Alert();
                Modal();
            ?>
            <div class='col-lg-12'>
                <div class='row'>
                    <div class='col-sm-12'>
                        <a href='frm_payrollgroup.php' class='btn btn-danger btn-flat pull-right' > <span class='fa fa-plus'></span> Create New  </a>
                    </div> 
                </div>
                <br/>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='dataTable_wrapper '>
                            <table id='dataTables' class='table table-bordered table-striped'>
                                <thead>
                                    <tr>
                                        <th class='text-center'>Payroll Group Name</th>
                                        <th class='text-center'>Website</th>
                                        <th class='text-center'>Address</th>
                                        <th class='text-center'>Email</th>
                                        <th class='text-center'>Mobile No </th>
                                        <th class='text-center'>Bank Account No. </th>
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
        </div>
    </section>
</div>

<script>
    var dttable="";
    $(document).ready(function() 
    {
        dttable=$('#dataTables').DataTable(
        {
                "scrollX":"100%",
                "searching": true,
                "processing": true,
                "serverSide": true,
                "select":true,
                "ajax":
                {
                    "url":"ajax/view_payrollgroups.php"
                },
                "language": 
                {
                    "zeroRecords": "No Records Found."
                },
                "columnDefs": [
                { 
                    "orderable": false, "targets": [5] 
                }] 
        });
    });
    function filter_search() 
    {                
        dttable.ajax.reload();  
    }
</script>
<?php
    makeFoot(WEBAPP,1);
?>