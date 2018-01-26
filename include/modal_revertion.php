<div class="modal" id='modal_reject'>
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action='save_project_phase_status1.php' enctype="multipart/form-data">
          <input type='hidden' name='id' id='reject_id' value=''>
          <input type='hidden' name='action' value='reject'>
          <input type='hidden' name='type' value='rev'>
          <input type='hidden' name='phase_id' value='<?php echo $cur_phase['project_phase_id'];?>'>
         <input type='hidden' name='proj_id' value='<?php echo $cur_phase['project_id'];?>'>
         <input type='hidden' name='manager_id' value='<?php echo $project_details['manager'];?>'>
         <input type='hidden' name='admin_id' value='<?php echo $project_details['employee_id'];?>'>
         <input type='hidden' name='des_id' value='<?php echo $cur_phase['cur_des_id'];?>'>
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Revert to Previous Phase</h4>
          </div>
          <div class="modal-body" >
            <div class='form-group'>
                    <label for="purpose" class="col-sm-3 control-label text-right">Upload File: <br/> <small>Upload Limit: <?php echo ini_get('upload_max_filesize')."B";?> </small></label>
            <input type='file' name='file' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true' required>
            </div>
            <div class='form-group'>
            <label class='pull-left'>Hours of Extension:</label>
              <input type='number' name='hours' id='hours' min='1' required="1" class='form-control'>
            </div>
            <div class='form-group'>
            <label class='pull-left'>Reason for Revertion:</label>
              <textarea name='reason' required="1" class='form-control' style='resize: none' rows='4'></textarea>
            </div>
          </div>
          <div class="modal-footer">
             <div class='text-center'>
            <button type="submit" class="btn btn-warning">Save</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
          </div>
        </form>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->