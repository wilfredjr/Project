<?php
    require_once("support/config.php");
     if(!isLoggedIn()){
        toLogin();
        die();
     }

    if(!AllowUser(array(1,4))){
         redirect("index.php");
    }

    $tab="1";
    if(!empty($_GET['tab']) && !is_numeric($_GET['tab'])){
        redirect("frm_products.php".(!empty($products)?'?id='.$products['id']:''));
        die;
    }
    else{
        if(!empty($_GET['tab'])){
            if($_GET['tab'] >0 && $_GET['tab']<=9){
                $tab=$_GET['tab'];
            }
            else{
                #invalid TAB
                redirect("frm_employee.php".(!empty($products)?'?id='.$products['id']:''));
            }
        }
    }
    
    if(!empty($_GET['id'])){
        $products=$con->myQuery("SELECT * FROM products WHERE id=?",array($_GET['id']))->fetch(PDO::FETCH_ASSOC);
        if(empty($products)){
            Modal("Invalid Record Selected");
            redirect("products.php");
            die;
        }
    }
    else{
        if($tab>"1"){
            Modal("Product information must be saved first.");
            redirect("frm_products.php");
        }

    }
    

    makeHead("Product Form");
?>

<?php
    require_once("template/header.php");
    require_once("template/sidebar.php");
?>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Product Form
          </h1>
          <br/>
          <a href='products.php' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Product list</a>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Main row -->
          <div class="row">
            <div class='col-md-12'>
              <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <?php
                        $no_employee_msg=' Product information must be saved.';
                    ?>
                    <li <?php echo $tab=="1"?'class="active"':''?>><a href="frm_product.php<?php echo !empty($products)?"?id={$products['id']}":''; ?>" >Product Details</a>
                    </li>
                    <li <?php echo empty($products)?'class="disabled"':''; ?> <?php echo $tab=="2"?'class="active"':''?> ><a href="?tab=2<?php echo !empty($products)?"&id={$products['id']}":''; ?>" <?php echo empty($products)?'onclick="alert(\''.$no_employee_msg.'\');return false;"':''; ?>>Suppliers</a>
                    </li>
                </ul>
                <div class="tab-content">
                  <div class="active tab-pane" >
                    <?php
                        switch ($tab) {
                            case '1':
                                #PERSONAL INFORMATION
                                $form='products_details.php';
                                break;
                            case '2':
                                #EDUCATION
                                $form='products_suppliers.php';
                                break;
                            default:
                                $form='products_details.php';
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
/*  $(function () {
        $('#ResultTable').DataTable({
               dom: 'Bfrtip',
                    buttons: [
                        {
                            extend:"excel",
                            text:"<span class='fa fa-download'></span> Download as Excel File "
                        }
                        ]
        });
      });
*/
</script>

<?php
    Modal();
    makeFoot();
?>