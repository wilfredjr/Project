<?php
	require_once("support/config.php");
	if(!isLoggedIn()){
		toLogin();
		die();
	}
	makeHead();
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");

    $pending_requests=$con->myQuery("SELECT date_filed,`status`,step_name,'Overtime Claim' AS 'type' FROM vw_employees_ot WHERE employee_id =:employee_id AND request_status_id = 1
            UNION
            SELECT date_filed,`status`,step_name,'Attendance Adjustments' AS 'type' FROM vw_employees_adjustments WHERE employee_id = :employee_id AND request_status_id = 1
            UNION
            SELECT date_filed,`status`,step_name,'Allowance' AS 'type' FROM vw_employees_allowances WHERE employee_id = :employee_id AND request_status_id = 1
            UNION
            SELECT date_filed,`status`,step_name,'Change Shift' AS 'type' FROM vw_employees_change_shift WHERE employee_id = :employee_id AND request_status_id = 1
            UNION
            SELECT DATE_FORMAT(date_filed,'%Y-%m-%d')as date_filed,`status`,step_name,'Leave' AS 'type' FROM vw_employees_leave WHERE employee_id = :employee_id AND request_status_id = 1
            UNION
            SELECT date_filed,`status`,step_name,'Official Business' AS 'type' FROM vw_employees_ob WHERE employee_id = :employee_id AND request_status_id = 1
            UNION
            SELECT date_filed,`status`,step_name,'Offset' AS 'type' FROM vw_employees_offset WHERE employees_id = :employee_id AND request_status_id = 1
            ORDER BY date_filed DESC", array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchAll(PDO::FETCH_ASSOC);
    $user=$con->myQuery("SELECT user_type_id FROM users WHERE employee_id=?",array($_SESSION[WEBAPP]['user']['employee_id']))->fetch(PDO::FETCH_ASSOC);
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          	<div class="page-header text-center">
				<h1><u>DASHBOARD</u></h1>
	        </div>
        </section>
<?php if($user['user_type_id']!=5){ ?>
        <!-- Main content -->
        <section class="content">
        <div class="text-center">
        </div>
        <div class="row">
        	<?php
        		Alert();
        		Modal();
        	?>
        	<?php
        		$attendance=$con->myQuery("SELECT id FROM attendance WHERE employees_id=? AND out_time='0000-00-00 00:00:00' LIMIT 1",array($_SESSION[WEBAPP]['user']['employee_id']))->fetch(PDO::FETCH_ASSOC);
        	?>
        	   <div class='panel panel-default'>
            <div class='panel-body ' >
                <div class='box box-warning box-solid'>
                <div class='box-header with-border'>
                    <h4> Overall Projects</h4>
                </div>
                <div class='box-body'>
                <table class='table table-bordered table-condensed table-hover ' id='ResultTable'>
                    <thead>
                        <tr>
                            <th class='text-center' style="width:20%;" >Project Name</th>
                            <th class='text-center' style="width:20%;" >Current Phase</th>
<!--                             <th class='text-center'>Date Filed</th>
                            <th class='text-center'>Date Start</th>
                            <th class='text-center'>Date End</th>
                            <th class='text-center'>Description</th> -->
                            <th class='text-center' style="width:20%;">Status</th>
                            <th class='text-center' style="width:30%;">Progress</th>
                            <th class='text-center' style="width:10%;">Percentage</th>
                        </tr>
                    </thead>
                    <tbody> 
                    </tbody>
                </table>
                    </div>
                </div>
            </div>
        </div>
        

        </section><!-- /.content -->
  </div>

<?php
}
   	$todaybdays=$con->myQuery("SELECT DATE_FORMAT(birthday,'%M %d') AS dob, CONCAT(e.last_name, ', ', e.first_name) As employee_name FROM employees e WHERE is_deleted=0 and is_terminated=0 and ( MONTH(birthday) = MONTH(CURDATE()) and DAY(birthday) = DAY(CURDATE()) )")->fetchAll(PDO::FETCH_ASSOC);

   	$upcomingbdays=$con->myQuery("SELECT DATE_FORMAT(birthday,'%M %d') AS dob, CONCAT(e.last_name, ', ', e.first_name) As employee_name FROM employees e WHERE is_deleted=0 and is_terminated=0 and WEEK(birthday) BETWEEN WEEK(CURDATE()) and WEEK( DATE_ADD(CURDATE(), INTERVAL +7 DAY) ) Order by dob")->fetchAll(PDO::FETCH_ASSOC);

    $leave=$data=$con->myQuery("SELECT 
    leave_id,
    balance_per_year,
    total_leave
    
    
    FROM employees_available_leaves
    
    WHERE is_cancelled=0 AND is_deleted=0 AND employee_id=?",array($_SESSION[WEBAPP]['user']['employee_id']));
?>

<div class="modal fade" id="birthdayModal" tabindex="-1" role="dialog" aria-labelledby="birthdayModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action=''>
        <div class="modal-header">
          	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          	<h4 class="modal-title">Birthday Celebrants</h4>
        </div>
        <div class="modal-body" >
        	<div class='form-group'>
          		<label>Today's Birthday Celebrant</label> <br/>
          		<table class="table table-condensed">
          			<tbody> 
		          	<?php 
			          	if(!empty($todaybdays)):
			          		foreach ($todaybdays as $todaybday):
			        ?>
          				<tr >
		          			<?php
			          			foreach ($todaybday as $key => $value):
			        		?>
		          				<td >
			                    	<?php echo htmlspecialchars($value); ?>
			                   	</td>
				    		<?php
				                endforeach;
				    		?>
		                </tr>
		    		<?php
	                		endforeach;
                		else:                                            
                    		echo ("No Results");
                		endif;
            		?>
            		</tbody>
            	</table>                     
          	</div>

          	<div class='form-group'>
          		<label>Upcoming Birthday Celebrants</label> <br/>
          		<table class="table table-condensed">
          			<tbody> 
          			<?php 
          				if(!empty($upcomingbdays)):
          					foreach ($upcomingbdays as $upcomingbday):
          			?>
          				<tr>
          					<?php
	          					foreach ($upcomingbday as $key => $value):
	        				?>
	          				<td >
		                    	<?php echo htmlspecialchars($value); ?>
		                   	</td>
		    <?php
		                endforeach;
		    ?>
		                </tr>
		    <?php
	                endforeach;
                else:                                            
                    echo ("No Results");
                endif;
            ?>
            </tbody></table>
          	</div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal">Okay</button>
        </div>

      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="LeaveModal" tabindex="-1" role="dialog" aria-labelledby="LeaveyModal">
  <div class="modal-dialog">
    <div class="modal-content">
     
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Leave Entitlement</h4>
        </div>
        <div class="modal-body" >
            <div class='form-group'>
                <label>Available Leave</label> <br/>
                <table class="table table-condensed">
                    
                                        
                                            <tr>
                                                <th class='text-center'></th>                    
                                                <th class='text-center'>Remaining</th>
                                                <th class='text-center'>Used</th>
                                            </tr>
                                            
                                                
                                               <?php //echo makeOptions($available_leave)
                                               $ctr=0;
                                                    while($row = $data->fetch(PDO::FETCH_ASSOC)):

                                                        $ctr = $ctr +1;
                                                        
                                                          $leave_name=$con->myQuery("SELECT name FROM leaves WHERE id = ".$row['leave_id']);
                             
                                                            while($rows = $leave_name->fetch(PDO::FETCH_ASSOC)):

                                                            echo "<tr><td>" .htmlspecialchars($rows['name']) ."</td>";
                                                            endwhile;


                                                            echo "<td class='text-center'>". htmlspecialchars($row['balance_per_year']). "</td>";
                                                            echo "<td class='text-center'>". htmlspecialchars($row['total_leave'] - $row['balance_per_year']). "</td></tr>";
                                                        


                                                    endwhile;
                                                    if ($ctr < 1) {
                                                            
                                                            echo "<tr><td></td>";

                                                            echo "<td>0</td>";
                                                            echo "<td>0</td></tr>";

                                                        }

                                                ?>
                                        
                </table>                     
            </div>

            

        </div>


    </div>
  </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        <?php
        if (!empty($pending_requests)){
        ?>
            var tbl_pending=$("#tbl_pending").DataTable({
                "ordering":false,
                "searching":false,
                "lengthChange":false,
                "pageLength":7,
                "scrollY":"300px"
            });
            $('#tbl_pending').on('click', 'tbody tr', function() {
                switch (tbl_pending.row(this).data()[1]) {
                    case "&nbsp;Overtime Claim":
                    case "&nbsp;Pre-Overtime":
                        window.location.href = './overtime.php';
                        break;
                    case "&nbsp;Official Business":
                        window.location.href = './ob_request.php';
                        break;
                    case "&nbsp;Allowance":
                        window.location.href = './allowance_request.php';
                        break;
                    case "&nbsp;Change Shift":
                        window.location.href = './shift_request.php';
                        break;
                    case "&nbsp;Attendance Adjustments":
                        window.location.href = './adjustment_request.php';
                        break;
                    
                    case "&nbsp;Leave":
                        window.location.href = './employee_leave_request.php';
                        break;
                    case "&nbsp;Offset":
                        window.location.href = './offset.php';
                        break;
                }
            })
            
        <?php
        }
        ?>
        <?php
        if (!empty($total_for_approval)){
        ?>
            var tbl_approval = $("#tbl_for_approval").DataTable({
                    "ordering":false,
                    "searching":false,
                    "lengthChange":false,
                    "pageLength":7,
                    "scrollY":"300px",
                    "autoWidth":false,
                    "paging":false,
                    "bInfo": false
                });
                $('#tbl_for_approval').on('click', 'tbody tr', function() {
                    console.log(tbl_approval.row(this).data()[0]);
                    switch (tbl_approval.row(this).data()[0]) {
                        case '<i class="fa fa-chevron-circle-right fa-lg"></i> Attendance Adjustment':
                            window.location.href = './adjustments_approval.php';
                            break;
                        case '<i class="fa fa-chevron-circle-right fa-lg"></i> Overtime':
                            window.location.href = './overtime_approval.php';
                            break;
                        case '<i class="fa fa-chevron-circle-right fa-lg"></i> Official Business':
                            window.location.href = './ob_approval.php';
                            break;
                        case '<i class="fa fa-chevron-circle-right fa-lg"></i> Allowance':
                            window.location.href = './allowance_approval.php';
                            break;
                        case '<i class="fa fa-chevron-circle-right fa-lg"></i> Change Shift':
                            window.location.href = './shift_approval.php';
                            break;
                        case '<i class="fa fa-chevron-circle-right fa-lg"></i> Leave':
                            window.location.href = './leave_approval.php';
                            break;
                        case '<i class="fa fa-chevron-circle-right fa-lg"></i> Offset':
                            window.location.href = './offset_approval.php';
                            break;
                        case '<i class="fa fa-chevron-circle-right fa-lg"></i> Overtime Adjustment':
                            window.location.href = './ot_adjustments_approval.php';
                            break;
                    }
                })
        <?php
        }
        ?>
    });
$(function () {
    dtable=$('#ResultTable').DataTable({
        "processing": true,
        "serverSide": true,
        "searching": false,

        "ajax":{
            "url":"ajax/index_projects.php",
            "data":function(d)
            {
                d.date_start=$("input[name='date_start']").val();
                // d.half_day_mode=$("select[name='half_day_mode']").val();
                d.date_end=$("input[name='date_end']").val();
                d.proj_name=$("select[name='proj_name']").val();
                d.status=$("select[name='status']").val();
                d.manager=$("select[name='manager']").val();
            }
        },
        "columnDefs": [{ "orderable": false, "targets": 3}],
          "order": [[ 1, "desc" ]]
        
    });
});
</script>
<?php
	makeFoot();
?>