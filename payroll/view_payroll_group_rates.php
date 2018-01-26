<?php
require_once("../support/config.php");
if(!isLoggedIn()){
  toLogin();
  die();
}
makeHead("Payroll Group Settings",1);
?>

<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>
<div class='content-wrapper'>
  <section >
    <div class="content-header">
    <h1 class="page-header text-center text-red">
      Payroll Group List
      
    </h1>
    </div>
  </section>
  <section class='content'>
    <div class="row">
          <?php
          Alert();
          ?>
      <div class='col-lg-12'>

        <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='dataTable_wrapper '>
          <table id='dataTables' class='table table-bordered table-striped'>
            <thead>
              <tr>
                <th class='text-center'> Code </th>
                <th class='text-center'>Payroll Group List</th>
                <th class='text-center'>Status </th>
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
      $(document).ready(function() {
        dttable=$('#dataTables').DataTable({
                //"scrollY":"400px",
                "scrollX":"100%",
                "searching": true,
                "processing": true,
                "serverSide": true,
                "select":true,
                "ajax":{
                  "url":"ajax/view_payroll_group_rates.php"
                },"language": {
                    "zeroRecords": "Payroll Group/s Not Found."
                },
                order:[[0,'desc']]
                ,"columnDefs": [
                    { "orderable": false, "targets": [-1] }
                  ] 
        });
        $("select[name='company']").select2(
        {
          allowClear:true
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