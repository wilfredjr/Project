<?php
	require_once("support/config.php");
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}
    $usertype=$con->myQuery("SELECT user_type_id FROM users WHERE employee_id=:employee_id",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
    if ($usertype!=5) {
        redirect("index.php");
    }
    // if(!AllowUser(array(1,2)))
    // {
    //     redirect("index.php");
    // }
	$data=""; 

	if(!empty($_GET['id']))
    {
  		$data=$con->myQuery("SELECT id,name,cur_phase,project_status_id,manager_id FROM projects WHERE id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
  		if(empty($data) OR ($data['project_status_id']!=2))
        {
  			Modal("Invalid Record Selected");
  			redirect("bug_management.php");
  			die;
  		}
	}
    $emp_id=$_SESSION[WEBAPP]['user']['employee_id'];
    $project_status=$con->myQuery("SELECT status_name FROM project_status WHERE id=?",array($data['project_status_id']))->fetch(PDO::FETCH_ASSOC);
    $bug_rates=$con->myQuery("SELECT id,name FROM project_bug_rate")->fetchAll(PDO::FETCH_ASSOC);
	makeHead("Application for Bug Form");
?>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 <div class="content-wrapper">
    <section class="content-header text-center">
        <h1>
             Bug Management Form
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-10 col-md-offset-1'>
				<?php	Alert();
          Modal();	?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class='col-md-12'>
                                <div class='col-md-10  col-md-offset-2' >
                                      <br><table><tr>
                                      <td><b> Project Name: </b><?php echo $data['name'];?></td><td><b>Project Status: </b><?php echo $project_status['status_name'];?> </td></tr>
                                    </table>
                                    </div><br><br><br>
                                <form class='form-horizontal disable-submit' action='save_bug_management.php' method="POST" enctype="multipart/form-data">
                                    <input type='hidden' name='project_id' value='<?php echo !empty($data)?$data['id']:''; ?>'>
                                    <input type='hidden' name='manager_id' value='<?php echo !empty($data)?$data['manager_id']:''; ?>'>
                                     <div class='form-group'>
                                        <label for="name" class="col-sm-2 control-label">Bug Name: </label>
                                        <div class='col-sm-5'>
                                            <input type='text' class="form-control" name='bug_name' required placeholder="Enter Bug Name">
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <label class='col-md-2 text-right'>Rating: </label>
                                        <div class='col-md-5'>
                                        <select class='form-control cbo' name='bug_rate' id='bug_rate' data-allow-clear='True' data-placeholder="Select Bug Rating" required>
                                        <?php echo makeOptions($bug_rates); ?>
                                       </select>
                                      </div>
                                    </div>
                                   <div class="form-group">
                                    <label for="file" class="col-sm-2 control-label">File: </label>
                                      <div class="col-sm-5">
                                        <input type='file' name='file' class="filestyle" data-classButton=""  data-buttonName="btn btn-flat btn-default" data-input="true" data-classIcon="icon-plus" data-buttonText=" &nbsp;Select File" data-buttonBefore='true' required>
                                      </div>
                                    </div>
                                     <div class="form-group">
                                        <label for="worked_done" class="col-sm-2 control-label">Description: </label>
                                        <div class="col-sm-9">
                                          <textarea class='form-control' id='desc' name='desc' rows='5' value='<?php echo !empty($data)?$data['worked_done']:''; ?>' required></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="text-center">
                                            <button type='submit' class='btn btn-warning'>Save </button>
                                            <a href='bug_management_project.php?id=<?php echo $_GET['id'];?>' class='btn btn-default' onclick="return confirm('Are you sure you want to cancel this application?')">Cancel</a>
                                        </div>
                                    </div>
                                </form>	
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    var employees_id="";

  $(function () {
        $('#ResultTable').DataTable();
        <?php
        if (!empty($canFileForEmployees)) {
        ?>
        employees_id=$("select[name='employees_id[]']").select2();
        <?php
        }
        ?>
      });
    function getEmployeeLeaves(emp_select) {
        leave_select=$("select[name='leave_id']");
        leave_select.select2("val","");
        leave_select.select2("enable",false);
        leave_select.select2({
          ajax: {
            url:'./ajax/cbo_employee_leaves.php?emp_id='+emp_select.value,
            dataType: "json",
            data: function (params) {

                var queryParameters = {
                    term: params.term
                }
                return queryParameters;
            },
            processResults: function (data) {
                  return {
                      results: $.map(data, function (item) {
                          // console.log(item);
                          return {
                              text: item.description,
                              id: item.id
                          }
                      })
                  };
            }
          }
        });
        leave_select.removeAttr('disabled')
        leave_select.select2("enable", true);
    }
    function getProjectEmployees(project_select) {
    project_id=$("input[name='id']").val();
    if(project_select.value==3){
        des=1;
    }else{
        des=2;
    }
    employees_id.select2("val", "");
    employees_id.select2("enable", false);
    employees_id.select2({
      ajax: {
        url:'./ajax/cbo_project_employees1.php?project_id='+project_id+'&des_id='+des,
        dataType: "json",
        data: function (params) {

            var queryParameters = {
                term: params.term
            }
            return queryParameters;
        },
        processResults: function (data) {
              return {
                  results: $.map(data, function (item) {
                      // console.log(item);
                      return {
                          text: item.description,
                          id: item.id
                      }
                  })
              };
        }
      }
    });
    employees_id.removeAttr('disabled')
    employees_id.select2("enable", true);
    // $.get( "./ajax/cbo_project_employees.php", { project_id: project_select.value } )
    // .done(function( data ) {
      
    // });
  }
  var dttable="";
$(document).ready(function ()
{
    dttable=$('#ResultTable1').DataTable({
        "scrollX": false,
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
<?php
    makeFoot();
?>