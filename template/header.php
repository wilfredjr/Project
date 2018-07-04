<header class="main-header" >

        <!-- Logo -->
        <a href="index.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini" ><img src='dist/img/sgtsi favico.png' /></span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg" ><b>SGTSI</b> PMS</span>
        </a>

        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <!-- Navbar Right Menu -->
          <div class="navbar-custom-menu" >
            <ul class="nav navbar-nav" >
            <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu" >

                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <?php    
                        $today = date("F j, Y l g:i a");
                        //echo $today;
                    ?>
                  <span class="hidden-xs">
                    <?php
                        echo htmlspecialchars("{$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['middle_name']}")
                      ?>
                  </span>
                    &nbsp;&nbsp;
                  <?php
                    if(empty($_SESSION[WEBAPP]['user']['image'])){
                        if($_SESSION[WEBAPP]['user']['gender']=='Male'){
                          $image="dist/img/avatar5.png";
                        }
                        else{
                          $image="dist/img/avatar2.png";
                        }
                    }
                    else{
                      $image="employee_images/".$_SESSION[WEBAPP]['user']['image'];
                    }
                  ?>
                  <img src="<?php echo $image;?>" class="user-image pull-left" alt="User Image">
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="<?php echo $image;?>" class="img-circle" alt="User Image">
                    <p>
                      <?php
                        echo htmlspecialchars("{$_SESSION[WEBAPP]['user']['last_name']}, {$_SESSION[WEBAPP]['user']['first_name']} {$_SESSION[WEBAPP]['user']['middle_name']}")
                      ?>
                    </p>
                  </li>
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <!-- <div class="pull-left">
                      <a href="user_profile.php" class="btn btn-default btn-flat">Profile</a>
                    </div> -->
                    <div class="text-center">
                      <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>

        </nav>
      </header>
