<?php
  require_once("../support/config.php");
  if(!isLoggedIn())
  {
    toLogin();
    die();
  }

if(!empty($_GET['date_start']) && !empty($_GET['date_end'])){
    $date_start=date_create($_GET['date_start']);
    $date_end=date_create($_GET['date_end']);
    $inputs['date_start']=date_format($date_start,'Y-m-d');
    $inputs['date_end']=date_format($date_end,'Y-m-d');
    $date_start=date_format($date_start,DATE_FORMAT_PHP);
    $date_end=date_format($date_end,DATE_FORMAT_PHP);

  makeHead("Philhealth ER-2",1);
?>
<?php
  require_once("../template/payroll_header.php");
  require_once("../template/payroll_sidebar.php");
?>
<div class="content-wrapper">
  <section>
    <div class="content-header">
      <h1 class="page-header text-center text-red">Philhealth ER-2</h1>
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
            <form class='form-horizontal' action='' method="GET" onsubmit='return validate(this)'>
              <div class='form-group'>
                <label class='col-md-3 text-right' >Date From * </label>
                <div class='col-md-3'>
                  <input type='text' class='form-control date_picker' id='date_start' name='date_start' value='<?php echo !empty($_GET['date_start'])?htmlspecialchars($_GET['date_start']):''?>' required>
                </div>
                <label class='col-md-2 text-right' >Date To * </label>
                <div class='col-md-3'>
                  <input type='text' class='form-control date_picker' id='date_end' name='date_end' value='<?php echo !empty($_GET['date_end'])?htmlspecialchars($_GET['date_end']):''?>' required>
                </div>
              </div>
              <div class="form-group">
                <div class='col-md-2 col-md-offset-5 text-right'>
                  <button type='button' class='btn-flat btn btn-danger'><span class="fa fa-search"></span> Filter</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-12">
        <?php
          if(!empty($_GET)):
        ?>
        <div class='panel panel-default'>
          <div class='panel-body ' >
            <div class='col-md-12 text-right'>
              <form action="report_ph_er2_download.php">
                <button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
              </form>
            </div>
            <table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
              <thead>
                <tr>
                  <th class='text-center'>Philhealth Number</th>
                  <th class='text-center'>Name of Employee</th>
                  <th class='text-center'>Position</th>
                  <th class='text-center'>Salary</th>
                  <th class='text-center'>Date of Employment</th>
                  <th class='text-center'>Previous Employer</th>
                </tr>
              </thead>
              <tbody align="center">
              
              </tbody>
            </table>
          </div>
        </div>
                <?php
                endif;
                ?>
      </div>    
    </div>  
  </section>
</div>

<!-- <?php
//  if(!empty($_GET)):
?> -->
<script type="text/javascript">
  $(function (){
    $('#ResultTable').DataTable({
      "processing": true,
      "serverSide": true,
      "searching": false,
      "columnDefs": [{"targets":5, "orderable":false}],
      "ajax":{
          "url":"ajax/view_ph_er2.php",
          "data":function(d)
                  {
                      d.date_start=$("input[name='date_start']").val();
                      d.date_end=$("input[name='date_end']").val();
                     
                  }
        },
      "oLanguage": { "sEmptyTaWble": "No employee/s found." }
      // dom: 'Blrtip',
   //              buttons: [
   //              {
   //                extend:"pdf",
   //                orientation: "landscape",
   //                pageSize: "A1",
   //                text:"<span class='fa fa-download'></span> Download PDF File ",
   //                extension:".pdf",
   //                exportOptions: {
   //                  columns: [0,1,2,3,4,5]
   //                }
   //              }],
    });

  });

</script>
<!--                  <?php
//                endif;
                ?>
 -->
 <script>

   function validate(frm) {

    if(Date.parse($("#date_start").val()) > Date.parse($("#date_end").val())){
      alert("Start Date cannot be greater than time out.");
      return false;
    }
    else if(Date.parse($("#date_start").val()) == Date.parse($("#date_end").val())){
      alert("End Date should be greater than time in.")
      return false;
    }

    return true;
  }

</script>
<?php
  makeFoot(WEBAPP,1);
?>