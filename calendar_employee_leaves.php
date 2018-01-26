<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }
   if (empty($_SESSION[WEBAPP]["user"]["can_apply_for_meal_transpo"])) {
    redirect("index.php");
    die;
   }
  $employees=$con->myQuery("SELECT 
e.id,CONCAT(e.last_name,', ',e.first_name,' ',IFNULL(e.middle_name,'')) AS 'employee'
FROM employees e LEFT JOIN job_title jt ON e.job_title_id=jt.id LEFT JOIN departments d ON e.department_id=d.id WHERE e.is_deleted=0 AND e.is_terminated=0 AND e.is_regular=0 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);
	makeHead("Employee Leaves");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Employee Leaves
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-12'>
              <?php 
                Alert();
              ?>
              <div class="box box-warning">
                <div class="box-body no-padding">
                  <!-- THE CALENDAR -->
                  <div id="calendar"></div>
                </div><!-- /.box-body -->
              </div><!-- /. box -->
            </div>
          </div><!-- /.row -->
        </section><!-- /.content -->
  </div>
<div class="modal fade" id="leaveModal" tabindex="-1" role="dialog" aria-labelledby="leaveModalLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel">Leave Details</h4>
      </div>
      <div class="modal-body">
        <h5><strong>Date of Leave</strong></h5>
        <p id='leave_details_date'></p>
        <h5><strong>Employee</strong></h5>
        <p id='leave_details_emp'></p>
        <h5><strong>Leave Type</strong></h5>
        <p id='leave_details_leave_type'></p>
        <h5><strong>Reason</strong></h5>
        <p id='leave_details_reason'></p>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function() {
    $('#calendar').fullCalendar({
      weekNumbers:false,
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,listWeek'
      },
      // customize the button names,
      // otherwise they'd all just say "list"
      views: {
        listWeek: { buttonText: 'List Week' },
        month: { buttonText: 'Month' }
      },
      navLinks: true, // can click day/week names to navigate views
      eventLimit: true, // allow "more" link when too many events
      weekNumberCalculation:"ISO",
        events: 'ajax/calendar_employee_leaves.php',
      eventClick: function(calEvent, jsEvent, view) {
          $("#leaveModal").modal('show');
          $("#leave_details_emp").text(calEvent.employee);
          $("#leave_details_reason").text(calEvent.reason);
          $("#leave_details_leave_type").text(calEvent.leave_type);
          
          if (calEvent.date_start == calEvent.date_end) {
            leaves_date = calEvent.date_start;
          } else {
            leaves_date = calEvent.date_start + " - "+calEvent.date_end;
          }
          $("#leave_details_date").text(leaves_date);
      }
    })
});
</script>

<?php
  Modal();
	makeFoot();
?>