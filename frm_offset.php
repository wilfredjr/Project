<?php
	require_once("support/config.php");
	if(!isLoggedIn())
    {
		toLogin();
		die();
	}
    // if(!AllowUser(array(1,2)))
    // {
    //     redirect("index.php");
    // }

    $count_offset=0;
    $offset=$con->myQuery("SELECT offset_count FROM employees_offset WHERE employees_id=?",array($_SESSION[WEBAPP]['user']['employee_id']))->fetch(PDO::FETCH_ASSOC);
    if(!empty($offset)) 
    {
        $count_offset=$offset['offset_count'];
    }
    
    $canFileForEmployees=canFileForEmployees($_SESSION[WEBAPP]['user']['employee_id']);
    
	makeHead("Offset Application Form");
?>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 <div class="content-wrapper">
    <section class="content-header text-center">
        <h1>
             Offset Application Form
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-10 col-md-offset-1'>
				<?php	Alert();	?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class='col-md-12'>
                                <form class='form-horizontal' action='save_offset.php' method="POST" onsubmit="return validate(this)">
                                    <div class='form-group'>
                                        <label for="ot_date" class="col-sm-3 control-label">Project *</label>
                                        <div class="col-sm-9">
                                            <select class='form-control cbo-project-id' name='project_id'  style='width:100%' data-allow-clear='true' <?php if (!empty($canFileForEmployees)) {?> onchange="getProjectEmployees(this)" <?php } ?> required=""></select>
                                        </div>
                                    </div>
                                    <?php
                                    if (!empty($canFileForEmployees)) {
                                    ?>
                                    <div class='form-group'>
                                        <label class='col-sm-9 col-md-offset-3'>Offset Count (in hours): &nbsp; <span id='count_offset'></span></label>
                                    </div>
                                    <div class='form-group'>
                                        <label for="employees_id" class="col-sm-3 control-label">Employee *</label>
                                        <div class="col-sm-9">
                                          <select class='form-control ' name='employee_id' id='employees_id' style='width:100%' required="" disabled="" onchange="getOffsetHours(this)" data-placeholder="Select Employee"></select>
                                        </div>
                                    </div>
                                    <?php
                                    } else {
                                    ?>
                                    <div class='form-group'>
                                        <label class='col-sm-3 control-label'>Offset Count (in hours): &nbsp; <?php echo $count_offset; ?> hour/s</label>
                                    </div>
                                    <br/>
                                    <?php
                                    }
                                    ?>
                                    <div class='form-group'>
                                        <label for="name" class="col-sm-3 control-label">Type of Request *</label>
                                        <div class='col-sm-9'>
                                            <select class='form-control cbo' name='request_type' data-placeholder="Select Type of Request" required>           
                                                <option value=""></option>
                                                <option value="1">Bank</option>
                                                <option value="2">Avail</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_start" class="col-md-3 control-label">Start Date and Time *</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control date_time_picker" id="date_start" name='date_start' required>
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label for="date_end" class="col-md-3 control-label">End Date and Time *</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control date_time_picker" id="date_end" name='date_end'>
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label for="reason" class="col-md-3 control-label">Remarks *</label>
                                        <div class="col-md-9">
                                            <textarea class='form-control' name='remarks' id='remarks'  required></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-9 col-md-offset-3 text-center">
                                            <button type='submit' class='btn btn-warning'>Save </button>
                                            <a href='offset.php' class='btn btn-default' onclick="return confirm('Are you sure you want to cancel this application?')">Cancel</a>
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
    $(function () 
    {
        $('#ResultTable').DataTable();
        employees_id=$("select[name='employee_id']").select2();

    });
    function getOffsetHours(emp_select) {
        $("#count_offset").html("").addClass("fa fa-spinner fa-spin");
        $.get( "ajax/getoffsethours.php?id="+emp_select.value, function( data ) {
          $( ".result" ).html( data );
            $("#count_offset").html(data+" hour/s").removeClass("fa fa-spinner fa-spin");
          
        });
    }
     function getProjectEmployees(project_select) {
        employees_id.select2("val", "");
        employees_id.select2("enable", false);
        employees_id.select2({
          ajax: {
            url:'./ajax/cbo_project_employees.php?project_id='+project_select.value,
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
         function validate(frm) {

    if(Date.parse($("#date_start").val()) > Date.parse($("#date_end").val())){
      alert("Start Date and Time cannot be greater than End Date and Time.");
      return false;
    }
    else if(Date.parse($("#date_start").val()) == Date.parse($("#date_end").val())){
      alert("End Date and Time should be greater than Start Date and Time.")
      return false;
    }
    $(frm).closest('form').find(':submit').button("loading");
    return true;
  }
</script>
<?php
    makeFoot();
?>