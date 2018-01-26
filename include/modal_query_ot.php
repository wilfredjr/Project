
  <div class="modal" id='modal_ot'>
    <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="">Inquiries</h4>
          </div>
          <div class="modal-body" >
            <div class='' id='comment_table_ot' style=''>
            </div>
          </div>
          <div class='modal-footer'>
            <form method='post' action='save_comments.php'>
              <input type='hidden' name='request_id' value='' id='request_id_ot'>
              <input type='hidden' name='request_type' value='overtime'>
              <input type='hidden' name='redirect_page' value='overtime_approval.php'>
              <div class='form-group'>
                <label class='pull-left'>Enter Message:</label>
                <textarea name='reason' required="" class='form-control' style='resize: none' rows='4'></textarea>
              </div>
              <div class='form-group'>
                <button type='submit' class='btn btn-warning'>
                  Send
                </button>
              </div>
            </form>
          </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->