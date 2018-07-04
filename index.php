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
				<h3><u>DASHBOARD</u></h3>
	        </div>
        </section>
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
            <?php if($user['user_type_id']!=5){ ?>
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
<?php }else{ 
                $bug_active=$con->myQuery("SELECT Count(id) as id FROM project_bug_list WHERE project_status_id=1")->fetch(PDO::FETCH_ASSOC);
                $bug_delay=$con->myQuery("SELECT Count(id) as id FROM project_bug_list WHERE project_status_id=4")->fetch(PDO::FETCH_ASSOC);
                $bug_tot=$con->myQuery("SELECT Count(id) as id FROM project_bug_list")->fetch(PDO::FETCH_ASSOC);
                $bug_done=$con->myQuery("SELECT Count(id) as id FROM project_bug_list WHERE project_status_id=2")->fetch(PDO::FETCH_ASSOC);
    ?>

                <div class="col-lg-3 col-xs-6">
                  <!-- small box -->
                  <div class="small-box bg-primary">
                    <div class="inner">
                      <h3><?php echo $bug_tot['id'];?></h3>

                      <p>Total Bugs</p>
                    </div>
                    <div class="icon">
                      <i class="fa fa-gears"></i>
                    </div>
                    <a href="bug_list.php" class="small-box-footer">
                      More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                  </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                  <!-- small box -->
                  <div class="small-box" style="background-color:  #ffa500 !important; color:white;">
                    <div class="inner">
                      <h3><?php echo $bug_active['id'];?></h3>

                      <p>Active Bugs</p>
                    </div>
                    <div class="icon">
                      <i class="fa fa-cog"></i>
                    </div>
                    <a href="bug_list.php" class="small-box-footer">
                      More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                  </div>
                </div>
                <div class="col-lg-3 col-xs-6">
                  <!-- small box -->
                  <div class="small-box bg-red" >
                    <div class="inner">
                      <h3><?php echo $bug_delay['id'];?></h3>

                      <p>Delayed Bugs</p>
                    </div>
                    <div class="icon">
                      <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <a href="bug_list.php" class="small-box-footer">
                      More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                  </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                  <!-- small box -->
                  <div class="small-box bg-green" >
                    <div class="inner">
                      <h3><?php echo $bug_done['id'];?></h3>

                      <p>Bugs Controlled</p>
                    </div>
                    <div class="icon">
                      <i class="fa fa-check-square"></i>
                    </div>
                    <a href="bug_list.php" class="small-box-footer">
                      More info <i class="fa fa-arrow-circle-right"></i>
                    </a>
                  </div>
                </div>



<?php } ?>
        </section><!-- /.content -->
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