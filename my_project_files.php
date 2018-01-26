<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }
   $employee_id=$_SESSION[WEBAPP]['user']['employee_id'];
	 $results=$con->myQuery("SELECT project_id FROM projects_employees WHERE employee_id='$employee_id'")->fetchAll(PDO::FETCH_ASSOC);

	makeHead("Project Files");
?>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Project Files
          </h1>
          <br/>
        </section>

        <!-- Main content -->
        <section class="content">

       <div class="row">
            <div class='col-md-12'>
              <?php 
                Alert();
              ?>
            
              <div class="box box-warning">
                <div class="box-body">
                  <div class="row">
                    <div class="col-sm-12">
<!--                     <?php if(AllowUser(array(1,4))): ?>
                       <form class='form-horizontal' action='save_comp_file.php' method="POST" enctype="multipart/form-data">
                          <div class="form-group">
                            <label for="certification_id" class="col-md-1 col-md-offset-8 control-label">File *</label>
                            <div class="col-md-1">
                              <input type='file' name='file' class="filestyle" data-classButton="btn btn-primary" data-input="false" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File">
                            </div>
                            <div class="col-md-2 text-center">
                              <button type='submit' class='btn btn-warning'>Upload </button>
                            </div>
                          </div>
                      </form>

                    <br/>
                    <?php endif; ?> -->
                    <table id='ResultTable' class='table table-bordered table-striped'>
                      <thead>
                        <tr>
                           <th class='text-center'>Project Name</th>
                          <th class='text-center'>File</th>
                          <th class='text-center'>Date Uploaded</th>
                          <th class='text-center'>Uploaded By</th>
                          <th class='text-center'>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                           foreach($results as $value) {
                              // your code
                              $data=$con->myQuery("SELECT pf.id as id,file_name,date_modified,pf.employee_id,project_phase_id,(SELECT CONCAT(e.first_name,' ',e.last_name) FROM employees e WHERE e.id=pf.employee_id) AS uploader,p.name AS project_name
                              FROM project_files pf JOIN projects p ON p.id=pf.project_id WHERE pf.is_deleted=0 AND pf.project_id=? ",array($value['project_id']))->fetchAll(PDO::FETCH_ASSOC);
                        
                          foreach($data as $row):
                        ?>
                          <tr>
                            <td class='text-center'><?php echo htmlspecialchars($row['project_name'])?></td>
                            <td class='text-center'><?php echo htmlspecialchars($row['file_name'])?></td>
                            <td class='text-center'><?php echo htmlspecialchars($row['date_modified'])?></td>
                             <td class='text-center'><?php echo htmlspecialchars($row['uploader'])?></td>
                            <td class='text-center'>
                              <a href='download_file.php?id=<?php echo $row['id']?>&type=c' class='btn btn-default'><span class='fa fa-download'></span></a>
<!--                               <?php
                                if($row['employee_id']==$employee_id):
                              ?>
                              <a href='delete.php?t=cf&id=<?php echo $row['id']?>' onclick="return confirm('This record will be deleted.')" class='btn btn-danger btn-sm'><span class='fa fa-trash'></span></a>
                              <?php
                                endif;
                              ?> -->
                            </td>
                          </tr>
                        <?php
                          endforeach;}
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

<script type="text/javascript">
  $(function () {
        $('#ResultTable').DataTable(<?php if(AllowUser(array(1,4))):?>{
               dom: 'Bfrtip',
                    buttons: [
                        // {
                        //     // extend:"excel",
                        //     // // text:"<span class='fa fa-download'></span> Download as Excel File "
                        // }
                        ]
        }<?php endif;?>);
      });
</script>

<?php
    Modal();
	makeFoot();
?>