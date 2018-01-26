<?php
	require_once("support/config.php");

    // if(!AllowUser(array(1,2)))
    // {
    //     redirect("index.php");
    // }
	
    // $proj_id=$con->myQuery("SELECT 
    // id,
    // employee_id
    // FROM projects
    // WHERE employee_id=?",array($_SESSION[WEBAPP]['user']['employee_id']));

    // $employee=$con->myQuery("SELECT 
    // id,
    // project_id,
    // employee_id,
    // is_deleted,
    
    
    // FROM project_employees
    // WHERE project_id=?",array($_SESSION[WEBAPP]['user']['employee_id']));


    if(!empty($_GET['id']))
    {
        $data=$con->myQuery("SELECT p.id,p.name,p.description,p.department_id,p.employee_id,DATE_FORMAT(p.start_date,'".DATE_FORMAT_SQL."') as start_date,p.end_date,p.date_filed,p.project_status_id, ps.status_name FROM projects p join project_status ps on p.project_status_id=ps.id WHERE p.id=? LIMIT 1",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        $project_status=$con->myQuery("SELECT status_name as proj_id, status_name as sta_name FROM project_status");
        if(empty($data))
        {
            Modal("Invalid Record Selected");
            redirect("project_management.php");
            die;
        }
    }





    $proj_sta=$con->myQuery("SELECT 
    status_name
    FROM project_status");
   

    
	makeHead("Application for Project Form");
?>
<style type="text/css">
        table.dataTable.select tbody tr,
        table.dataTable thead th:first-child {
            cursor: pointer;
        }
    </style>
<?php
	require_once("template/header.php");
	require_once("template/sidebar.php");
?>
 <div class="content-wrapper">
    <section class="content-header text-center">
        <h1>
             Project Application Form
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class='col-md-12'>
				<?php	Alert();	?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class='col-md-12'>
                                <form class='form-horizontal' id="frm-example" action='save_project_management.php' method="POST">
                                    <input type='hidden' name='project_id' class='form-control' id='project_id' value="<?php echo !empty($_GET['id'])?htmlspecialchars($_GET['id']):''?>">
                                    <div class='form-group'>

                                        <label for="proj_name" class="col-sm-2 control-label">Project Name *</label>
                                        <div class='col-sm-9'>
                                            <input type='text' class="form-control" name='proj_name' value='<?php echo !empty($data)?htmlspecialchars($data['name']):''; ?>' required>
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label for="date_start" class="col-md-2 control-label">Project Start Date *</label>
                                        <div class="col-md-9">
                                            <input type="text" value='<?php echo !empty($data)?htmlspecialchars($data['start_date']):''; ?>' class="form-control date_picker" id="date_start" name='date_start' required>
                                        </div>
                                    </div> 
                                    
                                    <div class="form-group">
                                        <label for="description" class="col-md-2 control-label">Description *</label>
                                        <div class="col-md-9">
                                            <input type='text' class="form-control" name='description' id='description' value='<?php echo !empty($data)?htmlspecialchars($data['description']):''; ?>' required>
                                        </div>
                                    </div>
                                    
                                    <div class='form-group'>
                                        <label for="status" class="col-sm-2 control-label">Status *</label>
                                        <div class='col-sm-9'>
                                            <select class='form-control cbo' name='status' data-placeholder="Select Status" <?php echo !(empty($data))?"data-selected='".$data['status_name']."'":NULL ?> required

                                             <?php
                                            if (empty($_GET['id'])) { 
                                                echo "disabled>";
                                                echo "<option value='1' selected>On-going</option>" ;  
                                            }  
                                            else {

                                          
                                                        echo ">". makeOptions($project_status);
                                                   
                                              
                                            }

                                                ?> 
                                            
                                               
                                            </select>
                                        </div>
                                    </div> 
                                   

                                    <div class="form-group">
                                        <div class="col-sm-9 col-md-offset-2 text-center">
                                            <button type='submit' class='btn btn-warning'>Save </button>
                                            <a href='project_management.php' class='btn btn-default' onclick="return confirm('Are you sure you want to Cancel?')">Cancel</a>
                                        </div>
                                    </div>
                                    <div class='panel-body ' >
                    <!-- <table class='table table-bordered table-condensed table-hover display select' id='ResultTable'> -->

                                        <table id="example" class="table table-bordered table-condensed table-hover display select" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th><input name="select_all" value="1" type="checkbox"></th>
                                                    <th class='text-center'>Code</th>
                                                    <th class='text-center'>Employee Name</th>
                                                    <th class='text-center'>Department</th>
                                                    <th class='text-center'>Job Title</th>
                                                    <th class='text-center'>Action</th>
                                                </tr>
                                            </thead>
                                            
                                        </table>
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

      function change(){
        var chkbox = document.getElementById("chkbox");
        if(chkbox.value=="Filter") chkbox.value="Generate";
        else chkbox.value="Filter";
      }
    
        // $(function () {
        //  data_table=$('#ResultTable').DataTable({
        //      "processing": true,
        //      "serverSide": true,
        //      "searching": false,
        //      "ajax":{
        //          "url":"ajax/shifting_sched.php",
        //          "data":function(s){
        //              s.emp_code=$("input[name='emp_code']").val();
        //              s.emp_name=$("select[name='emp_name']").val();
        //              s.department=$("select[name='dept']").val();
        //              s.job_title=$("select[name='job_title']").val();
        //          }
        //      },
        //      "oLanguage": { "sEmptyTable": "No employees found." }


        //  });
        // });



        function filter_search() 
        {
            //table.draw();
            table.ajax.reload();

        }


     

        function updateDataTableSelectAllCtrl(table){
            var $table             = table.table().node();
            var $chkbox_all        = $('tbody input[type="checkbox"]', $table);
            var $chkbox_checked    = $('tbody input[type="checkbox"]:checked', $table);
            var chkbox_select_all  = $('thead input[name="select_all"]', $table).get(0);

           // If none of the checkboxes are checked
           if($chkbox_checked.length === 0){
            chkbox_select_all.checked = false;
            if('indeterminate' in chkbox_select_all){
                chkbox_select_all.indeterminate = false;
            }

               // If all of the checkboxes are checked
            } else if ($chkbox_checked.length === $chkbox_all.length){
                chkbox_select_all.checked = true;
                if('indeterminate' in chkbox_select_all){
                    chkbox_select_all.indeterminate = false;
                }

               // If some of the checkboxes are checked
            } else {
                chkbox_select_all.checked = true;
                if('indeterminate' in chkbox_select_all){
                    chkbox_select_all.indeterminate = true;
                }
            }
        }

        $(document).ready(function (){
   // Array holding selected row IDs
   var rows_selected = [];

   var table = $('#example').DataTable({
    "ajax":{
        "url":"ajax/cjaytest.php",
        "data":function(d){
            // d.emp_code=$("input[name='emp_code']").val();
            // d.emp_name=$("select[name='emp_name']").val();
            // d.department=$("select[name='dept']").val();
            // d.job_title=$("select[name='job_title']").val();
            d.id='<?php echo !empty($_GET['id'])?intval($_GET['id']):"";?>'
        }
    },
    'columnDefs': [{
        'targets': 0,
        'searchable': false,
        'orderable': false,
        'width': '1%',
        'className': 'dt-body-center',
        'render': function (data, type, full, meta){

            if(full[6]==1){
                 if($.inArray(full[0], rows_selected) == -1){
                    rows_selected.push(full[0]);
                 }
                return '<input type="checkbox" checked>';
            } else {
                return '<input type="checkbox" >';
            }
        }
    }],
    'order': [[1, 'asc']],
    'rowCallback': function(row, data, dataIndex){
         // Get row ID
         var rowId = data[0];

         // If row ID is in the list of selected row IDs
         if($.inArray(rowId, rows_selected) !== -1){
            $(row).find('input[type="checkbox"]').prop('checked', true);
            $(row).addClass('selected');
         }
     }
 });

   // Handle click on checkbox
   $('#example tbody').on('click', 'input[type="checkbox"]', function(e){
    var $row = $(this).closest('tr');

      // Get row data
      var data = table.row($row).data();

      // Get row ID
      var rowId = data[0];

      // Determine whether row ID is in the list of selected row IDs 
      var index = $.inArray(rowId, rows_selected);

      // If checkbox is checked and row ID is not in list of selected row IDs
      if(this.checked && index === -1){
        rows_selected.push(rowId);

      // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
  } else if (!this.checked && index !== -1){
    rows_selected.splice(index, 1);
  }

  if(this.checked){
    $row.addClass('selected');
  } else {
    $row.removeClass('selected');
  }

      // Update state of "Select all" control
      updateDataTableSelectAllCtrl(table);

      // Prevent click event from propagating to parent
      e.stopPropagation();
  });

   // Handle click on table cells with checkboxes
   $('#example').on('click', 'tbody td, thead th:first-child', function(e){
    $(this).parent().find('input[type="checkbox"]').trigger('click');
   });

   // Handle click on "Select all" control
   $('thead input[name="select_all"]', table.table().container()).on('click', function(e){
    if(this.checked){
        $('#example tbody input[type="checkbox"]:not(:checked)').trigger('click');
    } else {
        $('#example tbody input[type="checkbox"]:checked').trigger('click');
    }

      // Prevent click event from propagating to parent
      e.stopPropagation();
  });

   // Handle table draw event
   table.on('draw', function(){
      // Update state of "Select all" control
      updateDataTableSelectAllCtrl(table);
  });

   // Handle form submission event 
   $('#frm-example').on('submit', function(e){
    var form = this;

      // Iterate over all selected checkboxes
      $.each(rows_selected, function(index, rowId){
         // Create a hidden element 
         $(form).append(
            $('<input>')
            .attr('type', 'hidden')
            .attr('name', 'emp_id[]')
            .val(rowId)
            );
     });
  });

});

</script>

<?php
    makeFoot();
?>