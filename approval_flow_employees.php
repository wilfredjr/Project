<?php
    require_once("support/config.php");
    if (!isLoggedIn()) {
        toLogin();
        die();
    }

    if (!AllowUser(array(1,4))) {
        redirect("index.php");
    }

    $step="";
    if (!empty($_GET['id'])) {
        $step=$con->myQuery("SELECT aps.id,aps.name,dep.name as department,aps.department_id FROM approval_steps aps JOIN departments dep ON aps.department_id=dep.id WHERE aps.is_deleted=0 AND aps.id=? LIMIT 1", array($_GET['id']))->fetch(PDO::FETCH_ASSOC);

        if (empty($step)) {
            Modal("Invalid Record Selected");
            redirect("departments.php");
            die;
        }
    } else {
      redirect('departments.php');
      die;
    }
    makeHead("Approval Step Employees");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Departments Form
          </h1>
          <br/>
          <a href='departments.php' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Department list</a>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">

            <div class='col-md-12'>
                <?php
                    Alert();
                ?>
              <div class="nav-tabs-custom">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                  <li role="presentation" class=""><a href="frm_departments.php?id=<?php echo $step['department_id']?>" >Department Information</a></li>
                  <li role="presentation" class="active"><a href="approval_flow.php<?php echo !empty($step['id'])?"?dep_id={$step['id']}":'' ?>" disabled>Approval Flow</a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                  <div role="tabpanel" class="tab-pane active" id="department_form">
                    <div class="row">
                      <div class='col-md-12'>
                      <a href='approval_flow.php?dep_id=<?php echo $step['department_id'];?>' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Approval Flow</a>
                      <br/>
                        <div class="col-md-8 col-md-offset-2">
                          <div class="box box-default">
                            <div class="box-header">
                              <h3 class="box-title">Add New Employee</h3>
                            </div>
                            <div class="box-body text-center">
                              <form method="POST" class="form-inline disable-submit" action="save_approval_flow_employees.php">
                                <input type="hidden" name="step_id" value="<?php echo $step['id'] ?>">
                                <div class="form-group">
                                  <br/>
                                  <label for="exampleInputEmail3">Employee </label>
                                  <select name='employee_id' class="form-control cbo-step-employee-id" id="employee_id" style="width:400px" required="true">
                                  </select>
                                  <button type="submit" class="btn btn-success">Add Employee</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                        
                        <table id="ResultTable" class='table table-bordered table-striped'>
                          <thead>
                            <th>Code</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Action</th>
                          </thead>
                          <tbody></tbody>
                        </table>
                      </div>
                      </div><!-- /.row -->
                  </div>
                </div>
              </div>
            </div>
          </div><!-- /.row -->
        </section><!-- /.content -->
  </div>

<script type="text/javascript">
  var data_table="";
  var counter = 1;
  function add_new_step() {
    
    if ($.trim($("#step_name").val())==""){
      alert('Enter step name.');
      return false;
    } else {
      return true;
      // data_table.row.add([counter,$.trim($("#step_name").val()),"<button class='btn btn-success'></button>"]).draw(false);
      // $("#step_name").val("");
      // counter++;
    }
    // return false;
  }
  $(function () {
        
        data_table=$('#ResultTable').DataTable({
          "ordering":false,
          "searching":false,
          "lengthChange":false,
          "processing":true,
          "pageLength":100,
          "autoWidth":false,
          "rowReorder":{
            "update":false
          },
          "ajax":{
            "url":"ajax/approval_flow_employees.php",
            "data":function(d){
                d.step_id=<?php echo $step['id'] ?>;
              }
          },


        });

        data_table.on('row-reorder', function (e, diff, target) {
          //console.log(diff);
          
          // console.clear();
          var post_data=diff;
          for ( var i=0, ien=post_data.length ; i<ien ; i++ ) {
            post_data[i].step_id=$("tbody").find("tr").eq(diff[i].newPosition).find("input[name='step_id']").val();
          // console.log($("tbody").find("tr").eq(i).find("input[name='step_id']").val());
          //     var rowData = data_table.row( diff[i].node ).data();
              
          //     // result += rowData[1]+' updated to be in position '+
          //     //     diff[i].newData+' (was '+diff[i].oldData+')<br>';
          }
          // console.log(jpost_data);
          // $.ajax({
          //  type: "POST",
          //  url: "ajax/reorder_approval_flow.php",
          //  data: post_data,
          //  contentType: false,
          //  dataType: false,
          //  success: function(msg) {
          //  }
          // });
          data_table.rowReorder.disable();
          data_table.processing( true );
          $.post("ajax/reorder_approval_flow.php",
            {post_data:JSON.stringify(post_data)}).always(function() {
              
              data_table.ajax.reload();
              setTimeout( function () {
                data_table.processing( false );
                data_table.rowReorder.enable();
              }, 500 );
            });
          // console.log();
        });

        $(".cbo-step-employee-id").select2({
                  placeholder:"Select Employee",
                  ajax: {
                      url: "ajax/cbo_step_employees.php",
                      dataType: "json",
                      type: "GET",
                      data: function (params) {
                          var queryParameters = {
                              term: params.term,
                              step_id:<?php echo $step['id'] ?>
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
                  },
                  allowClear:$(this).data("allow-clear")
        }); 
  
  });

</script>

<?php
    makeFoot();
?>