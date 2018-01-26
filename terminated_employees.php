<?php
	require_once("support/config.php");
	 if(!isLoggedIn()){
	 	toLogin();
	 	die();
	 }

     if(!AllowUser(array(1,4))){
         redirect("index.php");
     }

  $data=$con->myQuery("SELECT 
e.id,e.code,CONCAT(e.last_name,', ',e.first_name,' ',e.middle_name) as 'employee',e.private_email,e.contact_no, jt.description as 'job_title',d.name as 'department',termination_date
FROM employees e LEFT JOIN job_title jt ON e.job_title_id=jt.id LEFT JOIN departments d ON e.department_id=d.id WHERE e.is_deleted=0 AND e.is_terminated=1");
	makeHead("Employees");
?>

<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 	<div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Terminated Employees
          </h1>
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
                    <div class="col-sm-12">
                        <br/>
                        <table id='ResultTable' class='table table-bordered table-striped'>
                          <thead>
                            <tr>
                              <th class='text-center'>Terminate Date</th>
                              <th class='text-center'>Employee Code</th>
                              <th class='text-center'>Employee</th>
                              <th class='text-center'>Job Title</th>
                              <th class='text-center'>Department</th>
                              <th class='text-center'>Email</th>
                              <th class='text-center'>Contact No</th>
                              <th class='text-center' style='min-width:150px'>Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              while($row = $data->fetch(PDO::FETCH_ASSOC)):
                            ?>
                              <tr>
                                <td><?php echo htmlspecialchars($row['termination_date'])?></td>
                                <td><?php echo htmlspecialchars($row['code'])?></td>
                                <td><?php echo htmlspecialchars($row['employee'])?></td>
                                <td><?php echo htmlspecialchars($row['job_title'])?></td>
                                <td><?php echo htmlspecialchars($row['department'])?></td>
                                <td><?php echo htmlspecialchars($row['private_email'])?></td>
                                <td><?php echo htmlspecialchars($row['contact_no'])?></td>
                                <td class='text-center'>
                                  <a href='delete.php?t=te&id=<?php echo $row['id']?>' title='Delete Employee' onclick="return confirm('This record will be deleted.')" class='btn btn-danger btn-sm'><span class='fa fa-trash'></span></a>
                                  <a href='unterminate.php?id=<?php echo $row['id']?>' title='Restore Employee' onclick="return confirm('This employee will be restored.')" class='btn btn-success btn-sm'><span class='fa fa-refresh'></span></a>
                                  <form action="download_employee_details.php" method="post" name="export_excel" style='display:inline'>
                                      <input type='hidden' name='employees_id' value=<?php echo $row['id']; ?>>
                                      <button type="submit" id="export" name="export" class="btn btn-info btn-sm" ><span class='fa fa-download'></span></button>
                                </form> 
                                </td>
                              </tr>
                            <?php
                              endwhile;
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
        $('#ResultTable').DataTable({
            "scrollX": true,
            dom: 'Bfrtip',
                buttons: [
                    {
                        extend:"excel",
                        text:"<span class='fa fa-download'></span> Download as Excel File "
                    }
                    ],

        });
      });
</script>

<?php
  Modal();
	makeFoot();
?>