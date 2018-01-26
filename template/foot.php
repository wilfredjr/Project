<?php
if($pageTitle!="Login"):
?>
</div><!-- ./wrapper -->
<?php
endif;
?>
    
    <script src="<?php echo str_repeat('../',$level) ?>plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="<?php echo str_repeat('../',$level) ?>plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>plugins/datatables/extensions/RowReorder/js/dataTables.rowReorder.min.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>plugins/datatables/processing.js"></script>

    <script type="text/javascript" src="<?php echo str_repeat('../',$level) ?>plugins/datatables/media/js/jszip.min.js"></script>
    <script type="text/javascript" src="<?php echo str_repeat('../',$level) ?>plugins/datatables/media/js/pdfmake.min.js"></script>
    <script type="text/javascript" src="<?php echo str_repeat('../',$level) ?>plugins/datatables/media/js/vfs_fonts.js"></script>
    <script type="text/javascript" src="<?php echo str_repeat('../',$level) ?>plugins/datatables/media/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="<?php echo str_repeat('../',$level) ?>plugins/datatables/media/js/buttons.bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo str_repeat('../',$level) ?>plugins/datatables/media/js/buttons.flash.min.js"></script>
    <script type="text/javascript" src="<?php echo str_repeat('../',$level) ?>plugins/datatables/media/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="<?php echo str_repeat('../',$level) ?>plugins/datatables/media/js/buttons.print.min.js"></script>

    <!-- FastClick -->
    <script src="<?php echo str_repeat('../',$level) ?>plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo str_repeat('../',$level) ?>dist/js/app.min.js"></script>
    <!-- Sparkline -->
    <script src="<?php echo str_repeat('../',$level) ?>plugins/sparkline/jquery.sparkline.min.js"></script>
    <!-- jvectormap -->
    <script src="<?php echo str_repeat('../',$level) ?>plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <!-- SlimScroll 1.3.0 -->
    <script src="<?php echo str_repeat('../',$level) ?>plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <!-- ChartJS 1.0.1 -->
    <script src="<?php echo str_repeat('../',$level) ?>plugins/chartjs/Chart.min.js"></script>
    <!-- Select2 -->
    <script src="<?php echo str_repeat('../',$level) ?>plugins/select2/select2.full.min.js"></script>
    <!-- InputMask -->
    <script src="<?php echo str_repeat('../',$level) ?>plugins/input-mask/jquery.inputmask.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>plugins/input-mask/jquery.inputmask.extensions.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>plugins/input-mask/jquery.inputmask.numeric.extensions.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>plugins/input-mask/jquery.inputmask.regex.extensions.js"></script>
    <!-- date-range-picker -->
    <script src="<?php echo str_repeat('../',$level) ?>dist/js/moment.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>dist/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>dist/js/bootstrap-datetimepicker.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>plugins/daterangepicker/daterangepicker.js"></script>
    <!-- bootstrap time picker -->
    <script src="<?php echo str_repeat('../',$level) ?>plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>dist/js/bootstrap-filestyle.js"></script>
    <script src="<?php echo str_repeat('../',$level) ?>plugins/fullcalendar/fullcalendar.js"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <!-- <script src="<?php echo str_repeat('../',$level) ?>dist/js/pages/dashboard2.js"></script> -->
    <!-- AdminLTE for demo purposes -->
    <!-- <script src="<?php echo str_repeat('../',$level) ?>dist/js/demo.js"></script> -->
    <script type="text/javascript">
        function form_clear(frm_id) {
            $("#"+frm_id+" select").each(function(){
            $(this).val('').trigger('change');
            });
            $("#"+frm_id+" input").each(function(){
                $(this).val('').trigger('change');
            });
        }
        $(function(){

            $('.numeric').inputmask('Regex', { 
            regex: "^[0-9]+"
        });
            
    	 $('.cbo').select2({
        placeholder:$(this).data("placeholder"),
            allowClear:$(this).data("allow-clear")
		  });

		$('.cbo').each(function(index,element){
		    if(typeof $(element).data("selected") !== "undefined"){
		    $(element).val($(element).data("selected")).trigger("change");
		    }
        });
        //zip code format
        $(".zip").inputmask("9999", {"placeholder": "####"});
        //tin number format
        $(".tin").inputmask("999-999-999-999", {"placeholder": "###-###-###-###"});
        //sss number format
        $(".sss").inputmask("99-9999999-99", {"placeholder": "##-#######-##"});
        //philhealth number format
        $(".philhealth").inputmask("99-999999999-9", {"placeholder": "##-#########-#"});
        //pagibig number format
        $(".pagibig").inputmask("9999-9999-9999", {"placeholder": "####-####-####"});
		  // });
        $('.date_picker').datepicker();  
        $(".date_picker").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
        //Time picker
        $('.time_picker').timepicker({"showMeridian":true});  

    	//Date range picker
        $('.date_range').daterangepicker();
        //Date range picker with time picker
        
        $('.date_time_range').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'MM/DD/YYYY h:mm A'});
    	});

        $('.date_time_picker').datetimepicker();
        $('.date_time_picker').each(function(index,element){
            if(typeof $(element).data("default") !== "undefined"){
            //$(element).val($(element).data("default")).trigger("change");
            $(element).data("DateTimePicker").defaultDate(new Date($(element).data("default")));

            }

          });

        function query(id){
            $('#modal_comments').modal('show');
            $("#comment_table").html("<span class='fa fa-refresh fa-pulse'></span>")
            $("#comment_table").load("ajax/comments.php?id="+id+"&request_type=<?php echo !empty($request_type)?htmlspecialchars($request_type):"" ?>");

            $("#request_id").val(id);
        }
        function submit(id){
             $('#modal_submit').modal('show');
            $('#task_id').val(id);
        }
        function query_logs(id) {
            $('#modal_comments_logs').modal('show');

            dttable=$('#comment_table_logs').DataTable({
                "destroy": true,
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "searching": false,
                "lengthChange": false,
                "ajax":
                {
                    "url":"ajax/comment_logs.php",
                    "data":function(d)
                    {
                        d.request_id=id;
                        d.request_type="<?php echo !empty($request_type)?htmlspecialchars($request_type):"" ?>";
                    }
                },
                "order": [[ 2, "desc" ]],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend:"excel",
                        text:"<span class='fa fa-download'></span> Download as Excel File "
                    }
                ]
            });
            $('#modal_comments_logs').on('hidden.bs.modal', function (e) {
              // dttable.destroy();
            })
        }
        function reject(id){
            $('#modal_reject').modal('show');
            $('#reject_id').val(id);
        }

        function show_image_modal(filename){
            $("#img_modal").attr("src","ob_evidence/"+filename);
            $("#img_download").attr("href","ob_evidence/"+filename);
            $("#PicModal").modal("show");
        }
        // $('#modal_comments').on('show.bs.modal', function (e) {
        //   $("#comment_table").load("ajax/comments.php");
        // })
        
        $(".cbo-paygroup-id").select2({
                placeholder:"Select Pay Group",
                ajax: {
                    url: "./ajax/cbo_paygroups.php",
                    dataType: "json",
                    type: "GET",
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
                },
                allowClear:$(this).data("allow-clear")
        }); 

        $('.cbo-paygroup-id').each(function(index,element){
            if(typeof $(element).data("selected") !== "undefined"){
            $(element).val($(element).data("selected")).trigger("change");
            }
        });

        $(".cbo-employee-id").select2({
                placeholder:"Select Employee",
                ajax: {
                    url: "./ajax/cbo_employees.php",
                    dataType: "json",
                    type: "GET",
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
                },
                allowClear:$(this).data("allow-clear")
        }); 

        $('.cbo-employee-id').each(function(index,element){
            if(typeof $(element).data("selected") !== "undefined"){
            $(element).val($(element).data("selected")).trigger("change");
            }
        });

        $(".cbo-subordinate-id").select2({
                placeholder:"Select Subordinate",
                ajax: {
                    url: "./ajax/cbo_subordinates.php",
                    dataType: "json",
                    type: "GET",
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
                },
                allowClear:$(this).data("allow-clear")
        }); 

        $('.cbo-subordinate-id').each(function(index,element){
            if(typeof $(element).data("selected") !== "undefined"){
            $(element).val($(element).data("selected")).trigger("change");
            }
        });

        $(".cbo-project-id").select2({
                placeholder:"Select Project",
                ajax: {
                    url: "./ajax/cbo_projects.php",
                    dataType: "json",
                    type: "GET",
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
                },
                allowClear:$(this).data("allow-clear")
        }); 

        $('.cbo-project-id').each(function(index,element){
            if(typeof $(element).data("selected") !== "undefined"){
            $(element).val($(element).data("selected")).trigger("change");
            }
        });

        $(".cbo-all-project-id").select2({
                placeholder:"Select Projects",
                ajax: {
                    url: "./ajax/cbo_all_projects.php",
                    dataType: "json",
                    type: "GET",
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
                },
                allowClear:$(this).data("allow-clear")
        }); 

        $('.cbo-all-project-id').each(function(index,element){
            if(typeof $(element).data("selected") !== "undefined"){
            $(element).val($(element).data("selected")).trigger("change");
            }
        });
        $(".cbo-department-id").select2({
                placeholder:"Select Department",
                ajax: {
                    url: "./ajax/cbo_departments.php",
                    dataType: "json",
                    type: "GET",
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
                },
                allowClear:$(this).data("allow-clear")
        }); 

        $('.cbo-department-id').each(function(index,element){
            if(typeof $(element).data("selected") !== "undefined"){
            $(element).val($(element).data("selected")).trigger("change");
            }
        });

        $(".cbo-request-status-id").select2({
                placeholder:"Select Department",
                ajax: {
                    url: "./ajax/cbo_request_status.php",
                    dataType: "json",
                    type: "GET",
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
                },
                allowClear:$(this).data("allow-clear")
        }); 

        $('.cbo-request-status-id').each(function(index,element){
            if(typeof $(element).data("selected") !== "undefined"){
            $(element).val($(element).data("selected")).trigger("change");
            }
        });        

        $(".cbo-user-type-id").select2({
                placeholder:"Select User Type",
                ajax: {
                    url: "./ajax/cbo_user_types.php",
                    dataType: "json",
                    type: "GET",
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
                },
                allowClear:$(this).data("allow-clear")
        }); 

        $('.cbo-user-type-id').each(function(index,element){
            if(typeof $(element).data("selected") !== "undefined"){
            $(element).val($(element).data("selected")).trigger("change");
            }
        });
        $(".disable-submit").submit(function () {
            $(this).closest('form').find(':submit').button("loading");
        });
        function validate_times(start_time, end_time) {
            x = moment("2017-01-01 "+start_time, "YYYY-MM-DD hh:mm a");
            y = moment("2017-01-01 "+end_time, "YYYY-MM-DD hh:mm a");
            if (start_time==end_time) {
                return false;
            }
            else if (x.isSameOrAfter(y)) {
                return false;
            } else {
                return true;
            }
        }
    // });
     function query(id){
            $('#modal_comments').modal('show');
            $("#comment_table").html("<span class='fa fa-refresh fa-pulse'></span>")
            $("#comment_table").load("ajax/comments.php?id="+id+"&request_type=<?php echo htmlspecialchars($request_type) ?>");

            $("#request_id").val(id);
        }
        function reject(id){
            $('#modal_reject').modal('show');
            $('#reject_id').val(id);
        }
       

        function show_image_modal(filename){
            $("#img_modal").attr("src","ob_evidence/"+filename);
            $("#img_download").attr("href","ob_evidence/"+filename);
            $("#PicModal").modal("show");
        }

        function show_image_modal_allowance(filename){
            $("#img_modal").attr("src","allowance_evidence/"+filename);
            $("#img_download").attr("href","allowance_evidence/"+filename);
            $("#PicModal").modal("show");
        }
    </script>
  </body>
</html>