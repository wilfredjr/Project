<div class="modal" id='modal_reject'>
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action='save_bug_phase.php' enctype="multipart/form-data">
          <input type='hidden' name='id' id='reject_id' value=''>
          <input type='hidden' name='action' value='submit'>
          <input type='hidden' name='project_id' value='<?php echo htmlspecialchars($data['project_id'])?>'>
          <input type='hidden' name='bug_phase_id' value='<?php echo htmlspecialchars($data['bug_phase_id'])?>'>
          <input type='hidden' name='type' value='rev'>
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Endorse Errors</h4>
          </div>
          <div class="modal-body" >
             <div class="form-group">
            <label for="purpose" class="col-sm-3 control-label text-right">Upload File: <br/> <small>Upload Limit: <?php echo ini_get('upload_max_filesize')."B";?> </small></label>
                <div class="col-sm-9">
                  <input type='file' name='file' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true' required>
                </div>
            </div><br><br>
            <div class='form-group'>
            <label class='pull-left'>Comment: </label>
              <textarea name='work_done' id='work_done' required="" class='form-control' style='resize: none' rows='4'></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-warning">Save</button>
          </div>
        </form>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->