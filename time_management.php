<?php
  require_once("support/config.php");
   if(!isLoggedIn()){
    toLogin();
    die();
   }

$has_record=NULL;
  $attendance=$con->myQuery("SELECT id,note,out_time FROM attendance WHERE employees_id=? ORDER BY in_time DESC LIMIT 1",array($_SESSION[WEBAPP]['user']['employee_id']))->fetch(PDO::FETCH_ASSOC);
  if (!empty($attendance) && (empty($attendance['out_time']) || $attendance['out_time']=="0000-00-00 00:00:00") ) {
    $has_record=true;
  }
  makeHead("Attendance");
?>

<?php
  require_once("template/header.php");
  require_once("template/sidebar.php");
?>
  <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>Attendance</h1>
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
                <div class="box-body">
                  <div class="row">
                      <?php
                        $time_in_module=$con->myQuery("SELECT time_in_module FROM settings LIMIT 1")->fetchColumn();
                        if(!empty($time_in_module)):
                      ?>
                    <div class='col-md-12 text-right'>
                        <button class='btn btn-warning' type='button' data-toggle="modal" data-target="#timeModal">
                          <span class='fa fa-clock-o'></span> <?php echo !empty($has_record)?'Punch Out':'Punch In'?>
                        </button>
                      
                    </div>
                    <br/>
                    <br/>
                    <?php
                      endif;
                    ?>
                    <div class="col-sm-12">
                        
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
                              <th class='text-center date-td'>Date</th>
                              <th class='text-center date-td'>Time In</th>
                              <th class='text-center date-td'>Date</th>
                              <th class='text-center date-td'>Time Out</th>
                              <th class='text-center'>Note</th>
                              <th class='text-center' style='max-width: 60px'>Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            
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

   
    <div class="modal" id='modal_adjustment'>
    <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="">Adjust</h4>
          </div>
          <div class="modal-body" >
            <div class='' id='' style=''>
              <form class='form-horizontal' action='save_adjustment.php' method="POST" onsubmit='return validateAdjustment(this)'>
                <input type='hidden' value='' id='a_id' name='id'>
            

                <div class="form-group">
                    <label for="" class="col-sm-4 control-label">Original Time in * </label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control"  name='' id='orig_in_time' value='' disabled="">
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-4 control-label">Original Time Out * </label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control"  name='' id='orig_out_time' value='' disabled="">
                    </div>
                </div>
                
                <div class='form-group'>
                  <label for="adj_in_time" class="col-sm-4 control-label">Adjusted Time In *</label>
                    <div class="col-sm-8">
                      <div class="input-group bootstrap-timepicker timepicker">
                        <input type="text" class="form-control time_picker" name='adj_in_time' id='adj_in_time' value='' required>
                      </div>
                    </div>
                </div>
                <div class='form-group'>
                  <label for="adj_out_time" class="col-sm-4 control-label">Adjusted Time Out *</label>
                    <div class="col-sm-8">
                      <div class="input-group bootstrap-timepicker timepicker">
                        <input type="text" class="form-control time_picker" name='adj_out_time' id='adj_out_time' value='' required>
                      </div>
                    </div>
                </div>

                <div class='form-group'>
                  <label class='pull-left col-md-12'>Enter Reason *</label>
                  <div class='col-md-12'>
                    <textarea name='reason' required="" class='form-control ' style='resize: none' rows='4'></textarea>
                  </div>
                </div>
                <div class='form-group '>
                  <div class='col-md-4 col-md-offset-8'>
                    <button type='submit' class='btn btn-warning'>
                      Request for Adjustment
                    </button>
                  </div>
              </div>
              </form>
            </div>
          </div>
          
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
<div class="modal fade" id="timeModal" tabindex="-1" role="dialog" aria-labelledby="timeModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action='time_action.php'>
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title"><?php echo !empty($has_record)?'Punch Out':'Punch In'?></h4>
        </div>
        <div class="modal-body" >
          <div class='form-group'>
          <label class='pull-left'>Note:</label>
            <textarea name='note' class='form-control' style='resize: none' rows='4'><?php echo !empty($attendance)?$attendance['note']:''?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(function () {
        $('#ResultTable').DataTable({
                
                "processing": true,
                "serverSide": true,
                "ajax":"ajax/time_management.php",
                "aoColumnDefs": [
                  { "sClass": "text-center", "aTargets": [ -1 ] }
                ],
                "order": [[ 0, "desc" ]]
                
        });

        // $('#modal_comments').on('show.bs.modal', function (e) {
        //   $("#comment_table").load("ajax/comments.php");
        // })
      });

  function adjustment(btn){
            $('#modal_adjustment').modal('show');


           // $("#adj_in_time").data("DateTimePicker").defaultDate(new Date($(btn).data("in-time")));
           // $("#adj_out_time").data("DateTimePicker").defaultDate(new Date($(btn).data("out-time")));

            $("#orig_in_time").val($(btn).data("in-time"));
            $("#orig_out_time").val($(btn).data("out-time"));
            $("#a_id").val($(btn).data("id"));

        }
  function validateAdjustment(frm) {
    console.log($("#adj_in_time").val() > $("#adj_out_time").val());
    
    if(Date.parse($("#adj_in_time").val()) > Date.parse($("#adj_out_time").val())){
      alert("Time in cannot be greater than time out.");
      return false;
    }
    else if(Date.parse($("#adj_in_time").val()) == Date.parse($("#adj_out_time").val())){
      alert("Time out should be greater than time in.")
      return false;
    }

    // $("#adj_in_time").data("DateTimePicker").defaultDate(new Date($(btn).data("in-time")));
    // $("#adj_out_time").data("DateTimePicker").defaultDate(new Date($(btn).data("out-time")));
    return true;
  }
</script>

<?php
  Modal();
  makeFoot();
?>