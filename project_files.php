<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }

    $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
    $can_upload=$con->myQuery("SELECT id FROM projects_employees WHERE designation_id=? AND project_id=? AND employee_id=?",array($cur_phase['designation_id'],$project_id,$employee_id))->fetchAll(PDO::FETCH_ASSOC);
    $employees=$con->myQuery("SELECT e.id,CONCAT(e.first_name,' ',e.middle_name,' ',e.last_name) as employee_name FROM employees e JOIN departments ON e.department_id=departments.id WHERE e.is_deleted=0 AND e.is_terminated=0 AND (e.utype_id='1' OR e.utype_id='2' OR e.utype_id='3')")->fetchAll(PDO::FETCH_ASSOC);
        $project_phase=$con->myQuery("SELECT id,phase_name  FROM project_phases")->fetchAll(PDO::FETCH_ASSOC);
    $des=$con->myQuery("SELECT designation_id FROM projects_employees WHERE employee_id=? and project_id=?",array($employee_id,$project_id))->fetch(PDO::FETCH_ASSOC);
    $data=$con->myQuery("SELECT pf.id,pp.phase_name,file_name,date_modified,employee_id,pf.project_phase_id,pf.employee_id,(SELECT CONCAT(e.first_name,' ',e.last_name) FROM employees e WHERE e.id=employee_id) AS uploader FROM project_files pf JOIN project_phases pp ON pp.id=pf.project_phase_id WHERE is_deleted=0 AND project_id='$project_id' AND ((task_completion_id!=0 AND is_approved=1)OR(task_completion_id=0 AND project_application_id=0 AND phase_request_id=0 AND project_dev_id=0 AND is_approved=0)OR(project_application_id!=0 AND is_approved=1)OR(phase_request_id!=0 AND is_approved=1)OR(project_dev_id!=0 AND is_approved=1))")->fetchAll(PDO::FETCH_ASSOC);
    // var_dump($cur_phase);
    // die;
	makeHead("Project Files");
?>
       <div class="row">
            <div class='col-md-12'>
              <?php 
                Alert();
              ?>
            
              <div class="box box-warning">
                <div class="box-body"><br>
                  <div class="row">
                    <div class="col-sm-12">
                      <?php if($project_details['project_status_id']!=2){if((!empty($can_upload))||($des['designation_id']=='3')){?>
                       <form class='form-horizontal' action='save_proj_file.php' method="POST" enctype="multipart/form-data">
                          <div class="form-group">
                            <label for="purpose" class="col-md-3 control-label">Upload File: <br/> <small>Upload Limit: <?php echo ini_get('upload_max_filesize')."B";?> </small></label>
                            <div class="col-sm-5">
                              <input type='hidden' name='id' id='id' value='<?php echo $_GET['id'];?>'>
                              <input type='hidden' name='phase_id' id='id' value='<?php echo $cur_phase['project_phase_id'];?>'>
                              <input type='file' name='file' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true' required="">
                            </div>
                            <div class="col-md-1 text-center">
                              <button type='submit' class='btn btn-warning'>Upload </button>
                            </div>
                          </div>
                      </form>
                      <?php }} ?>
                      <!-- <form class='form-horizontal' action='' method="GET" onsubmit='return validate(this)'>
                      <div class="form-group">
                          <label for="phase_id" class="col-sm-2 control-label">Project Phase </label>
                          <div class="col-sm-3">
                            <select class='form-control cbo' name='phase_id' data-allow-clear='true' data-placeholder="Select Project Phases" <?php echo !(empty($_GET))?"data-selected='".$_GET['phase_id']."'":NULL ?> >
                            <?php
                              echo makeOptions($project_phase,"All Project Phases");
                            ?>
                            </select>
                          </div>
                          <label for="project_id" class="col-sm-2 control-label">Employee Name </label>
                          <div class="col-sm-3">
                            <select class='form-control cbo' name='employee_id' data-allow-clear='true' data-placeholder="Select Employee Name" <?php echo !(empty($_GET))?"data-selected='".$_GET['project_id']."'":NULL ?> >
                            <?php
                              echo makeOptions($employees);
                            ?>
                            </select>
                          </div>
                      </div>
                      <div class='form-group'>
                        <label for="date_from" class="col-sm-2 control-label">Date Start *</label>
                          <div class="col-sm-3">
                            <input type="text" class="form-control date_picker" id="date_from"  name='date_from' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_from']):''; ?>' required>
                          </div>
                        <label for="date_to" class="col-sm-2 control-label">Date End *</label>
                          <div class="col-sm-3">
                            <input type="text" class="form-control date_picker" id="date_to"  name='date_to' value='<?php echo !empty($_GET)?htmlspecialchars($_GET['date_to']):''; ?>' required>
                          </div>
                      </div>
                        <div class="form-group">
                          <div class="col-sm-6 col-md-offset-3 text-center">
                            <button type='submit' class='btn btn-warning'>Filter </button>
                            <a href='task_reports.php' class='btn btn-default'>Clear</a>
                          </div>
                        </div>
                    </form> -->
                    <table id='ResultTable' class='table table-bordered table-striped'>
                      <thead>
                        <tr>
                          <th class='text-center'>File</th>
                         <th class='text-center'>Phase Name</th>
                          <th class='text-center'>Date Uploaded</th>
                          <th class='text-center'>Uploaded By</th>
                          <th class='text-center'>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          foreach($data as $row):
                        ?>
                          <tr>
                            <td class='text-center'><?php echo htmlspecialchars($row['file_name'])?></td>
                            <td class='text-center'><?php echo htmlspecialchars($row['phase_name'])?></td>
                            <td class='text-center'><?php echo htmlspecialchars($row['date_modified'])?></td>
                            <td class='text-center'><?php echo htmlspecialchars($row['uploader'])?></td>
                            <td class='text-center'>
                              <a href='download_file.php?id=<?php echo $row['id']?>&type=c' class='btn btn-default'><span class='fa fa-download'></span></a>
                           <!--    <?php
                                if($row['employee_id']==$employee_id):
                              ?>
                              <a href='delete.php?t=pf&id=<?php echo $row['id']?>&proj=<?php echo $_GET['id']?> onclick="return confirm('This record will be deleted.')" class='btn btn-danger btn-sm'><span class='fa fa-trash'></span></a>
                              <?php
                                endif;
                              ?> -->
                            </td>
                          </tr>
                        <?php
                          endforeach;
                        ?>
                      </tbody>
                    </table>
                    </div><!-- /.col -->
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
          </div><!-- /.row -->

<script type="text/javascript">
  $(function () {
        $('#ResultTable').DataTable(<?php if(AllowUser(array(1,4))):?>{
               dom: 'Bfrtip',
                    buttons: [
                        // {
                        //     extend:"excel",
                        //     text:"<span class='fa fa-download'></span> Download as Excel File "
                        // }
                        ]
        }<?php endif;?>);
      });
     function validate(frm) {

    if(Date.parse($("#date_from").val()) > Date.parse($("#date_to").val())){
      alert("Date Start cannot be greater than Date End.");
      return false;
    }

    return true;
  }
</script>

<?php
    Modal();
	makeFoot();
?>