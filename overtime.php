<?php
    require_once("support/config.php");
     if(!isLoggedIn()){
        toLogin();
        die();
     }
     if (empty($_SESSION[WEBAPP]['user']['allow_overtime'])) {
        redirect('index.php');
        die;
     }
  $tab="2";
    if(!empty($_GET['tab']) && !is_numeric($_GET['tab'])){
        redirect("overtime.php");
        die;
    }
    else{
        if(!empty($_GET['tab'])){
            if($_GET['tab'] >1 && $_GET['tab']<=3){
                $tab=$_GET['tab'];
            }
            else{
                #invalid TAB
                redirect("overtime.php");
            }
        }
    }

    $ot_query=$con->myQuery("SELECT COUNT(id) FROM `employees_ot` where (employees_id=:employee_id OR requestor_id=:employee_id)  AND request_status_id = 3",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
    // $ot_pre_query=$con->myQuery("SELECT COUNT(id) FROM `employees_ot_pre` where employees_id=? AND request_status_id=3 ",array($_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();
    $ot_pre_query=0;
    $ot_adj_query=$con->myQuery("SELECT COUNT(id) FROM `employees_ot_adjustments` where (employees_id=:employee_id OR requestor_id=:employee_id) AND request_status_id = 3",array("employee_id"=>$_SESSION[WEBAPP]['user']['employee_id']))->fetchColumn();

    makeHead("Overtime Request");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Overtime Request
          </h1>
          <br/>
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
                <ul class="nav nav-tabs">
                    <!-- <li <?php echo $tab=="1"?'class="active"':''?>><a href="overtime.php" ><span>Pre-approved OT</span> &nbsp; <?php echo empty($ot_pre_query)?'':"<small class='label label-warning pull-right'>{$ot_pre_query}</small>";?> </a>
                    </li> -->
                    <li <?php echo $tab=="2"?'class="active"':''?> ><a href="?tab=2"><span>OT Claim</span> &nbsp; <?php echo empty($ot_query)?'':"<small class='label label-warning pull-right'>{$ot_query}</small>";?></a>
                    </li>
                    <li <?php echo $tab=="3"?'class="active"':''?> ><a href="?tab=3"><span>OT Adjustments</span> &nbsp; <?php echo empty($ot_adj_query)?'':"<small class='label label-warning pull-right'>{$ot_adj_query}</small>";?></a>
                    </li>
                </ul>
                <div class="tab-content">
                  <div class="active tab-pane" >
                    <?php
                        switch ($tab) {
                            case '1':
                                #OT Pre-Approval
                                $form='ot_pre_approval.php';
                                break;
                            case '2':
                                #OT CLAIM
                                $form='overtime_request.php';
                                break;
                            case '3':
                                #OT ADJUSTMENT
                                $form='overtime_adjustment_requests.php';
                                break;
                            default:
                                $form='overtime_request.php';
                                break;
                        }
                        require_once($form);
                    ?>
                  </div><!-- /.tab-pane -->
                </div><!-- /.tab-content -->
              </div><!-- /.nav-tabs-custom -->
            </div>
          </div><!-- /.row -->
        </section><!-- /.content -->
  </div>

<script type="text/javascript">
//   $(function () {
//         $('#ResultTable').DataTable({
//                dom: 'Bfrtip'
//  //                   buttons: [
// //                        {
// //                            extend:"excel",
// //                            text:"<span class='fa fa-download'></span> Download as Excel File "
// //                        }
// //                        ]
//         });
//      });
</script>

<?php
    Modal();
    makeFoot();
?>