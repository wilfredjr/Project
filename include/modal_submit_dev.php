<div class="modal" id='modal_submit'>
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action='move_approval.php' enctype="multipart/form-data">
          <input type='hidden' name='id' id='task_id' value=''>
          <input type='hidden' name='action' value='approve'>
          <input type='hidden' name='type' value='<?php echo htmlspecialchars($request_type)?>'>
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Development Man Hours Request</h4>
          </div>
          <div class="modal-body" >
             <div class="form-group">
            <label for="purpose" class="col-sm-3 control-label text-right">Upload File: <br/> <small>Upload Limit: <?php echo ini_get('upload_max_filesize')."B";?> </small></label>
                <div class="col-sm-9">
                  <input type='file' name='file' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true'>
                </div>
            </div><br><br>
            <div class="box box-warning">
            <div class="box-body">
            <div class='form-group'>
            <label class='pull-left'>Development Man Hours:</label>
              <input type='number' name='hours' id='hours' min='1' required="1" class='form-control' placeholder="Number of Development Man Hours">
            </div>
            <div class="text-center"><i>*Division of Hours* <br> *hours will be divided by the number of developers*</i></div>
            <div class='form-group'>
            <label class='pull-left'>UI:</label>
              <input type='number' name='ui' id='ui' min='1' required="1" class='form-control col-md-1' placeholder="Hours in User Interface">
            </div>
            <div class='form-group'>
            <label class='pull-left'>Database Design:</label>
              <input type='number' name='database' id='database' min='1' required="1" class='form-control' placeholder="Hours in database design">
            </div>
            <div class='form-group'>
            <label class='pull-left'>Coding:</label>
              <input type='number' name='coding' id='coding' min='1' required="1" class='form-control' placeholder="Hours in coding">
            </div>
            <div class='form-group'>
            <label class='pull-left'>Comment:</label>
              <textarea name='work_done' id='work_done' required="" class='form-control' style='resize: none' rows='4'></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-warning">Submit</button>
          </div>
          </div>
          </div>
        </form>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->