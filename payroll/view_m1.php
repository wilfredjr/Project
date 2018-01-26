<?php
require_once("../support/config.php");
if(!isLoggedIn())
{
  toLogin();
  die();
}

makeHead("View M1 Form",1);
// if(!empty($_GET['ref_no'])){
//   $r5 = $con->myQuery("SELECT * FROM sss_r5_main WHERE is_deleted=0 and ref_no = ?", array($_GET['ref_no']) )->fetch(PDO::FETCH_ASSOC);  
//   // var_dump(substr($r5['for_date_of'], 0, -3));
//   // die;
// }

    // $lt=$con->myQuery("SELECT loan_id, loan_name FROM loans WHERE is_deleted=0" )->fetchAll(PDO::FETCH_ASSOC);
    // $el=$con->myQuery("SELECT emp_loan_id, cut_off_no, loan_amount, balance FROM emp_loans" )->fetchAll(PDO::FETCH_ASSOC);
    // $ls=$con->myQuery("SELECT status_id, status_name FROM loan_status WHERE is_deleted=0" )->fetchAll(PDO::FETCH_ASSOC);
    // $em=$con->myQuery("SELECT employees.id, CONCAT(employees.first_name,' ',employees.middle_name,' ',employees.last_name) AS emp_name FROM employees
    // INNER JOIN employment_status ON employees.employment_status_id = employment_status.id
    // WHERE employees.is_terminated != '1' and employees.is_deleted ='0'")->fetchAll(PDO::FETCH_ASSOC);

?>
<?php
require_once("../template/payroll_header.php");
require_once("../template/payroll_sidebar.php");
?>

<script type="text/javascript">
  function isNumberKey(evt, element) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 46 || charCode == 8))
      return false;
    else {
      var len = $(element).val().length;
      var index = $(element).val().indexOf('.');
        //alert(index);
        
        if (index >= 0 && charCode == 46) {
          return false;
        }
      }
      return true;
    } 
    

  </script>
  <div class="content-wrapper">
    <section>
      <div class="content-header">
        
        <h1 class="page-header text-center text-red">View M1 form</h1>
        
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class='col-lg-12'>
          <?php Alert(); ?>
        </div>
      </div>  
      <div class="row">

        <div class='col-lg-12'>
          <div class="row">
            <div class='col-sm-12 col-md-8 col-md-offset-2'>
              

               <div class='form-group'>
                <label class='col-md-3 control-label' >Month: <span class='text-red'>*</span></label>
                <div class='col-md-7'>

                  <input type='month' name='month_year' class='form-control' id='month_year' >

                </div>
                <div class='col-md-12 text-center'>
                  <br>
                  <!-- <?php if(empty($r5)){ ?> -->
                  <a  id="view_m1" class='btn btn-danger btn-flat'"> View</a>
                  <!-- <?php }?> -->
                </div>
              </div>



              

               
              </div>
            </div>
            <br>
            <div class="row">
          
              <div class="col-md-12">
              <div class='panel panel-default'>
          <div class='panel-body ' >
           <div id="grp_report" style="display:inline;float:right;display:none;">
              <form action="report_pagibig_m1_download.php">
                <input type="hidden" id='rep_month_year' name='rep_month_year'>
                <button class="btn btn-danger btn-flat" ><span class='fa fa-download'></span> Download Report </button>
              </form>
            </div>
               <table class='table table-bordered table-condensed table-hover ' id='dataTables'>
        <thead>
          <tr>
            <th class='text-center' >Pag-IBIG ID/RTN</th>
            <th class='text-center' >Last Name</th>
            <th class='text-center' >First Name</th>
            <th class='text-center'>Middle Name</th>
            <th class='text-center'>EE share</th>
            <th class='text-center'>EC share</th>
            <th class='text-center'>Status</th>
</tr>
          </thead>
          <tfoot>
                <tr>
                <th colspan="4" style="text-align:right">Total:</th>
                <th></th>
                <th></th>
                <th></th>
                  
            </tr>
          </tfoot>
          <tbody style="text-align:center ">
          </tbody>

          </table>
          </div>
          </div>
             </div>

           </div>
          </div>
        </div>
      </section><!-- /.content -->
    </div>
    <script>
      $(document).ready(function() {
        Number.prototype.format = function(n, x) {
    var re = '(\\d)(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
    return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$1,');
};
        // $('#submit_button').click(function(){

        //   if($('#month_year').val() != ""){
        //     //alert("i'm GAY");
        //     ss_con =  $('#ss_contrib').text();
        //     arr = ss_con.split(' ');
        //     arr[0] = arr[0].replace(",","");
        //     ec_con = $('#ec_contrib').text();
        //     arr2 = ec_con.split(' ');
        //     arr2[0] = arr2[0].replace(",","");

        //     error = "You have following error/s:";
        //     if($('#SS').val()>arr[0]){
        //       //alert(':/');
        //       error = error.concat("\nInput amount is greater than SS Contribution.");
        //     }
        //     if($('#EC').val()>arr2[0]){
        //       error = error.concat("\nInput amount is greater than EC Contribution.");
        //     }

        //    if (error.length>27){
        //     alert(error);
        //     $("#submit_button").button('reset');
        //     return false;
        //    }
        //     //
            
        //   }else{
        //     ss_con = <?php echo !empty($r5['ss_contribution'])?str_replace(',','',$r5['ss_contribution']):""?>
        //     alert(ss_con);
        //     return;
        //     $("#submit_button").button('reset');
        //   }


        // });      
        $('#view_m1').click(function(){
          //alert('hi!');
           if($("input[name='month_year']").val()==""){
            alert('Please select Month/Year.');
            return;
          }
          //alert($("#rep_month_year").val());

          $("#rep_month_year").val($("input[name='month_year']").val());
          $('#grp_report').show();
          $('#dataTables').dataTable().fnDestroy();
          $('#dataTables').DataTable({
    "footer":true,
    "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
 
            // Total over all pages
            total = api
                .column( 4 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            // Total over this page
            pageTotal = api
                .column( 4, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            // Update footer
            $( api.column( 4 ).footer() ).html(
                pageTotal.format(2) +' PHP ( '+ total +' PHP total)'
            );


            total1 = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            // Total over this page
            pageTotal1 = api
                .column( 5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
 
            // Update footer
            $( api.column( 5 ).footer() ).html(
                pageTotal1.format(2) +' PHP ( '+ total1 +' PHP total)'
            );
        },
        "searching": false,
        "ajax":{
        "url":"ajax/view_m1.php?month_year=" + $("input[name='month_year']").val()
      },
        "language": {
          "zeroRecords": "Data not found"}

        });


        })


        


                });
              </script>
<!--   <script>
    function validate_inputs() {
      var return_value=true;
      var str_error="";
      if($("#salary_period").val()=='Weekly'){
          // strerror+="Please select a school fee.\n";
          alert('weekly!');
          return_value=false;

        }else if ($("#salary_period").val()=='Bi-Monthly'){

          alert('BM!');
          return_value=false;

          
        }else if ($("#salary_period").val()=='Monthly'){
          alert('monthly!');
          return_value=false;
        }
        if(str_error!==""){
          alert("You have the following error: \n"+str_error);
        }
        return return_value;
      }
    </script> -->
    <?php
    makeFoot(WEBAPP,1);
    ?>