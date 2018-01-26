<div class="modal" id='modal_submit'>
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action='save_project_phase_status1.php' enctype="multipart/form-data">
          <input type='hidden' name='id' id='task_id' value=''>
          <input type='hidden' name='action' value='submit'>
          <input type='hidden' name='type' value='comp'>
          <input type='hidden' name='phase_id' value='<?php echo $cur_phase['project_phase_id'];?>'>
         <input type='hidden' name='proj_id' value='<?php echo $cur_phase['project_id'];?>'>
         <input type='hidden' name='manager_id' value='<?php echo $project_details['manager'];?>'>
         <input type='hidden' name='admin_id' value='<?php echo $project_details['employee_id'];?>'>
         <input type='hidden' name='des_id' value='<?php echo $cur_phase['cur_des_id'];?>'>
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Phase Completion Request</h4>
          </div>
          <div class="modal-body" >
             <div class="form-group">
            <label for="purpose" class="col-sm-3 control-label text-right">Upload File: <br/> <small>Upload Limit: <?php echo ini_get('upload_max_filesize')."B";?> </small></label>
                <div class="col-sm-9">
                  <input type='file' name='file' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true' required>
                </div>
            </div><br><br>
            <div class='form-group'>
            <label class='pull-left'>Comment:</label>
              <textarea name='reason' id='reason' required="" class='form-control' style='resize: none' rows='4'></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-warning">Submit</button>
          </div>
        </form>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->