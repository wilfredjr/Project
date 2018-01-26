<?php
  require_once("support/config.php");
   if(!isLoggedIn()){
    toLogin();
    die();
   }


  /*$data=$con->myQuery("SELECT 
    id,
    code,
    employee_name,
    supervisor,
    final_approver,
    status,
    DATE_FORMAT(ob_date,'%m-%d-%Y') as ob_date,
    time_from,
    time_to,
    destination,
    purpose,
    DATE_FORMAT(date_filed,'%m-%d-%Y') as date_filed,
    evidence
    FROM vw_employees_ob
    WHERE CASE 
    when status='Supervisor Approval' then supervisor_id 
    when status='Final Approver Approval' then final_approver_id
    end 
    =:employee_id
    ",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']));
*/
  makeHead("Official Business Approval");
?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Official Business Approval
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

                <form action="" method="" class="form-horizontal" id='frmclear'>

                    <div class='form-group'>
                        <label class="col-sm-3 control-label">Department*</label>
                          <div class="col-sm-3">
                            <select class='form-control cbo-department-id' name='department_id' id='department_id' data-placeholder="Select Department" data-allow-clear="true" <?php echo !(empty($_GET))?"data-selected='".$_GET['department_id']."'":NULL ?> style='width:100%'>
                            <?php
                              echo makeOptions($departments,"Select Department");
                            ?>
                            </select>
                          </div>
                        <label class="col-sm-2 control-label">Employee Name*</label>
                        <div class='col-sm-3'>
                            <select class='form-control cbo-employee-id' name='employee_id' id='employee_id' data-allow-clear='True' data-placeholder="Select Employee">       
                               
                            </select>
                        </div>
                        
                    </div>
                    <div class='form-group'>
                        <label class="col-md-3 control-label">Official Business Start Date *</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control date_picker" id="date_start" name='date_start'>
                        </div>
                        <label class="col-md-2 control-label">Official Business End Date *</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control date_picker" id="date_end" name='date_end'>
                        </div>
                    </div>
                    
                    <div class='form-group'>
                        <div class='col-md-7 text-right'>
                            <button type='button' class='btn-flat btn btn-warning' onclick='filter_search()'><span class="fa fa-search"></span> Filter</button>
                           <button  type='button' onclick="form_clear('frmclear')" class="btn btn-default">Clear</button>
                        </div>
                    </div>
                </form>
   
            <div class='col-md-12'>
              <?php 
                Alert();
              ?>
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                      <div class="col-sm-12 text-right" style="margin-top: 10px; margin-bottom: 10px">
                        <form class="" method="POST" onsubmit="return approve_all()" action="approve_all.php">
                            <input type="hidden" name="approve_dep_id">
                            <input type="hidden" name="approve_emp_id">
                            <input type="hidden" name="approve_start_date">
                            <input type="hidden" name="approve_end_date">
                            <input type="hidden" name="type" value='official_business'>
                            <button class="btn btn-flat btn-success" title="Approve All Requests"><span class="ion ion-checkmark-round"></span> Approve All</button>
                        </form>
                      </div>
                    <div class="col-sm-12">
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
                              <th class='text-center'>Employee Code</th>
                              <th class='text-center'>Employee</th>
                              <th class='text-center'>Department</th>
                              <th class='text-center date-td'>Date Filed</th>
                              <th class='text-center date-td'>Date of OB</th>
                              <th class='text-center time-td'>Time From</th>
                              <th class='text-center time-td'>Time To</th>
                              <th class='text-center'>Destination</th>
                              <th class='text-center'>Purpose</th>
                              
                             
                              <th class='text-center'>Status</th>
                              <th class='text-center'>Step</th>
                              <th class='text-center' style='min-width:120px'>Action</th>
                            </tr>
                          </thead>
                          <tbody>
                          
                            <?php

                            /*
                              while($row = $data->fetch(PDO::FETCH_ASSOC)):
                            ?>
                              <tr>
                                <td><?php echo htmlspecialchars($row['code'])?></td>
                                <td><?php echo htmlspecialchars($row['employee_name'])?></td>
                                <td><?php echo htmlspecialchars($row['date_filed'])?></td>
                                <td><?php echo htmlspecialchars($row['ob_date'])?></td>
                                <td><?php echo htmlspecialchars($row['time_from'])?></td>
                                <td><?php echo htmlspecialchars($row['time_to'])?></td>
                                <td><?php echo htmlspecialchars($row['destination'])?></td>
                                <td><?php echo htmlspecialchars($row['purpose'])?></td>
                                <td><?php echo htmlspecialchars($row['supervisor'])?></td>
                                <td><?php echo htmlspecialchars($row['final_approver'])?></td>
                                <td><?php 
                                    echo htmlspecialchars($row['status'].($row['status']==""))
                                  ?></td>
                                <td class='text-center'>
                                  <form method="post" action='move_approval.php' style='display: inline' onsubmit='return confirm("Approve This Request?")'>
                                  <input type='hidden' name='id' value='<?php echo $row['id']; ?>'>
                                  <input type='hidden' name='type' value='official_business'>
                                  <button class='btn btn-sm btn-success' name='action' value='approve' title='Approve Request'><span class='fa fa-check'></span></button>
                                  </form>
                                  <?php
                                    if(!empty($row['evidence']) && file_exists("ob_evidence/".$row['evidence'])):?>
                                    <button class='btn btn-sm btn-info ' onclick='show_image_modal("<?php echo $row['evidence']?>")' title='View Evidence'><span class='fa fa-search'></span></button>
                                  <?php
                                    endif;
                                  ?>
                                  <button class='btn btn-sm btn-info'  title='Query Request' onclick='query("<?php echo $row['id'] ?>")'><span  class='fa fa-question'></span></button>
                                  <button class='btn btn-sm btn-danger'  title='Reject Request' onclick='reject("<?php echo $row['id'] ?>")'><span class='fa fa-times'></span></button>
                                </td>
                              </tr>
                            <?php
                              endwhile;
                              */
                            ?>

                          </tbody>
                        </table>
                    </div><!-- /.col -->
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
          </div><!-- /.row -->
        </section><!-- /.content -->
  </div>
  <?php
    $request_type="official_business";
    $redirect_page="ob_approval.php";
    require_once("include/modal_reject.php");
    require_once("include/modal_query.php");
    require_once("include/pic_modal.php");
  ?>


<script type="text/javascript">
var dttable="";
  $(function () {
        dttable=$('#ResultTable').DataTable({
          "scrollX": true,

          "processing": true,
          "serverSide": true,
          "searching": false,
            "ajax":
            {    
                "url":"ajax/ob_approval.php",
                "data":function(d)
                {
                   d.dep_id=$("select[name='department_id']").val();
                    d.emp_id=$("select[name='employee_id']").val();
                    d.start_date=$("input[name='date_start']").val();
                    d.end_date=$("input[name='date_end']").val();
                }
            },
            "columnDefs": [{ "orderable": false, "targets": 11 }],
          "order": [[ 3, "desc" ]]});
      });

  function filter_search() 
    {
            dttable.ajax.reload();
            //console.log(dttable);
    }
    
      function approve_all() {
        if (confirm('Are you sure you want to approve all requests?')) {
            $("input[name='approve_dep_id']").val($("select[name='department_id']").val());
            $("input[name='approve_emp_id']").val($("select[name='employee_id']").val());
            $("input[name='approve_start_date']").val($("input[name='date_start']").val());
            $("input[name='approve_end_date']").val($("input[name='date_end']").val());
            return true;
        }
        return false;
    }
</script>

<?php
  Modal();
  makeFoot();
?>