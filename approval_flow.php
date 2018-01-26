<?php
    require_once("support/config.php");
    if (!isLoggedIn()) {
        toLogin();
        die();
    }

    if (!AllowUser(array(1,4))) {
        redirect("index.php");
    }

    $department="";
    if (!empty($_GET['dep_id'])) {
        $department=$con->myQuery("SELECT id,name,description,parent_id,approver_id,payroll_group_id as company_id FROM departments dpt WHERE is_deleted=0 AND id=? LIMIT 1", array($_GET['dep_id']))->fetch(PDO::FETCH_ASSOC);

        if (empty($department)) {
            Modal("Invalid Record Selected");
            redirect("departments.php");
            die;
        }
    } else {
      redirect('departments.php');
      die;
    }
    $data="";
    if (!empty($_GET['id'])) {
      $data=$con->myQuery("SELECT id,name FROM approval_steps WHERE id =?", array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
      if (empty($data)) {
        redirect("approval_flow.php".(!empty($department['id'])?"?dep_id={$department['id']}":'') );
        die;
      }
    }
    makeHead("Departments Form");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
<style type="text/css">
  #ResultTable > tbody > tr > td:nth-child(1) {
    /*cursor: move;*/
  }
  /*#ResultTable > tbody > tr > td:nth-child(1)::after {
    display: inline-block;
    font: normal normal normal 14px/1 FontAwesome;
    font-size: 85%;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    float: right;
    content: "\f255  to reorder";
  }*/
</style>
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
                  <li role="presentation" class=""><a href="frm_departments.php?id=<?php echo $department['id']?>" >Department Information</a></li>
                  <li role="presentation" class="active"><a href="approval_flow.php<?php echo !empty($department['id'])?"?dep_id={$department['id']}":'' ?>" disabled>Approval Flow</a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                  <div role="tabpanel" class="tab-pane active" id="department_form">
                    <div class="row">
                      <div class='col-md-12'>
                        <div class="col-md-8 col-md-offset-2">
                          <div class="box box-default">
                            <div class="box-header ">
                              <h3 class="box-title">Step Form</h3>
                            </div>
                            <div class="box-body text-center">
                              <form class="form-inline disable-submit"  onsubmit="return add_new_step();" action="save_approval_flow_step.php" method="POST">
                                <input type="hidden" name="department_id" value="<?php echo $department['id'] ?>">
                                <input type="hidden" name="id" value="<?php echo !empty($data['id'])?$data['id']:'' ?>">
                                <div class="form-group">
                                  <label for="step_name">Step Name</label>
                                  <input type="text" style="width: 300px" name='step_name' class="form-control" id="step_name" placeholder="Enter Step Name" required="" value="<?php echo !empty($data['name'])?$data['name']:'' ?>">
                                </div>
                                <a href="approval_flow.php<?php echo !empty($department['id'])?"?dep_id={$department['id']}":'' ?>" class="btn btn-default">Cancel</a>
                                <button type="submit" class="btn btn-success"><?php echo (empty($_GET['id']))?"Add New Step":"Update"?></button>
                              </form>
                            </div>
                          </div>
                        </div>
                        <table id="ResultTable" class='table table-bordered table-striped'>
                          <thead>
                            <th style="width: 100px">Step Number</th>
                            <th>Step Name</th>
                            <th>Employees</th>
                            <th style="width: 100px">Action</th>
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
            "url":"ajax/approval_flow.php",
            "data":function(d){
                d.department_id=<?php echo $department['id'] ?>;
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
      });
</script>

<?php
    makeFoot();
?>