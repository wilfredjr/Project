<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $pageTitle; ?></title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>font-awesome-4.5.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>ionicons-2.0.1/css/ionicons.min.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/fullcalendar/fullcalendar.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>dist/css/AdminLTE6.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <?php
        if(substr(getcwd(), strrpos(getcwd(),"\\")+1)=="payroll"):
            #yellow
    ?>
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>dist/css/skins/skin-red-light.min.css">
    <?php
        else:
        #payroll
    ?>
    <!-- <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>dist/css/skins/skin-yellow-light.min.css"> -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>dist/css/skins/secret-6.css">
    <?php
        endif;
    ?>
        <!-- Select2 -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/timeline2/css/style.css">
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>dist/css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>dist/css/datepick-bootstrap.css">
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/datatables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/datatables/extensions/RowReorder/css/rowReorder.dataTables.min.css">
        <!-- daterange picker -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/daterangepicker/daterangepicker-bs3.css">
    <!-- iCheck for checkboxes and radio inputs -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/iCheck/all.css">
    <!-- Bootstrap Color Picker -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/colorpicker/bootstrap-colorpicker.min.css">
    <!-- Bootstrap time Picker -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/timepicker/bootstrap-timepicker.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?php echo str_repeat('../',$level) ?>plugins/select2/select2.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
        table{
            width: 100%!important;
        }
    </style>
  </head>
<!-- jQuery 2.1.4 -->
    <script src="<?php echo str_repeat('../',$level) ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="<?php echo str_repeat('../',$level) ?>bootstrap/js/bootstrap.min.js"></script>
<?php
if($pageTitle=="Login"):
?>
<body class="hold-transition login-page">
<?php
else:
?>
    <?php
        if(substr(getcwd(), strrpos(getcwd(),"\\")+1)=="payroll"):
            #yellow
    ?>
    <body class="hold-transition skin-red-light fixed sidebar-mini"
    <?php
        else:
        #payroll
    ?>
    <body class="hold-transition skin-yellow-light fixed sidebar-mini">
    <?php
        endif;
    ?>
    <div class="wrapper">
<?php
endif;
?>