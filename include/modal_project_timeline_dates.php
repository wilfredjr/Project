                                <!-- Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="alertModalLabel">
                <?php
                    echo $_SESSION[WEBAPP]['Modal']['Title'];
                ?>
                </h4>
            </div>
            <div class="modal-body">
                                       <div class='col-md-12'>
                                                                <table id='ResultTable1' class='table table-bordered table-striped'>
                                                                  <thead>
                                                                    <tr>
                                                                      <!-- <th class='text-center'>Type of OT</th> -->
                                                                      <th class='text-center'></th>
                                                                      <th class='text-center'>Phase Name</th>
                                                                      <th class='text-center'>Date Start</th>
                                                                      <th class='text-center'>Date End</th>
                                                                      <th class='text-center'>Status</th>
                                                                     <!--  <th class='text-center'>Deficit Days</th> -->
                                                                    </tr>
                                                                  </thead>
                                                                  <tbody>
                                                                  </tbody>
                                                                </table>
                                                            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<script type="text/javascript">
$('#alertModal').modal("show");
    var dttable="";
$(document).ready(function ()
{
    dttable=$('#ResultTable1').DataTable({
        "scrollX": true,
        "processing": true,
        "serverSide": true,
        "searching": false,
        "ajax":
              {    
                "url":"ajax/project_timeline_dates.php",
                "data":function(d)
                {
                     d.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
                }
     
            
            },
        "order": [[ 0, "asc" ]]
    });
});
</script>